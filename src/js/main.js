(function ($) {
  'use strict';

  // ─── List pagination ────────────────────────────────────────────────────────
  // listSel   – selector for the items wrapper (.news-list / .reviews-list)
  // itemSel   – selector for each item inside the wrapper
  // perPage   – items per page
  function initPagination(listSel, itemSel, perPage) {
    var list = document.querySelector(listSel);
    if (!list) return;

    var footer  = list.parentNode.querySelector('.list-footer');
    if (!footer) return;

    var items   = [].slice.call(list.querySelectorAll(itemSel));
    if (!items.length) return;

    var total   = Math.ceil(items.length / perPage);
    var cur     = 1;
    var cumul   = false; // true while in "show more" (append) mode

    var btnMore = footer.querySelector('.btn-show-more');
    var pgNav   = footer.querySelector('.pg');

    var prevSVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="15 18 9 12 15 6"></polyline></svg>';
    var nextSVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="9 18 15 12 9 6"></polyline></svg>';

    // Determine which page numbers to show in the nav
    function pageNums(active, last) {
      if (last <= 7) {
        return Array.from({length: last}, function (_, i) { return i + 1; });
      }
      if (active <= 4)         return [1, 2, 3, 4, 5, '…', last];
      if (active >= last - 3)  return [1, '…', last - 4, last - 3, last - 2, last - 1, last];
      return [1, '…', active - 1, active, active + 1, '…', last];
    }

    // Render item visibility
    function renderItems(page, append) {
      var from = append ? 0 : (page - 1) * perPage;
      var to   = page * perPage;
      items.forEach(function (el, i) {
        el.style.display = (i >= from && i < to) ? '' : 'none';
      });
    }

    // Rebuild pagination nav and bind its events
    function buildPg(active) {
      var html = '<a href="#" class="pg__item" aria-label="Назад" data-dir="-1">' + prevSVG + '</a>';

      pageNums(active, total).forEach(function (p) {
        if (p === '…') {
          html += '<span class="pg__item pg__item--dots">…</span>';
        } else {
          html += '<a href="#" class="pg__item' + (p === active ? ' pg__item--active' : '') +
                  '" data-pg="' + p + '">' + p + '</a>';
        }
      });

      html += '<a href="#" class="pg__item pg__item--filled" aria-label="Вперёд" data-dir="1">' + nextSVG + '</a>';

      pgNav.innerHTML = html;

      pgNav.querySelectorAll('[data-pg]').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          goTo(+this.getAttribute('data-pg'), false);
        });
      });

      pgNav.querySelectorAll('[data-dir]').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          var next = cur + (+this.getAttribute('data-dir'));
          if (next >= 1 && next <= total) goTo(next, false);
        });
      });
    }

    // Navigate to a page
    function goTo(page, append) {
      cur   = page;
      cumul = append;
      renderItems(page, append);
      buildPg(page);
      syncMore();
      if (!append) {
        var top = list.getBoundingClientRect().top + window.pageYOffset - 100;
        window.scrollTo({ top: top < 0 ? 0 : top, behavior: 'smooth' });
      }
    }

    // Keep "Показать еще" in sync
    function syncMore() {
      if (!btnMore) return;
      btnMore.style.display = cur >= total ? 'none' : '';
    }

    if (btnMore) {
      btnMore.addEventListener('click', function (e) {
        e.preventDefault();
        if (cur < total) goTo(cur + 1, true);
      });
    }

    // Single-page edge case – hide controls
    if (total === 1) {
      if (pgNav)   pgNav.style.display   = 'none';
      if (btnMore) btnMore.style.display = 'none';
      return;
    }

    goTo(1, false);
  }

  // ─── Mobile menu (burger) ─────────────────────────────────────────────────────
  function initMobileMenu() {
    var burger  = document.getElementById('headerBurger');
    var menu    = document.getElementById('mobileMenu');
    var overlay = document.getElementById('mobileMenuOverlay');
    if (!burger || !menu) return;

    function open() {
      burger.classList.add('is-open');
      menu.classList.add('is-open');
      if (overlay) overlay.classList.add('is-open');
      document.body.classList.add('menu-open');
      burger.setAttribute('aria-expanded', 'true');
    }

    function close() {
      burger.classList.remove('is-open');
      menu.classList.remove('is-open');
      if (overlay) overlay.classList.remove('is-open');
      document.body.classList.remove('menu-open');
      burger.setAttribute('aria-expanded', 'false');
    }

    burger.addEventListener('click', function () {
      if (menu.classList.contains('is-open')) close(); else open();
    });

    if (overlay) overlay.addEventListener('click', close);

    menu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 992) close();
    });
  }

  // ─── Mobile menu: "Услуги и цены" accordion ────────────────────────────────────
  function initMobileServicesAccordion() {
    var toggle = document.querySelector('.js-mobile-services-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    });
  }

  // ─── Review text truncation (mobile only) ──────────────────────────────────
  function initReviewTruncation() {
    if (window.innerWidth > 767) return;
    document.querySelectorAll('.review-card__text').forEach(function (el) {
      if (el.dataset.truncated) return;
      el.dataset.truncated = '1';
      if (el.textContent.trim().length <= 200) return;
      el.classList.add('review-card__text--clamped');
      var btn = document.createElement('button');
      btn.className = 'review-card__toggle';
      btn.textContent = 'Показать полностью';
      el.parentNode.insertBefore(btn, el.nextSibling);
      btn.addEventListener('click', function () {
        var clamped = el.classList.contains('review-card__text--clamped');
        el.classList.toggle('review-card__text--clamped', !clamped);
        btn.textContent = clamped ? 'Свернуть' : 'Показать полностью';
      });
    });
  }

  // ─── Модалка «Записаться на приём» / «Вызвать врача» ───────────────────────
  // Работает в связке с WordPress-темой: ожидает partials/appointment-modal.twig
  // и глобальный harmonyAjax (url/nonce) из wp_localize_script — в статической
  // сборке (без WP) модалка/формы просто не активируются.
  function initAppointmentModal() {
    var overlay = document.getElementById('appointmentModalOverlay');
    var closeBtn = document.getElementById('appointmentModalClose');
    var title = document.getElementById('appointmentModalTitle');
    var typeField = document.getElementById('appointmentRequestType');
    var doctorSelect = document.getElementById('appointmentDoctor');
    if (!overlay) return;

    function open(trigger) {
      var requestType = (trigger && trigger.dataset.requestType) || 'Запись на приём';
      title.textContent = requestType;
      typeField.value = requestType;
      doctorSelect.value = (trigger && trigger.dataset.doctorId) || '';
      doctorSelect.dispatchEvent(new Event('change'));
      overlay.classList.add('is-open');
      document.body.classList.add('menu-open');
    }

    function close() {
      overlay.classList.remove('is-open');
      document.body.classList.remove('menu-open');
    }

    $(document).on('click', '.js-open-appointment-modal', function (e) {
      e.preventDefault();
      open(this);
    });

    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });
  }

  // ─── Кастомный дропдаун выбора врача (вместо нативного <select>) ────────────
  // Работает по классам, а не по id — на странице может быть несколько модалок
  // с собственным .doctor-select (заявка, отзыв), у каждой свой скрытый <select>.
  function initDoctorSelects() {
    document.querySelectorAll('.doctor-select').forEach(function (wrap) {
      var trigger = wrap.querySelector('.doctor-select__trigger');
      var triggerText = trigger.querySelector('.doctor-select__trigger-text');
      var panel = wrap.querySelector('.doctor-select__panel');
      var search = wrap.querySelector('.doctor-select__search');
      var list = wrap.querySelector('.doctor-select__list');
      var empty = wrap.querySelector('.doctor-select__empty');
      var nativeSelect = wrap.querySelector('.doctor-select__native');
      var options = Array.prototype.slice.call(list.querySelectorAll('.doctor-select__option'));
      var defaultLabel = triggerText.textContent;
      var scrollParent = wrap.closest('.harmony-modal');
      var PANEL_HEIGHT_BUDGET = 340; // search + список ~17 пунктов, ограничено max-height в CSS

      // Панель — position: fixed (см. _appointment-modal.scss), поэтому её нужно
      // ставить вручную по координатам кнопки, иначе overflow-y модалки её обрежет.
      function positionPanel() {
        var rect = trigger.getBoundingClientRect();
        var openUpward = rect.bottom + PANEL_HEIGHT_BUDGET > window.innerHeight && rect.top > PANEL_HEIGHT_BUDGET;

        panel.style.left = rect.left + 'px';
        panel.style.width = rect.width + 'px';
        if (openUpward) {
          panel.style.top = 'auto';
          panel.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
          panel.style.bottom = 'auto';
          panel.style.top = (rect.bottom + 6) + 'px';
        }
      }

      function open() {
        positionPanel();
        wrap.classList.add('is-open');
        if (search) {
          search.value = '';
          filterOptions('');
          search.focus();
        }
      }

      function close() {
        wrap.classList.remove('is-open');
      }

      function selectOption(option) {
        nativeSelect.value = option.dataset.value;
        // Программная установка .value не порождает нативное change-событие —
        // диспатчим вручную, иначе слушатели вроде фильтра на /doctors/ не узнают о выборе.
        nativeSelect.dispatchEvent(new Event('change'));
        triggerText.textContent = option.dataset.label;
        options.forEach(function (o) { o.classList.remove('is-selected'); });
        option.classList.add('is-selected');
        close();
      }

      function syncFromNativeValue() {
        var value = nativeSelect.value;
        var match = null;
        options.forEach(function (o) {
          var isMatch = o.dataset.value === value;
          o.classList.toggle('is-selected', isMatch);
          if (isMatch) match = o;
        });
        triggerText.textContent = match ? match.dataset.label : defaultLabel;
      }

      function filterOptions(query) {
        query = query.trim().toLowerCase();
        var visibleCount = 0;
        options.forEach(function (o) {
          if (o.classList.contains('doctor-select__option--any')) {
            o.classList.remove('is-hidden');
            visibleCount++;
            return;
          }
          var match = !query || (o.dataset.search || '').indexOf(query) !== -1;
          o.classList.toggle('is-hidden', !match);
          if (match) visibleCount++;
        });
        if (empty) empty.style.display = visibleCount === 0 ? 'block' : 'none';
      }

      trigger.addEventListener('click', function () {
        if (wrap.classList.contains('is-open')) close(); else open();
      });

      options.forEach(function (o) {
        o.addEventListener('click', function () { selectOption(o); });
      });

      if (search) {
        search.addEventListener('input', function () {
          filterOptions(search.value);
        });
      }

      nativeSelect.addEventListener('change', syncFromNativeValue);

      document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target) && !panel.contains(e.target)) close();
      });

      // Панель зафиксирована относительно вьюпорта, а не модалки — если модалку
      // проскроллили, координаты устареют, поэтому проще закрыть список.
      if (scrollParent) scrollParent.addEventListener('scroll', close);
      window.addEventListener('resize', close);
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
      });

      wrap.harmonyReset = function () {
        nativeSelect.value = '';
        nativeSelect.dispatchEvent(new Event('change'));
      };
    });
  }

  // ─── Отправка форм (заявка + отзыв) через admin-ajax.php ───────────────────
  function submitHarmonyForm(action, data, $form, $message, onSuccess) {
    if (typeof harmonyAjax === 'undefined') return;

    var $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);
    $message.removeClass('is-visible harmony-modal__message--success harmony-modal__message--error');

    $.post(harmonyAjax.url, $.extend({ action: action, nonce: harmonyAjax.nonce }, data))
      .done(function (response) {
        if (response && response.success) {
          $message.addClass('is-visible harmony-modal__message--success').text(response.data.message);
          $form[0].reset();
          if (onSuccess) onSuccess();
        } else {
          $message.addClass('is-visible harmony-modal__message--error').text((response && response.data && response.data.message) || 'Ошибка отправки.');
        }
      })
      .fail(function () {
        $message.addClass('is-visible harmony-modal__message--error').text('Ошибка отправки. Попробуйте ещё раз.');
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  }

  function initAppointmentForm() {
    var $form = $('#appointmentForm');
    if (!$form.length) return;

    $form.on('submit', function (e) {
      e.preventDefault();
      var $message = $('#appointmentFormMessage');
      submitHarmonyForm('harmony_submit_appointment', {
        name: $('#appointmentName').val(),
        phone: $('#appointmentPhone').val(),
        doctor_id: $('#appointmentDoctor').val(),
        request_type: $('#appointmentRequestType').val()
      }, $form, $message, function () {
        setTimeout(function () {
          document.getElementById('appointmentModalOverlay').classList.remove('is-open');
          document.body.classList.remove('menu-open');
          $message.removeClass('is-visible');
        }, 2000);
      });
    });
  }

  // ─── Поиск + фильтр по категории + пагинация на странице «Энциклопедия» ────
  function initEncyclopediaFilter() {
    var grid = document.getElementById('encGrid');
    var searchInput = document.getElementById('encSearchInput');
    var categorySelect = document.getElementById('encCategorySelect');
    var pgNav = document.getElementById('encPgNav');
    var empty = document.getElementById('encGridEmpty');
    if (!grid || !searchInput || !categorySelect || !pgNav) return;

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.enc-card'));
    var PER_PAGE = 8;
    var filtered = cards;
    var curPage = 1;

    var prevSVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="15 18 9 12 15 6"></polyline></svg>';
    var nextSVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="9 18 15 12 9 6"></polyline></svg>';

    function pageNums(active, last) {
      if (last <= 7) return Array.from({ length: last }, function (_, i) { return i + 1; });
      if (active <= 4) return [1, 2, 3, 4, 5, '…', last];
      if (active >= last - 3) return [1, '…', last - 4, last - 3, last - 2, last - 1, last];
      return [1, '…', active - 1, active, active + 1, '…', last];
    }

    function renderPage() {
      var from = (curPage - 1) * PER_PAGE;
      var to = curPage * PER_PAGE;
      cards.forEach(function (card) { card.style.display = 'none'; });
      filtered.slice(from, to).forEach(function (card) { card.style.display = ''; });
      empty.style.display = filtered.length === 0 ? 'block' : 'none';
    }

    function renderPg() {
      var total = Math.max(1, Math.ceil(filtered.length / PER_PAGE));

      if (total <= 1) {
        pgNav.innerHTML = '';
        return;
      }

      var html = '<a href="#" class="pg__item" data-dir="-1" aria-label="Назад">' + prevSVG + '</a>';
      pageNums(curPage, total).forEach(function (n) {
        if (n === '…') {
          html += '<span class="pg__item pg__item--dots">…</span>';
        } else {
          html += '<a href="#" class="pg__item' + (n === curPage ? ' pg__item--active' : '') + '" data-pg="' + n + '">' + n + '</a>';
        }
      });
      html += '<a href="#" class="pg__item pg__item--filled" data-dir="1" aria-label="Вперёд">' + nextSVG + '</a>';
      pgNav.innerHTML = html;

      pgNav.querySelectorAll('[data-pg]').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          goTo(parseInt(this.getAttribute('data-pg'), 10));
        });
      });

      pgNav.querySelectorAll('[data-dir]').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          var next = curPage + parseInt(this.getAttribute('data-dir'), 10);
          if (next >= 1 && next <= total) goTo(next);
        });
      });
    }

    function goTo(page) {
      curPage = page;
      renderPage();
      renderPg();
      var top = grid.getBoundingClientRect().top + window.pageYOffset - 100;
      window.scrollTo({ top: top < 0 ? 0 : top, behavior: 'smooth' });
    }

    function applyFilter() {
      var query = searchInput.value.trim().toLowerCase();
      var category = categorySelect.value;
      filtered = cards.filter(function (card) {
        var matchesQuery = !query
          || card.dataset.title.indexOf(query) !== -1
          || card.dataset.excerpt.indexOf(query) !== -1;
        var matchesCategory = !category || card.dataset.category === category;
        return matchesQuery && matchesCategory;
      });
      curPage = 1;
      renderPage();
      renderPg();
    }

    searchInput.addEventListener('input', applyFilter);
    categorySelect.addEventListener('change', applyFilter);

    applyFilter();
  }

  // ─── Поиск по сайту (врачи + услуги) в шапке ─────────────────────────────────
  function initSiteSearch() {
    var overlay = document.getElementById('siteSearchOverlay');
    if (!overlay) return;

    var closeBtn = document.getElementById('siteSearchClose');
    var input = document.getElementById('siteSearchInput');
    var items = Array.prototype.slice.call(document.querySelectorAll('#siteSearchResults .site-search__item'));
    var empty = document.getElementById('siteSearchEmpty');

    function filter(query) {
      query = query.trim().toLowerCase();
      var visible = 0;
      items.forEach(function (item) {
        var match = !query || (item.dataset.title || '').indexOf(query) !== -1;
        item.classList.toggle('is-hidden', !match);
        if (match) visible++;
      });
      empty.style.display = visible === 0 ? 'block' : 'none';
    }

    function open() {
      overlay.classList.add('is-open');
      document.body.classList.add('menu-open');
      input.value = '';
      filter('');
      setTimeout(function () { input.focus(); }, 50);
    }

    function close() {
      overlay.classList.remove('is-open');
      document.body.classList.remove('menu-open');
    }

    $(document).on('click', '.js-open-site-search', function (e) {
      e.preventDefault();
      open();
    });

    // Клик по «Задать вопрос» внутри результатов поиска открывает модалку заявки
    // (свой обработчик уже висит на .js-open-appointment-modal) — здесь просто закрываем поиск.
    $(document).on('click', '#siteSearchResults .js-open-appointment-modal', function () {
      close();
    });

    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });

    input.addEventListener('input', function () {
      filter(input.value);
    });
  }

  // ─── Кнопка «наверх» — появляется после прокрутки, плавный скролл к началу ──
  function initScrollTopButton() {
    var btn = document.querySelector('.js-scroll-top');
    if (!btn) return;

    var THRESHOLD = 400;

    function update() {
      btn.classList.toggle('is-visible', window.scrollY > THRESHOLD);
    }

    window.addEventListener('scroll', update, { passive: true });
    update();

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ─── Sticky quick-nav bar on service-category pages (gynecology, etc.) ──────
  // position: sticky alone only holds within its own (short) parent section,
  // so it scrolls away with that section instead of following the whole page.
  // Swap to position: fixed once scrolled past its natural spot instead, with
  // a placeholder to keep the layout from jumping.
  function initStickySubnav() {
    var nav = document.querySelector('.js-sticky-subnav');
    var placeholder = document.querySelector('.js-sticky-subnav-placeholder');
    if (!nav || !placeholder) return;

    var PINNED_TOP = 16;
    var naturalTop = 0;

    function measure() {
      if (!nav.classList.contains('is-pinned')) {
        naturalTop = nav.getBoundingClientRect().top + window.scrollY;
      }
    }

    function update() {
      var shouldPin = window.scrollY > naturalTop - PINNED_TOP;
      if (shouldPin === nav.classList.contains('is-pinned')) return;

      if (shouldPin) {
        placeholder.style.height = nav.offsetHeight + 'px';
        placeholder.classList.add('is-active');
        nav.classList.add('is-pinned');
      } else {
        nav.classList.remove('is-pinned');
        placeholder.classList.remove('is-active');
        placeholder.style.height = '';
      }
    }

    measure();
    update();
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', function () {
      measure();
      update();
    });
  }

  // ─── Версия для слабовидящих (крупный шрифт/контраст, запоминается) ─────────
  function initAccessibilityToggle() {
    var btn = document.querySelector('.js-toggle-accessibility');
    if (!btn) return;

    var STORAGE_KEY = 'harmonyAccessibilityMode';

    function setState(on) {
      document.documentElement.classList.toggle('accessibility-mode', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      try {
        localStorage.setItem(STORAGE_KEY, on ? '1' : '0');
      } catch (e) { /* localStorage недоступен (приватный режим и т.п.) — просто не запоминаем */ }
    }

    btn.addEventListener('click', function () {
      setState(!document.documentElement.classList.contains('accessibility-mode'));
    });

    // Класс уже мог быть выставлен inline-скриптом в <head> (чтобы избежать мигания темы) —
    // синхронизируем aria-pressed с фактическим состоянием.
    btn.setAttribute('aria-pressed', document.documentElement.classList.contains('accessibility-mode') ? 'true' : 'false');
  }

  // ─── Поиск + фильтр по специальности на странице «Врачи» ────────────────────
  function initDoctorsPageFilter() {
    var grid = document.querySelector('.doctors-grid');
    var searchInput = document.getElementById('doctorsSearchInput');
    var specialtySelect = document.getElementById('specialtySelect');
    var empty = document.getElementById('doctorsGridEmpty');
    if (!grid || !searchInput || !specialtySelect) return;

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.dr-card'));

    function apply() {
      var query = searchInput.value.trim().toLowerCase();
      var specialty = specialtySelect.value;
      var visibleCount = 0;

      cards.forEach(function (card) {
        var matchesName = !query || (card.dataset.name || '').indexOf(query) !== -1;
        var matchesSpecialty = !specialty || card.dataset.specialty === specialty;
        var show = matchesName && matchesSpecialty;
        card.style.display = show ? '' : 'none';
        if (show) visibleCount++;
      });

      if (empty) empty.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', apply);
    specialtySelect.addEventListener('change', apply);
  }

  // ─── Модалка «Добавить отзыв» (с выбором врача, кнопки вне страницы врача) ──
  function initReviewModal() {
    var overlay = document.getElementById('reviewModalOverlay');
    if (!overlay) return;

    var closeBtn = document.getElementById('reviewModalClose');

    function open() {
      overlay.classList.add('is-open');
      document.body.classList.add('menu-open');
    }

    function close() {
      overlay.classList.remove('is-open');
      document.body.classList.remove('menu-open');
    }

    $(document).on('click', '.js-open-review-modal', function (e) {
      e.preventDefault();
      open();
    });

    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });

    var $form = $('#reviewModalForm');
    $form.on('submit', function (e) {
      e.preventDefault();
      var doctorId = $('#reviewModalDoctor').val();
      var $message = $('#reviewModalMessage');

      if (!doctorId) {
        $message.addClass('is-visible harmony-modal__message--error').text('Пожалуйста, выберите врача.');
        return;
      }

      submitHarmonyForm('harmony_submit_review', {
        author_name: $('#reviewModalAuthorName').val(),
        rating: $form.find('input[name="review_modal_rating"]:checked').val(),
        review_text: $('#reviewModalText').val(),
        doctor_id: doctorId
      }, $form, $message, function () {
        var wrap = $form.find('.doctor-select')[0];
        if (wrap && wrap.harmonyReset) wrap.harmonyReset();
        setTimeout(function () {
          close();
          $message.removeClass('is-visible');
        }, 2500);
      });
    });
  }

  // ─── Форма отзыва на странице врача ─────────────────────────────────────────
  function initReviewForm() {
    var $form = $('#reviewForm');
    if (!$form.length) return;

    $(document).on('click', '.js-toggle-review-form', function (e) {
      e.preventDefault();
      $form.toggleClass('is-open');
      if ($form.hasClass('is-open')) {
        $form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });

    $form.on('submit', function (e) {
      e.preventDefault();
      var $message = $('#reviewFormMessage');
      submitHarmonyForm('harmony_submit_review', {
        author_name: $('#reviewAuthorName').val(),
        rating: $form.find('input[name="rating"]:checked').val(),
        review_text: $('#reviewText').val(),
        doctor_id: $form.data('doctor-id')
      }, $form, $message, function () {
        setTimeout(function () {
          $form.removeClass('is-open');
          $message.removeClass('is-visible');
        }, 3000);
      });
    });
  }

  $(document).ready(function () {
    console.log('Harmony theme ready');

    initMobileMenu();
    initMobileServicesAccordion();
    window.setTimeout(initReviewTruncation, 50);
    initAppointmentModal();
    initReviewModal();
    initSiteSearch();
    initScrollTopButton();
    initStickySubnav();
    initAccessibilityToggle();
    initDoctorSelects();
    initDoctorsPageFilter();
    initAppointmentForm();
    initReviewForm();

    // Doctor card → profile page navigation
    $(document).on('click', '[data-doctor-url]', function (e) {
      var url = $(this).data('doctor-url');
      if (url) {
        e.preventDefault();
        window.location.href = url;
      }
    });

    // Service card → its page (the appointment-modal cards are handled by
    // .js-open-appointment-modal above)
    $(document).on('click', '[data-service-url]', function (e) {
      var url = $(this).data('service-url');
      if (url) {
        e.preventDefault();
        window.location.href = url;
      }
    });

    // Encyclopedia article card → its page
    $(document).on('click', '[data-article-url]', function (e) {
      var url = $(this).data('article-url');
      if (url) {
        e.preventDefault();
        window.location.href = url;
      }
    });

    // News card → its page
    $(document).on('click', '[data-news-url]', function (e) {
      var url = $(this).data('news-url');
      if (url) {
        e.preventDefault();
        window.location.href = url;
      }
    });

    // Paginate news list (8 per page)
    initPagination('.news-list', '.news-list-item', 8);

    // Paginate reviews list (4 per page)
    initPagination('.reviews-list', '.review-card', 4);

    // Энциклопедия — поиск + фильтр по категории + пагинация в одном модуле (initEncyclopediaFilter),
    // .enc-grid туда НЕ отдаём через общий initPagination — он не умеет комбинировать с фильтрами.
    initEncyclopediaFilter();

    // Paginate doctors grid (8 per page) — используется и на /doctors/, и на /gynecology/,
    // на каждой странице реально найдётся только один из двух селекторов карточек.
    initPagination('.doctors-grid', '.s_dr-card', 8);
  });

}(jQuery));
