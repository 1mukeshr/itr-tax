<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/efiling', [HomeController::class, 'efiling'])->name('efiling');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/tax-calculator', [HomeController::class, 'taxCalculator'])->name('tax-calculator');
Route::get('/tools', [HomeController::class, 'tools'])->name('tools');
Route::get('/tools/hra-calculator', [HomeController::class, 'hraCalculator'])->name('tools.hra');
Route::post('/tools/hra-calculator', [HomeController::class, 'hraCalculatorCompute'])->name('tools.hra.compute');
Route::get('/tools/rent-receipt', [HomeController::class, 'rentReceipt'])->name('tools.rent-receipt');
Route::post('/tools/rent-receipt', [HomeController::class, 'rentReceiptGenerate'])->name('tools.rent-receipt.generate');
Route::get('/refund-status', [HomeController::class, 'refundStatus'])->name('refund-status');
Route::post('/refund-status', [HomeController::class, 'refundStatusCheck'])->name('refund-status.check');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/blogs', [HomeController::class, 'blogs'])->name('blogs');
Route::get('/blogs/{slug}', [HomeController::class, 'blogShow'])->name('blog.show');
Route::get('/faqs', [HomeController::class, 'faqs'])->name('faqs');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');

// Public chatbot (FAQ knowledge base + message log in DB)
Route::prefix('chatbot')->name('chatbot.')->group(function () {
    Route::get('/suggestions', [ChatbotController::class, 'suggestions'])->name('suggestions');
    Route::post('/ask', [ChatbotController::class, 'ask'])->name('ask');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->middleware('auth')
    ->name('verification.send');

// Customer ↔ Tax expert chat (assigned filings only)
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/filing/{filing}', [ChatController::class, 'openFiling'])->name('open-filing');
    Route::get('/{thread}/poll', [ChatController::class, 'poll'])->name('poll');
    Route::get('/{thread}', [ChatController::class, 'show'])->name('show');
    Route::post('/{thread}', [ChatController::class, 'send'])->name('send');
});

// User panel
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile/complete', [UserController::class, 'completeProfile'])->name('user.complete-profile');
    Route::post('/profile/complete', [UserController::class, 'saveCompleteProfile'])->name('user.save-complete-profile');
    Route::get('/itr/service', [UserController::class, 'chooseService'])->name('user.choose-service');
    Route::get('/itr/new', [UserController::class, 'chooseService'])->name('user.start-filing');
    Route::post('/itr/new', [UserController::class, 'createFiling'])->name('user.create-filing');
    Route::get('/itr/questions/{filing}', [UserController::class, 'questions'])->name('user.questions');
    Route::post('/itr/questions/{filing}', [UserController::class, 'saveQuestions'])->name('user.save-questions');
    Route::get('/documents/{filing}', [UserController::class, 'documents'])->name('user.documents');
    Route::post('/documents/{filing}', [UserController::class, 'uploadDocument'])->name('user.upload-document');
    Route::post('/documents/{filing}/continue', [UserController::class, 'continueAfterDocuments'])->name('user.continue-documents');
    Route::get('/itr/review-details/{filing}', [UserController::class, 'reviewDetails'])->name('user.review-details');
    Route::post('/itr/review-details/{filing}', [UserController::class, 'confirmDetails'])->name('user.confirm-details');
    Route::get('/summary/{filing}', [UserController::class, 'summary'])->name('user.summary');
    Route::post('/summary/{filing}', [UserController::class, 'saveSummary'])->name('user.save-summary');
    Route::post('/summary/{filing}/approve', [UserController::class, 'approveSummary'])->name('user.approve-summary');
    Route::get('/review/{filing}', [UserController::class, 'review'])->name('user.review');
    Route::post('/review/{filing}/file', [UserController::class, 'selfFile'])->name('user.self-file');
    Route::get('/payment/{filing}', [UserController::class, 'payment'])->name('user.payment');
    Route::post('/payment/{filing}', [UserController::class, 'processPayment'])->name('user.process-payment');
    Route::get('/track', [UserController::class, 'trackList'])->name('user.track-list');
    Route::get('/track/{filing}', [UserController::class, 'track'])->name('user.track');
    Route::post('/track/{filing}/cancel', [UserController::class, 'cancelFiling'])->name('user.cancel-filing');
    Route::post('/track/{filing}/upgrade', [UserController::class, 'upgradeToAssisted'])->name('user.upgrade-assisted');
    Route::get('/acknowledgement/{filing}', [UserController::class, 'acknowledgement'])->name('user.acknowledgement');
    Route::post('/acknowledgement/{filing}/everify', [UserController::class, 'markEverified'])->name('user.mark-everify');
    Route::get('/acknowledgement/{filing}/download', [UserController::class, 'downloadReceipt'])->name('user.download-receipt');
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
});

// CA panel
Route::middleware(['auth', 'role:ca'])->prefix('ca')->name('ca.')->group(function () {
    Route::get('/', [CaController::class, 'dashboard'])->name('dashboard');
    Route::get('/clients', [CaController::class, 'clients'])->name('clients');
    Route::get('/filings/{filing}', [CaController::class, 'showFiling'])->name('filing');
    Route::post('/filings/{filing}/note', [CaController::class, 'addNote'])->name('add-note');
    Route::post('/filings/{filing}/request-docs', [CaController::class, 'requestDocuments'])->name('request-docs');
    Route::post('/filings/{filing}/review', [CaController::class, 'startReview'])->name('start-review');
    Route::post('/filings/{filing}/send-summary', [CaController::class, 'sendSummary'])->name('send-summary');
    Route::post('/filings/{filing}/mark-filed', [CaController::class, 'markFiled'])->name('mark-filed');
    Route::post('/filings/{filing}/receipt', [CaController::class, 'uploadReceipt'])->name('upload-receipt');
    Route::get('/docs/{doc}', [CaController::class, 'downloadDoc'])->name('download-doc');
});

// Admin panel (optional separate host/IP via ADMIN_PORTAL_SEPARATE)
Route::middleware(['admin.host', 'auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('toggle-user');
    Route::get('/cas', [AdminController::class, 'cas'])->name('cas');
    Route::get('/cas/create', [AdminController::class, 'createCa'])->name('cas.create');
    Route::post('/cas', [AdminController::class, 'storeCa'])->name('cas.store');
    Route::get('/cas/{ca}/edit', [AdminController::class, 'editCa'])->name('cas.edit');
    Route::post('/cas/{ca}', [AdminController::class, 'updateCa'])->name('cas.update');
    Route::post('/cas/{ca}/activate', [AdminController::class, 'activateCa'])->name('cas.activate');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::post('/orders/{filing}/assign', [AdminController::class, 'assignCa'])->name('assign-ca');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings.save');
    Route::post('/settings/account', [AdminController::class, 'updateAccount'])->name('settings.account');
    Route::post('/plans/{plan}', [AdminController::class, 'updatePlan'])->name('plans.update');
});
