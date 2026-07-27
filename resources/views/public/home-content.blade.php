<section class="itr-hero-banner">
    <div class="itr-hero-banner-media" aria-hidden="true">
        <img src="{{ asset('assets/images/itr-tax-hero.png') }}?v=2" alt="" width="1600" height="900" decoding="async" fetchpriority="high">
        <div class="itr-hero-banner-shade"></div>
    </div>
    <div class="itr-container itr-hero-banner-inner">
        <div class="itr-hero-banner-copy">
            <p class="itr-hero-eyebrow">Income Tax eFiling · FY {{ $app['financial_year'] }}</p>
            <h1>File ITR with <em>clear regime comparison</em></h1>
            <p class="itr-lead itr-lead-banner">Upload Form 16, enter your figures, compare old vs new regime, then self-prepare or Hire a Tax Expert.</p>
            <div class="itr-hero-banner-cta">
                <a class="itr-btn itr-btn-orange itr-btn-lg" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
                <a class="itr-btn itr-btn-outline itr-btn-lg" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
            </div>
            <div class="itr-hero-mini-trust">
                <span>{!! icon('check-circle') !!} Document vault</span>
                <span>{!! icon('clock') !!} Target 24 hr expert review</span>
                <span>{!! icon('shield') !!} E-verify in 30 days</span>
            </div>
        </div>
    </div>
</section>

<section class="itr-section itr-path-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Choose how you want to file</h2>
            <p>Self Filing for Form 16 cases — or Hire a Tax Expert when income gets complex.</p>
        </div>
        <div class="itr-dual-cards">
            <div class="itr-dual-card itr-rise">
                <div class="itr-dual-top">
                    {!! iconBox('spark') !!}
                    <div>
                        <span class="itr-tag itr-tag-orange">Self Filing</span>
                        <h3>Prepare your return simply</h3>
                    </div>
                </div>
                <ul class="itr-check-list">
                    @forelse(($processSelf ?? collect())->take(4) as $step)
                        <li>{!! icon('check') !!} {{ $step->title }}</li>
                    @empty
                        <li>{!! icon('check') !!} Upload Form 16 / AIS / 26AS</li>
                        <li>{!! icon('check') !!} Enter figures &amp; compare regimes</li>
                        <li>{!! icon('check') !!} Generate filing reference &amp; e-verify tips</li>
                    @endforelse
                </ul>
                <a class="itr-btn itr-btn-primary itr-btn-block" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
            </div>
            <div class="itr-dual-card itr-dual-hot itr-rise itr-rise-delay">
                <div class="itr-dual-top">
                    {!! iconBox('users') !!}
                    <div>
                        <span class="itr-tag">Hire a Tax Expert</span>
                        <h3>Tax expert review after payment</h3>
                    </div>
                </div>
                <ul class="itr-check-list">
                    @forelse(($processAssisted ?? collect())->take(4) as $step)
                        <li>{!! icon('check') !!} {{ $step->title }}</li>
                    @empty
                        <li>{!! icon('check') !!} Expert assigned after payment</li>
                        <li>{!! icon('check') !!} Capital gains / F&amp;O / NRI support</li>
                        <li>{!! icon('check') !!} Tracking till expert uploads ACK</li>
                    @endforelse
                </ul>
                <a class="itr-btn itr-btn-orange itr-btn-block" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
            </div>
        </div>

    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Built for common Indian income profiles</h2>
            <p>Choose the journey that matches your income this year.</p>
        </div>
        <div class="itr-grid-3">
            <div class="itr-audience">{!! iconBox('user') !!}<h3>Salaried Professionals</h3><p>Guided filing for Form 16 taxpayers with salary and interest income.</p><a href="{{ filingStartUrl('self') }}">Start Filing {!! icon('arrow-right') !!}</a></div>
            <div class="itr-audience">{!! iconBox('chart') !!}<h3>Investors &amp; Traders</h3><p>Capital gains from MFs, stocks &amp; crypto — expert-assisted when needed.</p><a href="{{ filingStartUrl('assisted') }}">Hire a Tax Expert {!! icon('arrow-right') !!}</a></div>
            <div class="itr-audience">{!! iconBox('briefcase') !!}<h3>Freelancers &amp; Professionals</h3><p>Consulting fees, TDS and advance tax — managed with expert help.</p><a href="{{ filingStartUrl('assisted') }}">Hire a Tax Expert {!! icon('arrow-right') !!}</a></div>
            <div class="itr-audience">{!! iconBox('spark') !!}<h3>Advanced Traders</h3><p>F&amp;O, intraday or complex capital gains — Hire a Tax Expert.</p><a href="{{ filingStartUrl('assisted') }}">Hire a Tax Expert {!! icon('arrow-right') !!}</a></div>
            <div class="itr-audience">{!! iconBox('shield') !!}<h3>NRIs &amp; RSU/ESOP holders</h3><p>Foreign income &amp; Schedule FA support with specialists.</p><a href="{{ filingStartUrl('assisted') }}">Hire a Tax Expert {!! icon('arrow-right') !!}</a></div>
            <div class="itr-audience">{!! iconBox('wallet') !!}<h3>Affluent Investors</h3><p>Salary to global income — specialist-assisted filing support.</p><a href="{{ filingStartUrl('assisted') }}">Hire a Tax Expert {!! icon('arrow-right') !!}</a></div>
        </div>
    </div>
