@extends('layouts.panel')

@section('title', 'Documents')

@section('content')
@php
    $hasForm16 = $docs->contains('doc_type', 'form16');
    $canContinue = $docs->isNotEmpty() && $hasForm16;
    $isAssisted = $filing->filing_mode === 'assisted';
    $continueOk = $isAssisted
        ? in_array($filing->status, ['documents_pending', 'draft', 'details_review'], true)
        : in_array($filing->status, ['documents_pending', 'docs_requested'], true);
    $nextLabel = $isAssisted
        ? ($filing->status === 'details_review' ? 'Back to confirm & pay' : 'Continue to review')
        : 'Continue to tax summary';
@endphp

<div class="itr-page-title">
    <h1>{{ $filing->status === 'docs_requested' ? 'Upload requested documents' : 'Upload documents' }}</h1>
    <p>Filing #{{ $filing->id }} · {{ $filing->itr_type }} · {!! statusBadge($filing->status) !!}</p>
</div>
@include('partials.steps', ['filing' => $filing])

@if($filing->status === 'docs_requested')
<div class="itr-alert itr-alert-warn">Your tax expert needs more documents. Upload them below to continue review.</div>
@endif

@foreach($mismatchHints as $hint)
    <div class="itr-alert itr-alert-{{ $hint['level'] === 'ok' ? 'success' : ($hint['level'] === 'warn' ? 'warn' : 'info') }} itr-mb-sm">
        <strong>{{ $hint['title'] }}</strong> — {{ $hint['body'] }}
    </div>
@endforeach

<div class="itr-order-req {{ $hasForm16 ? 'itr-order-req-ok' : '' }}">
    @if($hasForm16)
        {!! icon('check-circle') !!}
        <div>
            <strong>Form 16 uploaded</strong>
            <span>You can continue when ready. Other proofs are optional but helpful.</span>
        </div>
    @else
        {!! icon('file') !!}
        <div>
            <strong>Form 16 is required</strong>
            <span>Upload Form 16 (Part A &amp; B) to unlock the next step. AIS / 26AS and proofs are recommended.</span>
        </div>
    @endif
</div>

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Upload</div><div class="itr-card-b">
<form method="post" action="{{ route('user.upload-document', $filing) }}" enctype="multipart/form-data" id="docUploadForm">
    @csrf
    <div class="itr-form-group"><label>Document type</label>
        <select class="itr-form-control" name="doc_type" id="docType">
            @foreach($docTypes as $k => $label)
                <option value="{{ $k }}" {{ (! $hasForm16 && $k === 'form16') ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <label class="itr-dropzone" data-dropzone>
        <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png,.zip" hidden data-dropzone-input>
        <span class="itr-dropzone-ico">{!! icon('upload') !!}</span>
        <strong>Drag &amp; drop here</strong>
        <span>PDF / JPG / PNG / ZIP · max 10MB</span>
        <span class="itr-dropzone-file" data-dropzone-name>No file selected</span>
    </label>
    <div class="itr-actions-row itr-mt-md">
        <button class="itr-btn itr-btn-primary" type="submit">Upload</button>
    </div>
</form>

@if($continueOk)
<form method="post" action="{{ route('user.continue-documents', $filing) }}" class="itr-mt-md">
    @csrf
    <button class="itr-btn itr-btn-orange itr-btn-block" type="submit" {{ $canContinue ? '' : 'disabled' }}>
        {!! icon('arrow-right') !!} {{ $nextLabel }}
    </button>
    @unless($canContinue)
        <p class="itr-help itr-mt-sm itr-text-center">Upload Form 16 to continue.</p>
    @endunless
</form>
@endif
</div></div>

<div>
@if($requests->isNotEmpty())
<div class="itr-card"><div class="itr-card-h">Expert requests</div><div class="itr-card-b">
@foreach($requests as $r)
    <p class="itr-text-ink">{{ $r->message }}</p>
    <div class="itr-help">{{ $r->required_docs }} · {{ $r->status }}</div>
@endforeach
</div></div>
@endif

<div class="itr-card"><div class="itr-card-h">Checklist</div><div class="itr-card-b">
<ul class="itr-doc-checklist">
    <li class="{{ $hasForm16 ? 'itr-done-line' : '' }}">{!! icon('file') !!} Form 16 <em>(required)</em></li>
    <li class="{{ $docs->contains('doc_type', 'form26as') ? 'itr-done-line' : '' }}">{!! icon('list') !!} AIS / Form 26AS</li>
    <li>{!! icon('rupee') !!} Bank interest certificate</li>
    <li>{!! icon('shield') !!} 80C / 80D / home loan proofs</li>
    <li>{!! icon('chart') !!} Capital gains statements (if any)</li>
</ul>
</div></div>

<div class="itr-card"><div class="itr-card-h">Uploaded ({{ $docs->count() }})</div>
<div class="itr-table-wrap"><table>
<thead>
<tr><th>Type</th><th>File</th><th>Date</th></tr>
</thead>
<tbody>
@forelse($docs as $d)
<tr>
    <td><span class="itr-admin-cell-main">{{ $docTypes[$d->doc_type] ?? $d->doc_type }}</span></td>
    <td>{{ $d->original_name }}</td>
    <td class="itr-table-muted">{{ timeAgo($d->created_at) }}</td>
</tr>
@empty
<tr><td colspan="3" class="itr-empty">No files yet.</td></tr>
@endforelse
</tbody>
</table></div></div>
</div>
</div>
@endsection
