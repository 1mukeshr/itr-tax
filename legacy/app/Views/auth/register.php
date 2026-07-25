<?php use App\Core\Helper; ?>
<h2>Create your account</h2>
<p>File ITR for FY <?= Helper::e($app['financial_year']) ?> with 100% accuracy focus — Self Filing or Hire an Expert.</p>
<form method="post" action="/register">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><label>Full name</label><input class="itr-form-control" name="name" required placeholder="As per PAN"></div>
    <div class="itr-form-group"><label>Email</label><input class="itr-form-control" type="email" name="email" required placeholder="you@email.com"></div>
    <div class="itr-form-row">
        <div class="itr-form-group"><label>Phone</label><input class="itr-form-control" name="phone" placeholder="10-digit mobile"></div>
        <div class="itr-form-group"><label>PAN</label><input class="itr-form-control itr-pan-input" name="pan" maxlength="10" placeholder="ABCDE1234F"></div>
    </div>
    <div class="itr-form-row">
        <div class="itr-form-group"><label>Password</label><input class="itr-form-control" type="password" name="password" required placeholder="Min 6 characters"></div>
        <div class="itr-form-group"><label>Confirm</label><input class="itr-form-control" type="password" name="password_confirmation" required placeholder="Repeat password"></div>
    </div>
    <button class="itr-btn itr-btn-orange itr-btn-block" type="submit">Create account &amp; Start Filing</button>
</form>
<p class="itr-auth-foot">Already have an account? <a href="/login">Login</a></p>
<p class="itr-help itr-mt-sm">By continuing you agree to secure handling of your tax documents for filing purposes only.</p>
