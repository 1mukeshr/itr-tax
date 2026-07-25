<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>Users</h1></div>
<form method="get" class="itr-search-form">
    <input class="itr-form-control itr-search-input" name="q" value="<?= Helper::e($q) ?>" placeholder="Search...">
    <button class="itr-btn itr-btn-primary" type="submit">Search</button>
</form>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th></th></tr>
<?php foreach ($users as $u): ?>
<tr>
    <td><?= Helper::e($u['name']) ?></td>
    <td><?= Helper::e($u['email']) ?></td>
    <td><?= Helper::e($u['phone']) ?></td>
    <td><?= Helper::statusBadge($u['status'] === 'active' ? 'completed' : 'cancelled') ?></td>
    <td>
        <form method="post" action="/admin/users/<?= (int)$u['id'] ?>/toggle">
            <?= Helper::csrfField() ?>
            <button class="itr-btn itr-btn-outline itr-btn-sm" type="submit"><?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table></div></div>
