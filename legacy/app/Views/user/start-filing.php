<?php use App\Core\Helper; ?>
<div class="itr-page-title">
    <h1>Start Filing — FY <?= Helper::e($financial_year) ?></h1>
    <p>Choose Self Filing or Hire an Expert, set your income profile, then upload Form 16.</p>
</div>

<form method="post" action="/itr/new" id="startForm">
<?= Helper::csrfField() ?>

<div class="itr-card"><div class="itr-card-h">1. Choose how you want to file</div><div class="itr-card-b">
<div class="itr-grid-2">
    <label class="itr-plan itr-is-clickable">
        <input type="radio" name="filing_mode" value="self" checked>
        <span class="itr-tag itr-tag-orange">Self ITR Filing</span>
        <h3 class="itr-plan-name">File yourself — free demo</h3>
        <p>Best for salaried Form 16 cases. Upload documents, compare regimes and file in minutes.</p>
        <ul>
            <li><?= Helper::icon('check') ?> Form 16 / AIS / 26AS upload</li>
            <li><?= Helper::icon('check') ?> Auto tax summary</li>
            <li><?= Helper::icon('check') ?> Old vs New regime compare</li>
            <li><?= Helper::icon('check') ?> Self e-file + ACK download</li>
        </ul>
    </label>
    <label class="itr-plan itr-hot itr-is-clickable">
        <input type="radio" name="filing_mode" value="assisted">
        <span class="itr-tag">Hire an Expert</span>
        <h3 class="itr-plan-name">Expert files for you</h3>
        <p>Ideal for investors, traders, freelancers &amp; NRIs. Instant CA match after payment — filed in 24 hrs.</p>
        <ul>
            <li><?= Helper::icon('check') ?> Dedicated CA review</li>
            <li><?= Helper::icon('check') ?> Accuracy &amp; mismatch checks</li>
            <li><?= Helper::icon('check') ?> Maximum refund focus</li>
            <li><?= Helper::icon('check') ?> Live status till ACK</li>
        </ul>
    </label>
</div>
</div></div>

<div class="itr-card"><div class="itr-card-h">2. Your income profile</div><div class="itr-card-b">
<p class="itr-help itr-mb-sm">This helps us suggest the right ITR type and expert plan.</p>
<div class="itr-form-row">
    <div class="itr-form-group">
        <label>I'm filing as</label>
        <select class="itr-form-control" name="income_profile">
            <option value="salaried">Salaried Professional</option>
            <option value="investor">Investor / Trader</option>
            <option value="freelancer">Freelancer / Professional</option>
            <option value="advanced_trader">Advanced Trader (F&amp;O)</option>
            <option value="nri">NRI / RSU / ESOP</option>
            <option value="affluent">Affluent Investor</option>
        </select>
    </div>
    <div class="itr-form-group">
        <label>ITR Type</label>
        <select class="itr-form-control" name="itr_type">
            <option>ITR-1</option>
            <option>ITR-2</option>
            <option>ITR-3</option>
            <option>ITR-4</option>
        </select>
    </div>
</div>
<div class="itr-form-group">
    <label>PAN</label>
    <input class="itr-form-control itr-pan-input" name="pan" maxlength="10" placeholder="ABCDE1234F">
    <div class="itr-help">Enter PAN as on your Form 16 / Income Tax profile.</div>
</div>
</div></div>

<div class="itr-card" id="expertPlans"><div class="itr-card-h">3. Choose expert plan</div><div class="itr-card-b">
<p class="itr-help itr-mb-sm">Shown only when Hire an Expert is selected. You can apply coupons at payment.</p>
<div class="itr-grid-3">
<?php foreach ($plans as $i => $plan): ?>
<label class="itr-plan itr-is-clickable <?= $i === 1 ? 'itr-hot' : '' ?>">
    <input type="radio" name="plan_id" value="<?= (int)$plan['id'] ?>" <?= $i === 1 ? 'checked' : '' ?>>
    <h3><?= Helper::e($plan['name']) ?></h3>
    <div class="itr-price"><?= Helper::money($plan['price']) ?></div>
    <p><?= Helper::e($plan['description']) ?></p>
</label>
<?php endforeach; ?>
</div>
</div></div>

<button class="itr-btn itr-btn-orange" type="submit">Continue → Upload Form 16</button>
</form>
