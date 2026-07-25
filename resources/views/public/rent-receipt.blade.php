@extends('layouts.main')

@section('title', 'Rent receipt')

@section('content')
<div class="itr-page-banner"><div class="itr-container">
    <div class="itr-banner-inner">
        <span class="itr-banner-kicker">Free tool</span>
        <h1>Rent receipt generator</h1>
        <p>Create a simple printable rent receipt for HRA / landlord records.</p>
    </div>
</div></div>

<section class="itr-section"><div class="itr-container">
<div class="itr-calc-grid">
    <div class="itr-card">
        <div class="itr-card-h">{!! icon('file') !!} Receipt details</div>
        <div class="itr-card-b">
            <form method="post" action="{{ route('tools.rent-receipt.generate') }}">
                @csrf
                <div class="itr-form-group"><label>Tenant name</label><input class="itr-form-control" name="tenant_name" required value="{{ old('tenant_name', $receipt['tenant_name'] ?? '') }}"></div>
                <div class="itr-form-group"><label>Landlord name</label><input class="itr-form-control" name="landlord_name" required value="{{ old('landlord_name', $receipt['landlord_name'] ?? '') }}"></div>
                <div class="itr-form-group"><label>Landlord PAN (optional)</label><input class="itr-form-control" name="landlord_pan" maxlength="10" value="{{ old('landlord_pan', $receipt['landlord_pan'] ?? '') }}"></div>
                <div class="itr-form-group"><label>Property address</label><textarea class="itr-form-control" name="property_address" rows="2" required>{{ old('property_address', $receipt['property_address'] ?? '') }}</textarea></div>
                <div class="itr-form-row">
                    <div class="itr-form-group"><label>Month / period</label><input class="itr-form-control" name="month" required placeholder="April 2025" value="{{ old('month', $receipt['month'] ?? '') }}"></div>
                    <div class="itr-form-group"><label>Rent amount (₹)</label><input class="itr-form-control" type="number" name="amount" min="1" step="1" required value="{{ old('amount', $receipt['amount'] ?? '') }}"></div>
                </div>
                <div class="itr-form-group"><label>City</label><input class="itr-form-control" name="city" value="{{ old('city', $receipt['city'] ?? '') }}"></div>
                <button class="itr-btn itr-btn-primary" type="submit">Generate receipt</button>
            </form>
        </div>
    </div>
    <div class="itr-card" id="rentPrintArea">
        <div class="itr-card-h">{!! icon('download') !!} Preview</div>
        <div class="itr-card-b">
            @if($receipt)
                <div class="itr-ack-receipt">
                    <h2>Rent receipt</h2>
                    <p class="itr-help">Period: {{ $receipt['month'] }}@if(!empty($receipt['city'])) · {{ $receipt['city'] }}@endif</p>
                    <div class="itr-ack-no">{{ money($receipt['amount']) }}</div>
                    <p>Received from <strong>{{ $receipt['tenant_name'] }}</strong> the sum of <strong>{{ money($receipt['amount']) }}</strong> towards rent for the property at:</p>
                    <p>{{ $receipt['property_address'] }}</p>
                    <div class="itr-ack-grid">
                        <div><span>Landlord</span><strong>{{ $receipt['landlord_name'] }}</strong></div>
                        <div><span>PAN</span><strong>{{ $receipt['landlord_pan'] ?: '—' }}</strong></div>
                    </div>
                    <p class="itr-help itr-mt-md">Landlord signature / stamp ________________</p>
                </div>
                <button class="itr-btn itr-btn-outline itr-mt-md" type="button" onclick="window.print()">Print</button>
            @else
                <div class="itr-empty">Fill the form to preview a receipt.</div>
            @endif
            <a class="itr-btn itr-btn-outline itr-mt-md" href="{{ route('tools') }}">All tools</a>
        </div>
    </div>
</div>
</div></section>
@endsection
