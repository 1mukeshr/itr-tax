@extends('layouts.panel')

@section('title', 'Chat')

@section('content')
<div class="itr-chat-page">
    <div class="itr-page-title itr-chat-page-title">
        <h1>Chat</h1>
        <p>{{ $role === 'ca' ? 'Message clients on assigned filings.' : 'Message your assigned tax expert.' }}</p>
    </div>

    <div class="itr-card itr-chat-list-card">
        <div class="itr-card-h">
            <span>Conversations</span>
            @if(method_exists($threads, 'total') && $threads->total() > 0)
                <span class="itr-chat-count">{{ $threads->total() }}</span>
            @endif
        </div>
        <div class="itr-card-b itr-chat-list-wrap">
            @forelse($threads as $thread)
                @php
                    $peer = $role === 'ca' ? $thread->customer : $thread->expert;
                    $unread = $thread->unreadCountFor(auth()->id());
                    $preview = $thread->latestMessage?->body;
                    $initial = strtoupper(substr($peer->name ?? '?', 0, 1));
                @endphp
                <a class="itr-chat-list-item {{ $unread ? 'has-unread' : '' }}" href="{{ route('chat.show', $thread) }}">
                    <span class="itr-chat-avatar" aria-hidden="true">{{ $initial }}</span>
                    <span class="itr-chat-list-main">
                        <span class="itr-chat-list-row">
                            <strong>{{ $peer->name ?? 'User' }}</strong>
                            <time>{{ optional($thread->last_message_at)->diffForHumans() }}</time>
                        </span>
                        <span class="itr-chat-list-meta">
                            Filing #{{ $thread->filing_id }}
                            · {{ $thread->filing->itr_type ?? 'ITR' }}
                            · {{ statusLabel($thread->filing->status ?? '') }}
                        </span>
                        <span class="itr-chat-preview">{{ $preview ? \Illuminate\Support\Str::limit($preview, 100) : 'No messages yet — say hello' }}</span>
                    </span>
                    @if($unread)
                        <em class="itr-chat-badge" aria-label="{{ $unread }} unread">{{ $unread }}</em>
                    @else
                        <span class="itr-chat-list-go" aria-hidden="true">{!! icon('arrow-right') !!}</span>
                    @endif
                </a>
            @empty
                <div class="itr-chat-empty-state">
                    {!! iconBox('message') !!}
                    <h3>No conversations yet</h3>
                    <p>
                        @if($role === 'ca')
                            Chats open automatically when a filing is assigned to you.
                        @else
                            After payment and expert assignment, open chat from My Filings or Track.
                        @endif
                    </p>
                    @if($role === 'user')
                        <a class="itr-btn itr-btn-orange itr-btn-sm" href="{{ route('user.track-list') }}">My Filings</a>
                    @else
                        <a class="itr-btn itr-btn-primary itr-btn-sm" href="{{ route('ca.clients') }}">My clients</a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    @if(method_exists($threads, 'links'))
        <div class="itr-mt-md">{{ $threads->links('vendor.pagination.itr') }}</div>
    @endif
</div>
@endsection
