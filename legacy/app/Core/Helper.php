<?php

namespace App\Core;

class Helper
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function money(float|int|string|null $amount): string
    {
        return '₹' . number_format((float) $amount, 2);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'documents_pending' => 'Upload Form 16',
            'summary_pending' => 'Tax Summary',
            'payment_pending' => 'Payment Pending',
            'paid' => 'Paid',
            'assigned' => 'Expert Assigned',
            'under_review' => 'Expert Review',
            'docs_requested' => 'More Docs Needed',
            'ready_to_file' => 'Ready to File',
            'filed' => 'ITR Filed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function statusBadge(string $status): string
    {
        $map = [
            'draft' => 'itr-badge-muted',
            'documents_pending' => 'itr-badge-warn',
            'summary_pending' => 'itr-badge-info',
            'payment_pending' => 'itr-badge-warn',
            'paid' => 'itr-badge-info',
            'assigned' => 'itr-badge-info',
            'under_review' => 'itr-badge-info',
            'docs_requested' => 'itr-badge-warn',
            'ready_to_file' => 'itr-badge-info',
            'filed' => 'itr-badge-success',
            'completed' => 'itr-badge-success',
            'cancelled' => 'itr-badge-danger',
        ];
        $class = $map[$status] ?? 'itr-badge-muted';
        return '<span class="itr-badge ' . $class . '">' . self::e(self::statusLabel($status)) . '</span>';
    }

    /** ClearTax-style filing steps for progress bar */
    public static function filingSteps(array $filing): array
    {
        $mode = $filing['filing_mode'] ?? 'assisted';
        if ($mode === 'self') {
            return [
                ['key' => 'docs', 'label' => 'Upload Form 16', 'statuses' => ['documents_pending', 'docs_requested']],
                ['key' => 'summary', 'label' => 'Tax Summary', 'statuses' => ['summary_pending']],
                ['key' => 'review', 'label' => 'Review & File', 'statuses' => ['ready_to_file']],
                ['key' => 'done', 'label' => 'E-Verify / ACK', 'statuses' => ['filed', 'completed']],
            ];
        }
        return [
            ['key' => 'docs', 'label' => 'Upload Docs', 'statuses' => ['documents_pending', 'docs_requested']],
            ['key' => 'summary', 'label' => 'Tax Summary', 'statuses' => ['summary_pending']],
            ['key' => 'pay', 'label' => 'Payment', 'statuses' => ['payment_pending']],
            ['key' => 'expert', 'label' => 'Expert Files', 'statuses' => ['paid', 'assigned', 'under_review']],
            ['key' => 'done', 'label' => 'ACK Ready', 'statuses' => ['filed', 'completed']],
        ];
    }

    public static function currentStepIndex(array $filing): int
    {
        $steps = self::filingSteps($filing);
        $status = $filing['status'] ?? 'draft';
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

    /** Simple demo tax calc (illustrative, not legal tax engine) */
    public static function computeTaxSummary(float $gross, float $deductions): array
    {
        $gross = max(0, $gross);
        $deductions = max(0, min($deductions, $gross));

        // New regime: standard deduction style, limited other deductions
        $newTaxable = max(0, $gross - 75000);
        $newTax = self::slabTaxNew($newTaxable);

        // Old regime: allow deductions (80C etc.)
        $oldTaxable = max(0, $gross - 50000 - $deductions);
        $oldTax = self::slabTaxOld($oldTaxable);

        $better = $newTax <= $oldTax ? 'new' : 'old';
        $saving = abs($oldTax - $newTax);

        return [
            'gross_salary' => $gross,
            'total_deductions' => $deductions,
            'tax_old_regime' => $oldTax,
            'tax_new_regime' => $newTax,
            'recommended' => $better,
            'saving' => $saving,
        ];
    }

    private static function slabTaxNew(float $taxable): float
    {
        $tax = 0.0;
        $slabs = [
            [400000, 0.00],
            [800000, 0.05],
            [1200000, 0.10],
            [1600000, 0.15],
            [2000000, 0.20],
            [2400000, 0.25],
            [PHP_FLOAT_MAX, 0.30],
        ];
        $prev = 0;
        foreach ($slabs as [$upto, $rate]) {
            if ($taxable <= $prev) break;
            $chunk = min($taxable, $upto) - $prev;
            $tax += $chunk * $rate;
            $prev = $upto;
        }
        return round($tax * 1.04, 2); // +cess demo
    }

    private static function slabTaxOld(float $taxable): float
    {
        $tax = 0.0;
        $slabs = [
            [250000, 0.00],
            [500000, 0.05],
            [1000000, 0.20],
            [PHP_FLOAT_MAX, 0.30],
        ];
        $prev = 0;
        foreach ($slabs as [$upto, $rate]) {
            if ($taxable <= $prev) break;
            $chunk = min($taxable, $upto) - $prev;
            $tax += $chunk * $rate;
            $prev = $upto;
        }
        return round($tax * 1.04, 2);
    }

    public static function slug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    public static function timeAgo(?string $datetime): string
    {
        if (!$datetime) {
            return '-';
        }
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hrs ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('d M Y', $ts);
    }

    public static function formatDate(?string $datetime, string $format = 'd M Y'): string
    {
        if (!$datetime) return '-';
        return date($format, strtotime($datetime));
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_token" value="' . self::e(Session::csrfToken()) . '">';
    }

    /** Inline SVG icon (stroke, 24×24). Use inside .itr-ico or alone. */
    public static function icon(string $name, string $class = 'itr-svg'): string
    {
        $paths = [
            'logo' => '<path d="M12 2 4 6v6c0 5 3.4 9.4 8 10 4.6-.6 8-5 8-10V6l-8-4Z"/><path d="M9.5 12.5 11.2 14.2 14.8 10"/>',
            'user' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'chart' => '<path d="M3 3v18h18"/><path d="M7 14v4"/><path d="M12 10v8"/><path d="M17 6v12"/>',
            'briefcase' => '<path d="M16 20V6a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v14"/><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M10 12h4"/>',
            'rupee' => '<path d="M6 4h12"/><path d="M6 8h12"/><path d="M6 4c6 0 8 8 0 8"/><path d="M6 12 17 20"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 5-5"/>',
            'upload' => '<path d="M12 16V5"/><path d="m8 9 4-4 4 4"/><path d="M4 19h16"/>',
            'file' => '<path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/>',
            'phone' => '<path d="M22 16.9v2.2a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.3 1h2.2a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.9 8.1a16 16 0 0 0 6 6l1.7-1.1a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2.1Z"/>',
            'building' => '<path d="M4 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01"/><path d="M15 10h.01"/><path d="M9 14h.01"/><path d="M15 14h.01"/>',
            'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 2.5-3 4.5"/><path d="M12 17h.01"/>',
            'pen' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'list' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
            'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>',
            'download' => '<path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>',
            'shield' => '<path d="M12 2 4 6v6c0 5 3.4 9.4 8 10 4.6-.6 8-5 8-10V6l-8-4Z"/>',
            'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
            'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
            'home' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/>',
            'wallet' => '<path d="M20 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1Z"/><path d="M16 12h.01"/><path d="M2 9V6a2 2 0 0 1 2-2h13"/>',
            'spark' => '<path d="M12 3v4"/><path d="M12 17v4"/><path d="M3 12h4"/><path d="M17 12h4"/><path d="m5.6 5.6 2.8 2.8"/><path d="m15.6 15.6 2.8 2.8"/><path d="m5.6 18.4 2.8-2.8"/><path d="m15.6 8.4 2.8-2.8"/>',
            'alert' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        ];

        $inner = $paths[$name] ?? $paths['spark'];
        $cls = self::e($class);

        return '<svg class="' . $cls . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
    }

    public static function iconBox(string $name, string $boxClass = 'itr-ico'): string
    {
        return '<div class="' . self::e($boxClass) . '">' . self::icon($name) . '</div>';
    }

    public static function notify(int $userId, string $title, string $message, ?string $link = null): void
    {
        // In-app notifications removed.
    }

    public static function logStatus(int $filingId, ?string $old, string $new, ?int $by = null, ?string $remark = null): void
    {
        Database::insert('status_logs', [
            'filing_id' => $filingId,
            'old_status' => $old,
            'new_status' => $new,
            'changed_by' => $by,
            'remark' => $remark,
        ]);
    }

    public static function upload(array $file, string $subdir = 'uploads'): array|false
    {
        $app = require __DIR__ . '/../../config/app.php';
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $app['allowed_extensions'], true)) {
            return false;
        }

        $maxBytes = $app['upload_max_mb'] * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return false;
        }

        $dir = __DIR__ . '/../../storage/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = uniqid('doc_', true) . '.' . $ext;
        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        return [
            'original_name' => $file['name'],
            'file_path' => $subdir . '/' . $filename,
            'file_size' => $file['size'],
            'mime_type' => $file['type'] ?? null,
        ];
    }
}
