<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>Orders</h1></div>
<div class="itr-tabs">
    <a class="<?= !$filter ? 'itr-active' : '' ?>" href="/admin/orders">All</a>
    <?php foreach (['paid','assigned','under_review','filed','completed'] as $s): ?>
    <a class="<?= $filter === $s ? 'itr-active' : '' ?>" href="/admin/orders?status=<?= $s ?>"><?= Helper::e(Helper::statusLabel($s)) ?></a>
    <?php endforeach; ?>
</div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>ID</th><th>Client</th><th>Plan</th><th>CA</th><th>Status</th><th>Assign</th></tr>
<?php foreach ($orders as $o): ?>
<tr>
    <td>#<?= (int)$o['id'] ?></td>
    <td><?= Helper::e($o['client_name']) ?></td>
    <td><?= Helper::e($o['plan_name'] ?? '-') ?></td>
    <td><?= Helper::e($o['ca_name'] ?? '-') ?></td>
    <td><?= Helper::statusBadge($o['status']) ?></td>
    <td>
        <form method="post" action="/admin/orders/<?= (int)$o['id'] ?>/assign" class="itr-inline-form">
            <?= Helper::csrfField() ?>
            <select class="itr-form-control itr-select-sm" name="ca_id">
                <?php foreach ($cas as $ca): ?>
                <option value="<?= (int)$ca['id'] ?>" <?= (int)$o['ca_id'] === (int)$ca['id'] ? 'selected' : '' ?>><?= Helper::e($ca['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Save</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table></div></div>
