<?php use App\Core\Helper; ?>
<div class="itr-page-title"><h1>Settings</h1></div>
<div class="itr-grid-2">
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="/admin/settings">
<?= Helper::csrfField() ?>
<div class="itr-form-group"><label>Site name</label><input class="itr-form-control" name="site_name" value="<?= Helper::e($settings['site_name'] ?? $app['name']) ?>"></div>
<div class="itr-form-group"><label>Support email</label><input class="itr-form-control" name="support_email" value="<?= Helper::e($settings['support_email'] ?? '') ?>"></div>
<div class="itr-form-group"><label>Support phone</label><input class="itr-form-control" name="support_phone" value="<?= Helper::e($settings['support_phone'] ?? '') ?>"></div>
<div class="itr-form-group"><label>Razorpay key</label><input class="itr-form-control" name="razorpay_key" value="<?= Helper::e($settings['razorpay_key'] ?? '') ?>"></div>
<div class="itr-form-group"><label>Address</label><textarea class="itr-form-control" name="company_address" rows="3"><?= Helper::e($settings['company_address'] ?? '') ?></textarea></div>
<button class="itr-btn itr-btn-primary" type="submit">Save</button>
</form>
</div></div>
<div class="itr-card"><div class="itr-card-h">Plans</div><div class="itr-card-b">
<?php foreach ($plans as $plan): ?>
<form method="post" action="/admin/plans/<?= (int)$plan['id'] ?>" class="itr-plan-split">
<?= Helper::csrfField() ?>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Name</label><input class="itr-form-control" name="name" value="<?= Helper::e($plan['name']) ?>"></div>
    <div class="itr-form-group"><label>Price</label><input class="itr-form-control" type="number" step="0.01" name="price" value="<?= Helper::e($plan['price']) ?>"></div>
</div>
<div class="itr-form-group"><label>Description</label><input class="itr-form-control" name="description" value="<?= Helper::e($plan['description']) ?>"></div>
<label class="itr-check-inline"><input type="checkbox" name="is_active" value="1" <?= $plan['is_active'] ? 'checked' : '' ?>> Active</label>
<button class="itr-btn itr-btn-outline itr-btn-sm" type="submit">Update</button>
</form>
<?php endforeach; ?>
</div></div>
</div>
