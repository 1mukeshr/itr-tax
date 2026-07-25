<?php

/**
 * Database installer + demo seeder
 * Run: php database/install.php
 */

require __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$config = require __DIR__ . '/../config/database.php';
$dbPath = $config['sqlite']['path'];

if (file_exists($dbPath)) {
    unlink($dbPath);
    echo "Removed old database.\n";
}

$pdo = Database::connection();
$schema = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($schema);
echo "Schema created.\n";

$password = password_hash('password', PASSWORD_BCRYPT);

$adminId = Database::insert('users', [
    'name' => 'Admin User',
    'email' => 'admin@itr-tax.in',
    'phone' => '9999900001',
    'password' => $password,
    'role' => 'admin',
    'status' => 'active',
]);

$caId = Database::insert('users', [
    'name' => 'Priya Sharma (CA)',
    'email' => 'ca@itr-tax.in',
    'phone' => '9999900002',
    'password' => $password,
    'role' => 'ca',
    'status' => 'active',
]);

Database::insert('ca_profiles', [
    'user_id' => $caId,
    'membership_no' => 'ICAI-445566',
    'specialization' => 'Salary & Capital Gains',
    'experience_years' => 8,
    'max_clients' => 40,
    'bio' => 'Experienced CA specializing in salaried ITR and capital gains.',
    'is_available' => 1,
]);

$ca2 = Database::insert('users', [
    'name' => 'Rahul Mehta (CA)',
    'email' => 'ca2@itr-tax.in',
    'phone' => '9999900003',
    'password' => $password,
    'role' => 'ca',
    'status' => 'active',
]);

Database::insert('ca_profiles', [
    'user_id' => $ca2,
    'membership_no' => 'ICAI-778899',
    'specialization' => 'Business & Professionals',
    'experience_years' => 12,
    'max_clients' => 50,
    'bio' => 'Handles ITR-3/4 and freelancers.',
    'is_available' => 1,
]);

$userId = Database::insert('users', [
    'name' => 'Amit Verma',
    'email' => 'user@itr-tax.in',
    'phone' => '9876543210',
    'pan' => 'ABCDE1234F',
    'password' => $password,
    'role' => 'user',
    'status' => 'active',
    'city' => 'Delhi',
    'state' => 'Delhi',
]);

$plans = [
    [
        'name' => 'Self Free',
        'slug' => 'self-free',
        'description' => 'Self ITR filing — Form 16 upload, tax summary, regime compare & file.',
        'price' => 0,
        'features' => json_encode(['Form 16 upload', 'Tax summary', 'Old vs New regime', 'Self e-file demo']),
        'itr_types' => 'ITR-1,ITR-2',
        'sort_order' => 0,
    ],
    [
        'name' => 'Basic',
        'slug' => 'basic',
        'description' => 'Salaried professionals — salary <50L, rental & interest. Expert files in 24hrs.',
        'price' => 2499,
        'features' => json_encode([
            'Salary, rental & interest',
            'CG: MFs, Stocks, Crypto',
            'Instant expert filing',
            '24hrs guaranteed',
            'Maximum tax savings focus',
            'Real-time tax consultation',
        ]),
        'itr_types' => 'ITR-1,ITR-2',
        'sort_order' => 1,
    ],
    [
        'name' => 'Standard',
        'slug' => 'standard',
        'description' => 'Active investors & traders — everything in Basic + live filing & AI accuracy check.',
        'price' => 3799,
        'features' => json_encode([
            'Everything in Basic',
            'Live tax filing',
            'Accuracy check (AI + Expert)',
            'FnO & Intraday support',
            'Maximum tax refunds focus',
            'Priority CA desk',
        ]),
        'itr_types' => 'ITR-1,ITR-2,ITR-3',
        'sort_order' => 2,
    ],
    [
        'name' => 'Premium',
        'slug' => 'premium',
        'description' => 'Global wealth builders — ESOPs/RSUs, US stocks, foreign income & Schedule FA.',
        'price' => 5999,
        'features' => json_encode([
            'Everything in Standard',
            'Live tax savings advisory',
            'Protect ESOP/RSU gains',
            'Accurate Schedule FA filing',
            'US stocks & foreign income',
            'Business income support',
        ]),
        'itr_types' => 'ITR-1,ITR-2,ITR-3,ITR-4',
        'sort_order' => 3,
    ],
];

