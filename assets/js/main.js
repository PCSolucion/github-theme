/**
 * GitHub Theme — Bundle (Ultra-Lite)
 * Combinación de main.js y live-search.js para máxima eficiencia.
 */
document.addEventListener("DOMContentLoaded", () => {
  "use strict";
  const $ = (s, c = document) => c.querySelector(s),
    $$ = (s, c = document) => [...c.querySelectorAll(s)];

  // --- CONFIG & ICONS ---
  const lsCfg = {
    MS: 300,
    MAX: 15,
    URL: (window.liveSearchData?.restUrl || "/wp-json/wp/v2").replace(
      /\/$/,
      "",
    ),
  };
  const ICONS = {
    s: '<path d="M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z"/>',
    a: '<path d="M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z"/>',
    x: '<path d="M3.72 3.72a.75.75 0 011.06 0L8 6.94l3.22-3.22a.75.75 0 111.06 1.06L9.06 8l3.22 3.22a.75.75 0 11-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 01-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 010-1.06z"/>',
  };
  const svgIcon = (p, c = "", w = 16) =>
    `<svg class="${c}" viewBox="0 0 16 16" width="${w}" height="${w}" fill="currentColor">${p}</svg>`;

  let lsAbort,
    lsTimer,
    lsSelIdx = -1,
    lsLastOpen = 0,
    lsOverlay,
    lsResBox,
    lsInput;

  // --- LIGHTBOX LOGIC ---
  let imgOverlay;
  const imgClose = () => {
    if (imgOverlay) {
      imgOverlay.classList.remove("active");
      document.body.style.overflow = "";
      setTimeout(() => {
        if (!imgOverlay.classList.contains("active")) {
          const img = $(".image-lightbox-img", imgOverlay);
          if (img) img.src = "";
        }
      }, 250);
    }
  };

  const imgOpen = (src, alt = "") => {
    if (!imgOverlay) {
      document.body.insertAdjacentHTML(
        "beforeend",
        `<div class="image-lightbox-overlay"><div class="image-lightbox-modal"><button class="image-lightbox-close" aria-label="Cerrar">${svgIcon(ICONS.x, "", 24)}</button><img class="image-lightbox-img" src="" alt=""></div></div>`,
      );
      imgOverlay = $(".image-lightbox-overlay");
      $(".image-lightbox-close", imgOverlay)?.addEventListener("click", (e) => {
        e.stopPropagation();
        imgClose();
      });
      imgOverlay.addEventListener("click", (e) => {
        if (!e.target.closest(".image-lightbox-img")) imgClose();
      });
    }
    const img = $(".image-lightbox-img", imgOverlay);
    if (img) {
      img.src = src;
      img.alt = alt;
      imgOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  };

  document.addEventListener("click", (e) => {
    const a = e.target.closest("a");
    if (!a) return;
    const href = a.getAttribute("href");
    if (href && /\.(jpg|jpeg|png|gif|webp|svg|bmp)(\?.*)?$/i.test(href)) {
      if (e.ctrlKey || e.metaKey || e.shiftKey) return;
      e.preventDefault();
      imgOpen(href, a.querySelector("img")?.alt || "");
    }
  }, { capture: true });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      imgClose();
      if (typeof lsClose === "function") lsClose();
    }
  });

  // --- LIVE SEARCH LOGIC ---

  const lsRender = (h) => {
    if (lsResBox) lsResBox.innerHTML = h;
  };
  const lsState = (m, q = "", i = "") =>
    lsRender(
      `<div class="live-search-empty">${i}${m} <strong>${q}</strong></div>`,
    );
  const lsHigh = (t, q) =>
    q
      ? t.replace(
          new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`, "gi"),
          "<mark>$1</mark>",
        )
      : t;
  const lsFmt = (s) =>
    new Date(s).toLocaleDateString("es-ES", {
      day: "numeric",
      month: "short",
      year: "numeric",
    });

  const lsSearch = async (q) => {
    if (lsTimer) clearTimeout(lsTimer);
    if (!q.trim()) return lsState("Escribe para buscar...");
    lsRender(
      '<div class="live-search-loading"><div class="live-search-spinner"></div>Buscando...</div>',
    );

    lsTimer = setTimeout(async () => {
      lsAbort?.abort();
      lsAbort = new AbortController();
      try {
        const u = `${lsCfg.URL.replace("/wp/v2", "")}/github-theme/v1/search?q=${encodeURIComponent(q)}&per_page=${lsCfg.MAX}`;
        const r = await fetch(u, { signal: lsAbort.signal });
        const posts = r.ok ? await r.json() : await lsFallback(q);
        if (!posts.length) return lsState("Sin resultados para", `"${q}"`);
        lsSelIdx = -1;
        lsRender(
          posts
            .map(
              (p) => `
          <div class="live-search-item"><a href="${p.link}" class="live-search-item-link">
            <div class="live-search-item-content">
              <div class="live-search-item-title">${lsHigh(p.title, q)}</div>
              <div class="live-search-item-excerpt">${lsHigh(p.excerpt, q)}</div>
              <div class="live-search-item-meta">${lsFmt(p.date)} ${p.categories ? `<span class="live-search-item-cat">${p.categories}</span>` : ""}</div>
            </div>${svgIcon(ICONS.a, "live-search-item-arrow")}
          </a></div>`,
            )
            .join(""),
        );
      } catch (e) {
        if (e.name !== "AbortError") lsState("Error al buscar");
      }
    }, lsCfg.MS);
  };

  const lsFallback = async (q) => {
    const r = await fetch(
      `${lsCfg.URL}/posts?search=${encodeURIComponent(q)}&per_page=20&_embed=wp:term`,
      { signal: lsAbort.signal },
    );
    const p = await r.json();
    return p
      .map((p) => ({
        title: p.title.rendered,
        excerpt: p.excerpt.rendered.replace(/<[^>]*>/g, "").slice(0, 120),
        date: p.date,
        link: p.link,
        categories:
          p._embedded?.["wp:term"]?.[0]?.map((t) => t.name).join(", ") || "",
      }))
      .filter((i) => i.title.toLowerCase().includes(q.toLowerCase()))
      .slice(0, lsCfg.MAX);
  };

  const lsOpen = () => {
    if (!lsOverlay) {
      document.body.insertAdjacentHTML(
        "beforeend",
        `<div class="live-search-overlay"><div class="live-search-modal"><div class="live-search-header">${svgIcon(ICONS.s, "live-search-icon", 20)}<input type="search" class="live-search-input" placeholder="Buscar..." aria-label="Buscar contenido"><kbd>ESC</kbd></div><div class="live-search-results"></div><div class="live-search-footer"><span><kbd>↑↓</kbd> navegar</span><span><kbd>↵</kbd> abrir</span></div></div></div>`,
      );
      lsOverlay = $(".live-search-overlay");
      lsResBox = $(".live-search-results", lsOverlay);
      lsInput = $("input", lsOverlay);
      lsInput.addEventListener("input", (e) => lsSearch(e.target.value));
      lsInput.addEventListener("keydown", (e) => {
        const items = [...lsResBox.children];
        if (e.key === "Escape") lsClose();
        if (!items.length) return;
        if (e.key === "ArrowDown") lsSelIdx = (lsSelIdx + 1) % items.length;
        else if (e.key === "ArrowUp")
          lsSelIdx =
            lsSelIdx <= 0 ? (lsSelIdx = items.length - 1) : lsSelIdx - 1;
        else if (e.key === "Enter") {
          if (lsSelIdx >= 0)
            window.location.href = $("a", items[lsSelIdx]).href;
          else if (lsInput.value.trim())
            window.location.href = `${window.liveSearchData?.homeUrl || "/"}?s=${encodeURIComponent(lsInput.value)}`;
          return;
        } else return;
        e.preventDefault();
        items.forEach((it, i) =>
          it.classList.toggle("selected", i === lsSelIdx),
        );
        items[lsSelIdx]?.scrollIntoView({ block: "nearest" });
      });
    }
    lsOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
    lsInput.value = "";
    lsState("Escribe para buscar...");
    lsLastOpen = Date.now();
    setTimeout(() => lsInput.focus(), 50);
  };

  const lsClose = () => {
    if (lsOverlay) {
      lsOverlay.classList.remove("active");
      document.body.style.overflow = "";
      lsAbort?.abort();
    }
  };

  // --- MAIN THEME LOGIC ---
  const getNetText = (el) => {
    const code = $("code", el),
      target = code || el;
    if (code && !$(".line-numbers-rows", code)) return code.textContent;
    const clone = target.cloneNode(true);
    [".copy-button", ".line-numbers-rows", ".code-language-label"].forEach(
      (s) => $$(s, clone).forEach((n) => n.remove()),
    );
    return clone.textContent;
  };

  // 1. Smooth Scroll
  $$('a[href^="#"]').forEach((a) =>
    a.addEventListener("click", (e) => {
      const href = a.getAttribute("href"),
        t = href !== "#" ? $(href) : null;
      if (t) {
        e.preventDefault();
        window.scrollTo({
          top: t.getBoundingClientRect().top + window.scrollY - 120,
          behavior: "smooth",
        });
      }
    }),
  );

  // 2. Form & Nav
  $$(".search-form input").forEach((i) => {
    i.addEventListener("focus", () =>
      i.closest(".search-form")?.classList.add("focused"),
    );
    i.addEventListener("blur", () =>
      i.closest(".search-form")?.classList.remove("focused"),
    );
  });

  const mBtn = $(".mobile-menu-toggle"),
    mNav = $(".main-navigation");
  if (mBtn && mNav) {
    mBtn.addEventListener("click", () =>
      [mBtn, mNav].forEach((el) => el.classList.toggle("active")),
    );
    window.addEventListener(
      "click",
      (e) =>
        !e.target.closest(".main-navigation, .mobile-menu-toggle") &&
        [mBtn, mNav].forEach((el) => el.classList.remove("active")),
    );
  }

  // 3. Code Blocks (Copy + Lines)
  const content = $(".entry-content");
  if (content) {
    $$("pre", content).forEach((pre) => {
      pre.style.position = "relative";
      const btn = document.createElement("button");
      btn.className = "copy-button";
      btn.setAttribute("aria-label", "Copiar código");
      btn.innerHTML =
        '<svg aria-hidden="true" viewBox="0 0 16 16" width="16"><path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"/><path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"/></svg>';
      (
        pre
          .closest(".code-block-wrapper")
          ?.querySelector(".code-block-header") || pre
      ).append(btn);

      btn.addEventListener("click", () =>
        navigator.clipboard.writeText(getNetText(pre)).then(() => {
          btn.classList.add("copied");
          setTimeout(() => btn.classList.remove("copied"), 2000);
        }),
      );

      if (!pre.classList.contains("line-numbers")) {
        const text = getNetText(pre),
          lines = text.split("\n");
        if (lines[lines.length - 1] === "") lines.pop();
        if (lines.length > 1) {
          const row = document.createElement("span");
          row.className = "line-numbers-rows";
          row.innerHTML = "<span></span>".repeat(lines.length);
          row.style.top = getComputedStyle(pre).paddingTop;
          pre.prepend(row);
          pre.classList.add("line-numbers");
        }
      }
    });
  }

  // 4. TOC Spy
  const toc = $("#table-of-contents");
  if (toc && content) {
    const heads = $$("h2", content).filter((h) => h.innerText.trim());
    if (!heads.length) {
      if ($(".toc-box")) $(".toc-box").style.display = "none";
    } else if (window.IntersectionObserver) {
      const obs = new IntersectionObserver(
        (es) =>
          es.forEach((e) => {
            if (e.isIntersecting) {
              $$("a", toc).forEach((a) => a.classList.remove("active"));
              $(`a[href="#${e.target.id}"]`, toc)?.classList.add("active");
            }
          }),
        { rootMargin: "-50px 0px -80% 0px" },
      );
      heads.forEach((h) => obs.observe(h));
    }
  }

  // 5. Tooltip (Contributions)
  if ($(".contribution-cell")) {
    const tip = document.createElement("div");
    tip.className = "github-tooltip";
    document.body.append(tip);
    let raf;
    document.addEventListener("mouseover", (e) => {
      const c = e.target.closest(".contribution-cell[data-tooltip]");
      if (!c) return;
      if (new Date(c.dataset.date.split("-").join("/")) > new Date()) return;
      tip.innerHTML =
        `<strong>${c.dataset.tooltip}</strong>` +
        (c.dataset.titles
          ? `<div style="font-size:11px;color:#8b949e;border-top:1px solid #30363d;margin-top:4px;padding-top:4px">${c.dataset.titles
              .split("|||")
              .map((t) => `<div>• ${t}</div>`)
              .join("")}</div>`
          : "");
      tip.style.opacity = 1;
    });
    document.addEventListener(
      "mouseout",
      (e) => e.target.closest(".contribution-cell") && (tip.style.opacity = 0),
    );
    document.addEventListener("mousemove", (e) => {
      if (tip.style.opacity == 1) {
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => {
          let y = e.clientY - tip.offsetHeight - 15,
            x = e.clientX;
          tip.classList.toggle("bottom", y < 10);
          if (y < 10) y = e.clientY + 15;
          x = Math.max(
            tip.offsetWidth / 2 + 10,
            Math.min(window.innerWidth - tip.offsetWidth / 2 - 10, x),
          );
          tip.style.cssText += `;top:${y}px;left:${x}px;transform:translate(-50%,0)`;
        });
      }
    });
  }

  // 6. Visual Fixes & Deep Linking
  if (content)
    $$("a img", content).forEach((i) => {
      const a = i.closest("a");
      if (a) {
        a.classList.add("image-link");
        if (!a.innerText.trim()) {
          const label = i.alt || i.title || "Ver imagen a tamaño completo";
          a.setAttribute("aria-label", label);
        }
      }
    });

  $$(".entry-content h2, .entry-content h3, .entry-content h4").forEach(
    (h) => !h.innerText.trim() && (h.style.display = "none"),
  );

  $$(".heading-anchor").forEach((a) => {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      navigator.clipboard
        .writeText(location.href.split("#")[0] + a.getAttribute("href"))
        .then(() => {
          const ot = a.innerText;
          a.innerText = "✓";
          a.style.color = "var(--github-success)";
          const h = a.parentElement;
          h.style.cssText +=
            ";transition:background .5s;background:rgba(56,139,253,.1)";
          history.pushState(null, null, a.getAttribute("href"));
          setTimeout(() => {
            a.innerText = ot;
            a.style.color = "";
            h.style.background = "";
          }, 2000);
        });
    });
  });

  // --- INITIALIZE LIVE SEARCH ---
  const lsBtn = $(".AppHeader-search-input"),
    lsFrm = $(".AppHeader-search");
  if (lsFrm)
    lsFrm.addEventListener("submit", (e) => {
      e.preventDefault();
      lsOpen();
    });
  if (lsBtn) {
    lsBtn.addEventListener("focus", (e) => {
      e.target.blur();
      lsOpen();
    });
    lsBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      lsOpen();
    });
  }
  window.addEventListener(
    "click",
    (e) => {
      if (
        lsOverlay?.classList.contains("active") &&
        Date.now() - lsLastOpen > 400 &&
        !e.target.closest(".live-search-modal, .AppHeader-search")
      )
        lsClose();
    },
    true,
  );
  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      lsClose();
      imgClose();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault();
      lsOverlay?.classList.contains("active") ? lsClose() : lsOpen();
    }
    if (
      e.key === "/" &&
      !/INPUT|TEXTAREA/.test(e.target.tagName) &&
      !e.target.isContentEditable
    ) {
      e.preventDefault();
      lsOpen();
    }
  });

  // 7. Checklist Persistence & Progress
  const checklistStorageKey = (id) => `github_theme_checklist_${window.location.pathname}_${id}`;
  
  const updateProgress = () => {
    const checkboxes = $$('.entry-content input[type="checkbox"]');
    if (!checkboxes.length) return;

    // 1. Identificar categorías basadas en la leyenda
    const categories = {};
    const legendItems = $$('.entry-content div[style*="gap"], .guide-legend-item, .legend-item').filter(el => {
      const text = el.innerText.trim();
      return text.includes('●') || el.classList.contains('guide-legend-item');
    });

    legendItems.forEach(container => {
      const items = container.querySelectorAll('strong');
      items.forEach(strong => {
        const rawText = strong.innerText.trim();
        const type = rawText.toLowerCase().split(' ')[0].normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        
        // El color suele estar en un span justo antes
        const colorIndicator = strong.previousElementSibling;
        const color = colorIndicator ? window.getComputedStyle(colorIndicator).color : null;
        
        if (type && !categories[type]) {
          categories[type] = {
            name: rawText.split(' ')[0].toLowerCase(),
            color: color,
            total: 0,
            checked: 0,
            elements: []
          };
          
          let progEl = strong.querySelector('.legend-progress');
          if (!progEl) {
            progEl = document.createElement('span');
            progEl.className = 'legend-progress';
            strong.appendChild(progEl);
          }
          categories[type].elements.push(progEl);
        }
      });
    });

    // 2. Contar progresos
    let totalItems = 0;
    let totalChecked = 0;

    checkboxes.forEach(cb => {
      totalItems++;
      const listItem = cb.closest('li, p, .checklist-item');
      if (!listItem) return;

      const itemText = listItem.innerText.toLowerCase();
      // En BioShock, los colores están en el link <a> dentro del <li>
      const itemLink = listItem.querySelector('a');
      const itemColor = itemLink ? window.getComputedStyle(itemLink).color : window.getComputedStyle(listItem).color;

      Object.keys(categories).forEach(type => {
        const cat = categories[type];
        // Coincidencia por nombre O por color (muy útil en BioShock)
        const nameMatch = itemText.includes(cat.name.substring(0, 4));
        const colorMatch = cat.color && itemColor === cat.color;

        if (nameMatch || colorMatch) {
          cat.total++;
          if (cb.checked) cat.checked++;
        }
      });

      if (cb.checked) {
        totalChecked++;
        listItem.classList.add('is-checked');
        listItem.style.backgroundColor = 'rgba(56, 139, 253, 0.08)';
      } else {
        listItem.classList.remove('is-checked');
        listItem.style.backgroundColor = '';
      }
    });

    // 3. Actualizar Leyendas
    Object.keys(categories).forEach(key => {
      const cat = categories[key];
      const percent = cat.total > 0 ? Math.round((cat.checked / cat.total) * 100) : 0;
      cat.elements.forEach(el => {
        el.textContent = ` (${percent}%)`;
        el.style.opacity = percent > 0 ? "1" : "0.5";
        el.style.color = (percent === 100) ? "var(--github-success)" : "inherit";
        el.style.fontWeight = "normal";
        el.style.marginLeft = "2px";
      });
    });

    // 4. Barra de Progreso Total
    const tocBox = $('.toc-box');
    if (tocBox) {
      let totalBar = $('.guide-total-wrapper', tocBox);
      if (!totalBar) {
        const wrapper = document.createElement('div');
        wrapper.className = 'guide-total-wrapper';
        wrapper.innerHTML = `
          <div class="guide-progress-text">Tu progreso: <span class="percent">0%</span></div>
          <div class="guide-progress-bar"><div class="guide-progress-fill"></div></div>
        `;
        const h3 = tocBox.querySelector('h3');
        if (h3) h3.insertAdjacentElement('afterend', wrapper);
        else tocBox.prepend(wrapper);
        totalBar = wrapper;
      }

      const totalPercent = totalItems > 0 ? Math.round((totalChecked / totalItems) * 100) : 0;
      const percentEl = totalBar.querySelector('.percent');
      if (percentEl) percentEl.textContent = `${totalPercent}%`;
      
      const fillEl = totalBar.querySelector('.guide-progress-fill');
      if (fillEl) {
        fillEl.style.width = `${totalPercent}%`;
        
        if (totalChecked === 0) {
          fillEl.style.background = "var(--github-bg-tertiary)";
          fillEl.style.width = "4px";
        } else {
          // Volvemos a un color sólido y elegante
          fillEl.style.background = "var(--github-success)";
        }
      }
    }
  };

  const initChecklists = () => {
    const checkboxes = $$('.entry-content input[type="checkbox"]');
    if (!checkboxes.length) return;

    checkboxes.forEach((cb, index) => {
      const id = cb.id || `cb-${index}`;
      const savedState = localStorage.getItem(checklistStorageKey(id));
      
      if (savedState === 'true') {
        cb.checked = true;
      }

      cb.addEventListener('change', () => {
        localStorage.setItem(checklistStorageKey(id), cb.checked);
        updateProgress();
      });
    });

    updateProgress();
  };

  initChecklists();
});
