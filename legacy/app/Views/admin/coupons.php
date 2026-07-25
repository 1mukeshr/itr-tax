<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>Coupons</h1></div>
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="/admin/coupons">
<?= Helper::csrfField() ?>
<div class="itr-form-group"><label>Code</label><input class="itr-form-control" name="code" required></div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Type</label><select class="itr-form-control" name="type"><option value="percent">Percent</option><option value="fixed">Fixed</option></select></div>
    <div class="itr-form-group"><label>Value</label><input class="itr-form-control" type="number" step="0.01" name="value" required></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Max uses</label><input class="itr-form-control" type="number" name="max_uses" value="0"></div>
    <div class="itr-form-group"><label>Min amount</label><input class="itr-form-control" type="number" step="0.01" name="min_amount" value="0"></div>
</div>
<div class="itr-form-group"><label>Expires</label><input class="itr-form-control" type="date" name="expires_at"></div>
<button class="itr-btn itr-btn-primary" type="submit">Create</button>
</form>
</div></div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Code</th><th>Value</th><th>Used</th><th></th></tr>
<?php foreach ($coupons as $c): ?>
<tr>
    <td><?= Helper::e($c['code']) ?></td>
    <td><?= $c['type'] === 'percent' ? (float)$c['value'].'%' : Helper::money($c['value']) ?></td>
    <td><?= (int)$c['used_count'] ?></td>
    <td><form method="post" action="/admin/coupons/<?= (int)$c['id'] ?>/toggle"><?= Helper::csrfField() ?><button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Toggle</button></form></td>
</tr>
<?php endforeach; ?>
</table></div></div>
</div>
