<?php

use App\Models\ProcessStep;
use App\Models\StatusLog;
use App\Support\Icon;
use App\Support\Portal;
use App\Support\TaxCalculator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

if (! function_exists('icon')) {
    function icon(string $name, string $class = 'itr-svg'): string
    {
        return Icon::render($name, $class);
    }
}

if (! function_exists('iconBox')) {
    function iconBox(string $name, string $boxClass = 'itr-ico'): string
    {
        return '<div class="'.e($boxClass).'">'.icon($name).'</div>';
    }
}

if (! function_exists('defaultProcessSteps')) {
    /**
     * Hardcoded fallbacks when process_steps is empty or unavailable.
     *
     * @return Collection<int, ProcessStep>
     */
    function defaultProcessSteps(?string $mode = null)
    {
        $mode = in_array($mode, ['self', 'assisted', 'both'], true) ? $mode : 'both';
        $catalog = [
            'both' => [
                ['title' => 'Pick Self or Tax Expert', 'description' => 'Choose free Self Filing for Form 16 cases, or hire an expert for complex income.', 'icon' => 'spark'],
                ['title' => 'Answer a few questions', 'description' => 'Quick checks about salary, house, investments and deductions.', 'icon' => 'list'],
                ['title' => 'Upload documents', 'description' => 'Form 16 is required. Add AIS / 26AS and proofs if you have them.', 'icon' => 'file'],
                ['title' => 'Finish & e-verify', 'description' => 'Confirm figures (or pay for expert help), then e-verify on the Income Tax portal.', 'icon' => 'check'],
            ],
            'self' => [
                ['title' => 'Answer questions', 'description' => 'Tell us about your income in short answers.', 'icon' => 'list'],
                ['title' => 'Upload Form 16', 'description' => 'Add Form 16 and any other proofs to your vault.', 'icon' => 'file'],
                ['title' => 'Enter tax figures', 'description' => 'Fill income/TDS and compare old vs new regime.', 'icon' => 'chart'],
                ['title' => 'Confirm & finish', 'description' => 'Generate your filing reference and e-verify tips.', 'icon' => 'check'],
            ],
            'assisted' => [
                ['title' => 'Answer questions', 'description' => 'Share a few details so we match the right plan path.', 'icon' => 'list'],
                ['title' => 'Upload documents', 'description' => 'Form 16 plus AIS / proofs your expert will need.', 'icon' => 'file'],
                ['title' => 'Pay for expert', 'description' => 'Confirm plan checkout after documents are ready.', 'icon' => 'wallet'],
                ['title' => 'Approve & get ACK', 'description' => 'Review the expert summary, then download acknowledgement.', 'icon' => 'check'],
            ],
        ];

        return collect($catalog[$mode])->values()->map(function (array $row, int $i) use ($mode) {
            return new ProcessStep([
                'mode' => $mode,
                'title' => $row['title'],
                'description' => $row['description'],
                'icon' => $row['icon'],
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        });
    }
}

if (! function_exists('processSteps')) {
    /**
     * Active filing process steps from DB (mode: self|assisted|both).
     *
     * @return Collection<int, ProcessStep>
     */
    function processSteps(?string $mode = null)
    {
        try {
            $query = ProcessStep::query()->active()->orderBy('sort_order')->orderBy('id');
            if ($mode === 'self' || $mode === 'assisted' || $mode === 'both') {
                $query->forMode($mode);
            }

            $steps = $query->get();
            if ($steps->isNotEmpty()) {
                return $steps;
            }
        } catch (Throwable) {
            // Table missing or DB unavailable — use fallbacks below.
        }

        return defaultProcessSteps($mode);
    }
}

if (! function_exists('brandLogo')) {
    /**
     * ITR Tax brand mark (SVG preferred) with optional wordmark.
     *
     * @param  'mark'|'full'|'light'  $variant
     */
    function brandLogo(string $variant = 'full', string $class = ''): string
    {
        $name = e(config('itr.name', 'ITR Tax'));
        $src = e(asset('assets/images/itr-tax-logo.svg'));
        $cls = trim('itr-brand-logo '.($variant !== 'full' ? 'itr-brand-logo-'.$variant.' ' : '').$class);
        $mark = '<img class="itr-brand-logo-img" src="'.$src.'" width="40" height="40" alt="'.$name.'" decoding="async">';

        if ($variant === 'mark') {
            return '<span class="'.e(trim($cls)).'">'.$mark.'</span>';
        }

        return '<span class="'.e(trim($cls)).'">'
            .$mark
            .'<span class="itr-brand-logo-text"><strong>'.$name.'</strong><small>Income Tax eFiling</small></span>'
            .'</span>';
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }
}

if (! function_exists('roleLabel')) {
    function roleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'ca' => 'Tax Expert',
            'user' => 'User',
            default => ucfirst($role),
        };
    }
}

