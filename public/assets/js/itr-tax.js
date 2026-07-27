/**
 * ITR Tax ? itr-tax.js
 */
document.addEventListener('DOMContentLoaded', () => {
  // Mobile nav
  const toggle = document.querySelector('[data-nav-toggle]');
  const header = document.querySelector('[data-header]') || document.querySelector('.itr-header');
  if (toggle && header) {
    toggle.addEventListener('click', () => {
      const open = header.classList.toggle('itr-menu-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Sticky header shadow on scroll
  if (header) {
    const onScroll = () => header.classList.toggle('itr-header-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // Panel sliding sidebar
  const panelWrap = document.querySelector('[data-panel-wrap]');
  const sideToggles = Array.from(document.querySelectorAll('[data-side-toggle]'));
  const sideClose = document.querySelector('[data-side-close]');
  const sideBackdrop = document.querySelector('[data-side-backdrop]');
  const SIDE_KEY = 'itr-side-collapsed';

  const isMobileSide = () => window.matchMedia('(max-width: 960px)').matches;

  const syncSideToggle = () => {
    if (!panelWrap || !sideToggles.length) return;
    const open = isMobileSide()
      ? panelWrap.classList.contains('is-side-open')
      : !panelWrap.classList.contains('is-side-collapsed');
    sideToggles.forEach((btn) => {
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (btn.classList.contains('itr-side-rail')) {
        btn.setAttribute('aria-label', open ? 'Collapse sidebar' : 'Expand sidebar');
        btn.title = open ? 'Collapse sidebar' : 'Expand sidebar';
      }
    });
  };

  const openMobileSide = () => {
    panelWrap?.classList.add('is-side-open');
    if (sideBackdrop) sideBackdrop.hidden = false;
    document.body.classList.add('itr-side-lock');
    syncSideToggle();
  };

  const closeMobileSide = () => {
    panelWrap?.classList.remove('is-side-open');
    if (sideBackdrop) sideBackdrop.hidden = true;
    document.body.classList.remove('itr-side-lock');
    syncSideToggle();
  };

  if (panelWrap && sideToggles.length) {
    if (!isMobileSide() && localStorage.getItem(SIDE_KEY) === '1') {
      panelWrap.classList.add('is-side-collapsed');
    }
    syncSideToggle();

    sideToggles.forEach((btn) => {
      btn.addEventListener('click', () => {
        if (isMobileSide()) {
          if (panelWrap.classList.contains('is-side-open')) closeMobileSide();
          else openMobileSide();
          return;
        }
        panelWrap.classList.toggle('is-side-collapsed');
        localStorage.setItem(SIDE_KEY, panelWrap.classList.contains('is-side-collapsed') ? '1' : '0');
        syncSideToggle();
      });
    });

    sideClose?.addEventListener('click', closeMobileSide);
    sideBackdrop?.addEventListener('click', closeMobileSide);

    document.querySelectorAll('.itr-side a').forEach((link) => {
      link.addEventListener('click', () => {
        if (isMobileSide()) closeMobileSide();
      });
    });

    window.addEventListener('resize', () => {
      if (!isMobileSide()) closeMobileSide();
      syncSideToggle();
    }, { passive: true });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeMobileSide();
    });
  }

  // Header / panel profile dropdown
  const closeProfileMenus = (except = null) => {
    document.querySelectorAll('[data-profile-menu].is-open').forEach((menu) => {
      if (except && menu === except) return;
      menu.classList.remove('is-open');
      const trigger = menu.querySelector('[data-profile-trigger]');
      const dropdown = menu.querySelector('[data-profile-dropdown]');
      trigger?.setAttribute('aria-expanded', 'false');
      if (dropdown) dropdown.hidden = true;
    });
  };

  document.querySelectorAll('[data-profile-menu]').forEach((menu) => {
    const trigger = menu.querySelector('[data-profile-trigger]');
    const dropdown = menu.querySelector('[data-profile-dropdown]');
    if (!trigger || !dropdown) return;

    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const willOpen = !menu.classList.contains('is-open');
      closeProfileMenus(menu);
      menu.classList.toggle('is-open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      dropdown.hidden = !willOpen;
    });
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-profile-menu]')) closeProfileMenus();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeProfileMenus();
  });

  // Confirm dialogs
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Auto-hide alerts (no motion ? just remove)
  document.querySelectorAll('.itr-alert[data-auto-hide]').forEach((alert) => {
    setTimeout(() => alert.remove(), 4000);
  });

  // Prevent double-submit + show busy state on forms
  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (form.dataset.itrBusy === '1') {
        e.preventDefault();
        return;
      }
      // Allow Razorpay intercept (payment form opens checkout first)
      if (form.id === 'payForm' && window.RAZORPAY_LIVE) return;
      form.dataset.itrBusy = '1';
      form.classList.add('itr-form-busy');
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((btn) => {
        if (btn.disabled) return;
        btn.dataset.itrLabel = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('itr-btn-busy');
        if (btn.tagName === 'BUTTON') {
          btn.innerHTML = '<span class="itr-spinner" aria-hidden="true"></span> Please wait?';
        }
      });
    });
  });

  // Start filing: Self vs Expert plan toggle
  const modeInputs = document.querySelectorAll('input[name="filing_mode"]');
  const expertPlans = document.getElementById('expertPlans');
  if (modeInputs.length && expertPlans) {
    const syncPlans = () => {
      const assisted = document.querySelector('input[name="filing_mode"][value="assisted"]');
      expertPlans.classList.toggle('itr-hidden', !(assisted && assisted.checked));
    };
    modeInputs.forEach((input) => input.addEventListener('change', syncPlans));
    syncPlans();
  }

  // FAQ category filter
  const faqCats = document.querySelectorAll('.itr-faq-cat');
  const faqs = document.querySelectorAll('.itr-faq[data-faq-cat]');
  if (faqCats.length && faqs.length) {
    faqCats.forEach((cat) => {
      cat.addEventListener('click', () => {
        faqCats.forEach((c) => c.classList.remove('itr-active'));
        cat.classList.add('itr-active');
        const label = (cat.getAttribute('data-faq-filter') || cat.textContent || '').trim();
        faqs.forEach((faq) => {
          const match = label === 'All' || faq.getAttribute('data-faq-cat') === label;
          faq.classList.toggle('itr-hidden', !match);
        });
      });
    });
  }

  // Plan / regime highlight on click
  document.querySelectorAll('.itr-plan.itr-is-clickable, .itr-regime-pick').forEach((box) => {
    box.addEventListener('click', () => {
      const input = box.querySelector('input[type="radio"]');
      if (input) input.checked = true;
      const name = input?.name;
      if (!name) return;
      document.querySelectorAll(`input[name="${name}"]`).forEach((radio) => {
        radio.closest('.itr-plan, .itr-regime-pick')?.classList.toggle('itr-hot', radio.checked);
      });
    });
  });

  // Tax calculator ? mirrors App\Support\TaxCalculator (FY 2025-26 simplified)
  const money = (n) => '?' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const slabTaxBase = (taxable, slabs) => {
    let tax = 0;
    let prev = 0;
    for (const [upto, rate] of slabs) {
      if (taxable <= prev) break;
      const chunk = Math.min(taxable, upto) - prev;
      tax += chunk * rate;
      prev = upto;
    }
    return tax;
  };
  const taxWithRebateAndCess = (taxable, regime) => {
    let tax = regime === 'new'
      ? slabTaxBase(taxable, [[400000,0],[800000,0.05],[1200000,0.1],[1600000,0.15],[2000000,0.2],[2400000,0.25],[1e15,0.3]])
      : slabTaxBase(taxable, [[250000,0],[500000,0.05],[1000000,0.2],[1e15,0.3]]);
    if (regime === 'new' && taxable <= 1200000) tax = Math.max(0, tax - Math.min(tax, 60000));
    if (regime === 'old' && taxable <= 500000) tax = Math.max(0, tax - Math.min(tax, 12500));
    return Math.round(tax * 1.04 * 100) / 100;
  };
  const calcBtn = document.getElementById('calcBtn');
  if (calcBtn) {
    const run = () => {
      const gross = Math.max(0, Number(document.getElementById('calcGross').value) || 0);
      const deduct = Math.max(0, Math.min(Number(document.getElementById('calcDeduct').value) || 0, gross));
      const newTaxable = Math.max(0, gross - 75000);
      const oldTaxable = Math.max(0, gross - 50000 - deduct);
      const newTax = taxWithRebateAndCess(newTaxable, 'new');
      const oldTax = taxWithRebateAndCess(oldTaxable, 'old');
      document.getElementById('calcOld').textContent = money(oldTax);
      document.getElementById('calcNew').textContent = money(newTax);
      const better = newTax <= oldTax ? 'NEW' : 'OLD';
      const saving = Math.abs(oldTax - newTax);
      document.getElementById('calcRec').textContent = `Lower estimated tax: ${better} regime (difference about ${money(saving)}). Estimate only ? not a complete tax computation.`;
    };
    calcBtn.addEventListener('click', run);
    run();
  }

  // Dropzone
  document.querySelectorAll('[data-dropzone]').forEach((zone) => {
    const input = zone.querySelector('[data-dropzone-input]');
    const nameEl = zone.querySelector('[data-dropzone-name]');
    if (!input) return;
    const showName = () => {
      if (nameEl) nameEl.textContent = input.files?.[0]?.name || 'No file selected';
    };
    zone.addEventListener('click', () => input.click());
    input.addEventListener('change', showName);
    ;['dragenter', 'dragover'].forEach((ev) => {
      zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('itr-drag'); });
    });
    ;['dragleave', 'drop'].forEach((ev) => {
      zone.addEventListener(ev, (e) => {
        e.preventDefault();
        zone.classList.remove('itr-drag');
        if (ev === 'drop' && e.dataTransfer?.files?.length) {
          input.files = e.dataTransfer.files;
          showName();
        }
      });
    });
  });

  // Suggest ITR from income profile
  const profileSel = document.querySelector('[data-itr-suggest]');
  const itrType = document.getElementById('itrTypeSelect') || document.getElementById('itrType');
  const itrHint = document.getElementById('itrHint');
  const mapItr = {
    salaried: ['ITR-1', 'ITR-1 suits most salaried Form 16 cases.'],
    investor: ['ITR-2', 'ITR-2 for capital gains / multiple house property.'],
    freelancer: ['ITR-3', 'ITR-3 for business / professional income.'],
    advanced_trader: ['ITR-3', 'ITR-3 recommended for F&O / trading business.'],
    nri: ['ITR-2', 'ITR-2 commonly used for NRI / foreign income cases.'],
    affluent: ['ITR-2', 'ITR-2 for complex investment portfolios.'],
  };
  const syncItr = () => {
    if (!profileSel || !itrType) return;
    const row = mapItr[profileSel.value] || mapItr.salaried;
    itrType.value = row[0];
    if (itrHint) itrHint.textContent = row[1];
  };
  if (profileSel) {
    profileSel.addEventListener('change', syncItr);
    syncItr();
  }

  // Live tax summary (mirrors TaxCalculator)
  const summaryForm = document.querySelector('[data-live-summary]');
  if (summaryForm) {
    const runSummary = () => {
      const gross = Math.max(0, Number(document.getElementById('sumGross')?.value) || 0);
      const deduct = Math.max(0, Math.min(Number(document.getElementById('sumDeduct')?.value) || 0, gross));
      let tds = Number(document.getElementById('sumTds')?.value);
      if (!Number.isFinite(tds) || tds < 0) tds = 0;
      const newTaxable = Math.max(0, gross - 75000);
      const oldTaxable = Math.max(0, gross - 50000 - deduct);
      const newTax = taxWithRebateAndCess(newTaxable, 'new');
      const oldTax = taxWithRebateAndCess(oldTaxable, 'old');
      const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = money(val); };
      set('sumOldTax', oldTax); set('sumNewTax', newTax);
      set('brGrossOld', gross); set('brGrossNew', gross);
      set('brStdOld', 50000); set('brStdNew', 75000);
      set('brDedOld', deduct);
      set('brTaxableOld', oldTaxable); set('brTaxableNew', newTaxable);
      set('brTaxOld', oldTax); set('brTaxNew', newTax);
      set('brTdsOld', tds); set('brTdsNew', tds);
      set('brNetOld', oldTax - tds); set('brNetNew', newTax - tds);
      const better = newTax <= oldTax ? 'NEW' : 'OLD';
      const saving = Math.abs(oldTax - newTax);
      const rec = document.getElementById('sumRec');
      if (rec) rec.innerHTML = `Lower estimated tax: <strong>${better} regime</strong> (difference about ${money(saving)}). Simplified estimate including �87A where applicable.`;
    };
    ['sumGross', 'sumDeduct', 'sumTds'].forEach((id) => {
      document.getElementById(id)?.addEventListener('input', runSummary);
    });
    summaryForm.querySelector('[name="ais_tds"]')?.addEventListener('input', runSummary);
    runSummary();
  }

  // Coupon quick-fill + pay method highlight
  document.querySelectorAll('[data-fill-coupon]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = document.getElementById('couponInput');
      if (input) input.value = btn.getAttribute('data-fill-coupon') || '';
    });
  });
  document.querySelectorAll('.itr-pay-method').forEach((box) => {
    box.addEventListener('click', () => {
      document.querySelectorAll('.itr-pay-method').forEach((b) => b.classList.remove('itr-hot'));
      box.classList.add('itr-hot');
      const radio = box.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });

  // Demo login chips
  document.querySelectorAll('[data-demo-email]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const email = document.querySelector('input[name="email"]');
      const pass = document.querySelector('input[name="password"]');
      if (email) email.value = btn.getAttribute('data-demo-email') || '';
      if (pass) pass.value = 'password';
    });
  });

  // Custom select for every <select>
  const closeAllSelects = (except) => {
    document.querySelectorAll('.itr-cselect.is-open').forEach((wrap) => {
      if (wrap !== except) {
        wrap.classList.remove('is-open', 'is-dropup');
        wrap.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
        const m = wrap.querySelector('.itr-cselect-menu');
        if (m) {
          m.classList.remove('is-fixed');
          m.style.cssText = '';
        }
      }
    });
  };

  const enhanceSelect = (select) => {
    if (!select || select.dataset.itrEnhanced === '1' || select.dataset.itrNative === '1') return;
    if (select.multiple || select.size > 1) return;

    select.dataset.itrEnhanced = '1';
    const wrap = document.createElement('div');
    wrap.className = 'itr-cselect';
    if (select.classList.contains('itr-select-sm')) wrap.classList.add('itr-cselect-sm');
    if (select.disabled) wrap.classList.add('is-disabled');

    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add('itr-cselect-native');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'itr-cselect-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = '<span class="itr-cselect-label"></span><span class="itr-cselect-caret" aria-hidden="true"></span>';
    wrap.appendChild(trigger);

    const menu = document.createElement('ul');
    menu.className = 'itr-cselect-menu';
    menu.setAttribute('role', 'listbox');
    wrap.appendChild(menu);

    const labelEl = trigger.querySelector('.itr-cselect-label');

    const selectedText = () => {
      const opt = select.options[select.selectedIndex];
      if (!opt || opt.value === '') {
        labelEl.classList.add('is-placeholder');
        return opt?.textContent?.trim() || 'Select';
      }
      labelEl.classList.remove('is-placeholder');
      return opt.textContent.trim();
    };

    const rebuild = () => {
      menu.innerHTML = '';
      Array.from(select.options).forEach((opt, idx) => {
        const li = document.createElement('li');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'itr-cselect-option';
        btn.setAttribute('role', 'option');
        btn.dataset.index = String(idx);
        btn.textContent = opt.textContent.trim();
        if (opt.disabled) {
          btn.disabled = true;
          btn.classList.add('is-disabled');
        }
        if (opt.selected) {
          btn.classList.add('is-selected');
          btn.setAttribute('aria-selected', 'true');
        }
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          if (opt.disabled) return;
          select.selectedIndex = idx;
          select.dispatchEvent(new Event('change', { bubbles: true }));
          close();
          trigger.focus();
        });
        li.appendChild(btn);
        menu.appendChild(li);
      });
      labelEl.textContent = selectedText();
      wrap.classList.toggle('is-disabled', select.disabled);
      trigger.disabled = !!select.disabled;
    };

    const clearMenuPos = () => {
      menu.classList.remove('is-fixed');
      menu.style.cssText = '';
    };

    const placeMenu = () => {
      const rect = trigger.getBoundingClientRect();
      const gap = 6;
      const pad = 10;
      const vw = window.innerWidth;
      const vh = window.innerHeight;
      const spaceBelow = vh - rect.bottom - gap - pad;
      const spaceAbove = rect.top - gap - pad;
      const preferDown = spaceBelow >= 160 || spaceBelow >= spaceAbove;
      const available = Math.max(120, preferDown ? spaceBelow : spaceAbove);
      const maxH = Math.min(360, available);

      wrap.classList.toggle('is-dropup', !preferDown);
      menu.classList.add('is-fixed');

      const width = Math.max(rect.width, 160);
      let left = rect.left;
      if (left + width > vw - pad) left = Math.max(pad, vw - pad - width);
      if (left < pad) left = pad;

      menu.style.width = `${width}px`;
      menu.style.maxHeight = `${maxH}px`;
      menu.style.left = `${left}px`;
      menu.style.right = 'auto';

      if (preferDown) {
        menu.style.top = `${rect.bottom + gap}px`;
        menu.style.bottom = 'auto';
      } else {
        menu.style.top = 'auto';
        menu.style.bottom = `${vh - rect.top + gap}px`;
      }
    };

    const open = () => {
      if (select.disabled) return;
      closeAllSelects(wrap);
      wrap.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      placeMenu();
      requestAnimationFrame(() => {
        placeMenu();
        const active = menu.querySelector('.itr-cselect-option.is-selected');
        active?.scrollIntoView({ block: 'nearest' });
      });
    };

    const close = () => {
      wrap.classList.remove('is-open', 'is-dropup');
      trigger.setAttribute('aria-expanded', 'false');
      clearMenuPos();
    };

    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (wrap.classList.contains('is-open')) close();
      else open();
    });

    trigger.addEventListener('keydown', (e) => {
      const options = Array.from(menu.querySelectorAll('.itr-cselect-option:not(:disabled)'));
      const current = options.findIndex((o) => o.classList.contains('is-active') || o.classList.contains('is-selected'));
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (!wrap.classList.contains('is-open')) open();
        const next = e.key === 'ArrowDown'
          ? Math.min(options.length - 1, Math.max(0, current) + 1)
          : Math.max(0, (current < 0 ? 0 : current) - 1);
        options.forEach((o) => o.classList.remove('is-active'));
        options[next]?.classList.add('is-active');
        options[next]?.scrollIntoView({ block: 'nearest' });
      } else if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        if (!wrap.classList.contains('is-open')) {
          open();
          return;
        }
        const focused = menu.querySelector('.itr-cselect-option.is-active') || menu.querySelector('.itr-cselect-option.is-selected');
        focused?.click();
      } else if (e.key === 'Escape') {
        close();
      }
    });

    select.addEventListener('change', rebuild);
    rebuild();

    const mo = new MutationObserver(rebuild);
    mo.observe(select, { childList: true, subtree: true });

    wrap._itrPlaceMenu = () => {
      if (wrap.classList.contains('is-open')) placeMenu();
    };
  };

  document.querySelectorAll('select').forEach(enhanceSelect);

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.itr-cselect')) closeAllSelects();
  });

  window.addEventListener('resize', () => {
    document.querySelectorAll('.itr-cselect.is-open').forEach((wrap) => wrap._itrPlaceMenu?.());
  }, { passive: true });

  document.addEventListener('scroll', (e) => {
    // Reposition on page/panel scroll so menu stays under the trigger
    const open = document.querySelector('.itr-cselect.is-open');
    if (!open) return;
    if (e.target && e.target.closest?.('.itr-cselect-menu')) return;
    open._itrPlaceMenu?.();
  }, true);

  // Enhance selects added later (e.g. dynamic admin rows)
  const selectObserver = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
      m.addedNodes.forEach((node) => {
        if (!(node instanceof HTMLElement)) return;
        if (node.matches?.('select')) enhanceSelect(node);
        node.querySelectorAll?.('select').forEach(enhanceSelect);
      });
    });
  });
  selectObserver.observe(document.body, { childList: true, subtree: true });

  // Floating FAQ chatbot (DB-backed)
  const botRoot = document.querySelector('[data-chatbot]');
  if (botRoot) {
    const panel = botRoot.querySelector('#itrBotPanel');
    const toggleBtn = botRoot.querySelector('[data-bot-toggle]');
    const closeBtn = botRoot.querySelector('[data-bot-close]');
    const form = botRoot.querySelector('[data-bot-form]');
    const input = botRoot.querySelector('[data-bot-input]');
    const messages = botRoot.querySelector('[data-bot-messages]');
    const suggests = botRoot.querySelector('[data-bot-suggests]');
    const askUrl = botRoot.dataset.askUrl;
    const suggestUrl = botRoot.dataset.suggestUrl;
    const csrf = botRoot.dataset.csrf;
    let sessionId = localStorage.getItem('itr-bot-session') || '';

    const openBot = () => {
      if (panel) panel.hidden = false;
      botRoot.classList.add('is-open');
      toggleBtn?.setAttribute('aria-expanded', 'true');
      input?.focus();
    };
    const closeBot = () => {
      if (panel) panel.hidden = true;
      botRoot.classList.remove('is-open');
      toggleBtn?.setAttribute('aria-expanded', 'false');
    };
    const addBubble = (text, who) => {
      const div = document.createElement('div');
      div.className = 'itr-bot-bubble itr-bot-bubble-' + who;
      div.textContent = text;
      messages?.appendChild(div);
      if (messages) messages.scrollTop = messages.scrollHeight;
    };

    toggleBtn?.addEventListener('click', () => {
      if (panel?.hidden) openBot(); else closeBot();
    });
    closeBtn?.addEventListener('click', closeBot);

    fetch(suggestUrl, { headers: { 'Accept': 'application/json' } })
      .then((r) => r.json())
      .then((data) => {
        if (!suggests || !data.suggestions) return;
        suggests.innerHTML = '';
        data.suggestions.forEach((s) => {
          const chip = document.createElement('button');
          chip.type = 'button';
          chip.className = 'itr-bot-chip';
          chip.textContent = s.question.length > 42 ? s.question.slice(0, 40) + '?' : s.question;
          chip.addEventListener('click', () => {
            if (input) input.value = s.question;
            form?.requestSubmit();
          });
          suggests.appendChild(chip);
        });
      })
      .catch(() => {});

    form?.addEventListener('submit', (e) => {
      e.preventDefault();
      const msg = (input?.value || '').trim();
      if (!msg) return;
      addBubble(msg, 'user');
      if (input) input.value = '';
      const body = new FormData();
      body.append('message', msg);
      body.append('session_id', sessionId);
      body.append('_token', csrf);
      fetch(askUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body
      }).then((r) => r.json()).then((data) => {
        if (data.session_id) {
          sessionId = data.session_id;
          localStorage.setItem('itr-bot-session', sessionId);
        }
        addBubble(data.reply || 'Sorry, something went wrong.', 'bot');
      }).catch(() => addBubble('Could not reach the assistant. Check your connection.', 'bot'));
    });
  }
});
