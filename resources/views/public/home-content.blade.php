<section class="itr-hero-banner">
    <div class="itr-hero-banner-media" aria-hidden="true">
        <img src="{{ asset('assets/images/itr-tax-hero.png') }}?v=3" alt="" width="1600" height="900" decoding="async" fetchpriority="high">
        <div class="itr-hero-banner-shade"></div>
    </div>
    <div class="itr-container itr-hero-banner-inner">
        <div class="itr-hero-banner-copy itr-fade-up">
            <p class="itr-hero-eyebrow">Income Tax eFiling · FY {{ $app['financial_year'] }}</p>
            <h1>File ITR with <em>clear regime comparison</em></h1>
            <p class="itr-lead itr-lead-banner">Upload Form 16, compare old vs new regime, then self-prepare or hire a tax expert.</p>
            <div class="itr-hero-banner-cta">
                <a class="itr-btn itr-btn-orange itr-btn-lg" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
                <a class="itr-btn itr-btn-outline itr-btn-lg" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
            </div>
            <div class="itr-hero-mini-trust">
                <span>{!! icon('check-circle') !!} Document vault</span>
                <span>{!! icon('clock') !!} Target 24 hr review</span>
                <span>{!! icon('shield') !!} E-verify in 30 days</span>
            </div>
        </div>
    </div>
</section>

<section class="itr-section itr-path-section">
    <div class="itr-container">
        <div class="itr-section-title itr-reveal">
            <h2>Choose how you want to file</h2>
            <p>One clear path for Form 16 cases — or expert help when income gets complex.</p>
        </div>
        <div class="itr-dual-cards">
            <div class="itr-dual-card itr-reveal">
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
                        <li>{!! icon('check') !!} Confirm &amp; e-verify tips</li>
                    @endforelse
                </ul>
                <a class="itr-btn itr-btn-primary itr-btn-block" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
            </div>
            <div class="itr-dual-card itr-dual-hot itr-reveal" style="--reveal-delay:.08s">
                <div class="itr-dual-top">
                    {!! iconBox('users') !!}
                    <div>
                        <span class="itr-tag">Hire a Tax Expert</span>
                        <h3>Expert review after payment</h3>
                    </div>
                </div>
                <ul class="itr-check-list">
                    @forelse(($processAssisted ?? collect())->take(4) as $step)
                        <li>{!! icon('check') !!} {{ $step->title }}</li>
                    @empty
                        <li>{!! icon('check') !!} Documents &amp; plan checkout</li>
                        <li>{!! icon('check') !!} Tax expert match</li>
                        <li>{!! icon('check') !!} Approve &amp; get ACK</li>
                    @endforelse
                </ul>
                <a class="itr-btn itr-btn-orange itr-btn-block" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
            </div>
        </div>
    </div>
</section>

<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title itr-reveal">
            <h2>Why file with {{ $app['name'] }}</h2>
            <p>Clear estimates, guided steps, and optional expert help.</p>
        </div>
        <div class="itr-grid-3">
            <div class="itr-feature-mini itr-reveal">{!! iconBox('rupee') !!}<h3>Regime comparison</h3><p>Old vs new estimated tax from the figures you enter — including §87A where applicable.</p></div>
            <div class="itr-feature-mini itr-reveal" style="--reveal-delay:.06s">{!! iconBox('file') !!}<h3>Form 16 to summary</h3><p>Upload documents, enter Part B figures, and confirm before you file or pay.</p></div>
            <div class="itr-feature-mini itr-reveal" style="--reveal-delay:.12s">{!! iconBox('users') !!}<h3>Expert when you need it</h3><p>Assisted plans include review, notes, and acknowledgement upload after payment.</p></div>
        </div>
    </div>
</section>

<section class="itr-section">
    <div class="itr-container">
        <div class="itr-section-title itr-reveal">
            <h2>Simple process</h2>
            <p>Switch between overview, Self Filing, and Tax Expert steps.</p>
        </div>
        <div class="itr-process-tabs itr-reveal" role="tablist" aria-label="Filing process">
            <button type="button" class="is-active" data-process-tab="both" role="tab" aria-selected="true">Overview</button>
            <button type="button" data-process-tab="self" role="tab" aria-selected="false">Self Filing</button>
            <button type="button" data-process-tab="assisted" role="tab" aria-selected="false">Tax Expert</button>
        </div>
        <div class="itr-process-panels itr-reveal">
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

<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title itr-reveal">
            <h2>Assisted plans</h2>
            <p>FY {{ $app['financial_year'] }} — matched with an available tax expert after payment.</p>
        </div>
        <div class="itr-grid-3">
            @foreach($plans as $i => $plan)
            @php $features = $plan->featuresList() ?: []; @endphp
            <div class="itr-plan itr-reveal {{ $i === 1 ? 'itr-hot' : '' }}" style="--reveal-delay:{{ $i * 0.06 }}s">
                @if($i === 1)
                    <span class="itr-tag">Popular</span>
                @elseif($i === 0)
                    <span class="itr-tag itr-tag-orange">Starter</span>
                @else
                    <span class="itr-tag">Premium</span>
                @endif
                <h3 class="itr-plan-name">{{ $plan->name }}</h3>
                <div class="itr-price">{{ money($plan->price) }}</div>
                <p>{{ $plan->description }}</p>
                <ul>
                    @foreach(array_slice($features, 0, 4) as $f)
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

<section class="itr-guarantee-band" aria-labelledby="itr-guarantee-title">
    <div class="itr-container">
        <div class="itr-guarantee itr-reveal">
            <div class="itr-guarantee-copy">
                <p class="itr-guarantee-kicker">FY {{ $app['financial_year'] }} · AY {{ $app['assessment_year'] }}</p>
                <h2 id="itr-guarantee-title">Ready to prepare your return?</h2>
                <p>Clear regime summary, optional expert review, and tracking till acknowledgement.</p>
            </div>
            <div class="itr-guarantee-actions">
                <a class="itr-btn itr-btn-orange itr-btn-lg" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
                <a class="itr-btn itr-btn-white itr-btn-lg" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
                <a class="itr-guarantee-link" href="{{ url('/how-it-works') }}">See how it works {!! icon('arrow-right') !!}</a>
            </div>
        </div>
    </div>
</section>

@if($faqs->isNotEmpty())
<section class="itr-section">
    <div class="itr-container itr-container-narrow">
        <div class="itr-section-title itr-reveal">
            <h2>Frequently asked questions</h2>
        </div>
        @foreach($faqs as $faq)
        <details class="itr-faq itr-reveal">
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

@if($blogs->isNotEmpty())
<section class="itr-section itr-alt">
    <div class="itr-container">
        <div class="itr-section-title itr-reveal">
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
