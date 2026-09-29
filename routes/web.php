<?php

/**
 * File: routes/web.php
 * Responsibility: Customer portal routes and the landing route.
 * What it does:
 * - "/" sends guests to the portal login, customers to the portal and staff to
 *   the admin panel. Handling authenticated users here is what stops the
 *   /login <-> / redirect loop: the guest middleware sends signed-in users to
 *   "/", so "/" must never send them back to /login.
 * - Serves the passwordless login flow (email form, OTP send, signed verify).
 * - Serves the authenticated portal: dashboard plus one detail page per
 *   process for shipments (Livewire full-page components).
 * How to use: `php artisan route:list`.
 * How to extend: add portal pages as Livewire components and register here.
 */

use App\Http\Controllers\Customer\LoginController;
use App\Http\Controllers\Customer\LogoutController;
use App\Livewire\Customer\Dashboard;
use App\Livewire\Customer\ExportShipmentDetail;
use App\Livewire\Customer\ImportShipmentDetail;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('customer.login');
    }

    return $user->isCustomer()
        ? redirect()->route('customer.dashboard')
        : redirect('/'.Filament::getPanel('admin')->getPath());
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('customer.login');
    Route::post('/login', [LoginController::class, 'send'])->name('customer.login.send');

    // The code entry screen is signed so it can only be used by the browser flow
    // that requested the code. The signature is verified in the controller and by
    // the OTP action (not by the `signed` middleware) so failures surface as
    // friendly messages instead of a bare 403 page.
    Route::get('/login/verify/{otp}', [LoginController::class, 'showVerify'])
        ->name('customer.verify');

    Route::post('/login/verify/{otp}', [LoginController::class, 'verify'])
        ->name('customer.verify.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', LogoutController::class)->name('customer.logout');

    Route::get('/portal', Dashboard::class)->name('customer.dashboard');

    Route::get('/portal/export-shipments/{exportShipment}', ExportShipmentDetail::class)
        ->name('customer.export-shipments.show');
    Route::get('/portal/import-shipments/{importShipment}', ImportShipmentDetail::class)
        ->name('customer.import-shipments.show');
});
