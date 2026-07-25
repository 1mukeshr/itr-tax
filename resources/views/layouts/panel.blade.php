@php
    $role = auth()->user()->role ?? 'user';
    $menu = match ($role) {
        'admin' => [
            'Main' => [
                ['Dashboard', route('admin.dashboard'), 'chart'],
                ['Settings', route('admin.settings'), 'shield'],
            ],
            'Orders' => [
                ['All Orders', route('admin.orders'), 'file', ['status' => null]],
                ['Pending ITR', route('admin.orders', ['status' => 'pending']), 'list', ['status' => 'pending']],
                ['Complete ITR', route('admin.orders', ['status' => 'complete']), 'check', ['status' => 'complete']],
                ['Payments', route('admin.payments'), 'wallet'],
            ],
            'People' => [
                ['Users', route('admin.users'), 'user'],
                ['Tax Experts', route('admin.cas'), 'users'],
            ],
        ],
        'ca' => [
            'Work' => [
                ['Dashboard', route('ca.dashboard'), 'chart'],
                ['Clients', route('ca.clients'), 'users'],
                ['Chat', route('chat.index'), 'message'],
            ],
        ],
        default => [
            'Filing' => [
                ['Dashboard', route('user.dashboard'), 'chart'],
                ['Start Filing', route('user.choose-service'), 'spark'],
                ['My Filings', route('user.track-list'), 'list'],
                ['Chat', route('chat.index'), 'message'],
                ['Profile', route('user.profile'), 'user'],
            ],
        ],
    };
    $path = '/'.ltrim(request()->path(), '/');
    $statusFilter = request()->query('status');
    $sideTag = match ($role) {
        'admin' => 'Admin',
        'ca' => 'Tax Expert',
        default => 'Filing',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel') | {{ $app['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/itr-tax.css') }}?v={{ @filemtime(public_path('assets/css/itr-tax.css')) ?: time() }}">
    <link rel="icon" href="{{ asset('assets/images/itr-tax-logo.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/itr-tax-logo.png') }}">
</head>
<body class="itr-tax itr-panel-body">
<div class="itr-wrap" data-panel-wrap>
    <div class="itr-side-backdrop" data-side-backdrop hidden></div>
    <aside class="itr-side" data-side id="panelSide" aria-label="Sidebar">
        <button class="itr-side-rail" type="button" data-side-toggle aria-controls="panelSide" aria-expanded="true" aria-label="Collapse sidebar" title="Collapse sidebar">
            <span class="itr-side-rail-ico itr-side-rail-collapse" aria-hidden="true">{!! icon('chevron-left') !!}</span>
            <span class="itr-side-rail-ico itr-side-rail-expand" aria-hidden="true">{!! icon('chevron-right') !!}</span>
        </button>
        <div class="itr-side-nav">
            <div class="itr-side-brand-row">
                <a class="itr-logo itr-logo-side" href="{{ url('/') }}" aria-label="{{ $app['name'] }} — {{ $sideTag }}">
                    <span class="itr-side-logo-mark" aria-hidden="true">
                        <img src="{{ asset('assets/images/itr-tax-logo.svg') }}" width="40" height="40" alt="" decoding="async">
                    </span>
                    <span class="itr-side-brand-text">
                        <strong>{{ $app['name'] }}</strong>
                        <small>{{ $sideTag }}</small>
                    </span>
                </a>
                <button class="itr-side-close" type="button" data-side-close aria-label="Close menu">{!! icon('x') !!}</button>
            </div>
            @foreach($menu as $label => $items)
                <div class="label">{{ $label }}</div>
                <div class="itr-side-group">
                    @foreach($items as $item)
                        @php
                            [$name, $href, $ico, $match] = array_pad($item, 4, null);
                            $hrefPath = parse_url($href, PHP_URL_PATH) ?: $href;
                            $roots = [route('admin.dashboard', [], false), route('ca.dashboard', [], false), route('user.dashboard', [], false)];
                            $on = $path === $hrefPath || (! in_array($hrefPath, $roots, true) && str_starts_with($path, rtrim($hrefPath, '/')));
                            // Order filter links: only one of All / Pending / Complete active.
                            if (is_array($match) && array_key_exists('status', $match)) {
                                $want = $match['status'];
                                $on = $path === $hrefPath && (
                                    ($want === null && ($statusFilter === null || $statusFilter === ''))
                                    || (string) $statusFilter === (string) $want
                                );
                            }
                        @endphp
                        <a class="{{ $on ? 'itr-active' : '' }}" href="{{ $href }}" title="{{ $name }}">
                            <span class="itr-side-ico">{!! icon($ico) !!}</span>
                            <span class="itr-side-link-text">{{ $name }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="itr-side-foot">
            <div class="itr-side-user">
                <span class="itr-side-user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="itr-side-user-meta">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ roleLabel($role) }}</span>
                </div>
            </div>
            <form method="post" action="{{ route('logout') }}" class="itr-logout-form">
                @csrf
                <button type="submit" class="itr-side-logout" title="Logout">
                    {!! icon('arrow-right') !!}
                    <span class="itr-side-link-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>
    <div class="itr-main">
        <header class="itr-top {{ $role === 'admin' ? 'itr-top-admin' : '' }}">
            <div class="itr-top-left">
                <button class="itr-side-toggle itr-side-toggle-mobile" type="button" data-side-toggle aria-controls="panelSide" aria-expanded="false" aria-label="Open sidebar">
                    {!! icon('menu') !!}
                </button>
                <div>
                    <div class="itr-top-title">@yield('title', 'Panel')</div>
                </div>
            </div>
            @if($role === 'admin')
                <form class="itr-admin-search" method="get" action="{{ route('admin.orders') }}" role="search">
                    <span class="itr-admin-search-ico" aria-hidden="true">{!! icon('search') !!}</span>
                    <input
                        class="itr-admin-search-input"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Order ID, client name, or PAN"
                        autocomplete="off"
                        aria-label="Search orders by ID, name, or PAN"
                    >
                    @if(request()->filled('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request()->filled('q'))
                        <a class="itr-admin-search-clear" href="{{ route('admin.orders', array_filter(['status' => request('status') ?: null])) }}" aria-label="Clear search" title="Clear">{!! icon('x') !!}</a>
                    @endif
                    <button class="itr-admin-search-btn" type="submit" aria-label="Search">{!! icon('arrow-right') !!}</button>
                </form>
            @endif
            <div class="itr-top-right">
                @if($role === 'admin')
                    <a class="itr-btn itr-btn-orange itr-btn-sm itr-top-cta" href="{{ route('admin.orders', ['status' => 'paid']) }}">Assign tax experts</a>
                @elseif($role === 'user')
                    <a class="itr-btn itr-btn-orange itr-btn-sm itr-top-cta" href="{{ route('user.choose-service', ['mode' => 'self']) }}">{!! icon('spark') !!} Self Filing</a>
                @elseif($role === 'ca')
                    <a class="itr-btn itr-btn-orange itr-btn-sm itr-top-cta" href="{{ route('ca.clients') }}">My clients</a>
                @endif
                @php
                    $profileDash = match ($role) {
                        'admin' => route('admin.dashboard'),
                        'ca' => route('ca.dashboard'),
                        default => route('user.dashboard'),
                    };
                    $profileInitial = strtoupper(substr(auth()->user()->name, 0, 1));
                @endphp
                <div class="itr-profile-menu" data-profile-menu>
                    <button class="itr-profile-trigger" type="button" data-profile-trigger aria-expanded="false" aria-haspopup="menu">
                        <span class="itr-top-avatar" aria-hidden="true">{{ $profileInitial }}</span>
                        <span class="itr-top-user-text">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ roleLabel($role) }}</span>
                        </span>
                        <span class="itr-profile-caret" aria-hidden="true">{!! icon('chevron-down') !!}</span>
                    </button>
                    <div class="itr-profile-dropdown" data-profile-dropdown role="menu" hidden>
                        <div class="itr-profile-drop-head">
                            <span class="itr-top-avatar" aria-hidden="true">{{ $profileInitial }}</span>
                            <div>
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ $profileDash }}">{!! icon('chart') !!} Dashboard</a>
                        @if($role === 'user')
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('user.profile') }}">{!! icon('user') !!} Profile</a>
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('user.track-list') }}">{!! icon('list') !!} My Filings</a>
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('chat.index') }}">{!! icon('message') !!} Chat</a>
                        @elseif($role === 'admin')
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('admin.users') }}">{!! icon('users') !!} Users</a>
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('admin.settings') }}">{!! icon('shield') !!} Settings</a>
                        @elseif($role === 'ca')
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('ca.clients') }}">{!! icon('users') !!} My clients</a>
                            <a class="itr-profile-drop-item" role="menuitem" href="{{ route('chat.index') }}">{!! icon('message') !!} Chat</a>
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
            </div>
        </header>
        <div class="itr-content">
            @if(session('success') || session('error') || session('info'))
                @if(session('error'))
                    <div class="itr-alert itr-alert-error" data-auto-hide>{{ session('error') }}</div>
                @elseif(session('info'))
                    <div class="itr-alert itr-alert-info" data-auto-hide>{{ session('info') }}</div>
                @else
                    <div class="itr-alert itr-alert-success" data-auto-hide>{{ session('success') }}</div>
                @endif
            @endif
            @if(session('demo_verify_url'))
                <div class="itr-alert itr-alert-info" data-auto-hide>Demo verify link: <a href="{{ session('demo_verify_url') }}">Verify email</a></div>
            @endif
            @if(auth()->check() && auth()->user()->isUser() && ! auth()->user()->hasVerifiedEmail())
                <div class="itr-alert itr-alert-warn">
                    Please verify your email ({{ auth()->user()->email }}).
                    <form class="itr-inline-form" method="post" action="{{ route('verification.send') }}" style="display:inline">
                        @csrf
                        <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Send verify link</button>
                    </form>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/itr-tax.js') }}?v={{ @filemtime(public_path('assets/js/itr-tax.js')) ?: time() }}"></script>
@stack('scripts')
@unless(($role ?? '') === 'admin')
    @include('partials.chatbot')
@endunless
</body>
</html>
