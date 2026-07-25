<?php

use App\Core\Router;

$router = new Router();

// Public
$router->get('/', ['HomeController', 'index']);
$router->get('/efiling', ['HomeController', 'efiling']);
$router->get('/pricing', ['HomeController', 'pricing']);
$router->get('/how-it-works', ['HomeController', 'howItWorks']);
$router->get('/tax-calculator', ['HomeController', 'taxCalculator']);
$router->get('/tools', ['HomeController', 'tools']);
$router->get('/refund-status', ['HomeController', 'refundStatus']);
$router->get('/about', ['HomeController', 'about']);
$router->get('/privacy', ['HomeController', 'privacy']);
$router->get('/terms', ['HomeController', 'terms']);
$router->get('/blogs', ['HomeController', 'blogs']);
$router->get('/blogs/{slug}', ['HomeController', 'blogShow']);
$router->get('/faqs', ['HomeController', 'faqs']);
$router->get('/contact', ['HomeController', 'contact']);
$router->post('/contact', ['HomeController', 'contactSubmit']);

// Auth
$router->get('/login', ['AuthController', 'showLogin'], ['GuestMiddleware']);
$router->post('/login', ['AuthController', 'login'], ['GuestMiddleware']);
$router->get('/register', ['AuthController', 'showRegister'], ['GuestMiddleware']);
$router->post('/register', ['AuthController', 'register'], ['GuestMiddleware']);
$router->get('/logout', ['AuthController', 'logout'], ['AuthMiddleware']);

// User panel
$router->get('/dashboard', ['UserController', 'dashboard'], ['UserMiddleware']);
$router->get('/itr/new', ['UserController', 'startFiling'], ['UserMiddleware']);
$router->post('/itr/new', ['UserController', 'createFiling'], ['UserMiddleware']);
$router->get('/documents/{id}', ['UserController', 'documents'], ['UserMiddleware']);
$router->post('/documents/{id}', ['UserController', 'uploadDocument'], ['UserMiddleware']);
$router->get('/summary/{id}', ['UserController', 'summary'], ['UserMiddleware']);
$router->post('/summary/{id}', ['UserController', 'saveSummary'], ['UserMiddleware']);
$router->get('/review/{id}', ['UserController', 'review'], ['UserMiddleware']);
$router->post('/review/{id}/file', ['UserController', 'selfFile'], ['UserMiddleware']);
$router->get('/payment/{id}', ['UserController', 'payment'], ['UserMiddleware']);
$router->post('/payment/{id}', ['UserController', 'processPayment'], ['UserMiddleware']);
$router->get('/track', ['UserController', 'trackList'], ['UserMiddleware']);
$router->get('/track/{id}', ['UserController', 'track'], ['UserMiddleware']);
$router->get('/acknowledgement/{id}', ['UserController', 'acknowledgement'], ['UserMiddleware']);
$router->get('/acknowledgement/{id}/download', ['UserController', 'downloadReceipt'], ['UserMiddleware']);
$router->get('/profile', ['UserController', 'profile'], ['UserMiddleware']);
$router->post('/profile', ['UserController', 'updateProfile'], ['UserMiddleware']);

// CA panel
$router->get('/ca', ['CaController', 'dashboard'], ['CaMiddleware']);
$router->get('/ca/clients', ['CaController', 'clients'], ['CaMiddleware']);
$router->get('/ca/filings/{id}', ['CaController', 'showFiling'], ['CaMiddleware']);
$router->post('/ca/filings/{id}/note', ['CaController', 'addNote'], ['CaMiddleware']);
$router->post('/ca/filings/{id}/request-docs', ['CaController', 'requestDocuments'], ['CaMiddleware']);
$router->post('/ca/filings/{id}/review', ['CaController', 'startReview'], ['CaMiddleware']);
$router->post('/ca/filings/{id}/mark-filed', ['CaController', 'markFiled'], ['CaMiddleware']);
$router->post('/ca/filings/{id}/receipt', ['CaController', 'uploadReceipt'], ['CaMiddleware']);
$router->get('/ca/docs/{docId}', ['CaController', 'downloadDoc'], ['CaMiddleware']);

// Admin panel
$router->get('/admin', ['AdminController', 'dashboard'], ['AdminMiddleware']);
$router->get('/admin/users', ['AdminController', 'users'], ['AdminMiddleware']);
$router->post('/admin/users/{id}/toggle', ['AdminController', 'toggleUser'], ['AdminMiddleware']);
$router->get('/admin/cas', ['AdminController', 'cas'], ['AdminMiddleware']);
$router->get('/admin/cas/create', ['AdminController', 'createCa'], ['AdminMiddleware']);
$router->post('/admin/cas', ['AdminController', 'storeCa'], ['AdminMiddleware']);
$router->get('/admin/cas/{id}/edit', ['AdminController', 'editCa'], ['AdminMiddleware']);
$router->post('/admin/cas/{id}', ['AdminController', 'updateCa'], ['AdminMiddleware']);
$router->get('/admin/orders', ['AdminController', 'orders'], ['AdminMiddleware']);
$router->post('/admin/orders/{id}/assign', ['AdminController', 'assignCa'], ['AdminMiddleware']);
$router->get('/admin/payments', ['AdminController', 'payments'], ['AdminMiddleware']);
$router->get('/admin/coupons', ['AdminController', 'coupons'], ['AdminMiddleware']);
$router->post('/admin/coupons', ['AdminController', 'storeCoupon'], ['AdminMiddleware']);
$router->post('/admin/coupons/{id}/toggle', ['AdminController', 'toggleCoupon'], ['AdminMiddleware']);
$router->get('/admin/blogs', ['AdminController', 'blogs'], ['AdminMiddleware']);
$router->post('/admin/blogs', ['AdminController', 'storeBlog'], ['AdminMiddleware']);
$router->post('/admin/blogs/{id}/delete', ['AdminController', 'deleteBlog'], ['AdminMiddleware']);
$router->get('/admin/faqs', ['AdminController', 'faqs'], ['AdminMiddleware']);
$router->post('/admin/faqs', ['AdminController', 'storeFaq'], ['AdminMiddleware']);
$router->post('/admin/faqs/{id}/delete', ['AdminController', 'deleteFaq'], ['AdminMiddleware']);
$router->get('/admin/settings', ['AdminController', 'settings'], ['AdminMiddleware']);
$router->post('/admin/settings', ['AdminController', 'saveSettings'], ['AdminMiddleware']);
$router->post('/admin/plans/{id}', ['AdminController', 'updatePlan'], ['AdminMiddleware']);

return $router;
