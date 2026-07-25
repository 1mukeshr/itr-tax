<?php

/**
 * End-to-end smoke test for ITR Tax expert flow.
 * Run: php tools/e2e-smoke.php
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Document;
use App\Models\ItrFiling;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\TaxExpertAssignment;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function ok(string $msg): void
{
    echo "[OK] {$msg}\n";
}

function fail(string $msg): void
{
    echo "[FAIL] {$msg}\n";
    exit(1);
}

try {
    if (Role::count() < 3) {
        fail('Roles missing — run php artisan db:seed');
    }
    ok('Roles present');

    $user = User::where('email', 'user@itr-tax.in')->first();
    $admin = User::where('email', 'admin')->first();
    $expert = User::where('email', 'ca@itr-tax.in')->first();

    if (! $user || ! $admin || ! $expert) {
        fail('Seeded accounts missing');
    }
    if ($user->role !== 'user' || $admin->role !== 'admin' || $expert->role !== 'ca') {
        fail('Role accessors broken: user='.$user->role.' admin='.$admin->role.' expert='.$expert->role);
    }
    if (! Hash::check('password', $user->password)) {
        fail('User password hash invalid');
    }
    ok('Auth users + roles + password');

    if (! $user->profile) {
        fail('User profile missing');
    }
    ok('User profile pan='.($user->pan ?: '-'));

    $plan = Plan::where('slug', 'basic')->first();
    if (! $plan) {
        fail('Basic plan missing');
    }

    $filing = ItrFiling::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'assessment_year' => config('itr.assessment_year'),
        'itr_type' => 'ITR-1',
        'filing_mode' => 'assisted',
        'income_profile' => 'salaried',
        'status' => 'documents_pending',
        'pan' => $user->pan ?: 'ABCDE1234F',
        'amount' => $plan->price,
    ]);
    logFilingStatus($filing->id, null, 'documents_pending', $user->id, 'e2e start');
    ok('Created itr_orders #'.$filing->id);

    Storage::disk('local')->put('uploads/e2e-form16.txt', "Form 16 demo\n");
    Document::create([
        'filing_id' => $filing->id,
        'user_id' => $user->id,
        'doc_type' => 'form16',
        'original_name' => 'Form16.pdf',
        'file_path' => 'uploads/e2e-form16.txt',
        'file_size' => 12,
        'mime_type' => 'text/plain',
        'uploaded_by' => $user->id,
    ]);
    $filing->update(['status' => 'payment_pending']);
    logFilingStatus($filing->id, 'documents_pending', 'payment_pending', $user->id, 'e2e docs');
    ok('Document saved in itr_documents');

    Payment::create([
        'filing_id' => $filing->id,
        'user_id' => $user->id,
        'amount' => $plan->price,
        'discount' => 0,
        'method' => 'demo',
        'transaction_id' => 'E2E'.time(),
        'status' => 'success',
        'paid_at' => now(),
    ]);
    $filing->update(['status' => 'paid']);
    logFilingStatus($filing->id, 'payment_pending', 'paid', $user->id, 'e2e pay');
    ok('Payment recorded');

    $filing->update(['ca_id' => $expert->id, 'status' => 'assigned']);
    TaxExpertAssignment::create([
        'order_id' => $filing->id,
        'tax_expert_id' => $expert->id,
        'assigned_by' => $admin->id,
        'status' => 'active',
        'remark' => 'e2e assign',
        'assigned_at' => now(),
    ]);
    logFilingStatus($filing->id, 'paid', 'assigned', $admin->id, 'e2e assign');
    ok('Tax expert assignment saved');

    $filing->update(['status' => 'under_review']);
    $filing->update([
        'status' => 'filed',
        'acknowledgement_no' => 'ACK-E2E-'.time(),
        'filed_at' => now(),
    ]);
    Storage::disk('local')->put('receipts/e2e-ack.txt', 'ACK demo');
    Receipt::create([
        'filing_id' => $filing->id,
        'uploaded_by' => $expert->id,
        'acknowledgement_no' => $filing->acknowledgement_no,
        'file_path' => 'receipts/e2e-ack.txt',
        'original_name' => 'ack.txt',
    ]);
    $filing->update(['status' => 'completed']);
    logFilingStatus($filing->id, 'filed', 'completed', $expert->id, 'e2e done');
    ok('Expert filed + completed');

    $fresh = ItrFiling::with(['user', 'ca', 'plan', 'documents', 'payments', 'assignments', 'statusLogs'])->find($filing->id);
    if ($fresh->status !== 'completed' || $fresh->documents->isEmpty() || $fresh->assignments->isEmpty()) {
        fail('Relations incomplete');
    }
    ok('Relations: docs='.$fresh->documents->count().' payments='.$fresh->payments->count().' logs='.$fresh->statusLogs->count());

    // Quick register-path profile create
    $email = 'e2e'.time().'@example.com';
    $nu = User::create([
        'name' => 'E2E User',
        'email' => $email,
        'password' => 'secret12',
        'role_id' => Role::idFor('user'),
        'status' => 'active',
    ]);
    UserProfile::create(['user_id' => $nu->id, 'pan' => 'ZZZZZ1234Z']);
    $nu->load(['roleRelation', 'profile']);
    if ($nu->role !== 'user' || $nu->pan !== 'ZZZZZ1234Z') {
        fail('New register profile path broken');
    }
    ok('Register-style user+profile works');

    echo "\nAll smoke checks passed.\n";
} catch (Throwable $e) {
    fail($e->getMessage()."\n".$e->getTraceAsString());
}
