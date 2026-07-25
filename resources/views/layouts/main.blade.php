<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Income Tax eFiling') | {{ $app['name'] }}</title>
    <meta name="description" content="Prepare and manage ITR online for FY {{ $app['financial_year'] }} (AY {{ $app['assessment_year'] }}) with regime comparison. Self filing or hire a tax expert on ITR Tax.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/itr-tax.css') }}?v={{ @filemtime(public_path('assets/css/itr-tax.css')) ?: time() }}">
    <link rel="icon" href="{{ asset('assets/images/itr-tax-logo.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/itr-tax-logo.png') }}">
</head>
<body class="itr-tax">
@php
    $path = '/'.ltrim(request()->path(), '/');
    if ($path === '/') {
        $path = '/';
    }
    $navActive = function (string $href) use ($path): string {
        if ($href === '/') {
            return $path === '/' ? 'itr-nav-active' : '';
        }
        return ($path === $href || str_starts_with($path, $href.'/')) ? 'itr-nav-active' : '';
    };
@endphp
<header class="itr-header" data-header>
    <div class="itr-header-top">
        <div class="itr-container itr-header-top-inner">
            <span>{!! icon('shield') !!} ITR eFiling · FY {{ $app['financial_year'] }} · AY {{ $app['assessment_year'] }}</span>
            <span class="itr-header-top-right">
                <a href="{{ url('/refund-status') }}">Filing status</a>
                <a href="{{ url('/contact') }}">Support</a>
            </span>
        </div>
    </div>
    <div class="itr-container itr-header-inner">
        <a class="itr-logo" href="{{ url('/') }}">{!! brandLogo('full') !!}</a>
        <button class="itr-nav-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-nav-toggle>
            {!! icon('menu') !!}
        </button>
        <nav class="itr-menu" data-nav-menu>
            <div class="itr-menu-links">
                @foreach([
                    ['/how-it-works', 'How it works'],
                    ['/efiling', 'eFiling'],
                    ['/about', 'About'],
                ] as [$href, $label])
                    <a class="{{ $navActive($href) }}" href="{{ url($href) }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="itr-menu-actions">
                @auth
                    @php
                        $authUser = auth()->user();
                        $dash = $authUser->isAdmin()
                            ? route('admin.dashboard')
                            : ($authUser->isCa() ? route('ca.dashboard') : route('user.dashboard'));
                        $authInitial = strtoupper(substr($authUser->name, 0, 1));
                        $authRole = $authUser->isAdmin() ? 'admin' : ($authUser->isCa() ? 'ca' : 'user');
                    @endphp
                    @unless($authUser->isAdmin())
                        <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ filingStartUrl('assisted') }}">Hire Tax Expert</a>
                        <a class="itr-btn itr-btn-orange itr-btn-sm" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing</a>
                    @endunless
                    <div class="itr-profile-menu itr-profile-menu-header" data-profile-menu>
                        <button class="itr-profile-trigger" type="button" data-profile-trigger aria-expanded="false" aria-haspopup="menu">
                            <span class="itr-top-avatar" aria-hidden="true">{{ $authInitial }}</span>
                            <span class="itr-top-user-text">
                                <strong>{{ $authUser->name }}</strong>
                                <span>{{ roleLabel($authRole) }}</span>
                            </span>
                            <span class="itr-profile-caret" aria-hidden="true">{!! icon('chevron-down') !!}</span>
                        </button>
                        <div class="itr-profile-dropdown" data-profile-dropdown role="menu" hidden>
                            <div class="itr-profile-drop-head">
                                <span class="itr-top-avatar" aria-hidden="true">{{ $authInitial }}</span>
                                <div>
                                    <strong>{{ $authUser->name }}</strong>
                                    <span>{{ $authUser->email }}</span>
                                </div>
                            </div>
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ $dash }}">{!! icon('chart') !!} Dashboard</a>
                            @if($authUser->isUser())
                                <a class="itr-profile-drop-item" role="menuitem" href="{{ route('user.profile') }}">{!! icon('user') !!} Profile</a>
                                <a class="itr-profile-drop-item" role="menuitem" href="{{ route('user.track-list') }}">{!! icon('list') !!} My Filings</a>
                            @elseif($authUser->isAdmin())
                                <a class="itr-profile-drop-item" role="menuitem" href="{{ route('admin.settings') }}">{!! icon('shield') !!} Settings</a>
                            @elseif($authUser->isCa())
                                <a class="itr-profile-drop-item" role="menuitem" href="{{ route('ca.clients') }}">{!! icon('users') !!} My clients</a>
                            @endif
                            <div class="itr-profile-drop-sep"></div>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="itr-profile-drop-item itr-profile-drop-logout" type="submit" role="menuitem">
                                    {!! icon('arrow-right') !!} Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="itr-header-link" href="{{ route('login') }}">Login</a>
                    <a class="itr-header-link" href="{{ route('register') }}">Register</a>
                    <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ filingStartUrl('assisted') }}">Hire Tax Expert</a>
                    <a class="itr-btn itr-btn-orange itr-btn-sm" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Filing</a>
                @endauth
            </div>
        </nav>
    </div>
