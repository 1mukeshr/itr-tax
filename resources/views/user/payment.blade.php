@extends('layouts.panel')

@section('title', 'Payment')

@section('content')
@php
    $canPay = $filing->status === 'payment_pending';
    $features = $plan && method_exists($plan, 'featuresList') ? ($plan->featuresList() ?: []) : [];
    $payable = (float) ($plan->price ?? $filing->amount);
    $razorpayLive = !empty($razorpayLive);
@endphp
<div class="itr-page-title">
    <h1>Pay &amp; get an expert</h1>
    <p>Filing #{{ $filing->id }} · {{ $filing->itr_type }} · {{ money($payable) }}
        @if($razorpayLive)
            · <span class="itr-tag">Razorpay</span>
        @else
            · <span class="itr-help">Demo checkout</span>
        @endif
    </p>
</div>
@include('partials.steps', ['filing' => $filing])

<div class="itr-grid-2">
<div class="itr-card itr-pay-card"><div class="itr-card-h">Order summary</div><div class="itr-card-b">
    <div class="itr-pay-plan">
        <span class="itr-tag">Hire a Tax Expert</span>
        <h3>{{ $plan->name ?? 'Expert Plan' }}</h3>
        <div class="itr-price itr-price-lg">{{ money($payable) }}</div>
        <div class="itr-help">AY {{ $filing->assessment_year }} · {{ $filing->itr_type }}</div>
    </div>
    <ul class="itr-pay-lines">
        <li><span>Plan fee</span><strong>{{ money($payable) }}</strong></li>
        <li class="itr-pay-total"><span>Amount payable</span><strong>{{ money($payable) }}</strong></li>
    </ul>
    @if($features)
    <ul class="itr-tip-list">
        @foreach(array_slice($features, 0, 4) as $f)
            <li>{!! icon('check') !!} {{ $f }}</li>
        @endforeach
    </ul>
    @endif

    @if(!$canPay)
        <div class="itr-alert itr-alert-success itr-mt-md">Payment already completed or not required at this stage.</div>
        <a class="itr-btn itr-btn-primary" href="{{ route('user.track', $filing) }}">Track filing</a>
    @else
    <form method="post" action="{{ route('user.process-payment', $filing) }}" class="itr-mt-md" id="payForm">
        @csrf
        <input type="hidden" name="razorpay_order_id" id="rzp_order_id" value="">
        <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id" value="">
        <input type="hidden" name="razorpay_signature" id="rzp_signature" value="">
        <div class="itr-form-group">
            <label>Coupon code</label>
            <div class="itr-coupon-row">
                <input class="itr-form-control" name="coupon_code" placeholder="Enter coupon" id="couponInput" value="{{ old('coupon_code') }}">
                @foreach(($coupons ?? []) as $coupon)
                    <button class="itr-btn itr-btn-outline" type="button" data-fill-coupon="{{ $coupon->code }}">{{ $coupon->code }}</button>
                @endforeach
            </div>
        </div>
        <div class="itr-form-group"><label>Payment method</label>
            <div class="itr-pay-methods">
                <label class="itr-pay-method itr-hot"><input type="radio" name="method" value="upi" checked><span>{!! icon('rupee') !!} UPI</span></label>
                <label class="itr-pay-method"><input type="radio" name="method" value="card"><span>{!! icon('wallet') !!} Card</span></label>
                <label class="itr-pay-method"><input type="radio" name="method" value="netbanking"><span>{!! icon('building') !!} Net banking</span></label>
            </div>
        </div>
        <button class="itr-btn itr-btn-orange itr-btn-block" type="submit" id="paySubmit">
            {!! icon('shield') !!} {{ $razorpayLive ? 'Pay with Razorpay' : 'Confirm &amp; Continue' }}
        </button>
        <div class="itr-pay-secure">
            {!! icon('shield') !!}
            @if($razorpayLive)
                Opens Razorpay checkout. Amount is collected via your Razorpay account.
            @else
                Records a simulated payment in ITR Tax. Add Razorpay key + secret in Admin → Settings for live collection.
            @endif
            An available tax expert is assigned after confirmation.
        </div>
    </form>
    @endif
</div></div>

<div>
<div class="itr-card"><div class="itr-card-h">What happens next</div><div class="itr-card-b">
<div class="itr-journey itr-journey-compact">
    <div class="itr-journey-step"><div class="itr-journey-num">1</div><div class="itr-journey-body"><h3>Expert assigned</h3><p>An available tax expert is assigned after successful payment.</p></div></div>
    <div class="itr-journey-step"><div class="itr-journey-num">2</div><div class="itr-journey-body"><h3>Expert review</h3><p>Your expert reviews documents and may request more proofs.</p></div></div>
    <div class="itr-journey-step"><div class="itr-journey-num">3</div><div class="itr-journey-body"><h3>File &amp; ACK</h3><p>After you approve the summary, expert files and uploads acknowledgement.</p></div></div>
</div>
</div></div>

<div class="itr-card"><div class="itr-card-h">Payment history</div>
<div class="itr-table-wrap"><table>
<thead>
<tr><th>Txn</th><th>Amount</th><th>Status</th></tr>
</thead>
<tbody>
@forelse($payments as $p)
<tr>
    <td><span class="itr-table-id">{{ $p->transaction_id }}</span></td>
    <td>
        <span class="itr-table-money">{{ money($p->amount) }}</span>
        @if($p->discount > 0) <span class="itr-table-muted">(−{{ money($p->discount) }})</span>@endif
    </td>
    <td>{!! statusBadge($p->status === 'success' ? 'completed' : 'payment_pending') !!}</td>
</tr>
@empty
<tr><td colspan="3" class="itr-empty">No payments yet.</td></tr>
@endforelse
</tbody>
</table></div></div>
</div>
</div>
@endsection

@if($canPay && $razorpayLive)
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var form = document.getElementById('payForm');
    if (!form) return;
    window.RAZORPAY_LIVE = true;
    form.addEventListener('submit', function (e) {
        if (document.getElementById('rzp_payment_id').value) return;
        e.preventDefault();
        var btn = document.getElementById('paySubmit');
        btn.disabled = true;
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('[name=_token]').value
            },
            body: new FormData(form)
        }).then(function (r) {
            return r.text().then(function (text) {
                var d = {};
                try { d = JSON.parse(text); } catch (e) { d = { message: 'Payment request failed. Please try again.' }; }
                return { ok: r.ok, d: d };
            });
        })
        .then(function (res) {
            if (!res.ok || !res.d.order_id) {
                btn.disabled = false;
                alert((res.d && res.d.message) || 'Could not start Razorpay. Check keys or try again.');
                return;
            }
            var options = {
                key: res.d.key,
                amount: res.d.amount,
                currency: res.d.currency || 'INR',
                name: res.d.name || 'ITR Tax',
                description: res.d.description || '',
                order_id: res.d.order_id,
                prefill: res.d.prefill || {},
                handler: function (response) {
                    document.getElementById('rzp_order_id').value = response.razorpay_order_id;
                    document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('rzp_signature').value = response.razorpay_signature;
                    form.submit();
                },
                modal: { ondismiss: function () { btn.disabled = false; } }
            };
            new Razorpay(options).open();
        }).catch(function (err) {
            btn.disabled = false;
            alert((err && err.message) || 'Payment request failed.');
        });
    });
})();
</script>
@endpush
@endif
