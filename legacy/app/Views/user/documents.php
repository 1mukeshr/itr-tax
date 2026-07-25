<?php
use App\Core\Helper;
use App\Core\View;
?>
<div class="itr-page-title">
    <h1>Upload documents</h1>
    <p>Filing #<?= (int)$filing['id'] ?> · <?= Helper::statusBadge($filing['status']) ?> · <?= $filing['filing_mode'] === 'self' ? 'Self Filing' : 'Hire Expert' ?></p>
</div>
<?php View::partial('partials/steps', ['filing' => $filing]); ?>

<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-h">Drop your Form 16</div><div class="itr-card-b">
<form method="post" action="/documents/<?= (int)$filing['id'] ?>" enctype="multipart/form-data">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><label>Document type</label>
        <select class="itr-form-control" name="doc_type">
            <?php foreach ($docTypes as $k => $label): ?><option value="<?= Helper::e($k) ?>"><?= Helper::e($label) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="itr-form-group"><label>File (PDF / JPG / PNG / ZIP)</label><input class="itr-form-control" type="file" name="document" required></div>
    <button class="itr-btn itr-btn-primary" type="submit">Upload &amp; Build Tax Summary</button>
    <?php if (in_array($filing['status'], ['summary_pending','payment_pending','ready_to_file'], true)): ?>
        <a class="itr-btn itr-btn-orange" href="/summary/<?= (int)$filing['id'] ?>">Go to Tax Summary</a>
    <?php endif; ?>
</form>
<div class="itr-soft-note itr-mt-md">Tip: Upload Form 16 first. Then add AIS/26AS and investment proofs for a cleaner summary.</div>
</div></div>

<div>
<?php if ($requests): ?>
<div class="itr-card"><div class="itr-card-h">Expert document requests</div><div class="itr-card-b">
<?php foreach ($requests as $r): ?>
    <p class="itr-text-ink"><?= Helper::e($r['message']) ?></p>
    <div class="itr-help"><?= Helper::e($r['required_docs']) ?></div>
<?php endforeach; ?>
</div></div>
<?php endif; ?>

<div class="itr-card"><div class="itr-card-h">Recommended checklist</div><div class="itr-card-b">
<ul class="itr-doc-checklist">
    <li><?= Helper::icon('file') ?> Form 16 (Part A &amp; B)</li>
    <li><?= Helper::icon('list') ?> AIS / Form 26AS PDF</li>
    <li><?= Helper::icon('rupee') ?> Bank interest certificate</li>
    <li><?= Helper::icon('shield') ?> 80C / 80D / home loan proofs</li>
    <li><?= Helper::icon('chart') ?> Capital gains / broker statements (if any)</li>
</ul>
</div></div>

<div class="itr-card"><div class="itr-card-h">Uploaded files</div>
<div class="itr-table-wrap"><table>
<tr><th>Type</th><th>File</th><th>Date</th></tr>
<?php if (!$docs): ?><tr><td colspan="3" class="itr-empty">No files yet — upload Form 16 to continue.</td></tr><?php endif; ?>
<?php foreach ($docs as $d): ?>
<tr>
    <td><?= Helper::e($docTypes[$d['doc_type']] ?? $d['doc_type']) ?></td>
    <td><?= Helper::e($d['original_name']) ?></td>
    <td><?= Helper::timeAgo($d['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table></div></div>
</div>
</div>
