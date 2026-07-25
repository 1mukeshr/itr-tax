<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Helper;
use App\Core\Session;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $stats = [
            'users' => Database::fetch("SELECT COUNT(*) as c FROM users WHERE role = 'user'")['c'] ?? 0,
            'cas' => Database::fetch("SELECT COUNT(*) as c FROM users WHERE role = 'ca'")['c'] ?? 0,
            'filings' => Database::fetch('SELECT COUNT(*) as c FROM itr_filings')['c'] ?? 0,
            'revenue' => Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM payments WHERE status = 'success'")['s'] ?? 0,
            'pending' => Database::fetch("SELECT COUNT(*) as c FROM itr_filings WHERE status IN ('paid','assigned','under_review','docs_requested')")['c'] ?? 0,
            'filed' => Database::fetch("SELECT COUNT(*) as c FROM itr_filings WHERE status IN ('filed','completed')")['c'] ?? 0,
        ];

        $recentOrders = Database::fetchAll(
            "SELECT f.*, u.name as client_name, p.name as plan_name
             FROM itr_filings f
             JOIN users u ON u.id = f.user_id
             LEFT JOIN plans p ON p.id = f.plan_id
             ORDER BY f.created_at DESC LIMIT 8"
        );

        $recentPayments = Database::fetchAll(
            "SELECT pay.*, u.name as user_name FROM payments pay
             JOIN users u ON u.id = pay.user_id
             ORDER BY pay.created_at DESC LIMIT 8"
        );

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentPayments' => $recentPayments,
        ], 'layouts/panel');
    }

    public function users(): void
    {
        $q = $this->input('q');
        $sql = "SELECT * FROM users WHERE role = 'user'";
        $params = [];
        if ($q) {
            $sql .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR pan LIKE ?)';
            $like = '%' . $q . '%';
            $params = [$like, $like, $like, $like];
        }
        $sql .= ' ORDER BY created_at DESC';
        $users = Database::fetchAll($sql, $params);

        $this->view('admin/users', [
            'title' => 'Users',
            'users' => $users,
            'q' => $q,
        ], 'layouts/panel');
    }

    public function toggleUser(string $id): void
    {
        $this->validateCsrf();
        $user = Database::fetch('SELECT * FROM users WHERE id = ? AND role = ?', [$id, 'user']);
        if ($user) {
            $status = $user['status'] === 'active' ? 'suspended' : 'active';
            Database::update('users', ['status' => $status], 'id = ?', [$id]);
            Session::flash('success', 'User status updated.');
        }
        $this->redirect('/admin/users');
    }

    public function cas(): void
    {
        $cas = Database::fetchAll(
            "SELECT u.*, cp.membership_no, cp.specialization, cp.experience_years, cp.is_available, cp.max_clients,
                    (SELECT COUNT(*) FROM itr_filings f WHERE f.ca_id = u.id) as client_count
             FROM users u
             LEFT JOIN ca_profiles cp ON cp.user_id = u.id
             WHERE u.role = 'ca'
             ORDER BY u.created_at DESC"
        );
        $this->view('admin/cas', ['title' => 'CA Management', 'cas' => $cas], 'layouts/panel');
    }

    public function createCa(): void
    {
        $this->view('admin/ca-form', [
            'title' => 'Add CA',
            'ca' => null,
        ], 'layouts/panel');
    }

    public function storeCa(): void
    {
        $this->validateCsrf();
        $email = strtolower($this->input('email'));
        if (Database::fetch('SELECT id FROM users WHERE email = ?', [$email])) {
            Session::flash('error', 'Email already exists.');
            $this->redirect('/admin/cas/create');
        }

        $uid = Database::insert('users', [
            'name' => $this->input('name'),
            'email' => $email,
            'phone' => $this->input('phone'),
            'password' => password_hash($_POST['password'] ?: 'password123', PASSWORD_BCRYPT),
            'role' => 'ca',
            'status' => 'active',
        ]);

        Database::insert('ca_profiles', [
            'user_id' => $uid,
            'membership_no' => $this->input('membership_no'),
            'specialization' => $this->input('specialization'),
            'experience_years' => (int) $this->input('experience_years'),
            'max_clients' => (int) ($this->input('max_clients') ?: 50),
            'bio' => $this->input('bio'),
            'is_available' => 1,
        ]);

        Session::flash('success', 'CA created successfully.');
        $this->redirect('/admin/cas');
    }

    public function editCa(string $id): void
    {
        $ca = Database::fetch(
            "SELECT u.*, cp.membership_no, cp.specialization, cp.experience_years, cp.max_clients, cp.bio, cp.is_available
             FROM users u LEFT JOIN ca_profiles cp ON cp.user_id = u.id WHERE u.id = ? AND u.role = 'ca'",
            [$id]
        );
        if (!$ca) {
            Session::flash('error', 'CA not found.');
            $this->redirect('/admin/cas');
        }
        $this->view('admin/ca-form', ['title' => 'Edit CA', 'ca' => $ca], 'layouts/panel');
    }

    public function updateCa(string $id): void
    {
        $this->validateCsrf();
        Database::update('users', [
            'name' => $this->input('name'),
            'phone' => $this->input('phone'),
            'status' => $this->input('status', 'active'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        if ($this->input('password')) {
            Database::update('users', [
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            ], 'id = ?', [$id]);
        }

        $profile = Database::fetch('SELECT id FROM ca_profiles WHERE user_id = ?', [$id]);
        $data = [
            'membership_no' => $this->input('membership_no'),
            'specialization' => $this->input('specialization'),
            'experience_years' => (int) $this->input('experience_years'),
            'max_clients' => (int) $this->input('max_clients'),
            'bio' => $this->input('bio'),
            'is_available' => $this->input('is_available') === '1' ? 1 : 0,
        ];
        if ($profile) {
            Database::update('ca_profiles', $data, 'user_id = ?', [$id]);
        } else {
            $data['user_id'] = (int) $id;
            Database::insert('ca_profiles', $data);
        }

        Session::flash('success', 'CA updated.');
        $this->redirect('/admin/cas');
    }

    public function orders(): void
    {
        $status = $this->input('status');
        $sql = "SELECT f.*, u.name as client_name, ca.name as ca_name, p.name as plan_name
                FROM itr_filings f
                JOIN users u ON u.id = f.user_id
                LEFT JOIN users ca ON ca.id = f.ca_id
                LEFT JOIN plans p ON p.id = f.plan_id WHERE 1=1";
        $params = [];
        if ($status) {
            $sql .= ' AND f.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY f.created_at DESC';
        $orders = Database::fetchAll($sql, $params);
        $cas = Database::fetchAll("SELECT id, name FROM users WHERE role = 'ca' AND status = 'active'");

        $this->view('admin/orders', [
            'title' => 'Orders / Filings',
            'orders' => $orders,
            'cas' => $cas,
            'filter' => $status,
        ], 'layouts/panel');
    }

    public function assignCa(string $id): void
    {
        $this->validateCsrf();
        $caId = (int) $this->input('ca_id');
        $filing = Database::fetch('SELECT * FROM itr_filings WHERE id = ?', [$id]);
        if (!$filing || !$caId) {
            Session::flash('error', 'Invalid assignment.');
            $this->redirect('/admin/orders');
        }

        Database::update('itr_filings', [
            'ca_id' => $caId,
            'status' => 'assigned',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Helper::logStatus((int) $id, $filing['status'], 'assigned', Auth::id(), 'Admin assigned CA');
        Helper::notify($caId, 'New Assignment', 'Admin assigned filing #' . $id . ' to you.', '/ca/filings/' . $id);
        Helper::notify((int) $filing['user_id'], 'CA Assigned', 'An expert CA has been assigned to your filing.', '/track/' . $id);

        Session::flash('success', 'CA assigned successfully.');
        $this->redirect('/admin/orders');
    }

    public function payments(): void
    {
        $payments = Database::fetchAll(
            "SELECT pay.*, u.name as user_name, u.email FROM payments pay
             JOIN users u ON u.id = pay.user_id
             ORDER BY pay.created_at DESC"
        );
        $this->view('admin/payments', ['title' => 'Payments', 'payments' => $payments], 'layouts/panel');
    }

    public function coupons(): void
    {
        $coupons = Database::fetchAll('SELECT * FROM coupons ORDER BY created_at DESC');
        $this->view('admin/coupons', ['title' => 'Coupons', 'coupons' => $coupons], 'layouts/panel');
    }

    public function storeCoupon(): void
    {
        $this->validateCsrf();
        Database::insert('coupons', [
            'code' => strtoupper($this->input('code')),
            'type' => $this->input('type', 'percent'),
            'value' => (float) $this->input('value'),
            'max_uses' => (int) $this->input('max_uses'),
            'min_amount' => (float) $this->input('min_amount'),
            'expires_at' => $this->input('expires_at') ?: null,
            'is_active' => 1,
        ]);
        Session::flash('success', 'Coupon created.');
        $this->redirect('/admin/coupons');
    }

    public function toggleCoupon(string $id): void
    {
        $this->validateCsrf();
        $c = Database::fetch('SELECT * FROM coupons WHERE id = ?', [$id]);
        if ($c) {
            Database::update('coupons', ['is_active' => $c['is_active'] ? 0 : 1], 'id = ?', [$id]);
        }
        $this->redirect('/admin/coupons');
    }

    public function blogs(): void
    {
        $blogs = Database::fetchAll('SELECT b.*, u.name as author_name FROM blogs b LEFT JOIN users u ON u.id = b.author_id ORDER BY b.created_at DESC');
        $this->view('admin/blogs', ['title' => 'Blogs', 'blogs' => $blogs], 'layouts/panel');
    }

    public function storeBlog(): void
    {
        $this->validateCsrf();
        $title = $this->input('title');
        $slug = Helper::slug($title);
        $published = $this->input('is_published') === '1' ? 1 : 0;

        Database::insert('blogs', [
            'title' => $title,
            'slug' => $slug . '-' . substr(uniqid(), -4),
            'excerpt' => $this->input('excerpt'),
            'content' => $_POST['content'] ?? '',
            'author_id' => Auth::id(),
            'is_published' => $published,
            'published_at' => $published ? date('Y-m-d H:i:s') : null,
        ]);
        Session::flash('success', 'Blog saved.');
        $this->redirect('/admin/blogs');
    }

    public function deleteBlog(string $id): void
    {
        $this->validateCsrf();
        Database::delete('blogs', 'id = ?', [$id]);
        Session::flash('success', 'Blog deleted.');
        $this->redirect('/admin/blogs');
    }

    public function faqs(): void
    {
        $faqs = Database::fetchAll('SELECT * FROM faqs ORDER BY sort_order ASC, id DESC');
        $this->view('admin/faqs', ['title' => 'FAQs', 'faqs' => $faqs], 'layouts/panel');
    }

    public function storeFaq(): void
    {
        $this->validateCsrf();
        Database::insert('faqs', [
            'question' => $this->input('question'),
            'answer' => $_POST['answer'] ?? '',
            'category' => $this->input('category', 'General'),
            'sort_order' => (int) $this->input('sort_order'),
            'is_active' => 1,
        ]);
        Session::flash('success', 'FAQ added.');
        $this->redirect('/admin/faqs');
    }

    public function deleteFaq(string $id): void
    {
        $this->validateCsrf();
        Database::delete('faqs', 'id = ?', [$id]);
        Session::flash('success', 'FAQ deleted.');
        $this->redirect('/admin/faqs');
    }

    public function settings(): void
    {
        $rows = Database::fetchAll('SELECT * FROM settings');
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        $plans = Database::fetchAll('SELECT * FROM plans ORDER BY sort_order');
        $this->view('admin/settings', [
            'title' => 'Settings',
            'settings' => $settings,
            'plans' => $plans,
        ], 'layouts/panel');
    }

    public function saveSettings(): void
    {
        $this->validateCsrf();
        $keys = ['site_name', 'support_email', 'support_phone', 'razorpay_key', 'company_address'];
        foreach ($keys as $key) {
            $val = $this->input($key);
            $exists = Database::fetch('SELECT id FROM settings WHERE setting_key = ?', [$key]);
            if ($exists) {
                Database::update('settings', [
                    'setting_value' => $val,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'setting_key = ?', [$key]);
            } else {
                Database::insert('settings', ['setting_key' => $key, 'setting_value' => $val]);
            }
        }
        Session::flash('success', 'Settings saved.');
        $this->redirect('/admin/settings');
    }

    public function updatePlan(string $id): void
    {
        $this->validateCsrf();
        Database::update('plans', [
            'name' => $this->input('name'),
            'price' => (float) $this->input('price'),
            'description' => $this->input('description'),
            'is_active' => $this->input('is_active') === '1' ? 1 : 0,
        ], 'id = ?', [$id]);
        Session::flash('success', 'Plan updated.');
        $this->redirect('/admin/settings');
    }
}
