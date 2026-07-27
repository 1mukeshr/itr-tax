@extends('layouts.panel')

@section('title', 'Start Filing')

@section('content')
@php
    $mode = in_array($mode ?? null, ['self', 'assisted'], true) ? $mode : null;
    $planId = $planId ?? 0;
    $profile = $profile ?? 'salaried';
    $selfSteps = $processSelf ?? collect();
    $assistedSteps = $processAssisted ?? collect();
@endphp

<div class="itr-order">
    <div class="itr-page-title">
        <h1>Start your ITR</h1>
        <p>Pick a mode, enter quick details, then continue. The list under each card is the full journey after you start.</p>
    </div>

    <div class="itr-card itr-order-card">
        <div class="itr-card-h">
            <span class="itr-order-stepnum">1</span>
            How do you want to file?
        </div>
        <div class="itr-card-b">
            <div class="itr-easy-grid">
                <a class="itr-easy-card {{ $mode === 'self' ? 'itr-easy-active' : '' }}" href="{{ route('user.choose-service', ['mode' => 'self', 'plan_id' => $planId ?: null, 'profile' => $profile]) }}">
                    {!! iconBox('spark') !!}
                    <h3>Self Filing — Free</h3>
                    <p>Form 16 salary cases. You enter figures and confirm.</p>
                    <ol class="itr-order-mini">
                        @forelse($selfSteps as $step)
                            <li>{{ $step->title }}</li>
                        @empty
                            <li>Questions</li>
                            <li>Documents</li>
                            <li>Tax summary</li>
                            <li>Confirm</li>
                        @endforelse
                    </ol>
                    <span class="itr-link-more">{{ $mode === 'self' ? 'Selected' : 'Choose Self Filing' }} {!! icon('arrow-right') !!}</span>
                </a>
                <a class="itr-easy-card {{ $mode === 'assisted' ? 'itr-easy-active' : '' }}" href="{{ route('user.choose-service', ['mode' => 'assisted', 'plan_id' => $planId ?: null, 'profile' => $profile]) }}">
                    {!! iconBox('users') !!}
                    <h3>Hire a Tax Expert</h3>
                    <p>Pay after documents. Expert reviews, you approve, get ACK.</p>
                    <ol class="itr-order-mini">
                        @forelse($assistedSteps as $step)
                            <li>{{ $step->title }}</li>
                        @empty
                            <li>Questions</li>
                            <li>Documents</li>
                            <li>Pay</li>
                            <li>Approve → ACK</li>
                        @endforelse
                    </ol>
                    <span class="itr-link-more">{{ $mode === 'assisted' ? 'Selected' : 'Choose Tax Expert' }} {!! icon('arrow-right') !!}</span>
                </a>
            </div>
            @if(! $mode)
                <div class="itr-soft-note itr-mt-md">Select <strong>Self Filing</strong> or <strong>Hire a Tax Expert</strong> to continue.</div>
            @endif
        </div>
    </div>

    @if($mode)
    <form method="post" action="{{ route('user.create-filing') }}" id="startForm" data-start-filing class="itr-order-form">
        @csrf
        <input type="hidden" name="filing_mode" value="{{ $mode }}">

        <div class="itr-card itr-order-card">
            <div class="itr-card-h">
                <span class="itr-order-stepnum">2</span>
                Quick details
            </div>
            <div class="itr-card-b">
                <div class="itr-form-row">
                    <div class="itr-form-group">
                        <label>I'm filing as</label>
                        <select class="itr-form-control" name="income_profile" id="incomeProfile" data-itr-suggest>
                            <option value="salaried" {{ $profile === 'salaried' ? 'selected' : '' }}>Salaried Professional</option>
                            <option value="investor" {{ $profile === 'investor' ? 'selected' : '' }}>Investor / Trader</option>
                            <option value="freelancer" {{ $profile === 'freelancer' ? 'selected' : '' }}>Freelancer / Professional</option>
                            <option value="advanced_trader" {{ $profile === 'advanced_trader' ? 'selected' : '' }}>Advanced Trader (F&amp;O)</option>
                            <option value="nri" {{ $profile === 'nri' ? 'selected' : '' }}>NRI / RSU / ESOP</option>
                            <option value="affluent" {{ $profile === 'affluent' ? 'selected' : '' }}>Affluent Investor</option>
                        </select>
                    </div>
                    <div class="itr-form-group">
                        <label>PAN</label>
                        <input class="itr-form-control itr-pan-input" name="pan" maxlength="10" placeholder="ABCDE1234F" value="{{ old('pan', auth()->user()->pan) }}" required>
                    </div>
                </div>
                <div class="itr-form-group">
                    <label>ITR form <span class="itr-help">(suggested from profile)</span></label>
                    <select class="itr-form-control" name="itr_type" id="itrTypeSelect" required>
                        @foreach([
                            'ITR-1' => 'ITR-1 — Salary / pension',
                            'ITR-2' => 'ITR-2 — Capital gains / NRI',
                            'ITR-3' => 'ITR-3 — Business / F&O',
                            'ITR-4' => 'ITR-4 — Presumptive business',
                        ] as $type => $label)
                            <option value="{{ $type }}" {{ $type === suggestItrType($profile) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($mode === 'assisted')
        <div class="itr-card itr-order-card" id="expertPlans">
            <div class="itr-card-h">
                <span class="itr-order-stepnum">3</span>
                Choose Tax Expert plan
            </div>
            <div class="itr-card-b">
                <p class="itr-help itr-mb-sm">You pay after documents. Coupons apply at checkout.</p>
                <div class="itr-grid-3">
                @foreach($plans as $i => $plan)
                <label class="itr-plan itr-is-clickable {{ ($planId ? (int) $plan->id === (int) $planId : $i === 0) ? 'itr-hot' : '' }}">
                    <input type="radio" name="plan_id" value="{{ $plan->id }}" {{ ($planId ? (int) $plan->id === (int) $planId : $i === 0) ? 'checked' : '' }} required>
                    <h3>{{ $plan->name }}</h3>
                    <div class="itr-price">{{ money($plan->price) }}</div>
                    <p>{{ $plan->description }}</p>
                </label>
                @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="itr-order-bar">
            <div class="itr-order-bar-copy">
                <strong>{{ $mode === 'self' ? 'Self Filing' : 'Hire a Tax Expert' }}</strong>
                <span>Next: short questions</span>
            </div>
            <button class="itr-btn itr-btn-orange itr-btn-lg" type="submit">
                {!! icon('arrow-right') !!} Continue
            </button>
        </div>
    </form>
    @endif
</div>
@endsection
