<?php use App\Core\Helper; $edit = !empty($ca); ?>
<div class="itr-page-title"><h1><?= $edit ? 'Edit CA' : 'Add CA' ?></h1></div>
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="<?= $edit ? '/admin/cas/' . (int)$ca['id'] : '/admin/cas' ?>">
<?= Helper::csrfField() ?>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Name</label><input class="itr-form-control" name="name" required value="<?= Helper::e($ca['name'] ?? '') ?>"></div>
    <div class="itr-form-group"><label>Email</label>
        <?php if ($edit): ?><input class="itr-form-control" value="<?= Helper::e($ca['email']) ?>" disabled>
        <?php else: ?><input class="itr-form-control" type="email" name="email" required><?php endif; ?>
    </div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Phone</label><input class="itr-form-control" name="phone" value="<?= Helper::e($ca['phone'] ?? '') ?>"></div>
    <div class="itr-form-group"><label>Password</label><input class="itr-form-control" type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Membership No</label><input class="itr-form-control" name="membership_no" value="<?= Helper::e($ca['membership_no'] ?? '') ?>"></div>
    <div class="itr-form-group"><label>Specialization</label><input class="itr-form-control" name="specialization" value="<?= Helper::e($ca['specialization'] ?? '') ?>"></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Experience</label><input class="itr-form-control" type="number" name="experience_years" value="<?= Helper::e($ca['experience_years'] ?? '0') ?>"></div>
    <div class="itr-form-group"><label>Max clients</label><input class="itr-form-control" type="number" name="max_clients" value="<?= Helper::e($ca['max_clients'] ?? '50') ?>"></div>
</div>
<div class="itr-form-group"><label>Bio</label><textarea class="itr-form-control" name="bio" rows="3"><?= Helper::e($ca['bio'] ?? '') ?></textarea></div>
<?php if ($edit): ?>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Status</label>
        <select class="itr-form-control" name="status">
            <option value="active" <?= ($ca['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($ca['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <div class="itr-form-group"><label>Available</label>
        <select class="itr-form-control" name="is_available">
            <option value="1" <?= !empty($ca['is_available']) ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= empty($ca['is_available']) ? 'selected' : '' ?>>No</option>
        </select>
    </div>
</div>
<?php endif; ?>
<button class="itr-btn itr-btn-primary" type="submit">Save</button>
<a class="itr-btn itr-btn-outline" href="/admin/cas">Cancel</a>
</form>
</div></div>
