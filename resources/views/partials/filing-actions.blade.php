@php
    $status = $filing->status ?? '';
    $done = in_array($status, ['filed', 'completed'], true);
    $hasAck = filled($filing->acknowledgement_no) || $done;
    $showDocs = ! $done || in_array($status, ['docs_requested', 'documents_pending'], true);
    $primaryLabel = nextStepLabel($filing);
    $primaryUrl = filingContinueUrl($filing);
@endphp
<div class="itr-gap-row itr-filing-actions">
    <a class="itr-btn itr-btn-primary itr-btn-sm" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
    <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.track', $filing) }}">Track status</a>
    @if($showDocs)
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.documents', $filing) }}">Documents</a>
    @endif
    @if($hasAck)
        <a class="itr-btn itr-btn-outline itr-btn-sm" href="{{ route('user.acknowledgement', $filing) }}">Acknowledgement</a>
    @endif
</div>
