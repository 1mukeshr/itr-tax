@extends('layouts.panel')

@section('title', 'Confirm & Pay')

@section('content')
<div class="itr-page-title">
    <h1>Quick confirm</h1>
    <p>Check once, then pay · Filing #{{ $filing->id }}</p>
</div>
@include('partials.steps', ['filing' => $filing])

<div class="itr-card itr-order-confirm">
    <div class="itr-card-b">
        <div class="itr-order-confirm-grid">
            <div>
                <span class="itr-help">Service</span>
                <strong>{{ $filing->filing_mode === 'self' ? 'Self Filing' : 'Hire a Tax Expert' }}</strong>
            </div>
            <div>
                <span class="itr-help">Plan</span>
                <strong>{{ $plan->name ?? 'Plan' }} · {{ money($filing->amount) }}</strong>
            </div>
            <div>
                <span class="itr-help">ITR / PAN</span>
                <strong>{{ $filing->itr_type }} · {{ $filing->pan ?: '-' }}</strong>
            </div>
            <div>
                <span class="itr-help">Profile</span>
                <strong>{{ ucwords(str_replace('_', ' ', $filing->income_profile ?? 'salaried')) }}</strong>
            </div>
            <div>
                <span class="itr-help">Documents</span>
                <strong>{{ $docs->count() }} file(s)</strong>
                <a class="itr-link-more" href="{{ route('user.documents', $filing) }}">Edit</a>
            </div>
            <div>
                <span class="itr-help">Answers</span>
                <strong>{{ count(array_filter($answers ?? [])) }} / {{ count($questions ?? []) }}</strong>
                <a class="itr-link-more" href="{{ route('user.questions', $filing) }}">Edit</a>
            </div>
        </div>

        @if(!empty($answers))
        <details class="itr-order-details itr-mt-md">
            <summary>View answers &amp; files</summary>
            <div class="itr-table-wrap itr-mt-sm">
                <table>
                    <tr><th>Question</th><th>Answer</th></tr>
                    @foreach($questions as $key => $q)
                        @if(!empty($answers[$key]))
                        <tr>
                            <td>{{ $q['label'] }}</td>
                            <td>{{ $q['options'][$answers[$key]] ?? $answers[$key] }}</td>
                        </tr>
                        @endif
                    @endforeach
                </table>
            </div>
            <ul class="itr-tip-list itr-mt-sm">
                @foreach($docs as $d)
                    <li>{!! icon('file') !!} {{ $d->doc_type }} — {{ $d->original_name }}</li>
                @endforeach
            </ul>
        </details>
        @endif

        @if($filing->status === 'details_review')
        <form method="post" action="{{ route('user.confirm-details', $filing) }}" class="itr-order-bar itr-mt-lg">
            @csrf
            <div class="itr-order-bar-copy">
                <strong>Ready for checkout</strong>
                <span>All conditions still apply — Form 16 uploaded, plan selected, details saved.</span>
            </div>
            <button class="itr-btn itr-btn-orange itr-btn-lg" type="submit">
                {!! icon('check') !!} Confirm &amp; go to payment
            </button>
        </form>
        @else
        <div class="itr-order-bar itr-mt-lg">
            <div class="itr-order-bar-copy">
                <strong>Continue your filing</strong>
                <span>This step is already done — go to your current step.</span>
            </div>
            <a class="itr-btn itr-btn-primary itr-btn-lg" href="{{ filingContinueUrl($filing) }}">
                {!! icon('arrow-right') !!} {{ nextStepLabel($filing) }}
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
