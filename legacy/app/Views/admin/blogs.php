<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>Blogs</h1></div>
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="/admin/blogs">
<?= Helper::csrfField() ?>
<div class="itr-form-group"><label>Title</label><input class="itr-form-control" name="title" required></div>
<div class="itr-form-group"><label>Excerpt</label><textarea class="itr-form-control" name="excerpt" rows="2"></textarea></div>
<div class="itr-form-group"><label>Content</label><textarea class="itr-form-control" name="content" rows="5"></textarea></div>
<label class="itr-check-inline"><input type="checkbox" name="is_published" value="1" checked> Publish</label>
<button class="itr-btn itr-btn-primary" type="submit">Save</button>
</form>
</div></div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Title</th><th>Status</th><th></th></tr>
<?php foreach ($blogs as $b): ?>
<tr>
    <td><?= Helper::e($b['title']) ?></td>
    <td><?= $b['is_published'] ? 'Published' : 'Draft' ?></td>
    <td><form method="post" action="/admin/blogs/<?= (int)$b['id'] ?>/delete"><?= Helper::csrfField() ?><button class="itr-btn itr-btn-danger itr-btn-sm" type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
</table></div></div>
</div>
