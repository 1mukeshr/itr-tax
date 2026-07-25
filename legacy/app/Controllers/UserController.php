<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Helper;
use App\Core\Session;

class UserController extends Controller
{
    public function dashboard(): void
    {
        $uid = Auth::id();
        $filings = Database::fetchAll(
            'SELECT f.*, p.name as plan_name, u.name as ca_name
             FROM itr_filings f
             LEFT JOIN plans p ON p.id = f.plan_id
             LEFT JOIN users u ON u.id = f.ca_id
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC LIMIT 5',
            [$uid]
        );

        $stats = [
            'total' => Database::fetch('SELECT COUNT(*) as c FROM itr_filings WHERE user_id = ?', [$uid])['c'] ?? 0,
            'active' => Database::fetch("SELECT COUNT(*) as c FROM itr_filings WHERE user_id = ? AND status NOT IN ('completed','cancelled','filed')", [$uid])['c'] ?? 0,
            'filed' => Database::fetch("SELECT COUNT(*) as c FROM itr_filings WHERE user_id = ? AND status IN ('filed','completed')", [$uid])['c'] ?? 0,
            'notifications' => Database::fetch('SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0', [$uid])['c'] ?? 0,
        ];

        $notifications = Database::fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5',
            [$uid]
        );

        $this->view('user/dashboard', [
            'title' => 'Dashboard',
            'filings' => $filings,
            'stats' => $stats,
            'notifications' => $notifications,
        ], 'layouts/panel');
    }

    /** ClearTax Step 1: Self vs Hire Expert + income profile */
    public function startFiling(): void
    {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug != 'self-free' ORDER BY sort_order ASC");
        $app = require __DIR__ . '/../../config/app.php';
        $this->view('user/start-filing', [
            'title' => 'Start Filing',
            'plans' => $plans,
            'assessment_year' => $app['assessment_year'],
            'financial_year' => $app['financial_year'],
        ], 'layouts/panel');
    }

    public function createFiling(): void
    {
        $this->validateCsrf();
        $mode = $this->input('filing_mode', 'assisted') === 'self' ? 'self' : 'assisted';
        $profile = $this->input('income_profile', 'salaried');
        $itrType = $this->input('itr_type', 'ITR-1');
        $pan = strtoupper($this->input('pan'));
        $app = require __DIR__ . '/../../config/app.php';

        if ($mode === 'self') {
            $plan = Database::fetch("SELECT * FROM plans WHERE slug = 'self-free' AND is_active = 1");
            if (!$plan) {
                $plan = ['id' => null, 'price' => 0];
            }
        } else {
            $planId = (int) $this->input('plan_id');
            $plan = Database::fetch('SELECT * FROM plans WHERE id = ? AND is_active = 1', [$planId]);
            if (!$plan) {
                Session::flash('error', 'Please select an expert plan.');
                $this->redirect('/itr/new');
            }
        }

        $id = Database::insert('itr_filings', [
            'user_id' => Auth::id(),
            'plan_id' => $plan['id'] ?? null,
            'assessment_year' => $app['assessment_year'],
            'itr_type' => $itrType,
            'filing_mode' => $mode,
            'income_profile' => $profile,
            'status' => 'documents_pending',
            'pan' => $pan ?: (Auth::user()['pan'] ?? null),
            'amount' => (float) ($plan['price'] ?? 0),
        ]);

        Helper::logStatus($id, null, 'documents_pending', Auth::id(), $mode === 'self' ? 'Self filing started' : 'Expert assisted filing started');
        Helper::notify(Auth::id(), 'Filing started', 'Upload Form 16 to build your tax summary.', '/documents/' . $id);
        Session::flash('success', 'Step 1 done. Upload Form 16 / documents.');
        $this->redirect('/documents/' . $id);
    }

    /** ClearTax Step 2: Upload Form 16 */
    public function documents(string $id): void
    {
        $filing = $this->userFiling((int) $id);
        $docs = Database::fetchAll('SELECT * FROM documents WHERE filing_id = ? ORDER BY created_at DESC', [$id]);
        $requests = Database::fetchAll('SELECT * FROM document_requests WHERE filing_id = ? ORDER BY created_at DESC', [$id]);

        $this->view('user/documents', [
            'title' => 'Upload Form 16',
            'filing' => $filing,
            'docs' => $docs,
            'requests' => $requests,
            'docTypes' => [
                'form16' => 'Form 16',
                'form26as' => 'Form 26AS / AIS',
                'pan_card' => 'PAN Card',
                'aadhaar' => 'Aadhaar Card',
                'bank_statement' => 'Bank Interest Certificate',
                'investment_proof' => 'Investment Proofs (80C/80D)',
                'capital_gains' => 'Capital Gains Statement',
                'other' => 'Other',
            ],
            'step' => 'docs',
        ], 'layouts/panel');
    }

    public function uploadDocument(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->userFiling((int) $id);

        if (empty($_FILES['document'])) {
            Session::flash('error', 'Please choose a file.');
            $this->redirect('/documents/' . $id);
        }

        $uploaded = Helper::upload($_FILES['document'], 'uploads');
        if (!$uploaded) {
            Session::flash('error', 'Upload failed. Allowed: PDF, JPG, PNG, ZIP (max 10MB).');
            $this->redirect('/documents/' . $id);
        }

        Database::insert('documents', [
            'filing_id' => (int) $id,
            'user_id' => Auth::id(),
            'doc_type' => $this->input('doc_type', 'form16'),
            'original_name' => $uploaded['original_name'],
            'file_path' => $uploaded['file_path'],
            'file_size' => $uploaded['file_size'],
            'mime_type' => $uploaded['mime_type'],
            'uploaded_by' => Auth::id(),
        ]);

        Database::query(
            "UPDATE document_requests SET status = 'fulfilled' WHERE filing_id = ? AND status = 'open'",
            [$id]
        );

        if (in_array($filing['status'], ['documents_pending', 'docs_requested', 'draft'], true)) {
            // Prefill demo salary numbers if empty (simulates Form 16 parse)
            $gross = (float) ($filing['gross_salary'] ?: 900000);
            $ded = (float) ($filing['total_deductions'] ?: 150000);
            $summary = Helper::computeTaxSummary($gross, $ded);

            Database::update('itr_filings', [
                'status' => 'summary_pending',
                'gross_salary' => $summary['gross_salary'],
                'total_deductions' => $summary['total_deductions'],
                'tax_old_regime' => $summary['tax_old_regime'],
                'tax_new_regime' => $summary['tax_new_regime'],
                'tax_regime' => $summary['recommended'],
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);
            Helper::logStatus((int) $id, $filing['status'], 'summary_pending', Auth::id(), 'Form 16 uploaded — tax summary ready');
        }

        Session::flash('success', 'Document uploaded. Tax summary is ready.');
        $this->redirect('/summary/' . $id);
    }

    /** ClearTax Step 3: Tax summary + old vs new regime */
    public function summary(string $id): void
    {
        $filing = $this->userFiling((int) $id);
        $this->view('user/summary', [
            'title' => 'Tax Summary',
            'filing' => $filing,
            'step' => 'summary',
        ], 'layouts/panel');
    }

    public function saveSummary(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->userFiling((int) $id);

        $gross = (float) $this->input('gross_salary', $filing['gross_salary'] ?: 900000);
        $ded = (float) $this->input('total_deductions', $filing['total_deductions'] ?: 150000);
        $regime = $this->input('tax_regime', 'new') === 'old' ? 'old' : 'new';
        $summary = Helper::computeTaxSummary($gross, $ded);

        $next = $filing['filing_mode'] === 'self' ? 'ready_to_file' : 'payment_pending';

        Database::update('itr_filings', [
            'gross_salary' => $summary['gross_salary'],
            'total_deductions' => $summary['total_deductions'],
            'tax_old_regime' => $summary['tax_old_regime'],
            'tax_new_regime' => $summary['tax_new_regime'],
            'tax_regime' => $regime,
            'status' => $next,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], $next, Auth::id(), 'Regime selected: ' . strtoupper($regime));

        if ($next === 'ready_to_file') {
            Session::flash('success', 'Summary saved. Review and file your ITR.');
            $this->redirect('/review/' . $id);
        }

        Session::flash('success', 'Summary saved. Complete payment to assign an expert.');
        $this->redirect('/payment/' . $id);
    }

    /** Self filing: review & file */
    public function review(string $id): void
    {
        $filing = $this->userFiling((int) $id);
        if ($filing['filing_mode'] !== 'self' && !in_array($filing['status'], ['ready_to_file', 'filed', 'completed'], true)) {
            Session::flash('info', 'Expert filing — track status instead.');
            $this->redirect('/track/' . $id);
        }
        $this->view('user/review', [
            'title' => 'Review & File',
            'filing' => $filing,
            'step' => 'review',
        ], 'layouts/panel');
    }

    public function selfFile(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->userFiling((int) $id);

        if ($filing['filing_mode'] !== 'self') {
            Session::flash('error', 'Only self filing can use this action.');
            $this->redirect('/track/' . $id);
        }

        $ack = 'CF' . date('Y') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        Database::update('itr_filings', [
            'status' => 'filed',
            'acknowledgement_no' => $ack,
            'filed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], 'filed', Auth::id(), 'Self ITR filed. ACK: ' . $ack);
        Helper::notify(Auth::id(), 'ITR Filed', 'E-verify within 120 days. ACK: ' . $ack, '/acknowledgement/' . $id);

        // Create a simple text receipt file
        $dir = __DIR__ . '/../../storage/receipts';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $fname = 'ack_' . $id . '.txt';
        file_put_contents($dir . '/' . $fname, "ITR Tax ITR Acknowledgement\nACK No: {$ack}\nFiling ID: {$id}\nFiled at: " . date('Y-m-d H:i:s') . "\nRegime: " . strtoupper($filing['tax_regime'] ?? 'new'));
        Database::insert('receipts', [
            'filing_id' => (int) $id,
            'uploaded_by' => Auth::id(),
            'acknowledgement_no' => $ack,
            'file_path' => 'receipts/' . $fname,
            'original_name' => 'ITR-Acknowledgement-' . $ack . '.txt',
        ]);

        Database::update('itr_filings', [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
        Helper::logStatus((int) $id, 'filed', 'completed', Auth::id(), 'Acknowledgement ready — e-verify on IT portal');

        Session::flash('success', 'ITR filed successfully! ACK: ' . $ack);
        $this->redirect('/acknowledgement/' . $id);
    }

    public function payment(string $id): void
    {
        $filing = $this->userFiling((int) $id);
        $plan = Database::fetch('SELECT * FROM plans WHERE id = ?', [$filing['plan_id']]);
        $payments = Database::fetchAll('SELECT * FROM payments WHERE filing_id = ? ORDER BY created_at DESC', [$id]);

        $this->view('user/payment', [
            'title' => 'Payment',
            'filing' => $filing,
            'plan' => $plan,
            'payments' => $payments,
            'step' => 'pay',
        ], 'layouts/panel');
    }

    public function processPayment(string $id): void
    {
        $this->validateCsrf();
        $filing = $this->userFiling((int) $id);

        if (in_array($filing['status'], ['paid', 'assigned', 'under_review', 'filed', 'completed'], true)) {
            Session::flash('info', 'Payment already completed.');
            $this->redirect('/track/' . $id);
        }

        $amount = (float) $filing['amount'];
        $discount = 0;
        $couponCode = strtoupper($this->input('coupon_code'));
        $couponId = null;

        if ($couponCode) {
            $coupon = Database::fetch("SELECT * FROM coupons WHERE code = ? AND is_active = 1", [$couponCode]);
            if (!$coupon) {
                Session::flash('error', 'Coupon code not found.');
                $this->redirect('/payment/' . $id);
            }
            $valid = true;
            if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) $valid = false;
            if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) $valid = false;
            if ($amount < (float) $coupon['min_amount']) $valid = false;
            if (!$valid) {
                Session::flash('error', 'Coupon is invalid or expired.');
                $this->redirect('/payment/' . $id);
            }
            $discount = $coupon['type'] === 'percent'
                ? round($amount * $coupon['value'] / 100, 2)
                : (float) $coupon['value'];
            $discount = min($discount, $amount);
            $couponId = $coupon['id'];
            Database::update('coupons', ['used_count' => $coupon['used_count'] + 1], 'id = ?', [$coupon['id']]);
        }

        $final = max(0, $amount - $discount);
        $txn = 'TXN' . strtoupper(bin2hex(random_bytes(6)));

        Database::insert('payments', [
            'filing_id' => (int) $id,
            'user_id' => Auth::id(),
            'amount' => $final,
            'discount' => $discount,
            'coupon_code' => $couponCode ?: null,
            'method' => $this->input('method', 'demo'),
            'transaction_id' => $txn,
            'status' => 'success',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // Instant expert match (ClearTax style)
        $ca = Database::fetch(
            "SELECT u.id FROM users u
             JOIN ca_profiles cp ON cp.user_id = u.id
             WHERE u.role = 'ca' AND u.status = 'active' AND cp.is_available = 1
             ORDER BY (SELECT COUNT(*) FROM itr_filings f WHERE f.ca_id = u.id AND f.status NOT IN ('completed','cancelled')) ASC
             LIMIT 1"
        );

        $newStatus = $ca ? 'assigned' : 'paid';
        Database::update('itr_filings', [
            'status' => $newStatus,
            'ca_id' => $ca['id'] ?? null,
            'coupon_id' => $couponId,
            'discount_amount' => $discount,
            'amount' => $final,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], $newStatus, Auth::id(), 'Payment OK · Expert match: ' . $txn);
        if ($ca) {
            Helper::notify((int) $ca['id'], 'New Expert Assignment', 'Filing #' . $id . ' assigned.', '/ca/filings/' . $id);
            Helper::notify(Auth::id(), 'Expert Assigned', 'Your tax expert will file within 24 hours.', '/track/' . $id);
        }

        Session::flash('success', 'Payment successful. Expert assigned!');
        $this->redirect('/track/' . $id);
    }

    public function track(string $id): void
    {
        $filing = $this->userFiling((int) $id);
        $logs = Database::fetchAll(
            'SELECT l.*, u.name as changed_by_name FROM status_logs l
             LEFT JOIN users u ON u.id = l.changed_by
             WHERE l.filing_id = ? ORDER BY l.created_at ASC',
            [$id]
        );
        $notes = Database::fetchAll(
            'SELECT n.*, u.name as author_name FROM notes n
             JOIN users u ON u.id = n.author_id
             WHERE n.filing_id = ? AND n.is_internal = 0
             ORDER BY n.created_at DESC',
            [$id]
        );
        $ca = $filing['ca_id']
            ? Database::fetch('SELECT name, email, phone FROM users WHERE id = ?', [$filing['ca_id']])
            : null;

        $this->view('user/track', [
            'title' => 'Track Status',
            'filing' => $filing,
            'logs' => $logs,
            'notes' => $notes,
            'ca' => $ca,
            'step' => 'expert',
        ], 'layouts/panel');
    }

    public function trackList(): void
    {
        $filings = Database::fetchAll(
            'SELECT f.*, p.name as plan_name FROM itr_filings f
             LEFT JOIN plans p ON p.id = f.plan_id
             WHERE f.user_id = ? ORDER BY f.created_at DESC',
            [Auth::id()]
        );
        $this->view('user/track-list', [
            'title' => 'My Filings',
            'filings' => $filings,
        ], 'layouts/panel');
    }

    public function acknowledgement(string $id): void
    {
        $filing = $this->userFiling((int) $id);
        $receipt = Database::fetch('SELECT * FROM receipts WHERE filing_id = ? ORDER BY created_at DESC LIMIT 1', [$id]);

        $this->view('user/acknowledgement', [
            'title' => 'Acknowledgement',
            'filing' => $filing,
            'receipt' => $receipt,
            'step' => 'done',
        ], 'layouts/panel');
    }

    public function downloadReceipt(string $id): void
    {
        $this->userFiling((int) $id);
        $receipt = Database::fetch('SELECT * FROM receipts WHERE filing_id = ? ORDER BY created_at DESC LIMIT 1', [$id]);
        if (!$receipt) {
            Session::flash('error', 'Acknowledgement not available yet.');
            $this->redirect('/acknowledgement/' . $id);
        }

        $path = __DIR__ . '/../../storage/' . $receipt['file_path'];
        if (!file_exists($path)) {
            Session::flash('error', 'File missing on server.');
            $this->redirect('/acknowledgement/' . $id);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($receipt['original_name'] ?: 'acknowledgement.pdf') . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function profile(): void
    {
        $user = Database::fetch('SELECT * FROM users WHERE id = ?', [Auth::id()]);
        $this->view('user/profile', [
            'title' => 'My Profile',
            'user' => $user,
        ], 'layouts/panel');
    }

    public function updateProfile(): void
    {
        $this->validateCsrf();
        Database::update('users', [
            'name' => $this->input('name'),
            'phone' => $this->input('phone'),
            'pan' => strtoupper($this->input('pan')),
            'address' => $this->input('address'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'pincode' => $this->input('pincode'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [Auth::id()]);

        if ($this->input('password')) {
            Database::update('users', [
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            ], 'id = ?', [Auth::id()]);
        }

        Auth::refresh();
        Session::flash('success', 'Profile updated successfully.');
        $this->redirect('/profile');
    }

    private function userFiling(int $id): array
    {
        $filing = Database::fetch('SELECT * FROM itr_filings WHERE id = ? AND user_id = ?', [$id, Auth::id()]);
        if (!$filing) {
            Session::flash('error', 'Filing not found.');
            $this->redirect('/dashboard');
        }
        return $filing;
    }
}