foreach ($plans as $plan) {
    Database::insert('plans', $plan + ['is_active' => 1]);
}

Database::insert('coupons', [
    'code' => 'SAVE10',
    'type' => 'percent',
    'value' => 10,
    'max_uses' => 100,
    'min_amount' => 999,
    'expires_at' => '2027-03-31',
    'is_active' => 1,
]);

Database::insert('coupons', [
    'code' => 'FLAT500',
    'type' => 'fixed',
    'value' => 500,
    'max_uses' => 50,
    'min_amount' => 2499,
    'expires_at' => '2027-03-31',
    'is_active' => 1,
]);

$faqs = [
    ['Who should file an ITR?', "Anyone whose total income exceeds the basic exemption limit for the chosen tax regime should file an ITR. Filing is also useful to claim refunds, carry forward losses, apply for visas/loans, and keep your tax record clean — even when TDS was already deducted.", 'General', 1],
    ['Is TDS enough or do I still need to file ITR?', "TDS is only tax collected at source. You still need to file ITR to report full income, claim deductions/exemptions, reconcile AIS/26AS, and request any refund due.", 'General', 2],
    ['Self Filing or Hire an Expert — which should I pick?', "Choose Self Filing if you are salaried with Form 16 and simple interest/HRA. Hire an Expert for capital gains, F&O, crypto, ESOP/RSU, foreign income, multiple Form 16s, or if you want a CA to review and file.", 'Process', 3],
    ['How do I e-verify my ITR?', "After filing, e-verify within 120 days using Aadhaar OTP, net banking, or other options on the Income Tax portal. Without e-verification, your return may be treated as incomplete.", 'Process', 4],
    ['What documents do I need?', "Typically: PAN, Aadhaar, Form 16, AIS/Form 26AS, bank interest certificate, 80C/80D proofs, home-loan interest certificate, and capital-gains or business statements if applicable.", 'Documents', 5],
    ['How long does expert filing take?', "Most assisted filings are completed within 24 hours after documents and payment are complete. Complex capital gains or missing proofs may take longer if the CA requests additional documents.", 'Process', 6],
    ['How do I track my refund?', "Download your acknowledgement from ITR Tax, then track processing and refund status on the Income Tax e-filing portal. Refund timelines depend on CPC processing after successful e-verification.", 'Refund', 7],
    ['Can I switch from Self to Expert mid-way?', "Yes. If your return becomes complex after reviewing the tax summary, start an assisted filing or contact support to move to an expert plan before final submission.", 'Process', 8],
    ['Is my data secure on ITR Tax?', "Yes. Sessions use HTTPS-ready forms with CSRF protection, role-based access (User/CA/Admin), and documents are stored per filing for authorised users only. This demo showcases the product security model.", 'General', 9],
    ['What is old vs new tax regime?', "New regime offers lower slab rates with fewer deductions. Old regime lets you claim 80C, 80D, HRA and more. ITR Tax shows both on the Tax Summary step so you can pick the better outcome.", 'General', 10],
];

foreach ($faqs as [$q, $a, $cat, $sort]) {
    Database::insert('faqs', [
        'question' => $q,
        'answer' => $a,
        'category' => $cat,
        'sort_order' => $sort,
        'is_active' => 1,
    ]);
}

