/**
 * Live Search — GitHub Theme
 * Real-time search using the WordPress REST API.
 * Inspired by npmx.dev search UX.
 *
 * @package GitHubTheme
 */
(function () {
  "use strict";

  // --- Config ---
  const DEBOUNCE_MS = 300;
  const MIN_CHARS = 1;
  const MAX_RESULTS = 8;

  // Normalizar la URL base: quitar barra final si existe
  var rawBase = (window.liveSearchData && window.liveSearchData.restUrl) || '/wp-json/wp/v2';
  const API_BASE = rawBase.replace(/\/$/, '');

  // --- State ---
  let abortController = null;
  let debounceTimer = null;
  let selectedIndex = -1;
  let currentResults = [];
  let lastOpenTime = 0; // Timestamp de seguridad para evitar cierre prematuro

  // --- DOM refs (set on init) ---
  let searchInput, overlay, resultsContainer, searchForm;

  // =========================================================================
  // BUILD OVERLAY
  // =========================================================================
  function createOverlay() {
    overlay = document.createElement("div");
    overlay.className = "live-search-overlay";

    var modal = document.createElement("div");
    modal.className = "live-search-modal";

    var header = document.createElement("div");
    header.className = "live-search-header";

    var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("class", "live-search-icon");
    svg.setAttribute("viewBox", "0 0 16 16");
    svg.setAttribute("width", "20");
    svg.setAttribute("height", "20");
    svg.setAttribute("fill", "currentColor");
    var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("fill-rule", "evenodd");
    path.setAttribute("d", "M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z");
    svg.appendChild(path);
    header.appendChild(svg);

    var input = document.createElement("input");
    input.type = "search";
    input.className = "live-search-input";
    input.placeholder = "Buscar artículos…";
    input.setAttribute("aria-label", "Buscar artículos en tiempo real");
    input.autocomplete = "off";
    input.spellcheck = false;
    header.appendChild(input);

    var kbdEsc = document.createElement("kbd");
    kbdEsc.className = "live-search-kbd";
    kbdEsc.textContent = "ESC";
    header.appendChild(kbdEsc);

    modal.appendChild(header);

    var resultsContainerDiv = document.createElement("div");
    resultsContainerDiv.className = "live-search-results";
    modal.appendChild(resultsContainerDiv);

    var footer = document.createElement("div");
    footer.className = "live-search-footer";

    var span1 = document.createElement("span");
    var kbdUp = document.createElement("kbd"); kbdUp.textContent = "↑";
    var kbdDown = document.createElement("kbd"); kbdDown.textContent = "↓";
    span1.appendChild(kbdUp); span1.appendChild(kbdDown); span1.appendChild(document.createTextNode(" navegar"));
    
    var span2 = document.createElement("span");
    var kbdEnter = document.createElement("kbd"); kbdEnter.textContent = "↵";
    span2.appendChild(kbdEnter); span2.appendChild(document.createTextNode(" abrir"));

    var span3 = document.createElement("span");
    var kbdEscFooter = document.createElement("kbd"); kbdEscFooter.textContent = "esc";
    span3.appendChild(kbdEscFooter); span3.appendChild(document.createTextNode(" cerrar"));

    footer.appendChild(span1);
    footer.appendChild(span2);
    footer.appendChild(span3);

    modal.appendChild(footer);
    overlay.appendChild(modal);

    document.body.appendChild(overlay);

    resultsContainer = overlay.querySelector(".live-search-results");
    var modalInput = overlay.querySelector(".live-search-input");

    // Eventos del input
    modalInput.addEventListener("input", function () {
      handleInput(this.value);
    });
    
    modalInput.addEventListener("keyup", function (e) {
      // Ignorar teclas de navegación para no disparar búsquedas innecesarias
      var skip = ['ArrowUp','ArrowDown','ArrowLeft','ArrowRight','Enter','Escape','Tab','Shift','Control','Alt','Meta'];
      if (skip.indexOf(e.key) === -1) {
        handleInput(this.value);
      }
    });

    modalInput.addEventListener("keydown", handleKeydown);

    return modalInput;
  }

  // =========================================================================
  // OPEN / CLOSE
  // =========================================================================
  function openOverlay() {
    lastOpenTime = Date.now();
    if (!overlay) {
      searchInput = createOverlay();
    } else {
      searchInput = overlay.querySelector(".live-search-input");
    }
    
    overlay.classList.add("active");
    document.body.style.overflow = "hidden";
    
    searchInput.value = "";
    setHTML(resultsContainer, renderEmptyState());
    selectedIndex = -1;
    currentResults = [];

    // Múltiples intentos de foco para asegurar la usabilidad
    setTimeout(function () { searchInput.focus(); }, 10);
    setTimeout(function () { searchInput.focus(); }, 150);
  }

  function closeOverlay() {
    var el = overlay || document.querySelector(".live-search-overlay");
    if (!el) return;
    
    el.classList.remove("active");
    document.body.style.overflow = "";
    
    if (abortController) {
      abortController.abort();
      abortController = null;
    }
    
    // No devolvemos el foco automáticamente al trigger para evitar conflictos
    // con eventos 'focus' que podrían reabrir el modal accidentalmente.
  }

  // =========================================================================
  // SEARCH LOGIC
  // =========================================================================
  function handleInput(value) {
    var query = value.trim();

    if (debounceTimer) clearTimeout(debounceTimer);
    if (abortController) {
      abortController.abort();
      abortController = null;
    }

    if (query.length < MIN_CHARS) {
      setHTML(resultsContainer, renderEmptyState());
      selectedIndex = -1;
      currentResults = [];
      return;
    }

    setHTML(resultsContainer, renderLoading());

    debounceTimer = setTimeout(function () {
      performSearch(query);
    }, DEBOUNCE_MS);
  }

  function performSearch(query) {
    abortController = new AbortController();

    // 1. Intento: Endpoint personalizado (optimizado)
    var customBase = API_BASE.replace('/wp/v2', '');
    var params = new URLSearchParams();
    params.set('q', query);
    params.set('per_page', MAX_RESULTS);

    var url = customBase + '/github-theme/v1/search?' + params.toString();

    fetch(url, { signal: abortController.signal })
      .then(function (response) {
        if (!response.ok) throw new Error(response.status);
        return response.json();
      })
      .then(function (posts) {
        currentResults = posts;
        selectedIndex = -1;
        if (!posts.length) {
          setHTML(resultsContainer, renderNoResults(query));
        } else {
          setHTML(resultsContainer, renderResults(posts, query));
        }
      })
      .catch(function (err) {
        if (err.name === 'AbortError') return;
        // 2. Fallback: Endpoint estándar filtrado en cliente
        performSearchFallback(query);
      });
  }

  function performSearchFallback(query) {
    abortController = new AbortController();
    var queryLower = query.toLowerCase();
    var params = new URLSearchParams();
    params.set('search', query);
    params.set('per_page', '50'); // Traer más para filtrar localmente
    params.set('orderby', 'date');
    params.set('order', 'desc');
    params.set('_fields', 'id,title,excerpt,date,link,_embedded');
    params.set('_embed', 'wp:featuredmedia,wp:term');

    var url = API_BASE + '/posts?' + params.toString();
    fetch(url, { signal: abortController.signal })
      .then(function(r) { return r.json(); })
      .then(function(posts) {
        var filtered = posts
          .filter(function(p) {
            return (p.title.rendered || '').toLowerCase().indexOf(queryLower) !== -1;
          })
          .slice(0, MAX_RESULTS)
          .map(function(p) {
            var cats = p._embedded?.['wp:term']?.[0]?.map(function(t){ return t.name; }).join(', ') || '';
            return {
              id: p.id,
              title: p.title.rendered,
              excerpt: stripHTML(p.excerpt.rendered || '').substring(0, 120) + '…',
              date: p.date,
              link: p.link,
              categories: cats,
            };
          });
        currentResults = filtered;
        selectedIndex = -1;
        setHTML(resultsContainer, filtered.length ? renderResults(filtered, query) : renderNoResults(query));
      })
      .catch(function(err) {
        if (err.name === 'AbortError') return;
        setHTML(resultsContainer, renderError());
      });
  }


  // =========================================================================
  // KEYBOARD NAVIGATION
  // =========================================================================
  function handleKeydown(e) {
    var items = resultsContainer.querySelectorAll(".live-search-item");
    if (!items.length) {
      if (e.key === "Escape") closeOverlay();
      return;
    }

    switch (e.key) {
      case "ArrowDown":
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % items.length;
        updateSelection(items);
        break;
      case "ArrowUp":
        e.preventDefault();
        selectedIndex =
          selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
        updateSelection(items);
        break;
      case "Enter":
        e.preventDefault();
        if (selectedIndex >= 0 && items[selectedIndex]) {
          var link = items[selectedIndex].querySelector("a");
          if (link) window.location.href = link.href;
        } else if (searchInput.value.trim().length >= MIN_CHARS) {
          window.location.href =
            ((window.liveSearchData && window.liveSearchData.homeUrl) || "/") +
            "?s=" +
            encodeURIComponent(searchInput.value.trim());
        }
        break;
      case "Escape":
        e.preventDefault();
        closeOverlay();
        break;
    }
  }

  function updateSelection(items) {
    items.forEach(function (item, i) {
      item.classList.toggle("selected", i === selectedIndex);
    });
    if (items[selectedIndex]) {
      items[selectedIndex].scrollIntoView({ block: "nearest" });
    }
  }

  // =========================================================================
  // RENDERERS
  // =========================================================================
  function renderEmptyState() {
    var div = document.createElement('div');
    div.className = 'live-search-empty';
    
    var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", "0 0 16 16");
    svg.setAttribute("width", "32");
    svg.setAttribute("height", "32");
    svg.setAttribute("fill", "currentColor");
    svg.setAttribute("style", "opacity:0.3");
    var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("fill-rule", "evenodd");
    path.setAttribute("d", "M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z");
    svg.appendChild(path);
    
    var p = document.createElement('p');
    p.textContent = "Escribe para buscar artículos";
    
    div.appendChild(svg);
    div.appendChild(p);
    return div;
  }

  function renderLoading() {
    var div = document.createElement('div');
    div.className = 'live-search-loading';
    var spinner = document.createElement('div');
    spinner.className = 'live-search-spinner';
    var p = document.createElement('p');
    p.textContent = "Buscando…";
    div.appendChild(spinner);
    div.appendChild(p);
    return div;
  }

  function renderNoResults(query) {
    var div = document.createElement('div');
    div.className = 'live-search-empty';
    var p = document.createElement('p');
    p.appendChild(document.createTextNode("No se encontraron resultados para "));
    var strong = document.createElement('strong');
    strong.textContent = '"' + query + '"';
    p.appendChild(strong);
    div.appendChild(p);
    return div;
  }

  function renderError() {
    var div = document.createElement('div');
    div.className = 'live-search-empty';
    var p = document.createElement('p');
    p.textContent = "Error al buscar. Inténtalo de nuevo.";
    div.appendChild(p);
    return div;
  }

  function renderResults(posts, query) {
    var frag = document.createDocumentFragment();
    posts.forEach(function (post) {
      var title   = (typeof post.title === 'string') ? post.title : (post.title && post.title.rendered) || 'Sin título';
      var excerpt = (typeof post.excerpt === 'string') ? post.excerpt : stripHTML((post.excerpt && post.excerpt.rendered) || '');
      if (excerpt.length > 120) excerpt = excerpt.substring(0, 120) + '…';
      var date       = formatDate(post.date);
      var categories = (typeof post.categories === 'string') ? post.categories : getCategories(post);

      var div = document.createElement('div');
      div.className = 'live-search-item';
      div.setAttribute('data-url', post.link);

      var a = document.createElement('a');
      a.href = post.link;
      a.className = 'live-search-item-link';

      var content = document.createElement('div');
      content.className = 'live-search-item-content';

      var titleDiv = document.createElement('div');
      titleDiv.className = 'live-search-item-title';
      titleDiv.appendChild(highlightMatch(title, query));

      var excerptDiv = document.createElement('div');
      excerptDiv.className = 'live-search-item-excerpt';
      excerptDiv.appendChild(highlightMatch(excerpt, query));

      var metaDiv = document.createElement('div');
      metaDiv.className = 'live-search-item-meta';

      var dateSpan = document.createElement('span');
      dateSpan.className = 'live-search-item-date';
      dateSpan.textContent = date;
      metaDiv.appendChild(dateSpan);

      if (categories) {
        var catSpan = document.createElement('span');
        catSpan.className = 'live-search-item-cat';
        catSpan.textContent = categories;
        metaDiv.appendChild(catSpan);
      }

      content.appendChild(titleDiv);
      content.appendChild(excerptDiv);
      content.appendChild(metaDiv);

      a.appendChild(content);

      var arrowSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
      arrowSvg.setAttribute("class", "live-search-item-arrow");
      arrowSvg.setAttribute("viewBox", "0 0 16 16");
      arrowSvg.setAttribute("width", "16");
      arrowSvg.setAttribute("height", "16");
      arrowSvg.setAttribute("fill", "currentColor");
      var arrowPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
      arrowPath.setAttribute("fill-rule", "evenodd");
      arrowPath.setAttribute("d", "M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z");
      arrowSvg.appendChild(arrowPath);

      a.appendChild(arrowSvg);
      div.appendChild(a);
      frag.appendChild(div);
    });
    return frag;
  }

  // =========================================================================
  // HELPERS
  // =========================================================================
  function stripHTML(html) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    return doc.body.textContent || "";
  }

  function setHTML(node, content) {
    while (node.firstChild) {
      node.removeChild(node.firstChild);
    }
    if (content) {
      node.appendChild(content);
    }
  }

  function highlightMatch(text, query) {
    if (!query) return document.createTextNode(text);
    var regex = new RegExp(
      "(" + query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + ")",
      "gi",
    );
    var frag = document.createDocumentFragment();
    var parts = text.split(regex);
    for (var i = 0; i < parts.length; i++) {
      if (i % 2 === 1) { // Coincidencia
        var mark = document.createElement('mark');
        mark.textContent = parts[i];
        frag.appendChild(mark);
      } else if (parts[i]) {
        frag.appendChild(document.createTextNode(parts[i]));
      }
    }
    return frag;
  }

  function formatDate(dateStr) {
    var d = new Date(dateStr);
    var months = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return d.getDate() + " " + months[d.getMonth()] + " " + d.getFullYear();
  }


  function getCategories(post) {
    return post._embedded?.["wp:term"]?.[0]?.map(function (t) { return t.name; }).join(", ") || "";
  }

  // =========================================================================
  // INIT
  // =========================================================================
  function init() {
    searchForm = document.querySelector(".AppHeader-search");
    var headerInput = document.querySelector(".AppHeader-search-input");

    if (searchForm) {
      searchForm.addEventListener("submit", function (e) {
        e.preventDefault();
        openOverlay();
      });
    }

    if (headerInput) {
      headerInput.addEventListener("focus", function (e) {
        e.preventDefault();
        this.blur();
        openOverlay();
      });

      headerInput.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation(); // Evitar propagación para no confundir con clic outside
        openOverlay();
      });
    }

    // --- GLOBAL EVENT LISTENERS (CAPTURE PHASE) ---
    
    // Click global para cerrar
    window.addEventListener("click", function(e) {
        var activeOverlay = document.querySelector(".live-search-overlay.active");
        if (!activeOverlay) return;

        // TIEMPO DE SEGURIDAD: Evitar que el clic de apertura se interprete como clic de cierre
        if (Date.now() - lastOpenTime < 500) {
             return; 
        }

        // Ignorar clics dentro del modal o en los elementos disparadores
        if (e.target.closest(".AppHeader-search") || e.target.closest(".AppHeader-search-input")) {
            return;
        }

        if (!e.target.closest(".live-search-modal")) {
            e.preventDefault();
            e.stopPropagation();
            closeOverlay();
        }
    }, true);

    // Atajos de teclado (Esc, Ctrl+K, /)
    window.addEventListener("keydown", function (e) {
      // 1. ESCAPE
      if (e.key === "Escape") {
        var el = document.querySelector(".live-search-overlay.active");
        if (el && (Date.now() - lastOpenTime > 200)) { // Pequeño delay también aquí
          e.preventDefault();
          e.stopPropagation();
          closeOverlay();
          return;
        }
      }

      // 2. Ctrl+K / Meta+K
      if ((e.ctrlKey || e.metaKey) && e.key === "k") {
        e.preventDefault();
        var el = document.querySelector(".live-search-overlay.active");
        if (el) {
            closeOverlay();
        } else {
            openOverlay();
        }
        return;
      }

      // 3. Tecla "/"
      if (e.key === "/" && !isTyping(e.target)) {
        if (!document.querySelector(".live-search-overlay.active")) {
          e.preventDefault();
          openOverlay();
        }
      }
    }, true);
  }

  function isTyping(el) {
    var tag = el.tagName;
    return (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || el.isContentEditable);
  }

  // Start when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
