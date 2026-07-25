@extends('layouts.panel')

@section('title', 'Chat · Filing #'.$thread->filing_id)

@section('content')
@php
    $peerName = $counterpart->name ?? 'Chat';
    $peerInitial = strtoupper(substr($peerName, 0, 1));
    $peerRole = $role === 'user' ? 'Tax Expert' : 'Customer';
@endphp

<div class="itr-chat-shell" data-chat-thread="{{ $thread->id }}" data-chat-poll="{{ route('chat.poll', $thread) }}" data-chat-last="{{ $messages->last()?->id ?? 0 }}">
    <div class="itr-chat-window">
        <header class="itr-chat-window-head">
            <div class="itr-chat-window-peer">
                <a class="itr-chat-back" href="{{ route('chat.index') }}" aria-label="Back to conversations">{!! icon('chevron-left') !!}</a>
                <span class="itr-chat-avatar itr-chat-avatar-sm" aria-hidden="true">{{ $peerInitial }}</span>
                <div class="itr-chat-window-copy">
                    <strong>{{ $peerName }}</strong>
                    <span>
                        {{ $peerRole }}
                        · Filing #{{ $thread->filing_id }}
                        · {{ strtoupper($thread->filing->itr_type ?? '') }}
                        · {{ statusLabel($thread->filing->status ?? '') }}
                    </span>
                </div>
            </div>
            <div class="itr-chat-window-actions">
                @if($role === 'ca')
                    <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('ca.filing', $thread->filing_id) }}">Open filing</a>
                @else
                    <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.track', $thread->filing_id) }}">Track filing</a>
                @endif
            </div>
        </header>

        <div class="itr-chat-messages" data-chat-messages>
            @forelse($messages as $message)
                @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
                <div class="itr-chat-bubble {{ $mine ? 'is-mine' : 'is-theirs' }}" data-msg-id="{{ $message->id }}">
                    @unless($mine)
                        <div class="itr-chat-bubble-meta">
                            <strong>{{ $message->sender->name ?? 'User' }}</strong>
                        </div>
                    @endunless
                    <div class="itr-chat-bubble-body">{{ $message->body }}</div>
                    <time class="itr-chat-bubble-time">{{ $message->created_at?->format('d M · h:i A') }}</time>
                </div>
            @empty
                <div class="itr-chat-empty" data-chat-empty>
                    {!! iconBox('message') !!}
                    <p>Say hello to start the conversation about filing #{{ $thread->filing_id }}.</p>
                </div>
            @endforelse
        </div>

        <form class="itr-chat-composer" method="post" action="{{ route('chat.send', $thread) }}" data-chat-form>
            @csrf
            <textarea class="itr-form-control" name="body" rows="1" maxlength="4000" placeholder="Type a message… (Enter to send)" required data-chat-input></textarea>
            <button class="itr-btn itr-btn-primary itr-chat-send" type="submit" aria-label="Send">
                {!! icon('arrow-right') !!}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const shell = document.querySelector('[data-chat-thread]');
  if (!shell) return;
  const box = shell.querySelector('[data-chat-messages]');
  const form = shell.querySelector('[data-chat-form]');
  const input = shell.querySelector('[data-chat-input]');
  const pollUrl = shell.getAttribute('data-chat-poll');
  let lastId = Number(shell.getAttribute('data-chat-last') || 0);

  const scrollBottom = () => { box.scrollTop = box.scrollHeight; };
  scrollBottom();

  const autosize = () => {
    if (!input) return;
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 120) + 'px';
  };
  input?.addEventListener('input', autosize);
  autosize();

  const appendMessage = (msg) => {
    if (box.querySelector(`[data-msg-id="${msg.id}"]`)) return;
    const empty = box.querySelector('[data-chat-empty]');
    if (empty) empty.remove();
    const el = document.createElement('div');
    el.className = 'itr-chat-bubble ' + (msg.mine ? 'is-mine' : 'is-theirs');
    el.setAttribute('data-msg-id', String(msg.id));
    let html = '';
    if (!msg.mine) {
      html += '<div class="itr-chat-bubble-meta"><strong></strong></div>';
    }
    html += '<div class="itr-chat-bubble-body"></div><time class="itr-chat-bubble-time"></time>';
    el.innerHTML = html;
    const nameEl = el.querySelector('strong');
    if (nameEl) nameEl.textContent = msg.sender;
    el.querySelector('.itr-chat-bubble-body').textContent = msg.body;
    el.querySelector('time').textContent = msg.time;
    box.appendChild(el);
    lastId = Math.max(lastId, Number(msg.id));
    scrollBottom();
  };

  const poll = async () => {
    try {
      const res = await fetch(pollUrl + '?after=' + encodeURIComponent(lastId), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      if (!res.ok) return;
      const data = await res.json();
      (data.messages || []).forEach(appendMessage);
      if (data.last_id) lastId = Number(data.last_id);
    } catch (e) {}
  };

  setInterval(poll, 3000);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) poll();
  });

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = (input.value || '').trim();
    if (!body) return;
    const fd = new FormData(form);
    input.value = '';
    autosize();
    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      if (!res.ok) {
        input.value = body;
        autosize();
        return;
      }
      await poll();
    } catch (err) {
      input.value = body;
      autosize();
    }
  });

  input?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      form.requestSubmit();
    }
  });
})();
</script>
@endpush
