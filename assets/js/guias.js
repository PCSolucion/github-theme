/**
 * Guías Gallery — Client-side logic
 *
 * Handles:
 *  1. A-Z filter bar interaction
 *  2. Async RAWG cover fetching for uncached games
 *
 * @package GitHubTheme
 */

(function () {
  'use strict';

  const grid      = document.getElementById('guias-grid');
  const filterBar = document.getElementById('guias-filter');
  const emptyMsg  = document.getElementById('guias-empty');

  if (!grid || !filterBar) return;

  const cards = Array.from(grid.querySelectorAll('.guia-card'));

  // -----------------------------------------------------------------------
  // A-Z FILTER
  // -----------------------------------------------------------------------

  filterBar.addEventListener('click', function (e) {
    const btn = e.target.closest('.filter-btn');
    if (!btn || btn.disabled) return;

    // Update active state
    filterBar.querySelector('.filter-btn.active')?.classList.remove('active');
    btn.classList.add('active');

    const letter   = btn.dataset.letter;
    const isRecent = (letter === 'recent');
    let visible    = 0;

    cards.forEach(function (card) {
      let match = false;
      if (isRecent) {
        match = card.dataset.recent && parseInt(card.dataset.recent) > 0;
        if (match) {
          card.style.order = card.dataset.recent;
        }
      } else {
        match = (letter === 'all') || (card.dataset.letter === letter);
        card.style.order = ''; // reset order for alphabetical
      }
      
      card.classList.toggle('is-hidden', !match);
      if (match) visible++;
    });

    // Toggle empty state
    if (emptyMsg) {
      emptyMsg.hidden = (visible > 0);
    }
  });

  // -----------------------------------------------------------------------
  // ASYNC COVER LOADING
  // -----------------------------------------------------------------------

  /** @type {string} REST base URL from wp_localize_script */
  const restUrl = (typeof guiasData !== 'undefined') ? guiasData.restUrl : '';

  if (!restUrl) return;

  // Find cards that still need a cover (img with empty src).
  const pending = cards.filter(function (card) {
    const img = card.querySelector('.guia-cover-img');
    return img && !img.getAttribute('src');
  });

  const platformSvgs = {
    'pc': '<svg viewBox="0 0 16 16" width="14" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M0 13.772l6.545.902V8.426H0zM0 7.62h6.545V1.296L0 2.198zm7.265 7.15l8.704 1.2V8.425H7.265zm0-13.57v6.42h8.704V0z" fill="currentColor"/></svg>',
    'playstation': '<svg viewBox="0 0 21 16" width="16" height="12" xmlns="http://www.w3.org/2000/svg"><path d="M11.112 16L8 14.654V0s6.764 1.147 7.695 3.987c.931 2.842-.52 4.682-1.03 4.736-1.42.15-1.96-.748-1.96-.748V3.39l-1.544-.648L11.112 16zM12 14.32V16s7.666-2.338 8.794-3.24c1.128-.9-2.641-3.142-4.666-2.704 0 0-2.152.099-4.102.901-.019.008 0 1.51 0 1.51l4.948-1.095 1.743.73L12 14.32zm-5.024-.773s-.942.476-3.041.452c-2.1-.024-3.959-.595-3.935-1.833C.024 10.928 3.476 9.571 6.952 9v1.738l-3.693.952s-.632.786.217.81A11.934 11.934 0 007 12.046l-.024 1.5z" fill="currentColor"/></svg>',
    'xbox': '<svg viewBox="0 0 16 16" width="14" height="14" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M3.564 1.357l-.022.02c.046-.048.11-.1.154-.128C4.948.435 6.396 0 8 0c1.502 0 2.908.415 4.11 1.136.086.052.324.215.446.363C11.4.222 7.993 2.962 7.993 2.962c-1.177-.908-2.26-1.526-3.067-1.746-.674-.185-1.14-.03-1.362.141zm10.305 1.208c-.035-.04-.074-.076-.109-.116-.293-.322-.653-.4-.978-.378-.295.092-1.66.584-3.342 2.172 0 0 1.894 1.841 3.053 3.723 1.159 1.883 1.852 3.362 1.426 5.415A7.969 7.969 0 0016 7.999a7.968 7.968 0 00-2.13-5.434zM10.98 8.77a55.416 55.416 0 00-2.287-2.405 52.84 52.84 0 00-.7-.686l-.848.854c-.614.62-1.411 1.43-1.853 1.902-.787.84-3.043 3.479-3.17 4.958 0 0-.502-1.174.6-3.88.72-1.769 2.893-4.425 3.801-5.29 0 0-.83-.913-1.87-1.544l-.007-.002s-.011-.009-.03-.02c-.5-.3-1.047-.53-1.573-.56a1.391 1.391 0 00-.878.431A8 8 0 0013.92 13.381c0-.002-.169-1.056-1.245-2.57-.253-.354-1.178-1.46-1.696-2.04z"/></svg>'
  };

  // Fetch covers sequentially to avoid hammering the server.
  async function loadCovers() {
    for (const card of pending) {
      const slug = card.dataset.slug;
      if (!slug) continue;

      try {
        const resp = await fetch(restUrl + encodeURIComponent(slug));
        if (!resp.ok) continue;

        const data = await resp.json();
        if (data && data.cover) {
          const img         = card.querySelector('.guia-cover-img');
          const placeholder = card.querySelector('.guia-cover-placeholder');

          img.onload = function () {
            img.classList.add('loaded');
            if (placeholder) {
              placeholder.style.opacity = '0';
            }
          };

          img.src = data.cover;
          img.style.display = '';

          // Insert Metacritic
          if (data.metacritic) {
            const mcDiv = card.querySelector('.guia-metacritic');
            if (mcDiv) {
              mcDiv.querySelector('.mc-score').textContent = data.metacritic;
              mcDiv.style.display = '';
            }
          }

          // Insert Platforms
          if (data.platforms && data.platforms.length > 0) {
            const platDiv = card.querySelector('.guia-platforms');
            if (platDiv) {
              let svgs = '';
              data.platforms.forEach(p => {
                if (platformSvgs[p]) svgs += platformSvgs[p];
              });
              platDiv.innerHTML = svgs;
            }
          }
        }
      } catch (_) {
        // Silently skip failed fetches.
      }
    }
  }

  if (pending.length > 0) {
    loadCovers();
  }
})();
