<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Refunds</span>
        <h1>Check ITR refund status</h1>
        <p>Demo helper — after e-verification, refunds are processed by the Income Tax Department. Track ACK here on ITR Tax too.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container itr-container-form">
<div class="itr-card">
    <div class="itr-card-h"><?= Helper::icon('search') ?> Look up acknowledgement</div>
    <div class="itr-card-b">
        <form id="refundForm">
            <div class="itr-form-group">
                <label>PAN</label>
                <input class="itr-form-control itr-pan-input" id="refundPan" maxlength="10" placeholder="ABCDE1234F" required>
            </div>
            <div class="itr-form-group">
                <label>Acknowledgement number</label>
                <input class="itr-form-control" id="refundAck" placeholder="e.g. ITRTAX2026XXXX" required>
            </div>
            <button class="itr-btn itr-btn-primary itr-btn-block" type="submit"><?= Helper::icon('search') ?> Check status</button>
        </form>
        <div class="itr-alert itr-alert-info itr-mt-md itr-hidden" id="refundResult"></div>
        <div class="itr-soft-note itr-mt-md">Logged-in users can also open <a href="/track">My Filings → Track</a> for live status and ACK download.</div>
    </div>
</div>
</div></section>
