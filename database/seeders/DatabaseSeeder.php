<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Plan;
use App\Models\ProcessStep;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password';
        $adminPassword = 'admin@2026';

        foreach ([
            ['User', 'user', 'Taxpayer / client account'],
            ['Tax Expert', 'ca', 'Tax expert who reviews and files returns'],
            ['Admin', 'admin', 'Platform administrator'],
        ] as [$name, $slug, $desc]) {
            Role::updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $desc]);
        }

        // Migrate legacy admin@itr-tax.in → login ID "admin"
        User::where('email', 'admin@itr-tax.in')->update(['email' => 'admin']);

        $admin = User::updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Admin User',
                'phone' => '9999900001',
                'password' => $adminPassword,
                'role_id' => Role::idFor('admin'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(['user_id' => $admin->id], []);

        $expert = User::updateOrCreate(
            ['email' => 'ca@itr-tax.in'],
            [
                'name' => 'Priya Sharma',
                'phone' => '9999900002',
                'password' => $password,
                'role_id' => Role::idFor('ca'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $expert->id],
            [
                'membership_no' => 'TE-445566',
                'specialization' => 'Salary & Capital Gains',
                'experience_years' => 8,
                'max_clients' => 40,
                'bio' => 'Tax expert specializing in salaried ITR and capital gains.',
                'is_available' => true,
            ]
        );

        $expert2 = User::updateOrCreate(
            ['email' => 'ca2@itr-tax.in'],
            [
                'name' => 'Rahul Mehta',
                'phone' => '9999900003',
                'password' => $password,
                'role_id' => Role::idFor('ca'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $expert2->id],
            [
                'membership_no' => 'TE-778899',
                'specialization' => 'Business & Professionals',
                'experience_years' => 12,
                'max_clients' => 50,
                'bio' => 'Handles ITR-3/4 and freelancers as tax expert.',
                'is_available' => true,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@itr-tax.in'],
            [
                'name' => 'Amit Verma',
                'phone' => '9876543210',
                'password' => $password,
                'role_id' => Role::idFor('user'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pan' => 'ABCDE1234F',
                'city' => 'Delhi',
                'state' => 'Delhi',
            ]
        );

        $plans = [
            ['name' => 'Self Free', 'slug' => 'self-free', 'description' => 'Self ITR preparation — Form 16 upload, tax summary, regime compare and filing reference.', 'price' => 0, 'features' => json_encode(['Form 16 upload', 'Tax summary (figures you enter)', 'Old vs New regime estimate', 'Filing reference & e-verify tips']), 'itr_types' => 'ITR-1,ITR-2', 'sort_order' => 0],
            ['name' => 'Basic', 'slug' => 'basic', 'description' => 'Salaried professionals — salary, rental & interest. Target expert turnaround within 24 hours after complete docs & payment.', 'price' => 2499, 'features' => json_encode(['Salary, rental & interest', 'Listed equity / MF capital gains (ITR-2)', 'Expert review after payment', 'Target 24-hour turnaround', 'Regime comparison & deduction capture', 'Expert notes in your filing']), 'itr_types' => 'ITR-1,ITR-2', 'sort_order' => 1],
            ['name' => 'Standard', 'slug' => 'standard', 'description' => 'Active investors & traders — everything in Basic plus F&O / intraday support and priority expert desk.', 'price' => 3799, 'features' => json_encode(['Everything in Basic', 'Expert-assisted filing with tracking', 'Expert review checklist', 'F&O & Intraday support', 'Priority tax expert desk']), 'itr_types' => 'ITR-2,ITR-3', 'sort_order' => 2],
            ['name' => 'Premium', 'slug' => 'premium', 'description' => 'Complex portfolios — ESOPs/RSUs, foreign income, Schedule FA and business income support.', 'price' => 5999, 'features' => json_encode(['Everything in Standard', 'ESOP/RSU reporting support', 'Schedule FA reporting support', 'US stocks & foreign income', 'Business / professional income support']), 'itr_types' => 'ITR-2,ITR-3,ITR-4', 'sort_order' => 3],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan + ['is_active' => true]);
        }

        Coupon::updateOrCreate(
            ['code' => 'SAVE10'],
            ['type' => 'percent', 'value' => 10, 'max_uses' => 100, 'min_amount' => 999, 'expires_at' => '2027-03-31', 'is_active' => true]
        );
        Coupon::updateOrCreate(
            ['code' => 'FLAT500'],
            ['type' => 'fixed', 'value' => 500, 'max_uses' => 50, 'min_amount' => 2499, 'expires_at' => '2027-03-31', 'is_active' => true]
        );

        $faqs = [
            ['Who should file an ITR?', 'File if your total income exceeds the basic exemption limit for your regime, or when you need to claim a refund, carry forward losses, report foreign assets, or meet other Income Tax Act filing conditions.', 'General', 1],
            ['Is TDS enough or do I still need to file ITR?', 'TDS is only tax collected at source. You still need to file an ITR to report full income and claim any refund due.', 'General', 2],
            ['Self Filing or Hire an Expert — which should I pick?', 'Choose Self Filing if you are salaried with Form 16 and straightforward deductions. Hire an Expert for capital gains, F&O, crypto, foreign income, or if you want a specialist review.', 'Process', 3],
            ['How do I e-verify my ITR?', 'After your return is uploaded on the Income Tax e-filing portal, e-verify within 30 days (or the timeline notified by CBDT for your return) using Aadhaar OTP, net banking, DSC, or other available options.', 'Process', 4],
            ['What documents do I need?', 'Typically: PAN, Aadhaar, Form 16, AIS/Form 26AS, bank interest certificate, and 80C/80D or other deduction proofs as applicable.', 'Documents', 5],
            ['How long does expert filing take?', 'Target turnaround is within 24 hours after documents and payment are complete. Actual time depends on case complexity and how quickly you respond to document requests — it is not a guaranteed SLA.', 'Process', 6],
            ['How do I track my refund?', 'Track filing status on ITR Tax with PAN and acknowledgement. Refund credit is processed by the Income Tax Department after successful e-verification — check the Income Tax e-filing portal for CPC/refund updates.', 'Refund', 7],
            ['Can I switch from Self to Expert mid-way?', 'Yes. Start an assisted filing or contact support to move to an expert plan.', 'Process', 8],
            ['Is my data secure on ITR Tax?', 'ITR Tax uses CSRF-protected sessions and role-based access so documents are available to you, your assigned expert and admin. See the Privacy Policy for how we handle personal data.', 'General', 9],
            ['What is old vs new tax regime?', 'New regime generally has different slab rates, a higher standard deduction, fewer chapter-VI-A deductions, and §87A rebate rules that can make tax nil up to specified income limits. Old regime allows claims such as 80C, 80D and HRA. Compare using your actual figures before choosing.', 'General', 10],
        ];

        foreach ($faqs as [$q, $a, $cat, $sort]) {
            Faq::updateOrCreate(
                ['question' => $q],
                ['answer' => $a, 'category' => $cat, 'sort_order' => $sort, 'is_active' => true]
            );
        }

        $blogs = [
            [
                'title' => 'How to file ITR online for FY 2025-26 (AY 2026-27)',
                'slug' => 'how-to-file-itr-online-fy-2025-26',
                'excerpt' => 'A practical checklist to prepare and e-file.',
                'content' => "Preparing your income tax return for FY 2025-26 (AY 2026-27) is clearer when you follow a sequence.\n\n1. Collect Form 16, AIS/26AS, bank interest proofs.\n2. Create an ITR Tax account.\n3. Upload documents and enter income/TDS figures.\n4. Review the tax summary and choose a regime.\n5. Self-prepare or pay for expert review.\n6. Complete e-filing/e-verification on the Income Tax portal as required (typically within 30 days of upload).",
                'cover_image' => 'assets/images/guides/guide-file-online.svg',
            ],
            [
                'title' => 'Old vs New tax regime — how to compare before filing',
                'slug' => 'old-vs-new-tax-regime',
                'excerpt' => 'Compare regimes using your actual figures.',
                'content' => "Choosing the right tax regime can change tax payable or refund.\n\nNew regime: different slab rates, higher standard deduction, fewer chapter-VI-A deductions, and §87A rebate rules for eligible income levels.\nOld regime: claim 80C, 80D, HRA and other applicable deductions.\n\nAlways compare with your own numbers; online estimators are simplified.",
                'cover_image' => 'assets/images/guides/guide-regime.svg',
            ],
            [
                'title' => 'Form 16 vs Form 26AS: what to check before filing',
                'slug' => 'form-16-vs-form-26as',
                'excerpt' => 'Reconcile TDS to avoid processing delays.',
                'content' => 'Reconcile salary TDS in Form 16 with Form 26AS / AIS before submitting your return on the Income Tax portal.',
                'cover_image' => 'assets/images/guides/guide-form16.svg',
            ],
            [
                'title' => 'Documents checklist for salaried ITR filing',
                'slug' => 'salaried-itr-documents-checklist',
                'excerpt' => 'Keep these papers ready for faster filing.',
                'content' => 'PAN, Aadhaar, Form 16, AIS, bank interest certificate, 80C/80D proofs as applicable.',
                'cover_image' => 'assets/images/guides/guide-checklist.svg',
            ],
        ];

        foreach ($blogs as $blog) {
            Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                $blog + ['author_id' => $admin->id, 'is_published' => true, 'published_at' => now()]
            );
        }

        foreach ([
            'site_name' => 'ITR Tax',
            'support_email' => 'support@itr-tax.in',
            'support_phone' => '',
            'razorpay_key' => '',
            'razorpay_secret' => '',
            'company_address' => 'Bengaluru, India',
        ] as $k => $v) {
            Setting::updateOrCreate(['setting_key' => $k], ['setting_value' => $v]);
        }

        // Simple main-portal process steps (DB-driven, easy to edit).
        ProcessStep::query()->delete();
        $steps = [
            ['mode' => 'both', 'title' => 'Pick Self or Tax Expert', 'description' => 'Choose free Self Filing for Form 16 cases, or hire an expert for complex income.', 'icon' => 'spark', 'sort_order' => 1],
            ['mode' => 'both', 'title' => 'Answer a few questions', 'description' => 'Quick checks about salary, house, investments and deductions.', 'icon' => 'list', 'sort_order' => 2],
            ['mode' => 'both', 'title' => 'Upload documents', 'description' => 'Form 16 is required. Add AIS / 26AS and proofs if you have them.', 'icon' => 'file', 'sort_order' => 3],
            ['mode' => 'both', 'title' => 'Finish & e-verify', 'description' => 'Confirm figures (or pay for expert help), then e-verify on the Income Tax portal.', 'icon' => 'check', 'sort_order' => 4],

            ['mode' => 'self', 'title' => 'Answer questions', 'description' => 'Tell us about your income in short answers.', 'icon' => 'list', 'sort_order' => 1],
            ['mode' => 'self', 'title' => 'Upload Form 16', 'description' => 'Add Form 16 and any other proofs to your vault.', 'icon' => 'file', 'sort_order' => 2],
            ['mode' => 'self', 'title' => 'Enter tax figures', 'description' => 'Fill income/TDS and compare old vs new regime.', 'icon' => 'chart', 'sort_order' => 3],
            ['mode' => 'self', 'title' => 'Confirm & finish', 'description' => 'Generate your filing reference and e-verify tips.', 'icon' => 'check', 'sort_order' => 4],

            ['mode' => 'assisted', 'title' => 'Answer questions', 'description' => 'Share a few details so we match the right plan path.', 'icon' => 'list', 'sort_order' => 1],
            ['mode' => 'assisted', 'title' => 'Upload documents', 'description' => 'Form 16 plus AIS / proofs your expert will need.', 'icon' => 'file', 'sort_order' => 2],
            ['mode' => 'assisted', 'title' => 'Pay for expert', 'description' => 'Confirm plan checkout after documents are ready.', 'icon' => 'wallet', 'sort_order' => 3],
            ['mode' => 'assisted', 'title' => 'Approve & get ACK', 'description' => 'Review the expert summary, then download acknowledgement.', 'icon' => 'check', 'sort_order' => 4],
        ];
        foreach ($steps as $step) {
            ProcessStep::create($step + ['is_active' => true]);
        }
    }
}
