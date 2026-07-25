<?php
use App\Core\Helper;
$oldPrices = ['Basic' => 5000, 'Standard' => 7598, 'Premium' => 11998];
?>
<section class="itr-hero itr-hero-ct">
    <div class="itr-container">
        <div class="itr-trust-bar">
            <div class="itr-trust-item"><strong>8M+</strong><span>Users trust us*</span></div>
            <div class="itr-trust-item"><strong>★ 4.6</strong><span>App rating*</span></div>
            <div class="itr-trust-item"><strong>₹5346 Cr+</strong><span>Refunds delivered*</span></div>
            <div class="itr-trust-item"><strong>AY <?= Helper::e($app['assessment_year']) ?></strong><span>Filing season open</span></div>
        </div>

        <div class="itr-hero-copy itr-text-center itr-hero-center">
            <p class="itr-hero-eyebrow">Income Tax eFiling · FY <?= Helper::e($app['financial_year']) ?></p>
            <h1>File ITR in minutes with <em>100% Accuracy</em></h1>
            <p class="itr-lead itr-lead-center">Maximum Tax Refund, Guaranteed. Upload Form 16 — get tax summary, old vs new regime comparison, and expert filing when you need it.</p>
        </div>

        <div class="itr-dual-cards">
            <div class="itr-dual-card">
                <div class="itr-dual-top">
                    <?= Helper::iconBox('spark') ?>
                    <div>
                        <span class="itr-tag itr-tag-orange">Self Filing</span>
                        <h3>File your taxes in 3 simple steps</h3>
                    </div>
                </div>
                <ul class="itr-check-list">
                    <li><?= Helper::icon('check') ?> Upload Form 16 / AIS / 26AS</li>
                    <li><?= Helper::icon('check') ?> Review tax summary &amp; regime</li>
                    <li><?= Helper::icon('check') ?> File &amp; get acknowledgement</li>
                </ul>
                <a class="itr-btn itr-btn-primary itr-btn-block" href="/register"><?= Helper::icon('spark') ?> Start Filing Now</a>
            </div>
            <div class="itr-dual-card itr-dual-hot">
                <div class="itr-dual-top">
                    <?= Helper::iconBox('users') ?>
                    <div>
                        <span class="itr-tag">Hire an Expert</span>
                        <h3>ITR filed in 24 hrs by tax experts</h3>
                    </div>
                </div>
                <ul class="itr-check-list">
                    <li><?= Helper::icon('check') ?> Instant expert match after pay</li>
                    <li><?= Helper::icon('check') ?> Capital gains / F&amp;O / NRI support</li>
                    <li><?= Helper::icon('check') ?> Live tracking till ACK</li>
                </ul>
                <a class="itr-btn itr-btn-orange itr-btn-block" href="/pricing"><?= Helper::icon('users') ?> Hire an Expert</a>
            </div>
        </div>
        <p class="itr-text-center itr-hero-note-show">*Demo marketing stats for ClearTax-style UI parity</p>
    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>India’s most trusted tax filing platform for</h2>
            <p>Choose the journey that matches your income profile.</p>
        </div>
        <div class="itr-grid-3">
            <div class="itr-audience"><?= Helper::iconBox('user') ?><h3>Salaried Professionals</h3><p>Simple, accurate filing for every Form 16 taxpayer.</p><a href="/register">Start Filing <?= Helper::icon('arrow-right') ?></a></div>
            <div class="itr-audience"><?= Helper::iconBox('chart') ?><h3>Investors &amp; Traders</h3><p>Capital gains from MFs, stocks &amp; crypto — handled clearly.</p><a href="/pricing">Start Filing <?= Helper::icon('arrow-right') ?></a></div>
            <div class="itr-audience"><?= Helper::iconBox('briefcase') ?><h3>Freelancers &amp; Professionals</h3><p>Consulting fees, TDS and advance tax — all managed.</p><a href="/pricing">Start Filing <?= Helper::icon('arrow-right') ?></a></div>
            <div class="itr-audience"><?= Helper::iconBox('spark') ?><h3>Advanced Traders</h3><p>F&amp;O, intraday or complex capital gains — hire an expert.</p><a href="/pricing">Hire an Expert <?= Helper::icon('arrow-right') ?></a></div>
            <div class="itr-audience"><?= Helper::iconBox('shield') ?><h3>NRIs &amp; RSU/ESOP holders</h3><p>Foreign income &amp; Schedule FA support with specialists.</p><a href="/pricing">Hire an Expert <?= Helper::icon('arrow-right') ?></a></div>
            <div class="itr-audience"><?= Helper::iconBox('wallet') ?><h3>Affluent Investors</h3><p>Salary to global income — year-round specialist support.</p><a href="/pricing">Hire an Expert <?= Helper::icon('arrow-right') ?></a></div>
        </div>
    </div>
</section>

