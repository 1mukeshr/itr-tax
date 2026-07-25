<?php use App\Core\Helper; ?>
<div class="itr-page-title">
    <h1>Filing #<?= (int)$filing['id'] ?></h1>
    <p><?= Helper::e($client['name']) ?> · <?= Helper::statusBadge($filing['status']) ?></p>
</div>
<div class="itr-grid-2">
<div>
<div class="itr-card"><div class="itr-card-h">Documents</div>
<div class="itr-table-wrap"><table>
<tr><th>Type</th><th>File</th><th></th></tr>
<?php foreach ($docs as $d): ?>
<tr><td><?= Helper::e($d['doc_type']) ?></td><td><?= Helper::e($d['original_name']) ?></td><td><a href="/ca/docs/<?= (int)$d['id'] ?>">Download</a></td></tr>
<?php endforeach; ?>
<?php if (!$docs): ?><tr><td colspan="3" class="itr-empty">No documents.</td></tr><?php endif; ?>
</table></div></div>

<div class="itr-card"><div class="itr-card-h">Notes</div><div class="itr-card-b">
<form method="post" action="/ca/filings/<?= (int)$filing['id'] ?>/note">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><textarea class="itr-form-control" name="note" rows="3" required></textarea></div>
    <label class="itr-check-inline"><input type="checkbox" name="is_internal" value="1" checked> Internal only</label>
    <button class="itr-btn itr-btn-primary itr-btn-sm" type="submit">Add note</button>
</form>
<?php foreach ($notes as $n): ?>
<div class="itr-note-block">
    <strong><?= Helper::e($n['author_name']) ?></strong>
    <?= $n['is_internal'] ? '<span class="itr-badge itr-badge-muted">Internal</span>' : '<span class="itr-badge itr-badge-info">Client</span>' ?>
    <p><?= Helper::e($n['note']) ?></p>
</div>
<?php endforeach; ?>
</div></div>
</div>

<div class="itr-card"><div class="itr-card-h">Actions</div><div class="itr-card-b">
<?php if (in_array($filing['status'], ['assigned','docs_requested','paid'], true)): ?>
<form method="post" action="/ca/filings/<?= (int)$filing['id'] ?>/review" class="itr-stack-form">
    <?= Helper::csrfField() ?><button class="itr-btn itr-btn-primary" type="submit">Start review</button>
</form>
<?php endif; ?>

<form method="post" action="/ca/filings/<?= (int)$filing['id'] ?>/request-docs" class="itr-stack-form">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><label>Request more docs</label><textarea class="itr-form-control" name="message" rows="2" required></textarea></div>
    <div class="itr-form-group"><input class="itr-form-control" name="required_docs" placeholder="Required docs"></div>
    <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Send request</button>
</form>

<form method="post" action="/ca/filings/<?= (int)$filing['id'] ?>/mark-filed" class="itr-stack-form">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><label>Mark filed</label><input class="itr-form-control" name="acknowledgement_no" required placeholder="ACK number"></div>
    <button class="itr-btn itr-btn-orange itr-btn-sm" type="submit">Mark Filed</button>
</form>

<form method="post" action="/ca/filings/<?= (int)$filing['id'] ?>/receipt" enctype="multipart/form-data">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><label>Upload ITR receipt</label><input class="itr-form-control" type="file" name="receipt" required></div>
    <div class="itr-form-group"><input class="itr-form-control" name="acknowledgement_no" value="<?= Helper::e($filing['acknowledgement_no']) ?>"></div>
    <button class="itr-btn itr-btn-primary itr-btn-sm" type="submit">Upload & complete</button>
</form>
</div></div>
</div>
