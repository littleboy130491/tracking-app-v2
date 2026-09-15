{{-- File: resources/views/customer/login.blade.php
     Responsibility: Customer portal email step of the passwordless login.
     What it does: shows the brand mark and collects the registered email address;
       posts it to the OTP send endpoint and shows validation errors.
     How to use: served by LoginController@showLogin.
     How to extend: add branding or a "remember this device" option. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign in — {{ config('app.name', 'Tracking') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 font-sans text-slate-800 antialiased">
        <div class="w-full max-w-md">
            <div class="mb-6 flex justify-center">
                <x-brand-logo class="h-12" />
            </div>

            <div class="rounded-xl bg-white p-8 shadow-sm">
                <div class="h-1 w-16 rounded-full bg-brand-500"></div>

                <h1 class="mt-4 text-xl font-semibold text-slate-900">Sign in to your dashboard</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Enter the email address your company registered with us. We will send you a one-time code.
                </p>

                @if ($errors->any())
                    <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.login.send') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-brand-500 px-4 py-2 font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-200"
                    >
                        Send one-time code
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>
