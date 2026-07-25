<?php use App\Core\Helper; ?>
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">24×7 Support</span>
        <h1>We’re here to help you file</h1>
        <p>Questions on documents, payments, expert assignment or refunds? Reach the <?= Helper::e($app['name']) ?> desk — we respond quickly during filing season.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-contact-grid">
    <div class="itr-contact-channels">
        <div class="itr-channel">
            <?= Helper::iconBox('mail') ?>
            <div>
                <h3>Email support</h3>
                <p>support@itr-tax.in</p>
                <div class="itr-help">Best for document doubts &amp; account help</div>
            </div>
        </div>
        <div class="itr-channel">
            <?= Helper::iconBox('phone') ?>
            <div>
                <h3>Phone / WhatsApp desk</h3>
                <p>+91 98765 43210</p>
                <div class="itr-help">Mon–Sat · 9 AM – 9 PM IST</div>
            </div>
        </div>
        <div class="itr-channel">
            <?= Helper::iconBox('building') ?>
            <div>
                <h3>Registered office</h3>
                <p>ITR Tax Fintech Pvt Ltd, Bengaluru, India</p>
                <div class="itr-help">Demo address for product showcase</div>
            </div>
        </div>
        <div class="itr-box">
            <h3>Before you write in</h3>
            <ul class="itr-tip-list">
                <li><?= Helper::icon('file') ?> Keep your filing ID handy (Dashboard → Track)</li>
                <li><?= Helper::icon('upload') ?> Attach Form 16 / AIS screenshots if relevant</li>
                <li><?= Helper::icon('users') ?> Mention Self vs Expert mode and plan name</li>
            </ul>
        </div>
    </div>

    <div class="itr-card">
        <div class="itr-card-h"><?= Helper::icon('message') ?> Send us a message</div>
        <div class="itr-card-b">
            <form method="post" action="/contact">
                <?= Helper::csrfField() ?>
                <div class="itr-form-group">
                    <label>Full name</label>
                    <input class="itr-form-control" name="name" required placeholder="Your name">
                </div>
                <div class="itr-form-group">
                    <label>Email</label>
                    <input class="itr-form-control" type="email" name="email" required placeholder="you@email.com">
                </div>
                <div class="itr-form-group">
                    <label>How can we help?</label>
                    <textarea class="itr-form-control" name="message" rows="5" required placeholder="Describe your question — filing status, documents, refund, payment…"></textarea>
                </div>
                <button class="itr-btn itr-btn-primary itr-btn-block" type="submit"><?= Helper::icon('mail') ?> Send message</button>
                <p class="itr-help itr-mt-sm">We aim to reply within one business day. For urgent filing season issues, call the support desk.</p>
            </form>
        </div>
    </div>
</div>
</div></section>