Database::insert('blogs', [
    'title' => 'How to file ITR online for FY 2025-26 (AY 2026-27)',
    'slug' => 'how-to-file-itr-online-fy-2025-26',
    'excerpt' => 'A practical checklist to e-file in minutes — from Form 16 upload to acknowledgement and e-verification.',
    'content' => "E-filing your income tax return for FY 2025-26 (AY 2026-27) is straightforward when you follow a clear sequence.\n\n1. Collect Form 16, AIS/26AS, bank interest proofs and investment statements.\n2. Create an ITR Tax account and choose Self Filing or Hire an Expert.\n3. Upload documents to your secure vault.\n4. Review the tax summary and compare old vs new regime.\n5. Self-file, or pay for expert review and CA assignment.\n6. Download acknowledgement and e-verify within 120 days.\n\nSalary, house property, capital gains, business income and other sources can all be handled — pick Expert mode when things get complex.",
    'author_id' => $adminId,
    'is_published' => 1,
    'published_at' => date('Y-m-d H:i:s'),
]);

Database::insert('blogs', [
    'title' => 'Old vs New tax regime — which gives maximum refund?',
    'slug' => 'old-vs-new-tax-regime',
    'excerpt' => 'Compare regimes before you file so you don’t leave refund money on the table.',
    'content' => "Choosing the right tax regime can change your refund or tax payable significantly.\n\nNew regime: lower slab rates, but most common deductions (like 80C, HRA) are restricted.\nOld regime: higher slabs in some cases, but you can claim 80C, 80D, home-loan interest, HRA and more.\n\nOn ITR Tax, open Tax Summary after uploading Form 16. We show illustrative tax under both regimes so you (or your CA) can pick the better outcome before filing.",
    'author_id' => $adminId,
    'is_published' => 1,
    'published_at' => date('Y-m-d H:i:s'),
]);

Database::insert('blogs', [
    'title' => 'Form 16 vs Form 26AS: what to check before filing',
    'slug' => 'form-16-vs-form-26as',
    'excerpt' => 'Avoid TDS mismatches that delay refunds or trigger notices.',
    'content' => "Before you hit submit, reconcile salary TDS in Form 16 with Form 26AS / AIS.\n\nCheck employer TAN, TDS amounts, interest income reported by banks, and any other TDS entries.\n\nIf figures differ, pause and clarify with your employer/bank — or let your assigned CA raise a document request. Catching mismatches early is the simplest way to protect your refund timeline.",
    'author_id' => $adminId,
    'is_published' => 1,
    'published_at' => date('Y-m-d H:i:s'),
]);

Database::insert('blogs', [
    'title' => 'Documents checklist for salaried ITR filing',
    'slug' => 'salaried-itr-documents-checklist',
    'excerpt' => 'Keep these papers ready so Self Filing or Expert Filing finishes faster.',
    'content' => "For most salaried taxpayers, this checklist covers 90% of filings:\n\n• PAN and Aadhaar\n• Form 16 (Part A and Part B)\n• AIS and Form 26AS\n• Bank interest certificate / Form 16A\n• Rent receipts / HRA proofs if claiming under old regime\n• 80C (ELSS, PPF, life insurance), 80D (health insurance)\n• Home loan interest certificate (if applicable)\n\nUpload what you have on the Documents step — your expert can request anything missing.",
    'author_id' => $adminId,
    'is_published' => 1,
    'published_at' => date('Y-m-d H:i:s'),
]);

$settings = [
    'site_name' => 'ITR Tax',
    'support_email' => 'support@itr-tax.in',
    'support_phone' => '+91 98765 43210',
    'razorpay_key' => '',
    'company_address' => 'ITR Tax Fintech Pvt Ltd, Bengaluru, India',
];
foreach ($settings as $k => $v) {
    Database::insert('settings', ['setting_key' => $k, 'setting_value' => $v]);
}

echo "Seed data inserted.\n";
echo "----------------------------------------\n";
echo "Demo logins (password: password)\n";
echo "  Admin : admin@itr-tax.in\n";
echo "  CA    : ca@itr-tax.in\n";
echo "  User  : user@itr-tax.in\n";
echo "----------------------------------------\n";
echo "Done. Start server: php -c php.ini -S 0.0.0.0:8000 -t public public/router.php\n";
