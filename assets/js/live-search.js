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
    overlay.innerHTML = `
            <div class="live-search-modal">
                <div class="live-search-header">
                    <svg class="live-search-icon" viewBox="0 0 16 16" width="20" height="20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z"></path>
                    </svg>
                    <input
                        type="search"
                        class="live-search-input"
                        placeholder="Buscar artículos…"
                        autocomplete="off"
                        spellcheck="false"
                    />
                    <kbd class="live-search-kbd">ESC</kbd>
                </div>
                <div class="live-search-results"></div>
                <div class="live-search-footer">
                    <span><kbd>↑</kbd><kbd>↓</kbd> navegar</span>
                    <span><kbd>↵</kbd> abrir</span>
                    <span><kbd>esc</kbd> cerrar</span>
                </div>
            </div>
        `;
    document.body.appendChild(overlay);

    resultsContainer = overlay.querySelector(".live-search-results");
    const modalInput = overlay.querySelector(".live-search-input");

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
    resultsContainer.innerHTML = renderEmptyState();
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
      resultsContainer.innerHTML = renderEmptyState();
      selectedIndex = -1;
      currentResults = [];
      return;
    }

    resultsContainer.innerHTML = renderLoading();

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
          resultsContainer.innerHTML = renderNoResults(query);
        } else {
          resultsContainer.innerHTML = renderResults(posts, query);
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
            var thumb = '';
            try { thumb = p._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail.source_url; } catch(e) {}
            var cats = '';
            try { cats = p._embedded['wp:term'][0].map(function(t){ return t.name; }).join(', '); } catch(e) {}
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
        resultsContainer.innerHTML = filtered.length ? renderResults(filtered, query) : renderNoResults(query);
      })
      .catch(function(err) {
        if (err.name === 'AbortError') return;
        resultsContainer.innerHTML = renderError();
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
    return (
      '<div class="live-search-empty">' +
      '<svg viewBox="0 0 16 16" width="32" height="32" fill="currentColor" style="opacity:0.3">' +
      '<path fill-rule="evenodd" d="M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z"></path>' +
      "</svg>" +
      "<p>Escribe para buscar artículos</p>" +
      "</div>"
    );
  }

  function renderLoading() {
    return (
      '<div class="live-search-loading">' +
      '<div class="live-search-spinner"></div>' +
      "<p>Buscando…</p>" +
      "</div>"
    );
  }

  function renderNoResults(query) {
    return (
      '<div class="live-search-empty">' +
      '<p>No se encontraron resultados para <strong>"' +
      escapeHTML(query) +
      '"</strong></p>' +
      "</div>"
    );
  }

  function renderError() {
    return (
      '<div class="live-search-empty">' +
      "<p>Error al buscar. Inténtalo de nuevo.</p>" +
      "</div>"
    );
  }

  function renderResults(posts, query) {
    var html = "";
    posts.forEach(function (post) {
      // Detección automática de formato (endpoint custom vs estándar)
      var title   = (typeof post.title === 'string') ? post.title : (post.title && post.title.rendered) || 'Sin título';
      var excerpt = (typeof post.excerpt === 'string') ? post.excerpt : stripHTML((post.excerpt && post.excerpt.rendered) || '');
      if (excerpt.length > 120) excerpt = excerpt.substring(0, 120) + '…';
      var date       = formatDate(post.date);
      var categories = (typeof post.categories === 'string') ? post.categories : getCategories(post);

      // Resaltado de texto
      title   = highlightMatch(title, query);
      excerpt = highlightMatch(excerpt, query);

      html +=
        '<div class="live-search-item" data-url="' + escapeAttr(post.link) + '">' +
        '<a href="' + escapeAttr(post.link) + '" class="live-search-item-link">' +
        '<div class="live-search-item-content">' +
        '<div class="live-search-item-title">' + title + "</div>" +
        '<div class="live-search-item-excerpt">' + excerpt + "</div>" +
        '<div class="live-search-item-meta">' +
        '<span class="live-search-item-date">' + escapeHTML(date) + "</span>" +
        (categories ? '<span class="live-search-item-cat">' + escapeHTML(categories) + "</span>" : "") +
        "</div>" +
        "</div>" +
        '<svg class="live-search-item-arrow" viewBox="0 0 16 16" width="16" height="16" fill="currentColor">' +
        '<path fill-rule="evenodd" d="M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z"></path>' +
        "</svg>" +
        "</a>" +
        "</div>";
    });
    return html;
  }

  // =========================================================================
  // HELPERS
  // =========================================================================
  function stripHTML(html) {
    var tmp = document.createElement("div");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
  }

  function escapeHTML(str) {
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  function escapeAttr(str) {
    return str
      .replace(/&/g, "&amp;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  function highlightMatch(text, query) {
    if (!query) return text;
    var regex = new RegExp(
      "(" + query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + ")",
      "gi",
    );
    return text.replace(regex, "<mark>$1</mark>");
  }

  function formatDate(dateStr) {
    var d = new Date(dateStr);
    var months = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return d.getDate() + " " + months[d.getMonth()] + " " + d.getFullYear();
  }


  function getCategories(post) {
    try { return post._embedded["wp:term"][0].map(function (t) { return t.name; }).join(", "); } catch (e) { return ""; }
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
