/* ==========================================================================
   신마법공식 Lightweight Fintech UI Controller
   Replaces jQuery Mobile chrome with smooth drawers, modals, search & filters
   ========================================================================== */
(function () {
  'use strict';

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  /* ---------- Drawers (Left Nav & Right Search) ---------- */
  function closeAllDrawers() {
    var backdrop = qs('#ftDrawerBackdrop');
    if (backdrop) backdrop.classList.remove('is-open');
    qsa('.ft-drawer.is-open').forEach(function (el) {
      el.classList.remove('is-open');
      el.setAttribute('aria-hidden', 'true');
    });
    document.body.style.overflow = '';
  }

  function openDrawer(drawerId) {
    closeAllDrawers();
    var drawer = qs('#' + drawerId);
    var backdrop = qs('#ftDrawerBackdrop');
    if (!drawer) return;
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    if (backdrop) backdrop.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    if (drawerId === 'ftSearchDrawer') {
      var input = qs('#ftGlobalSearchInput');
      if (input) {
        setTimeout(function () {
          input.focus();
        }, 120);
      }
      if (typeof window.stockDateTotal === 'function') {
        window.stockDateTotal();
      }
    }
  }

  /* ---------- Modals (Strategy Info & Legacy Popups) ---------- */
  function closeAllModals() {
    qsa('.ft-modal-overlay.is-open').forEach(function (el) {
      el.classList.remove('is-open');
      el.setAttribute('aria-hidden', 'true');
    });
    document.body.style.overflow = '';
  }

  function openModal(modalId) {
    closeAllDrawers();
    closeAllModals();
    var cleanId = String(modalId || '').replace(/^#/, '');
    if (!cleanId) return;

    var overlay = qs('#ftModalOverlay-' + cleanId) || qs('#' + cleanId);
    if (overlay && overlay.classList.contains('ft-modal-overlay')) {
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      return;
    }

    // Legacy [data-role="popup"] compatibility
    var legacyPopup = qs('#' + cleanId);
    if (legacyPopup) {
      var dynamicOverlay = qs('#ftDynamicModal');
      var dynamicBody = qs('#ftDynamicModalBody');
      if (dynamicOverlay && dynamicBody) {
        var clone = legacyPopup.cloneNode(true);
        clone.style.display = 'block';
        clone.removeAttribute('id');
        // Remove old jQuery Mobile close button inside clone
        qsa('[data-rel="back"]', clone).forEach(function (btn) {
          btn.parentNode.removeChild(btn);
        });
        dynamicBody.innerHTML = '';
        dynamicBody.appendChild(clone);
        dynamicOverlay.classList.add('is-open');
        dynamicOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }
    }
  }

  /* ---------- Live Filter for Ranking Lists & Global Search ---------- */
  function attachListFilter(inputEl, itemSelector, emptySelector) {
    if (!inputEl) return;
    inputEl.addEventListener('input', function () {
      var query = (inputEl.value || '').trim().toLowerCase();
      var items = qsa(itemSelector);
      var visibleCount = 0;

      items.forEach(function (item) {
        var text = (item.textContent || '').toLowerCase();
        var match = !query || text.indexOf(query) !== -1;
        item.style.display = match ? '' : 'none';
        if (match) visibleCount++;
      });

      if (emptySelector) {
        var emptyEl = qs(emptySelector);
        if (emptyEl) {
          emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
        }
      }
    });
  }

  /* ---------- Legacy ul[data-role="listview"][data-filter="true"] Support ---------- */
  function initLegacyListFilters() {
    qsa('ul[data-role="listview"][data-filter="true"]').forEach(function (ul, idx) {
      if (ul.dataset.ftFilterAttached) return;
      ul.dataset.ftFilterAttached = '1';

      var placeholder = ul.getAttribute('data-filter-placeholder') || '종목명으로 검색..';
      var wrap = document.createElement('div');
      wrap.className = 'ft-search-box';
      var input = document.createElement('input');
      input.type = 'search';
      input.className = 'ft-search-input';
      input.placeholder = placeholder;
      input.setAttribute('aria-label', placeholder);
      wrap.innerHTML =
        '<svg class="ft-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>';
      wrap.appendChild(input);
      ul.parentNode.insertBefore(wrap, ul);

      input.addEventListener('input', function () {
        var q = (input.value || '').trim().toLowerCase();
        var lis = qsa(':scope > li', ul);
        lis.forEach(function (li, i) {
          if (i === 0 && li.querySelector('table')) return; // Keep header row
          var txt = (li.textContent || '').toLowerCase();
          li.style.display = !q || txt.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    });
  }

  /* ---------- Global Event Bindings ---------- */
  document.addEventListener('DOMContentLoaded', function () {
    // Open Left Nav Drawer
    qsa('.jqm-navmenu-link, [data-ft-open-nav]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openDrawer('ftNavDrawer');
      });
    });

    // Open Right Search Drawer
    qsa('.jqm-search-link, [data-ft-open-search]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openDrawer('ftSearchDrawer');
      });
    });

    // Close Drawers
    var backdrop = qs('#ftDrawerBackdrop');
    if (backdrop) {
      backdrop.addEventListener('click', closeAllDrawers);
    }
    qsa('[data-ft-close-drawer]').forEach(function (btn) {
      btn.addEventListener('click', closeAllDrawers);
    });

    // Accordion Toggle inside Left Drawer
    qsa('.ft-accordion-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var parent = btn.closest('.ft-accordion');
        if (parent) {
          parent.classList.toggle('is-open');
        }
      });
    });

    // Modal Triggers ([data-ft-modal] and legacy [data-rel="popup"])
    qsa('[data-ft-modal], a[data-rel="popup"]').forEach(function (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        var target = trigger.getAttribute('data-ft-modal') || trigger.getAttribute('href');
        openModal(target);
      });
    });

    // Modal Close Handlers
    qsa('[data-ft-close-modal]').forEach(function (btn) {
      btn.addEventListener('click', closeAllModals);
    });
    qsa('.ft-modal-overlay').forEach(function (overlay) {
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
          closeAllModals();
        }
      });
    });

    // Escape key closes drawers and modals
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeAllDrawers();
        closeAllModals();
      }
    });

    // Global Search Drawer Filter
    attachListFilter(
      qs('#ftGlobalSearchInput'),
      '#stockViewTotal > li',
      '#ftGlobalSearchEmpty'
    );

    // Modern Ranking Table Filter
    attachListFilter(
      qs('#ftRankSearchInput'),
      '#ftRankList > li.ft-rank-item',
      '#ftRankEmpty'
    );

    // Legacy Listview Filter Support for secondary pages
    initLegacyListFilters();

    // Debounced resize hook for Google Charts
    var resizeTimer = null;
    window.addEventListener('resize', function () {
      if (typeof window.ftRedrawCharts === 'function') {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(window.ftRedrawCharts, 200);
      }
    });
  });

  window.ftUI = {
    openDrawer: openDrawer,
    closeAllDrawers: closeAllDrawers,
    openModal: openModal,
    closeAllModals: closeAllModals
  };
})();
