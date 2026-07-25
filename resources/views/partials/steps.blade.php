@php
    $steps = filingSteps($filing);
    $current = currentStepIndex($filing);
    $last = count($steps) - 1;
    $modeLabel = data_get($filing, 'filing_mode') === 'self' ? 'Self' : 'Tax Expert';
    $pct = filingProgressPercent($filing);
@endphp
<div class="itr-steps-wrap">
    <div class="itr-steps-meta">
        <span class="itr-tag {{ data_get($filing, 'filing_mode') === 'self' ? 'itr-tag-orange' : '' }}">{{ $modeLabel }}</span>
        <span class="itr-help">Step {{ min($current + 1, $last + 1) }} of {{ $last + 1 }} · {{ $pct }}%</span>
    </div>
    <div class="itr-steps" aria-label="Filing progress">
    @foreach($steps as $i => $s)
        <div class="itr-step-item {{ $i < $current ? 'itr-done' : ($i === $current ? 'itr-active' : '') }}">
            <div class="n">{!! $i < $current ? icon('check') : ($i + 1) !!}</div>
            <div class="l">{{ $s['label'] }}</div>
        </div>
        @if($i < $last)
            <div class="itr-step-line {{ $i < $current ? 'itr-done' : '' }}"></div>
        @endif
    @endforeach
    </div>
</div>
