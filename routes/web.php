<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RaffleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'featuredRaffles' => \App\Models\Raffle::active()->featured()->latest()->take(3)->get(),
        'otherRaffles' => \App\Models\Raffle::active()->where('is_featured', false)->latest()->take(6)->get(),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware('affiliate')->group(function () {
    Route::get('/rifas', [RaffleController::class, 'index'])->name('raffles.index');
    Route::get('/rifa/{slug}', [RaffleController::class, 'show'])->name('raffles.show');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Payments
    Route::get('/pagos', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/pagos/{payment}', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'show'])->name('payments.show');
    Route::post('/pagos/{payment}/review', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'review'])->name('payments.review');

    // Sorteos
    Route::get('/sorteos/{raffle}/preparar', [\App\Http\Controllers\DrawController::class, 'show'])->name('draw.show');
    Route::post('/sorteos/{raffle}/ejecutar', [\App\Http\Controllers\DrawController::class, 'execute'])->name('draw.execute');
    
    // Raffles Management
    Route::resource('raffles', \App\Http\Controllers\Admin\AdminRaffleController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Purchase Wizard (Inertia)
    Route::get('/checkout/payment/{order}', [\App\Http\Controllers\PurchaseWizardController::class, 'showPayment'])->name('checkout.payment');
    Route::get('/checkout/confirmation/{order}', [\App\Http\Controllers\PurchaseWizardController::class, 'showConfirmation'])->name('checkout.confirmation');
    
    // Offline/Manual Payments
    Route::post('/payments/manual', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.manual.store');

    // Affiliates
    Route::get('/afiliados', [\App\Http\Controllers\AffiliateController::class, 'dashboard'])->name('affiliates.dashboard');
    Route::post('/afiliados/codigo', [\App\Http\Controllers\AffiliateController::class, 'generateCode'])->name('affiliates.generate_code');
});

// External Webhooks (CSRF exempt by tradition, handle in VerifyCsrfToken/Middleware)
Route::post('/webhooks/stripe', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook']);
Route::post('/webhooks/paypal', [\App\Http\Controllers\PaymentController::class, 'paypalWebhook']);

require __DIR__.'/auth.php';
