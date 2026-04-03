<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminParticipantController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RaffleController;
use App\Http\Controllers\WinnersController;
use App\Http\Resources\RaffleResource;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'featuredRaffles' => RaffleResource::collection(\App\Models\Raffle::active()->featured()->latest()->take(3)->get())->toArray(request()),
        'otherRaffles' => RaffleResource::collection(\App\Models\Raffle::active()->where('is_featured', false)->latest()->take(6)->get())->toArray(request()),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware('affiliate')->group(function () {
    Route::get('/rifas', [RaffleController::class, 'index'])->name('raffles.index');
    Route::get('/rifa/{raffle}', [RaffleController::class, 'show'])->name('raffles.show');
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
    Route::post('/pagos/lote', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'bulkReview'])->name('payments.bulk-review');

    // Participantes
    Route::get('/participantes', [AdminParticipantController::class, 'index'])->name('participants.index');

    // Sorteos
    Route::get('/sorteos/{raffle}/preparar', [\App\Http\Controllers\DrawController::class, 'show'])->name('draw.show');
    Route::post('/sorteos/{raffle}/ejecutar', [\App\Http\Controllers\DrawController::class, 'execute'])->name('draw.execute');
    
    // Raffles Management
    Route::resource('raffles', \App\Http\Controllers\Admin\AdminRaffleController::class);

    // Configuración
    Route::get('/configuracion', [AdminSettingsController::class, 'index'])->name('settings');
    Route::put('/configuracion', [AdminSettingsController::class, 'update'])->name('settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // My Tickets & Winners
    Route::get('/mis-rifas', [PurchasesController::class, 'index'])->name('purchases.index');
    Route::get('/ganadores', [WinnersController::class, 'index'])->name('winners.index');
    Route::post('/ganadores/{winner}/testimonio', [WinnersController::class, 'storeTestimony'])->name('winners.testimony.store');
    Route::get('/dev/components', function () {
        return Inertia::render('Dev/Components');
    })->name('dev.components');

    // Purchase Wizard (Inertia)
    Route::get('/checkout/payment/{order}', [\App\Http\Controllers\PurchaseWizardController::class, 'showPayment'])->name('checkout.payment');
    Route::get('/checkout/confirmation/{order}', [\App\Http\Controllers\PurchaseWizardController::class, 'showConfirmation'])->name('checkout.confirmation');
    
    // Offline/Manual Payments
    Route::post('/payments/manual', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.manual.store');

    // Affiliates
    Route::get('/afiliados', [\App\Http\Controllers\AffiliateController::class, 'dashboard'])->name('affiliates.dashboard');
    Route::post('/afiliados/codigo', [\App\Http\Controllers\AffiliateController::class, 'generateCode'])->name('affiliates.generate_code');

    // WebPush Subscriptions
    Route::post('/push/subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'update'])->name('push.update');
    Route::delete('/push/subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.destroy');
});

Route::get('/comprobantes/{order}/verificar', [ReceiptController::class, 'verify'])
    ->middleware('signed')
    ->name('receipts.verify');

// External Webhooks (CSRF exempt by tradition, handle in VerifyCsrfToken/Middleware)
Route::post('/webhooks/stripe', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook']);
Route::post('/webhooks/paypal', [\App\Http\Controllers\PaymentController::class, 'paypalWebhook']);

require __DIR__.'/auth.php';
