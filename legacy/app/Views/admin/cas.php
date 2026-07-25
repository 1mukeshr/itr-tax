<?php use App\Core\Helper; ?>
<div class="itr-page-title-row">
    <h1>CA Management</h1>
    <a class="itr-btn itr-btn-primary" href="/admin/cas/create">Add CA</a>
</div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Name</th><th>Membership</th><th>Clients</th><th></th></tr>
<?php foreach ($cas as $c): ?>
<tr>
    <td><?= Helper::e($c['name']) ?><div class="itr-help"><?= Helper::e($c['email']) ?></div></td>
    <td><?= Helper::e($c['membership_no']) ?></td>
    <td><?= (int)$c['client_count'] ?></td>
    <td><a href="/admin/cas/<?= (int)$c['id'] ?>/edit">Edit</a></td>
</tr>
<?php endforeach; ?>
</table></div></div>
