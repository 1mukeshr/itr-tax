@php
    $processMode = $processMode ?? 'both';
    $steps = isset($steps) ? $steps : processSteps($processMode);
    $numbered = $numbered ?? true;
    $class = $class ?? 'itr-process-grid';
@endphp
@if($steps->isNotEmpty())
<div class="{{ $class }}" data-process-mode="{{ $processMode }}">
    @foreach($steps as $i => $step)
        <div class="itr-process-card">
            @if($numbered)
                <span class="itr-process-num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
            @else
                <span class="itr-process-ico" aria-hidden="true">{!! icon($step->icon ?: 'check') !!}</span>
            @endif
            <h3>{{ $step->title }}</h3>
            @if($step->description)
                <p>{{ $step->description }}</p>
            @endif
        </div>
    @endforeach
</div>
@endif
