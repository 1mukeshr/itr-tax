<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Helper;

class HomeController extends Controller
{
    public function index(): void
    {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug != 'self-free' ORDER BY sort_order ASC");
        $faqs = Database::fetchAll('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 6');
        $blogs = Database::fetchAll('SELECT * FROM blogs WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3');

        $this->view('public/home', [
            'title' => 'Home',
            'plans' => $plans,
            'faqs' => $faqs,
            'blogs' => $blogs,
        ]);
    }

    public function pricing(): void
    {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug != 'self-free' ORDER BY sort_order ASC");
        $this->view('public/pricing', [
            'title' => 'Pricing Plans',
            'plans' => $plans,
        ]);
    }

    public function efiling(): void
    {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug != 'self-free' ORDER BY sort_order ASC LIMIT 3");
        $faqs = Database::fetchAll('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 6');
        $this->view('public/efiling', [
            'title' => 'Income Tax eFiling',
            'plans' => $plans,
            'faqs' => $faqs,
        ]);
    }

    public function taxCalculator(): void
    {
        $this->view('public/tax-calculator', ['title' => 'Income Tax Calculator']);
    }

    public function tools(): void
    {
        $this->view('public/tools', ['title' => 'Tax Tools']);
    }

    public function refundStatus(): void
    {
        $this->view('public/refund-status', ['title' => 'Check Refund Status']);
    }

    public function about(): void
    {
        $this->view('public/about', ['title' => 'About ITR Tax']);
    }

    public function privacy(): void
    {
        $this->view('public/privacy', ['title' => 'Privacy Policy']);
    }

    public function terms(): void
    {
        $this->view('public/terms', ['title' => 'Terms of Use']);
    }

    public function howItWorks(): void
    {
        $this->view('public/how-it-works', ['title' => 'How It Works']);
    }

    public function blogs(): void
    {
        $blogs = Database::fetchAll('SELECT b.*, u.name as author_name FROM blogs b LEFT JOIN users u ON u.id = b.author_id WHERE b.is_published = 1 ORDER BY b.published_at DESC');
        $this->view('public/blogs', ['title' => 'Tax Guides & Blogs', 'blogs' => $blogs]);
    }

    public function blogShow(string $slug): void
    {
        $blog = Database::fetch('SELECT b.*, u.name as author_name FROM blogs b LEFT JOIN users u ON u.id = b.author_id WHERE b.slug = ? AND b.is_published = 1', [$slug]);
        if (!$blog) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Not Found']);
            return;
        }
        $this->view('public/blog-show', ['title' => $blog['title'], 'blog' => $blog]);
    }

    public function faqs(): void
    {
        $faqs = Database::fetchAll('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC');
        $this->view('public/faqs', ['title' => 'FAQs', 'faqs' => $faqs]);
    }

    public function contact(): void
    {
        $this->view('public/contact', ['title' => 'Contact Us']);
    }

    public function contactSubmit(): void
    {
        $this->validateCsrf();
        Helper::notify(1, 'New Contact Message', 'From: ' . $this->input('name') . ' — ' . $this->input('message'));
        \App\Core\Session::flash('success', 'Thanks! We will get back to you shortly.');
        $this->redirect('/contact');
    }
}
