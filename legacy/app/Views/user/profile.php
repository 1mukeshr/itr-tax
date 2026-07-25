<?php use App\Core\Helper; ?>
<div class="itr-panel-hero">
    <div>
        <h1>My Profile</h1>
        <p>Keep PAN and contact details updated for smooth e-filing.</p>
    </div>
</div>
<div class="itr-card"><div class="itr-card-b">
<form method="post" action="/profile">
<?= Helper::csrfField() ?>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Name</label><input class="itr-form-control" name="name" value="<?= Helper::e($user['name']) ?>" required></div>
    <div class="itr-form-group"><label>Email</label><input class="itr-form-control" value="<?= Helper::e($user['email']) ?>" disabled></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Phone</label><input class="itr-form-control" name="phone" value="<?= Helper::e($user['phone']) ?>"></div>
    <div class="itr-form-group"><label>PAN</label><input class="itr-form-control itr-pan-input" name="pan" value="<?= Helper::e($user['pan']) ?>" maxlength="10"></div>
</div>
<div class="itr-form-group"><label>Address</label><textarea class="itr-form-control" name="address" rows="2"><?= Helper::e($user['address']) ?></textarea></div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>City</label><input class="itr-form-control" name="city" value="<?= Helper::e($user['city']) ?>"></div>
    <div class="itr-form-group"><label>State</label><input class="itr-form-control" name="state" value="<?= Helper::e($user['state']) ?>"></div>
</div>
<div class="itr-form-row">
    <div class="itr-form-group"><label>Pincode</label><input class="itr-form-control" name="pincode" value="<?= Helper::e($user['pincode']) ?>"></div>
    <div class="itr-form-group"><label>New password</label><input class="itr-form-control" type="password" name="password" placeholder="Leave blank to keep"></div>
</div>
<button class="itr-btn itr-btn-primary" type="submit"><?= Helper::icon('check') ?> Save profile</button>
</form>
</div></div>
