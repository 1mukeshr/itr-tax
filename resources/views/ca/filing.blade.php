@extends('layouts.panel')

@section('title', 'Filing')

@section('content')
<div class="itr-page-title">
    <h1>Filing #{{ $filing->id }} · {{ $filing->itr_type }}</h1>
    <p>{{ $client->name ?? '-' }} · {!! statusBadge($filing->status) !!}</p>
    <p class="itr-mt-sm"><a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('chat.open-filing', $filing) }}">{!! icon('message') !!} Chat with customer</a></p>
</div>

@include('partials.steps', ['filing' => $filing])

<div class="itr-flow-strip">
    <span class="{{ in_array($filing->status, ['assigned', 'under_review'], true) ? 'itr-flow-on' : '' }}">Review</span>
    <span class="{{ $filing->status === 'docs_requested' ? 'itr-flow-on' : '' }}">Need Docs?</span>
    <span class="{{ $filing->status === 'customer_summary' ? 'itr-flow-on' : '' }}">Send Summary</span>
    <span class="{{ $filing->status === 'customer_approved' ? 'itr-flow-on' : '' }}">Customer OK</span>
    <span class="{{ $filing->status === 'filed' ? 'itr-flow-on' : '' }}">File ITR</span>
    <span class="{{ $filing->status === 'completed' ? 'itr-flow-on' : '' }}">ACK</span>
</div>

<div class="itr-grid-2">
<div>
<div class="itr-card"><div class="itr-card-h">Documents</div>
<div class="itr-table-wrap"><table>
<tr><th>Type</th><th>File</th><th></th></tr>
@forelse($docs as $d)
<tr>
    <td>{{ $d->doc_type }}</td>
    <td>{{ $d->original_name }}</td>
    <td><a href="{{ route('ca.download-doc', $d) }}">Download</a></td>
</tr>
@empty
<tr><td colspan="3" class="itr-empty">No documents.</td></tr>
@endforelse
</table></div></div>

<div class="itr-card"><div class="itr-card-h">Notes</div><div class="itr-card-b">
<form method="post" action="{{ route('ca.add-note', $filing) }}">
    @csrf
    <div class="itr-form-group"><textarea class="itr-form-control" name="note" rows="3" required></textarea></div>
    <label class="itr-check-inline"><input type="checkbox" name="is_internal" value="1" checked> Internal only</label>
    <button class="itr-btn itr-btn-primary itr-btn-sm" type="submit">Add note</button>
</form>
@foreach($notes as $n)
<div class="itr-note-block">
    <strong>{{ $n->author->name ?? '' }}</strong>
    @if($n->is_internal)
        <span class="itr-badge itr-badge-muted">Internal</span>
    @else
        <span class="itr-badge itr-badge-info">Client</span>
    @endif
    <p>{{ $n->note }}</p>
</div>
@endforeach
</div></div>
</div>

@php
    $canReview = in_array($filing->status, ['assigned', 'docs_requested'], true);
    $canRequestDocs = in_array($filing->status, ['assigned', 'under_review', 'docs_requested'], true);
    $canSendSummary = in_array($filing->status, ['under_review', 'assigned', 'docs_requested'], true);
    $waitingCustomer = $filing->status === 'customer_summary';
    $canMarkFiled = in_array($filing->status, ['customer_approved', 'ready_to_file'], true);
    $canUploadAck = in_array($filing->status, ['filed', 'completed'], true) || filled($filing->acknowledgement_no);
    $done = in_array($filing->status, ['completed'], true);
@endphp
<div class="itr-card"><div class="itr-card-h">Your next action</div><div class="itr-card-b">
<p class="itr-help itr-mb-sm">{{ expertNextAction($filing) }}</p>

@if($done)
<div class="itr-alert itr-alert-success">This filing is completed. ACK is with the customer.</div>
@endif

@if($canReview)
<form method="post" action="{{ route('ca.start-review', $filing) }}" class="itr-stack-form">
    @csrf<button class="itr-btn itr-btn-primary" type="submit">1. Start / Resume Review</button>
</form>
@endif

@if($canRequestDocs)
<form method="post" action="{{ route('ca.request-docs', $filing) }}" class="itr-stack-form">
    @csrf
    <div class="itr-form-group"><label>Need more documents?</label><textarea class="itr-form-control" name="message" rows="2" required placeholder="Please upload AIS / bank interest…"></textarea></div>
    <div class="itr-form-group"><input class="itr-form-control" name="required_docs" placeholder="Required docs list"></div>
    <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Send request</button>
</form>
@endif

@if($canSendSummary)
<form method="post" action="{{ route('ca.send-summary', $filing) }}" class="itr-stack-form">
    @csrf
    <div class="itr-form-group"><label>2. Send tax summary to customer</label>
        <input class="itr-form-control" type="number" name="gross_salary" value="{{ old('gross_salary', $filing->gross_salary) }}" placeholder="Gross income" min="1" required>
    </div>
    <div class="itr-form-group"><input class="itr-form-control" type="number" name="total_deductions" value="{{ old('total_deductions', $filing->total_deductions ?? 0) }}" placeholder="Deductions" min="0" required></div>
    <div class="itr-form-group"><input class="itr-form-control" type="number" name="tds_deducted" value="{{ old('tds_deducted', data_get(json_decode($filing->notes ?: '{}', true), 'tds_deducted', 0)) }}" placeholder="TDS deducted" min="0" required></div>
    <div class="itr-form-group">
        <select class="itr-form-control" name="tax_regime" required>
            <option value="new" {{ ($filing->tax_regime ?? '') === 'new' ? 'selected' : '' }}>New regime</option>
            <option value="old" {{ ($filing->tax_regime ?? '') === 'old' ? 'selected' : '' }}>Old regime</option>
        </select>
    </div>
    <div class="itr-form-group"><textarea class="itr-form-control" name="expert_note" rows="2" placeholder="Optional note for customer">{{ old('expert_note') }}</textarea></div>
    <button class="itr-btn itr-btn-orange itr-btn-sm" type="submit">Send summary for approval</button>
</form>
@endif

@if($waitingCustomer)
<div class="itr-alert itr-alert-info">Waiting for customer to approve the tax summary. No further action until they approve.</div>
@endif

@if($canMarkFiled)
<form method="post" action="{{ route('ca.mark-filed', $filing) }}" class="itr-stack-form">
    @csrf
    <div class="itr-form-group"><label>3. Customer approved - file return (ACK no.)</label><input class="itr-form-control" name="acknowledgement_no" required placeholder="ACK number"></div>
    <button class="itr-btn itr-btn-orange itr-btn-sm" type="submit">Mark Filed</button>
</form>
@endif

@if($canUploadAck && ! $done)
<form method="post" action="{{ route('ca.upload-receipt', $filing) }}" enctype="multipart/form-data" class="itr-stack-form">
    @csrf
    <div class="itr-form-group"><label>4. Upload acknowledgement</label><input class="itr-form-control" type="file" name="receipt" required></div>
    <div class="itr-form-group"><input class="itr-form-control" name="acknowledgement_no" value="{{ $filing->acknowledgement_no }}" placeholder="ACK number"></div>
    <button class="itr-btn itr-btn-primary itr-btn-sm" type="submit">Upload ACK &amp; Complete</button>
</form>
@endif
</div></div>
</div>
@endsection