</header>

@yield('content')

<footer class="itr-footer">
    <div class="itr-footer-cta">
        <div class="itr-container itr-footer-cta-inner">
            <div>
                <p class="itr-footer-cta-kicker">FY {{ $app['financial_year'] }} · AY {{ $app['assessment_year'] }}</p>
                <h2>Ready to file your ITR?</h2>
                <p>Compare regimes, upload Form 16, or Hire a Tax Expert — in one guided flow.</p>
            </div>
            <div class="itr-footer-cta-actions">
                <a class="itr-btn itr-btn-orange" href="{{ filingStartUrl('self') }}">{!! icon('spark') !!} Start Self Filing</a>
                <a class="itr-btn itr-btn-footer-ghost" href="{{ filingStartUrl('assisted') }}">{!! icon('users') !!} Hire a Tax Expert</a>
            </div>
        </div>
    </div>

    <div class="itr-container">
        <div class="itr-footer-grid">
            <div class="itr-footer-brand">
                <div class="itr-logo itr-logo-light">{!! brandLogo('light') !!}</div>
                <p class="itr-footer-desc">{{ $app['tagline'] }}. Self prepare or get expert help for FY {{ $app['financial_year'] }}.</p>
                @php
                    $social = collect($app['social'] ?? [])->filter(fn ($url) => filled($url));
                    $socialMeta = [
                        'facebook' => ['Facebook', 'facebook.svg'],
                        'instagram' => ['Instagram', 'instagram.svg'],
                        'x' => ['X (Twitter)', 'x.svg'],
                        'linkedin' => ['LinkedIn', 'linkedin.svg'],
                        'youtube' => ['YouTube', 'youtube.svg'],
                        'whatsapp' => ['WhatsApp', 'whatsapp.svg'],
                    ];
                @endphp
                @if($social->isNotEmpty())
                <div class="itr-footer-social" aria-label="Social media">
                    <p class="itr-footer-social-label">Follow us</p>
                    <ul class="itr-footer-social-list">
                        @foreach($socialMeta as $key => [$label, $file])
                            @if(!empty($social[$key]))
                                <li>
                                    <a href="{{ $social[$key] }}" target="_blank" rel="noopener noreferrer" title="{{ $label }}" aria-label="{{ $label }}">
                                        <img src="{{ asset('assets/images/social/'.$file) }}" alt="{{ $label }}" width="32" height="32" loading="lazy" decoding="async">
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="itr-footer-contact-row">
                    <a class="itr-footer-contact-item" href="mailto:{{ $app['support_email'] }}">
                        <span class="itr-footer-contact-ico">{!! icon('mail') !!}</span>
                        <span>
                            <small>Email</small>
                            <strong>{{ $app['support_email'] }}</strong>
                        </span>
                    </a>
                    @if(!empty($app['support_phone']))
                    <a class="itr-footer-contact-item" href="tel:{{ preg_replace('/\s+/', '', $app['support_phone']) }}">
                        <span class="itr-footer-contact-ico">{!! icon('phone') !!}</span>
                        <span>
                            <small>Phone</small>
                            <strong>{{ $app['support_phone'] }}</strong>
                        </span>
                    </a>
                    @endif
                    <div class="itr-footer-contact-item itr-footer-contact-static">
                        <span class="itr-footer-contact-ico">{!! icon('clock') !!}</span>
                        <span>
                            <small>Support hours</small>
                            <strong>Mon–Sat · 9 AM – 9 PM IST</strong>
                        </span>
                    </div>
                </div>
            </div>

            <nav class="itr-footer-col" aria-label="Filing">
                <h4>Filing</h4>
                <ul class="itr-footer-links">
                    <li><a href="{{ filingStartUrl('self') }}">Self Filing</a></li>
                    <li><a href="{{ filingStartUrl('assisted') }}">Hire a Tax Expert</a></li>
                    <li><a href="{{ url('/how-it-works') }}">How it works</a></li>
                    <li><a href="{{ url('/pricing') }}">Pricing</a></li>
                    <li><a href="{{ url('/efiling') }}">eFiling overview</a></li>
                </ul>
            </nav>

            <nav class="itr-footer-col" aria-label="Tools">
                <h4>Tools</h4>
                <ul class="itr-footer-links">
                    <li><a href="{{ url('/tax-calculator') }}">Tax calculator</a></li>
                    <li><a href="{{ url('/tools/hra-calculator') }}">HRA calculator</a></li>
                    <li><a href="{{ url('/tools/rent-receipt') }}">Rent receipt</a></li>
                    <li><a href="{{ url('/refund-status') }}">Filing status</a></li>
                    <li><a href="{{ url('/tools') }}">All tools</a></li>
                </ul>
            </nav>

            <nav class="itr-footer-col" aria-label="Company">
                <h4>Company</h4>
                <ul class="itr-footer-links">
                    <li><a href="{{ url('/about') }}">About</a></li>
                    <li><a href="{{ url('/blogs') }}">Guides</a></li>
                    <li><a href="{{ url('/faqs') }}">FAQs</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                    <li><a href="{{ route('login') }}">Login</a></li>
                </ul>
            </nav>
        </div>

        <div class="itr-footer-payments" aria-label="Accepted payment methods">
            <div class="itr-footer-payments-head">
                <p class="itr-footer-payments-label">We accept</p>
                <p class="itr-footer-payments-note">4 secure payment options</p>
            </div>
            <ul class="itr-footer-pay-list itr-footer-pay-list-4">
                <li class="itr-footer-pay-hot">
                    <img src="{{ asset('assets/images/payments/upi.svg') }}" alt="UPI" width="48" height="16" loading="lazy" decoding="async">
                    <span>UPI</span>
                </li>
                <li class="itr-footer-pay-hot">
                    <img src="{{ asset('assets/images/payments/paytm.svg') }}" alt="Paytm" width="56" height="16" loading="lazy" decoding="async">
                    <span>Paytm</span>
                </li>
                <li>
                    <img src="{{ asset('assets/images/payments/visa.svg') }}" alt="Card" width="48" height="16" loading="lazy" decoding="async">
                    <span>Card</span>
                </li>
                <li>
                    <img src="{{ asset('assets/images/payments/netbanking.svg') }}" alt="Net Banking" width="48" height="16" loading="lazy" decoding="async">
                    <span>Netbanking</span>
                </li>
            </ul>
        </div>

        <div class="itr-footer-bottom">
            <div class="itr-footer-bottom-main">
                <p class="itr-footer-legal-line">© {{ date('Y') }} {{ $app['name'] }}. All rights reserved.</p>
                <div class="itr-footer-meta-links">
                    <a href="{{ url('/privacy') }}">Privacy</a>
                    <a href="{{ url('/terms') }}">Terms</a>
                    <a href="{{ url('/contact') }}">Support</a>
                </div>
            </div>
            <p class="itr-footer-disclaimer">{{ $app['name'] }} is not affiliated with the Income Tax Department of India. Figures shown are simplified estimates, not tax advice. Official e-filing and e-verification stay on the Income Tax portal.</p>
        </div>
    </div>
</footer>
<script src="{{ asset('assets/js/itr-tax.js') }}?v={{ @filemtime(public_path('assets/js/itr-tax.js')) ?: time() }}"></script>
@include('partials.chatbot')
</body>
</html>
