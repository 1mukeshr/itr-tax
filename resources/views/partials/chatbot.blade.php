{{-- Floating chatbot — answers from FAQs table, logs to chatbot_messages --}}
<div class="itr-bot" data-chatbot
     data-ask-url="{{ route('chatbot.ask') }}"
     data-suggest-url="{{ route('chatbot.suggestions') }}"
     data-csrf="{{ csrf_token() }}">
    <button type="button" class="itr-bot-fab" data-bot-toggle aria-expanded="false" aria-controls="itrBotPanel" aria-label="Open help chatbot">
        {!! icon('message') !!}
        <span class="itr-bot-fab-label">Help</span>
    </button>
    <div class="itr-bot-panel" id="itrBotPanel" hidden>
        <header class="itr-bot-head">
            <div>
                <strong>{{ $app['name'] }} Assistant</strong>
                <span>Answers from our FAQ database</span>
            </div>
            <button type="button" class="itr-bot-close" data-bot-close aria-label="Close">{!! icon('x') !!}</button>
        </header>
        <div class="itr-bot-messages" data-bot-messages>
            <div class="itr-bot-bubble itr-bot-bubble-bot">
                Hi! Ask about Form 16, e-verify, Self vs Expert, documents, or refunds.
            </div>
        </div>
        <div class="itr-bot-suggests" data-bot-suggests></div>
        <form class="itr-bot-form" data-bot-form>
            <input type="text" name="message" data-bot-input maxlength="1000" placeholder="Type your question…" required autocomplete="off">
            <button type="submit" aria-label="Send">{!! icon('arrow-right') !!}</button>
        </form>
    </div>
</div>