</section>

<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Why choose {{ $app['name'] }} to file your taxes</h2>
        </div>
        <div class="itr-grid-3">
            <div class="itr-feature-mini">{!! iconBox('rupee') !!}<h3>Regime comparison</h3><p>See estimated tax under old vs new regime from the figures you enter — including §87A where applicable.</p></div>
            <div class="itr-feature-mini">{!! iconBox('clock') !!}<h3>Email &amp; phone support</h3><p>Support desk help during filing season (see Contact for hours). Expert notes inside your filing for assisted plans.</p></div>
            <div class="itr-feature-mini">{!! iconBox('check-circle') !!}<h3>Guided checklists</h3><p>Document and field reminders before you confirm — you remain responsible for final accuracy.</p></div>
        </div>
    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>What you can do on {{ $app['name'] }}</h2>
            <p>Clear steps from documents to acknowledgement tracking.</p>
        </div>
        <div class="itr-feature-grid">
            <div class="itr-feat">{!! iconBox('upload') !!}<h3>Form 16 upload</h3><p>Upload Form 16, then enter Part B figures on Tax Summary.</p></div>
            <div class="itr-feat">{!! iconBox('chart') !!}<h3>Old vs New regime</h3><p>Side-by-side estimated tax so you can choose the better option.</p></div>
            <div class="itr-feat">{!! iconBox('list') !!}<h3>AIS / 26AS uploads</h3><p>Keep statements in your vault and reconcile TDS yourself before filing.</p></div>
            <div class="itr-feat">{!! iconBox('file') !!}<h3>ITR form guidance</h3><p>ITR-1 to ITR-4 suggestions based on the income profile you select.</p></div>
            <div class="itr-feat">{!! iconBox('users') !!}<h3>Tax Expert Assisted filing</h3><p>Tax expert match after payment, notes, doc requests and ACK upload.</p></div>
            <div class="itr-feat">{!! iconBox('shield') !!}<h3>Role-based document access</h3><p>Documents visible to you, your assigned tax expert and admin only.</p></div>
        </div>
    </div>
</section>

