/**
 * ITR Tax — itr-tax.js
 */
document.addEventListener('DOMContentLoaded', () => {
  // Mobile nav
  const toggle = document.querySelector('[data-nav-toggle]');
  const header = document.querySelector('.itr-header');
  if (toggle && header) {
    toggle.addEventListener('click', () => {
      header.classList.toggle('itr-menu-open');
    });
  }

  // Confirm dialogs
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Auto-hide alerts
  document.querySelectorAll('.itr-alert[data-auto-hide]').forEach((alert) => {
    setTimeout(() => {
      alert.style.transition = 'opacity .4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 4000);
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
        const label = cat.textContent.trim();
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

  // Tax calculator (demo slabs — mirrors PHP Helper)
  const money = (n) => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const slabTax = (taxable, slabs) => {
    let tax = 0;
    let prev = 0;
    for (const [upto, rate] of slabs) {
      if (taxable <= prev) break;
      const chunk = Math.min(taxable, upto) - prev;
      tax += chunk * rate;
      prev = upto;
    }
    return Math.round(tax * 1.04 * 100) / 100;
  };
  const calcBtn = document.getElementById('calcBtn');
  if (calcBtn) {
    const run = () => {
      const gross = Math.max(0, Number(document.getElementById('calcGross').value) || 0);
      const deduct = Math.max(0, Math.min(Number(document.getElementById('calcDeduct').value) || 0, gross));
      const newTaxable = Math.max(0, gross - 75000);
      const oldTaxable = Math.max(0, gross - 50000 - deduct);
      const newTax = slabTax(newTaxable, [[400000,0],[800000,0.05],[1200000,0.1],[1600000,0.15],[2000000,0.2],[2400000,0.25],[1e15,0.3]]);
      const oldTax = slabTax(oldTaxable, [[250000,0],[500000,0.05],[1000000,0.2],[1e15,0.3]]);
      document.getElementById('calcOld').textContent = money(oldTax);
      document.getElementById('calcNew').textContent = money(newTax);
      const better = newTax <= oldTax ? 'NEW' : 'OLD';
      const saving = Math.abs(oldTax - newTax);
      document.getElementById('calcRec').textContent = `Recommended: ${better} regime — you may save about ${money(saving)}.`;
    };
    calcBtn.addEventListener('click', run);
    run();
  }

  // Refund status demo
  const refundForm = document.getElementById('refundForm');
  if (refundForm) {
    refundForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const pan = (document.getElementById('refundPan').value || '').toUpperCase();
      const ack = document.getElementById('refundAck').value || '';
      const out = document.getElementById('refundResult');
      out.classList.remove('itr-hidden');
      if (pan.length === 10 && ack.length >= 6) {
        out.className = 'itr-alert itr-alert-success itr-mt-md';
        out.textContent = `Demo: ACK ${ack} for PAN ${pan} — status “Processing at CPC”. E-verify within 120 days if not done. Login to download ACK from My Filings.`;
      } else {
        out.className = 'itr-alert itr-alert-error itr-mt-md';
        out.textContent = 'Enter a valid 10-character PAN and acknowledgement number.';
      }
    });
  }
});
