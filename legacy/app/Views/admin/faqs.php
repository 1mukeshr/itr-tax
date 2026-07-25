<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>FAQs</h1></div>
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="/admin/faqs">
<?= Helper::csrfField() ?>
<div class="itr-form-group"><label>Question</label><input class="itr-form-control" name="question" required></div>
<div class="itr-form-group"><label>Answer</label><textarea class="itr-form-control" name="answer" rows="4" required></textarea></div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Category</label><input class="itr-form-control" name="category" value="General"></div>
    <div class="itr-form-group"><label>Sort</label><input class="itr-form-control" type="number" name="sort_order" value="0"></div>
</div>
<button class="itr-btn itr-btn-primary" type="submit">Add</button>
</form>
</div></div>
<div class="itr-card"><div class="itr-table-wrap"><table>
<tr><th>Question</th><th></th></tr>
<?php foreach ($faqs as $f): ?>
<tr>
    <td><?= Helper::e($f['question']) ?></td>
    <td><form method="post" action="/admin/faqs/<?= (int)$f['id'] ?>/delete"><?= Helper::csrfField() ?><button class="itr-btn itr-btn-danger itr-btn-sm" type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
</table></div></div>
</div>