if (! function_exists('statusLabel')) {
    function statusLabel(string $status): string
    {
        return match ($status) {
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
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

if (! function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        $map = [
            'draft' => 'itr-badge-muted',
            'questionnaire_pending' => 'itr-badge-warn',
            'documents_pending' => 'itr-badge-warn',
            'details_review' => 'itr-badge-info',
            'summary_pending' => 'itr-badge-info',
            'payment_pending' => 'itr-badge-warn',
            'paid' => 'itr-badge-info',
            'assigned' => 'itr-badge-info',
            'under_review' => 'itr-badge-info',
            'docs_requested' => 'itr-badge-warn',
            'customer_summary' => 'itr-badge-warn',
            'customer_approved' => 'itr-badge-info',
            'ready_to_file' => 'itr-badge-info',
            'filed' => 'itr-badge-success',
            'completed' => 'itr-badge-success',
            'cancelled' => 'itr-badge-danger',
        ];
        $class = $map[$status] ?? 'itr-badge-muted';

        return '<span class="itr-badge '.$class.'">'.e(statusLabel($status)).'</span>';
    }
}

if (! function_exists('filingSteps')) {
    function filingSteps(array|object $filing): array
    {
        $mode = data_get($filing, 'filing_mode', 'assisted');
        if ($mode === 'self') {
            return [
                ['key' => 'questions', 'label' => 'Questions', 'statuses' => ['draft', 'questionnaire_pending']],
                ['key' => 'docs', 'label' => 'Documents', 'statuses' => ['documents_pending', 'docs_requested']],
                ['key' => 'summary', 'label' => 'Tax Summary', 'statuses' => ['summary_pending']],
                ['key' => 'review', 'label' => 'Confirm', 'statuses' => ['ready_to_file']],
                ['key' => 'done', 'label' => 'Completed', 'statuses' => ['filed', 'completed']],
            ];
        }

        // Assisted: Questions → Docs → Review details → Payment → Expert → Approve → Completed
        // docs_requested stays under Expert (after payment), not early Documents.
        return [
            ['key' => 'questions', 'label' => 'Questions', 'statuses' => ['draft', 'questionnaire_pending']],
            ['key' => 'docs', 'label' => 'Documents', 'statuses' => ['documents_pending']],
            ['key' => 'details', 'label' => 'Review', 'statuses' => ['details_review']],
            ['key' => 'pay', 'label' => 'Payment', 'statuses' => ['payment_pending']],
            ['key' => 'assign', 'label' => 'Tax Expert', 'statuses' => ['paid', 'assigned', 'under_review', 'docs_requested']],
            ['key' => 'approve', 'label' => 'Approve', 'statuses' => ['customer_summary', 'customer_approved', 'ready_to_file']],
            ['key' => 'done', 'label' => 'Completed', 'statuses' => ['filed', 'completed']],
        ];
    }
}

if (! function_exists('currentStepIndex')) {
    function currentStepIndex(array|object $filing): int
    {
        $steps = filingSteps($filing);
        $status = data_get($filing, 'status', 'draft');
        foreach ($steps as $i => $step) {
            if (in_array($status, $step['statuses'], true)) {
                return $i;
            }
        }
        if (in_array($status, ['filed', 'completed'], true)) {
            return count($steps) - 1;
        }

        return 0;
    }
}

if (! function_exists('itrSlug')) {
    function itrSlug(string $text): string
    {
        return Str::slug($text);
    }
}

if (! function_exists('timeAgo')) {
    function timeAgo(mixed $datetime): string
    {
        if (! $datetime) {
            return '-';
        }
        if ($datetime instanceof DateTimeInterface) {
            $ts = $datetime->getTimestamp();
        } else {
            $ts = strtotime((string) $datetime);
        }
        if (! $ts) {
            return '-';
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return floor($diff / 60).' min ago';
        }
        if ($diff < 86400) {
            return floor($diff / 3600).' hrs ago';
        }
        if ($diff < 604800) {
            return floor($diff / 86400).' days ago';
        }

        return date('d M Y', $ts);
    }
}

if (! function_exists('formatDate')) {
    function formatDate(mixed $datetime, string $format = 'd M Y'): string
    {
        if (! $datetime) {
            return '-';
        }
        if ($datetime instanceof DateTimeInterface) {
            return $datetime->format($format);
        }

        $ts = strtotime((string) $datetime);

        return $ts ? date($format, $ts) : '-';
    }
}

if (! function_exists('computeTaxSummary')) {
    function computeTaxSummary(float $gross, float $deductions, float $tds = 0): array
    {
        return TaxCalculator::computeTaxSummary($gross, $deductions, $tds);
    }
}

if (! function_exists('filingStartUrl')) {
    function filingStartUrl(string $mode = 'assisted', ?int $planId = null): string
    {
        $params = array_filter(['mode' => $mode, 'plan_id' => $planId], fn ($v) => $v !== null && $v !== '');
        $user = auth()->user();
        if ($user && $user->isUser()) {
            if (! profileIsComplete($user)) {
                return route('user.complete-profile');
            }

            return route('user.choose-service', $params);
        }

        return route('register', $params);
    }
}

if (! function_exists('profileIsComplete')) {
    function profileIsComplete(mixed $user): bool
    {
        if (! $user) {
            return false;
        }

        return filled(data_get($user, 'phone'))
            && filled(data_get($user, 'pan'))
            && filled(data_get($user, 'city'))
            && filled(data_get($user, 'state'));
    }
}

if (! function_exists('questionnaireQuestions')) {
    /** @return array<string, array{label: string, type: string, options?: array<string, string>}> */
    function questionnaireQuestions(): array
    {
        return [
            'employment' => [
                'label' => 'What best describes your income?',
                'type' => 'select',
                'options' => [
                    'salaried' => 'Salaried / pension',
                    'business' => 'Business / profession',
                    'freelancer' => 'Freelancer / consultant',
                    'investor' => 'Investor / trader',
                    'other' => 'Other / mixed',
                ],
            ],
            'income_range' => [
                'label' => 'Approximate total income this year?',
                'type' => 'select',
                'options' => [
                    'below_5' => 'Below ₹5 lakh',
                    '5_10' => '₹5–10 lakh',
                    '10_25' => '₹10–25 lakh',
                    '25_50' => '₹25–50 lakh',
                    'above_50' => 'Above ₹50 lakh',
                ],
            ],
            'capital_gains' => [
                'label' => 'Did you sell shares, mutual funds or property?',
                'type' => 'select',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
            ],
            'house_property' => [
                'label' => 'Do you have more than one house property?',
                'type' => 'select',
                'options' => ['no' => 'No / only one', 'yes' => 'Yes, more than one'],
            ],
            'foreign_income' => [
                'label' => 'Any foreign income, RSUs or NRI status?',
                'type' => 'select',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
            ],
            'crypto_fno' => [
                'label' => 'Crypto, F&O or intraday trading?',
                'type' => 'select',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
            ],
            'deductions' => [
                'label' => 'Claiming 80C / 80D / other deductions?',
                'type' => 'select',
                'options' => ['no' => 'No / unsure', 'yes' => 'Yes'],
            ],
            'home_loan' => [
                'label' => 'Home loan interest to claim?',
                'type' => 'select',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
            ],
            'tax_regime' => [
                'label' => 'Preferred tax regime?',
                'type' => 'select',
                'options' => [
                    'unsure' => 'Not sure — help me choose',
                    'new' => 'New regime',
                    'old' => 'Old regime',
                ],
            ],
            'it_notice' => [
                'label' => 'Any notice or scrutiny from Income Tax?',
                'type' => 'select',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
            ],
        ];
    }
}

if (! function_exists('suggestItrType')) {
    function suggestItrType(string $profile): string
    {
        return match ($profile) {
            'investor', 'nri', 'affluent' => 'ITR-2',
            'freelancer', 'advanced_trader', 'business' => 'ITR-3',
            default => 'ITR-1',
        };
    }
}

if (! function_exists('filingContinueUrl')) {
    function filingContinueUrl(array|object $filing): string
    {
        $status = data_get($filing, 'status', 'draft');
        $mode = data_get($filing, 'filing_mode', 'assisted');

        return match (true) {
            $status === 'questionnaire_pending', $status === 'draft' => route('user.questions', $filing),
            in_array($status, ['documents_pending', 'docs_requested'], true) => route('user.documents', $filing),
            $status === 'details_review' => route('user.review-details', $filing),
            in_array($status, ['summary_pending', 'customer_summary'], true) => route('user.summary', $filing),
            $status === 'payment_pending' => route('user.payment', $filing),
            in_array($status, ['ready_to_file', 'customer_approved'], true) => $mode === 'self'
                ? route('user.review', $filing)
                : route('user.track', $filing),
            in_array($status, ['paid', 'assigned', 'under_review'], true) => route('user.track', $filing),
            in_array($status, ['filed', 'completed'], true) => route('user.acknowledgement', $filing),
            default => route('user.track', $filing),
        };
    }
}

if (! function_exists('filingProgressPercent')) {
    function filingProgressPercent(array|object $filing): int
    {
        $steps = filingSteps($filing);
        $idx = currentStepIndex($filing);
        $total = max(1, count($steps) - 1);
        if (in_array(data_get($filing, 'status'), ['filed', 'completed'], true)) {
            return 100;
        }

        return (int) round(($idx / $total) * 100);
    }
}

if (! function_exists('nextStepLabel')) {
    /** Short CTA for the customer's current filing step. */
    function nextStepLabel(array|object $filing): string
    {
        $status = data_get($filing, 'status', 'draft');
        $mode = data_get($filing, 'filing_mode', 'assisted');

        return match (true) {
            $status === 'questionnaire_pending', $status === 'draft' => 'Answer questions',
            in_array($status, ['documents_pending', 'docs_requested'], true) => 'Upload documents',
            $status === 'details_review' => 'Confirm & pay',
            $status === 'summary_pending' => 'Enter tax figures',
            $status === 'payment_pending' => 'Pay now',
            $status === 'customer_summary' => 'Approve summary',
            $status === 'ready_to_file' && $mode === 'self' => 'Confirm & finish',
            in_array($status, ['paid', 'assigned', 'under_review', 'customer_approved', 'ready_to_file'], true) => 'Track progress',
            in_array($status, ['filed', 'completed'], true) => 'View acknowledgement',
            default => 'Continue',
        };
    }
}

if (! function_exists('expertNextAction')) {
    /** Short CTA for tax expert workspace. */
    function expertNextAction(array|object $filing): string
    {
        return match (data_get($filing, 'status')) {
            'assigned' => 'Start review',
            'under_review' => 'Send summary / request docs',
            'docs_requested' => 'Waiting for documents',
            'customer_summary' => 'Waiting for customer approval',
            'customer_approved', 'ready_to_file' => 'Mark filed + upload ACK',
            'filed', 'completed' => 'View filing',
            default => 'Open filing',
        };
    }
}

if (! function_exists('adminRoute')) {
    /** Absolute admin URL (uses ADMIN_URL when portal separation is enabled). */
    function adminRoute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $path = route($name, $parameters, false);
        if (Portal::separationEnabled()) {
            return Portal::adminBaseUrl().$path;
        }

        return $absolute ? url($path) : $path;
    }
}

if (! function_exists('logFilingStatus')) {
    function logFilingStatus(int $filingId, ?string $old, string $new, ?int $by = null, ?string $remark = null): void
    {
        StatusLog::create([
            'filing_id' => $filingId,
            'user_id' => $by,
            'action' => 'status_change',
            'old_status' => $old,
            'new_status' => $new,
            'changed_by' => $by,
            'remark' => $remark,
        ]);
    }
}

if (! function_exists('uploadItrFile')) {
    function uploadItrFile(UploadedFile $file, string $subdir = 'uploads'): ?array
    {
        $allowed = config('itr.allowed_extensions', []);
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, $allowed, true)) {
            return null;
        }

        $maxBytes = config('itr.upload_max_mb', 10) * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return null;
        }

        $filename = uniqid('doc_', true).'.'.$ext;
        $path = $file->storeAs($subdir, $filename, 'local');

        return [
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }
}
