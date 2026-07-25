<?php

return [
    'name' => 'ITR Tax',
    'tagline' => 'File ITR in minutes with clear regime comparison',
    'financial_year' => '2025-26',
    'assessment_year' => '2026-27',
    'support_email' => 'support@itr-tax.in',
    'support_phone' => '',
    'company_address' => 'Bengaluru, India',
    'social' => [
        'facebook' => env('SOCIAL_FACEBOOK', 'https://www.facebook.com/'),
        'instagram' => env('SOCIAL_INSTAGRAM', 'https://www.instagram.com/'),
        'x' => env('SOCIAL_X', 'https://x.com/'),
        'linkedin' => env('SOCIAL_LINKEDIN', 'https://www.linkedin.com/'),
        'youtube' => env('SOCIAL_YOUTUBE', 'https://www.youtube.com/'),
        'whatsapp' => env('SOCIAL_WHATSAPP', ''),
    ],
    'currency' => 'INR',
    'upload_max_mb' => 10,
    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'zip'],

    /*
    | Separate admin portal (other IP/port/host), same DB & codebase.
    | When admin_portal_separate=true, admin login works only on admin host.
    | Example local:
    |   APP_URL=http://127.0.0.1:8000
    |   ADMIN_URL=http://127.0.0.1:8001
    |   ADMIN_HOSTS=127.0.0.1:8001
    |   ADMIN_PORTAL_SEPARATE=true
    */
    'admin_portal_separate' => env('ADMIN_PORTAL_SEPARATE', true),
    'admin_url' => env('ADMIN_URL', env('APP_URL', 'http://localhost')),
    'admin_hosts' => env('ADMIN_HOSTS', ''),

    'status_labels' => [
        'draft' => 'Draft',
        'questionnaire_pending' => 'Answer Questions',
        'documents_pending' => 'Upload Documents',
        'details_review' => 'Review Details',
        'summary_pending' => 'Tax Summary',
        'payment_pending' => 'Payment',
        'paid' => 'Awaiting Expert Assign',
        'assigned' => 'Tax Expert Assigned',
        'under_review' => 'Expert Review',
        'docs_requested' => 'Need Documents',
        'customer_summary' => 'Review Tax Summary',
        'customer_approved' => 'Approved - Filing',
        'ready_to_file' => 'Ready to Confirm',
        'filed' => 'Filed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
];