<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Why choose <?= Helper::e($app['name']) ?> to file your taxes</h2>
        </div>
        <div class="itr-grid-3">
            <div class="itr-feature-mini"><?= Helper::iconBox('rupee') ?><h3>Maximum Tax Refund</h3><p>Whether you file yourself or with experts, we help identify every deduction you qualify for.</p></div>
            <div class="itr-feature-mini"><?= Helper::iconBox('clock') ?><h3>24×7 Support</h3><p>Tax professionals guide you via chat, phone or email through every step of filing.</p></div>
            <div class="itr-feature-mini"><?= Helper::iconBox('check-circle') ?><h3>100% Accuracy</h3><p>Error checks catch mistakes and missed entries — so you can file confidently.</p></div>
        </div>
    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Tax filing, as easy as it gets</h2>
            <p>And as accurate as it needs to be.</p>
        </div>
        <div class="itr-feature-grid">
            <div class="itr-feat"><?= Helper::iconBox('upload') ?><h3>Form 16 pre-filled flow</h3><p>Upload once — income details flow into your tax summary.</p></div>
            <div class="itr-feat"><?= Helper::iconBox('chart') ?><h3>Old vs New regime</h3><p>Side-by-side comparison so you pick the better refund outcome.</p></div>
            <div class="itr-feat"><?= Helper::iconBox('list') ?><h3>AIS / 26AS checks</h3><p>Catch TDS mismatches before you submit and avoid refund delays.</p></div>
            <div class="itr-feat"><?= Helper::iconBox('file') ?><h3>Right ITR form</h3><p>ITR-1 to ITR-4 guidance based on your income profile.</p></div>
            <div class="itr-feat"><?= Helper::iconBox('users') ?><h3>Expert assisted filing</h3><p>Dedicated CA match, notes, doc requests and ACK download.</p></div>
            <div class="itr-feat"><?= Helper::iconBox('shield') ?><h3>Secure document vault</h3><p>Role-based access for you, your CA and admin only.</p></div>
        </div>
    </div>
</section>

<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Need expert help? India’s top tax experts are one click away</h2>
            <p>Assisted filing plans for FY <?= Helper::e($app['financial_year']) ?></p>
        </div>
        <div class="itr-grid-3">
            <?php foreach ($plans as $i => $plan): $features = json_decode($plan['features'] ?? '[]', true) ?: []; ?>
            <div class="itr-plan <?= $i === 1 ? 'itr-hot' : '' ?>">
                <?php if ($i === 1): ?><span class="itr-tag">Investors Favourite</span>
                <?php elseif ($i === 0): ?><span class="itr-tag itr-tag-orange">Basic</span>
                <?php else: ?><span class="itr-tag">Premium</span><?php endif; ?>
                <h3 class="itr-plan-name"><?= Helper::e($plan['name']) ?></h3>
                <div class="itr-price"><?= Helper::money($plan['price']) ?><?php if (!empty($oldPrices[$plan['name']])): ?><span class="itr-old"><?= Helper::money($oldPrices[$plan['name']]) ?></span><?php endif; ?></div>
                <p><?= Helper::e($plan['description']) ?></p>
                <ul><?php foreach (array_slice($features, 0, 5) as $f): ?><li><?= Helper::icon('check') ?> <?= Helper::e($f) ?></li><?php endforeach; ?></ul>
                <a class="itr-btn <?= $i === 1 ? 'itr-btn-primary' : 'itr-btn-outline' ?> itr-btn-block" href="/register">Buy now</a>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="itr-text-center itr-mt-md"><a class="itr-link-more" href="/pricing">Explore all assisted plans <?= Helper::icon('arrow-right') ?></a></p>
    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title"><h2>File ITR in 3 simple steps</h2></div>
        <div class="itr-grid-3">
            <div class="itr-box itr-process-card"><span class="itr-process-num">01</span><h3>Upload documents</h3><p>Form 16, AIS, 26AS and investment proofs — securely uploaded.</p></div>
            <div class="itr-box itr-process-card"><span class="itr-process-num">02</span><h3>Review &amp; pay</h3><p>Check summary, compare regimes, apply coupon, complete payment.</p></div>
            <div class="itr-box itr-process-card"><span class="itr-process-num">03</span><h3>File / track ACK</h3><p>Self-file instantly or let an expert file — download acknowledgement.</p></div>
        </div>
        <div class="itr-guarantee itr-mt-lg">
            <div>
                <h2>100% Accurate ITR Filed</h2>
                <p>Maximum Tax Refund, Guaranteed — demo accuracy promise for this showcase portal.</p>
            </div>
            <div class="itr-cta-actions">
                <a class="itr-btn itr-btn-orange" href="/register"><?= Helper::icon('spark') ?> Start Filing Now</a>
                <a class="itr-btn itr-btn-white" href="/tax-calculator"><?= Helper::icon('chart') ?> Tax Calculator</a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($blogs)): ?>
<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Guides to file smarter</h2>
            <p>ClearTax-style help articles for FY <?= Helper::e($app['financial_year']) ?></p>
        </div>
        <div class="itr-grid-3">
            <?php foreach ($blogs as $blog): ?>
            <article class="itr-blog-card">
                <div class="itr-blog-cover"><span><?= Helper::icon('pen') ?> Guide</span></div>
                <div class="itr-blog-body">
                    <div class="itr-blog-meta"><?= Helper::formatDate($blog['published_at']) ?></div>
                    <h3><a href="/blogs/<?= Helper::e($blog['slug']) ?>"><?= Helper::e($blog['title']) ?></a></h3>
                    <p><?= Helper::e($blog['excerpt']) ?></p>
                    <a class="itr-link-more" href="/blogs/<?= Helper::e($blog['slug']) ?>">Read <?= Helper::icon('arrow-right') ?></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="itr-section">
    <div class="itr-container itr-container-narrow">
        <div class="itr-section-title"><h2>Frequently Asked Questions</h2></div>
        <?php foreach ($faqs as $faq): ?>
        <details class="itr-faq">
            <summary><?= Helper::e($faq['question']) ?></summary>
            <p><?= nl2br(Helper::e($faq['answer'])) ?></p>
        </details>
        <?php endforeach; ?>
        <p class="itr-text-center itr-mt-md"><a class="itr-link-more" href="/faqs">View all FAQs <?= Helper::icon('arrow-right') ?></a></p>
    </div>
</section>
<?php endif; ?>
