<?php use App\Core\Helper; ?>
<h2>Welcome back</h2>
<p>Login to continue e-filing for AY <?= Helper::e($app['assessment_year']) ?> — track filings, upload documents and download acknowledgements.</p>
<form method="post" action="/login">
    <?= Helper::csrfField() ?>
    <div class="itr-form-group"><label>Email</label><input class="itr-form-control" type="email" name="email" required placeholder="you@email.com"></div>
    <div class="itr-form-group"><label>Password</label><input class="itr-form-control" type="password" name="password" required placeholder="Your password"></div>
    <button class="itr-btn itr-btn-primary itr-btn-block" type="submit">Login securely</button>
</form>
<p class="itr-auth-foot">New to <?= Helper::e($app['name']) ?>? <a href="/register">Create account &amp; Start Filing</a></p>
<div class="itr-box itr-demo-box">
    <strong>Demo logins</strong>
    <div class="itr-help">User · user@itr-tax.in / password</div>
    <div class="itr-help">CA · ca@itr-tax.in / password</div>
    <div class="itr-help">Admin · admin@itr-tax.in / password</div>
</div>
