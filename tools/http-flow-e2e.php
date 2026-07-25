<?php

use App\Models\ItrFiling;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

/**
 * Full assisted-filing HTTP flow smoke test.
 * Requires: php artisan serve on 127.0.0.1:8000
 * Run: php tools/http-flow-e2e.php
 */
$base = getenv('APP_URL') ?: 'http://127.0.0.1:8000';
$adminBase = getenv('ADMIN_URL') ?: 'http://127.0.0.1:8001';
$cookie = sys_get_temp_dir().'/itr-e2e-cookies-'.getmypid().'.txt';
@unlink($cookie);

$failed = 0;
$passed = 0;

function ok(string $msg): void
{
    global $passed;
    $passed++;
    echo "[OK] {$msg}\n";
}

function fail(string $msg): void
{
    global $failed;
    $failed++;
    echo "[FAIL] {$msg}\n";
}

function request(string $method, string $path, array $opts = []): array
{
    global $base, $cookie;
    $root = $opts['base'] ?? $base;
    $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
        ? $path
        : $root.$path;
    $ch = curl_init($url);
    $headers = $opts['headers'] ?? [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $opts['follow'] ?? false,
        CURLOPT_MAXREDIRS => 8,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if (! empty($opts['multipart'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['multipart']);
    } elseif (isset($opts['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opts['body']));
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);

        return ['status' => 0, 'headers' => '', 'body' => '', 'error' => $err, 'location' => null];
    }

    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $hdr = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    $location = null;
    if (preg_match('/^Location:\s*(.+)$/mi', $hdr, $m)) {
        $location = trim($m[1]);
    }

    return compact('status', 'headers', 'body', 'location') + ['error' => null, 'headers' => $hdr];
}

function csrfFrom(string $html): string
{
    if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="_token"\s+content="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return $m[1];
    }

    return '';
}

function loginAs(string $email, string $password, ?string $portalBase = null): bool
{
    global $cookie, $base, $adminBase;
    @unlink($cookie);
    $useBase = $portalBase ?: (strtolower($email) === 'admin' ? $adminBase : $base);
    $r = request('GET', '/login', ['base' => $useBase]);
    $token = csrfFrom($r['body']);
    if (! $token) {
        fail('Login page CSRF missing on '.$useBase);

        return false;
    }
    $r = request('POST', '/login', [
        'base' => $useBase,
        'body' => ['_token' => $token, 'email' => $email, 'password' => $password],
        'follow' => false,
    ]);
    if (! in_array($r['status'], [302, 303], true) || str_contains((string) $r['location'], '/login')) {
        fail("Login failed for {$email} status={$r['status']} loc=".($r['location'] ?? '-'));

        return false;
    }
    ok("Logged in as {$email}");

    return true;
}

function logout(): void
{
    global $cookie;
    $r = request('GET', '/login', ['follow' => true]);
    $token = csrfFrom($r['body']);
    // Try panel pages for CSRF if already authed
    if (! $token) {
        $r = request('GET', '/admin', ['follow' => true]);
        $token = csrfFrom($r['body']);
    }
    if ($token) {
        request('POST', '/logout', ['body' => ['_token' => $token]]);
    }
    @unlink($cookie);
}

function assertRedirect(array $r, string $needle, string $label): void
{
    if (! in_array($r['status'], [302, 303], true) || ! $r['location'] || ! str_contains($r['location'], $needle)) {
        fail("{$label}: expected redirect containing '{$needle}', got {$r['status']} loc=".($r['location'] ?? '-'));
    } else {
        ok($label.' → '.$needle);
    }
}

function assertPage(string $path, string $needle, string $label): void
{
    $r = request('GET', $path, ['follow' => true]);
    if ($r['status'] !== 200 || ! str_contains($r['body'], $needle)) {
        fail("{$label}: GET {$path} status={$r['status']} missing '{$needle}'");
    } else {
        ok($label);
    }
}

echo "=== Public pages ===\n";
foreach (['/' => 'ITR', '/pricing' => 'plan', '/how-it-works' => 'How', '/faqs' => 'FAQ', '/login' => 'password', '/register' => 'Register'] as $path => $needle) {
    $r = request('GET', $path);
    if ($r['status'] === 200 && (stripos($r['body'], $needle) !== false || strlen($r['body']) > 500)) {
        ok("GET {$path}");
    } else {
        fail("GET {$path} status={$r['status']} err=".($r['error'] ?? ''));
    }
}

echo "\n=== Assisted filing flow ===\n";
if (! loginAs('user@itr-tax.in', 'password')) {
    echo "Aborting.\n";
    exit(1);
}

// Ensure profile complete
$r = request('GET', '/profile/complete', ['follow' => true]);
$token = csrfFrom($r['body']);
if (str_contains($r['body'], 'name="pan"') || str_contains($r['body'], 'Complete your profile') || str_contains($r['body'], 'complete-profile')) {
    $r = request('POST', '/profile/complete', [
        'body' => [
            '_token' => $token ?: csrfFrom(request('GET', '/profile/complete')['body']),
            'name' => 'Demo User',
            'phone' => '9876543210',
            'pan' => 'ABCDE1234F',
            'address' => 'Demo Street',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
        ],
    ]);
    assertRedirect($r, '/dashboard', 'Complete profile');
} else {
    ok('Profile already complete (or redirected)');
}

$r = request('GET', '/itr/service', ['follow' => true]);
$token = csrfFrom($r['body']);
if (! $token) {
    fail('Choose service CSRF missing');
} else {
    ok('Choose service page');
}

// Get a plan id from page or DB bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$planId = (int) (Plan::where('slug', 'basic')->value('id') ?: Plan::where('is_active', true)->where('slug', '!=', 'self-free')->value('id'));

$r = request('POST', '/itr/new', [
    'body' => [
        '_token' => $token,
        'filing_mode' => 'assisted',
        'income_profile' => 'salaried',
        'itr_type' => 'ITR-1',
        'pan' => 'ABCDE1234F',
        'plan_id' => $planId,
    ],
]);
assertRedirect($r, '/itr/questions/', 'Create filing');

if (! preg_match('#/itr/questions/(\d+)#', $r['location'] ?? '', $m)) {
    fail('Could not parse filing id from redirect');
    echo "Failed={$failed} Passed={$passed}\n";
    exit(1);
}
$fid = (int) $m[1];
ok("Filing #{$fid}");

$r = request('GET', "/itr/questions/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$answers = [
    'q_employment' => 'salaried',
    'q_income_range' => '5_10',
    'q_capital_gains' => 'no',
    'q_house_property' => 'no',
    'q_foreign_income' => 'no',
    'q_crypto_fno' => 'no',
    'q_deductions' => 'yes',
    'q_home_loan' => 'no',
    'q_tax_regime' => 'new',
    'q_it_notice' => 'no',
];
$r = request('POST', "/itr/questions/{$fid}", ['body' => array_merge(['_token' => $token], $answers)]);
assertRedirect($r, "/documents/{$fid}", 'Save questions');

$r = request('GET', "/documents/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$tmp = sys_get_temp_dir().'/e2e-form16.pdf';
file_put_contents($tmp, "%PDF-1.4\nForm 16 demo content for e2e\n%%EOF\n");
$r = request('POST', "/documents/{$fid}", [
    'multipart' => [
        '_token' => $token,
        'doc_type' => 'form16',
        'document' => new CURLFile($tmp, 'application/pdf', 'Form16.pdf'),
    ],
]);
if (! in_array($r['status'], [302, 303, 200], true)) {
    fail('Upload document status='.$r['status'].' err='.($r['error'] ?? ''));
} else {
    ok('Upload document');
}

$r = request('GET', "/documents/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$r = request('POST', "/documents/{$fid}/continue", ['body' => ['_token' => $token]]);
assertRedirect($r, "/itr/review-details/{$fid}", 'Continue after documents');

$r = request('GET', "/itr/review-details/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$r = request('POST', "/itr/review-details/{$fid}", ['body' => ['_token' => $token]]);
assertRedirect($r, "/payment/{$fid}", 'Confirm details');

$r = request('GET', "/payment/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$r = request('POST', "/payment/{$fid}", [
    'body' => [
        '_token' => $token,
        'method' => 'upi',
        'coupon_code' => 'SAVE10',
    ],
]);
assertRedirect($r, "/track/{$fid}", 'Process payment');

$status = ItrFiling::find($fid)?->status;
if (in_array($status, ['paid', 'assigned'], true)) {
    ok('Filing status='.$status.' (paid/assigned)');
} else {
    fail("Expected paid or assigned, got {$status}");
}

logout();

echo "\n=== Admin assign expert ===\n";
$status = ItrFiling::find($fid)?->status;
if ($status === 'assigned') {
    ok('Expert already auto-assigned after payment');
} else {
    if (! loginAs('admin', 'admin@2026')) {
        exit(1);
    }
    assertPage('/admin/orders', 'Order', 'Admin orders page');
    $expertId = (int) User::where('email', 'ca@itr-tax.in')->value('id');
    $r = request('GET', '/admin/orders', ['follow' => true]);
    $token = csrfFrom($r['body']);
    $r = request('POST', "/admin/orders/{$fid}/assign", [
        'body' => ['_token' => $token, 'ca_id' => $expertId],
    ]);
    if (! in_array($r['status'], [302, 303], true)) {
        fail('Assign expert status='.$r['status']);
    } else {
        ok('Admin assigned tax expert');
    }
    $status = ItrFiling::find($fid)?->status;
    if ($status === 'assigned') {
        ok('Filing status=assigned');
    } else {
        fail("Expected assigned, got {$status}");
    }
    logout();
}
// Always clear session before expert login
logout();

echo "\n=== Tax expert review → send summary ===\n";
$assignedEmail = User::find(ItrFiling::find($fid)?->ca_id)?->email ?: 'ca@itr-tax.in';
if (! loginAs($assignedEmail, 'password')) {
    // Auto-assign may pick a CA without the demo password — reassign seeded expert.
    logout();
    if (! loginAs('admin', 'admin@2026')) {
        fail('Admin login failed while fixing expert assignment');
        exit(1);
    }
    $expertId = (int) User::where('email', 'ca@itr-tax.in')->value('id');
    $r = request('GET', '/admin/orders', ['follow' => true]);
    $token = csrfFrom($r['body']);
    $r = request('POST', "/admin/orders/{$fid}/assign", [
        'body' => ['_token' => $token, 'ca_id' => $expertId],
    ]);
    if (! in_array($r['status'], [302, 303], true)) {
        fail('Reassign expert failed status='.$r['status']);
        exit(1);
    }
    ok('Reassigned filing to ca@itr-tax.in for demo password');
    logout();
    $assignedEmail = 'ca@itr-tax.in';
    if (! loginAs($assignedEmail, 'password')) {
        fail('Login failed for '.$assignedEmail);
        exit(1);
    }
}
ok('Using assigned expert '.$assignedEmail);
assertPage("/ca/filings/{$fid}", 'Filing', 'Expert filing page');
$r = request('GET', "/ca/filings/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$r = request('POST', "/ca/filings/{$fid}/review", ['body' => ['_token' => $token]]);
if (! in_array($r['status'], [302, 303], true)) {
    fail('Start review failed');
} else {
    ok('Expert started review');
}

$r = request('GET', "/ca/filings/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$r = request('POST', "/ca/filings/{$fid}/send-summary", [
    'body' => [
        '_token' => $token,
        'gross_salary' => 900000,
        'total_deductions' => 150000,
        'tds_deducted' => 68000,
        'tax_regime' => 'new',
        'expert_note' => 'Looks good — new regime recommended.',
    ],
]);
if (! in_array($r['status'], [302, 303], true)) {
    fail('Send summary failed status='.$r['status']);
} else {
    ok('Expert sent tax summary');
}
$status = ItrFiling::find($fid)?->status;
if ($status === 'customer_summary') {
    ok('Filing status=customer_summary');
} else {
    fail("Expected customer_summary, got {$status}");
}
logout();

echo "\n=== Customer approve summary ===\n";
if (! loginAs('user@itr-tax.in', 'password')) {
    exit(1);
}
assertPage("/summary/{$fid}", 'Tax', 'Customer summary page');
$r = request('GET', "/summary/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$r = request('POST', "/summary/{$fid}/approve", ['body' => ['_token' => $token]]);
assertRedirect($r, "/track/{$fid}", 'Customer approve summary');
$status = ItrFiling::find($fid)?->status;
if ($status === 'customer_approved') {
    ok('Filing status=customer_approved');
} else {
    fail("Expected customer_approved, got {$status}");
}
logout();

echo "\n=== Expert file + receipt ===\n";
$assignedEmail = User::find(ItrFiling::find($fid)?->ca_id)?->email ?: 'ca@itr-tax.in';
if (! loginAs($assignedEmail, 'password')) {
    fail('Login failed for expert '.$assignedEmail.' (use seeded ca@itr-tax.in password)');
    exit(1);
}
$r = request('GET', "/ca/filings/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$ack = 'ACKHTTP'.time();
$r = request('POST', "/ca/filings/{$fid}/mark-filed", [
    'body' => ['_token' => $token, 'acknowledgement_no' => $ack],
]);
if (! in_array($r['status'], [302, 303], true)) {
    fail('Mark filed failed');
} else {
    ok('Expert marked filed');
}

$r = request('GET', "/ca/filings/{$fid}", ['follow' => true]);
$token = csrfFrom($r['body']);
$tmpAck = sys_get_temp_dir().'/e2e-ack.pdf';
file_put_contents($tmpAck, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\nACK {$ack}\n");
$r = request('POST', "/ca/filings/{$fid}/receipt", [
    'multipart' => [
        '_token' => $token,
        'acknowledgement_no' => $ack,
        'receipt' => new CURLFile($tmpAck, 'application/pdf', 'ack.pdf'),
    ],
]);
if (! in_array($r['status'], [302, 303, 200], true)) {
    fail('Upload receipt failed status='.$r['status'].' body='.substr(strip_tags($r['body']), 0, 120));
} else {
    ok('Expert uploaded receipt');
}
$status = ItrFiling::query()->find($fid)?->status;
if ($status === 'completed') {
    ok('Filing status=completed');
} else {
    fail("Expected completed, got {$status}");
}
logout();

echo "\n=== Customer ACK download ===\n";
if (! loginAs('user@itr-tax.in', 'password')) {
    exit(1);
}
assertPage("/acknowledgement/{$fid}", $ack, 'Acknowledgement page');
$r = request('GET', "/acknowledgement/{$fid}/download", ['follow' => true]);
if ($r['status'] === 200 && strlen($r['body']) > 0) {
    ok('ACK download works');
} else {
    fail('ACK download status='.$r['status']);
}

echo "\n=== Admin panels ===\n";
logout();
if (! loginAs('admin', 'admin@2026', $adminBase)) {
    fail('Admin portal login required on '.$adminBase);
} else {
    foreach (['/admin', '/admin/users', '/admin/payments', '/admin/settings'] as $path) {
        $r = request('GET', $path, ['follow' => true, 'base' => $adminBase]);
        if ($r['status'] === 200 && strlen($r['body']) > 200) {
            ok("GET {$adminBase}{$path}");
        } else {
            fail("GET {$adminBase}{$path} status={$r['status']}");
        }
    }
}

echo "\n========\nPassed: {$passed}  Failed: {$failed}\n";
@unlink($cookie);
exit($failed > 0 ? 1 : 0);
