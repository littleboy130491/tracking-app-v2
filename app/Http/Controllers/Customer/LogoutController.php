<?php

/**
 * File: app/Http/Controllers/Customer/LogoutController.php
 * Responsibility: Ends a customer portal session.
 * What it does:
 * - Logs the user out, invalidates the session and returns to the login screen.
 * How to use: POST route customer.logout from the portal layout.
 * How to extend: none needed; keep it as a single-action controller.
 */

namespace App\Http\Controllers\Customer;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController
{
    public function __invoke(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