<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Need expert help?</h2>
            <p>Assisted filing plans for FY {{ $app['financial_year'] }} — matched with an available tax expert after payment</p>
        </div>
        <div class="itr-grid-3">
            @foreach($plans as $i => $plan)
            @php $features = $plan->featuresList() ?: []; @endphp
            <div class="itr-plan {{ $i === 1 ? 'itr-hot' : '' }}">
                @if($i === 1)
                    <span class="itr-tag">Popular</span>
                @elseif($i === 0)
                    <span class="itr-tag itr-tag-orange">Starter</span>
                @else
                    <span class="itr-tag">Premium</span>
                @endif
                <h3 class="itr-plan-name">{{ $plan->name }}</h3>
                <div class="itr-price">
                    {{ money($plan->price) }}
                </div>
                <p>{{ $plan->description }}</p>
                <ul>
                    @foreach(array_slice($features, 0, 5) as $f)
                        <li>{!! icon('check') !!} {{ $f }}</li>
                    @endforeach
                </ul>
                <a class="itr-btn {{ $i === 1 ? 'itr-btn-primary' : 'itr-btn-outline' }} itr-btn-block" href="{{ filingStartUrl('assisted', (int) $plan->id) }}">Get started</a>
            </div>
            @endforeach
        </div>
        <p class="itr-text-center itr-mt-md"><a class="itr-link-more" href="{{ url('/pricing') }}">Explore all assisted plans {!! icon('arrow-right') !!}</a></p>
    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Simple process — easy to follow</h2>
            <p>Overview of the full journey, or switch to Self Filing / Tax Expert steps.</p>
        </div>
        <div class="itr-process-tabs" role="tablist" aria-label="Filing process">
            <button type="button" class="is-active" data-process-tab="both" role="tab" aria-selected="true">Overview</button>
            <button type="button" data-process-tab="self" role="tab" aria-selected="false">Self Filing</button>
            <button type="button" data-process-tab="assisted" role="tab" aria-selected="false">Tax Expert</button>
        </div>
        <div class="itr-process-panels">
            <div class="itr-process-panel is-active" data-process-panel="both">
                @include('partials.process-steps', ['steps' => $processBoth ?? collect(), 'processMode' => 'both', 'class' => 'itr-process-grid itr-process-grid-4'])
            </div>
            <div class="itr-process-panel" data-process-panel="self" hidden>
                @include('partials.process-steps', ['steps' => $processSelf ?? collect(), 'processMode' => 'self', 'class' => 'itr-process-grid itr-process-grid-4'])
                <p class="itr-text-center itr-mt-md"><a class="itr-btn itr-btn-primary" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a></p>
            </div>
            <div class="itr-process-panel" data-process-panel="assisted" hidden>
                @include('partials.process-steps', ['steps' => $processAssisted ?? collect(), 'processMode' => 'assisted', 'class' => 'itr-process-grid itr-process-grid-4'])
                <p class="itr-text-center itr-mt-md"><a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a></p>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var tabs = document.querySelectorAll('[data-process-tab]');
    var panels = document.querySelectorAll('[data-process-panel]');
    if (!tabs.length) return;
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-process-tab');
            tabs.forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) {
                var on = p.getAttribute('data-process-panel') === key;
                p.classList.toggle('is-active', on);
                p.hidden = !on;
            });
        });
    });
})();
</script>

<section class="itr-guarantee-band" aria-labelledby="itr-guarantee-title">
    <div class="itr-container">
        <div class="itr-guarantee">
            <div class="itr-guarantee-copy">
                <p class="itr-guarantee-kicker">FY {{ $app['financial_year'] }} · AY {{ $app['assessment_year'] }}</p>
                <h2 id="itr-guarantee-title">Ready to prepare your return?</h2>
                <p>Clear regime summary, optional expert review, and tracking till acknowledgement. Tax figures shown are estimates - not formal tax advice.</p>
                <ul class="itr-guarantee-points">
                    <li>{!! icon('check') !!} Self Filing for Form 16 cases</li>
                    <li>{!! icon('check') !!} Expert assist after payment</li>
                    <li>{!! icon('check') !!} E-verify guidance (30 days)</li>
                </ul>
            </div>
            <div class="itr-guarantee-actions">
                <a class="itr-btn itr-btn-orange itr-btn-lg" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
                <a class="itr-btn itr-btn-white itr-btn-lg" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
                <a class="itr-guarantee-link" href="{{ url('/how-it-works') }}">See how it works {!! icon('arrow-right') !!}</a>
            </div>
        </div>
    </div>
</section>

@if($blogs->isNotEmpty())
<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Guides to file smarter</h2>
            <p>Help articles for FY {{ $app['financial_year'] }}</p>
        </div>
        <div class="itr-grid-3">
            @foreach($blogs as $blog)
                @include('partials.blog-card', ['blog' => $blog])
            @endforeach
        </div>
    </div>
</section>
@endif

@if($faqs->isNotEmpty())
<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title">
            <h2>Frequently asked questions</h2>
        </div>
        @foreach($faqs as $faq)
        <details class="itr-faq">
            <summary>
                <span class="itr-faq-qico">{!! icon('help') !!}</span>
                <span class="itr-faq-qtext">{{ $faq->question }}</span>
                <span class="itr-faq-toggle">{!! icon('chevron-down') !!}</span>
            </summary>
            <div class="itr-faq-body"><p>{{ $faq->answer }}</p></div>
        </details>
        @endforeach
        <p class="itr-text-center itr-mt-md"><a class="itr-link-more" href="{{ url('/faqs') }}">View all FAQs {!! icon('arrow-right') !!}</a></p>
    </div>
</section>
@endif
