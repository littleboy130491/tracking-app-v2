{{-- File: resources/views/customer/verify.blade.php
     Responsibility: Customer portal code-entry step of the passwordless login.
     What it does: shows the brand mark and posts the one-time code back to the
       current (signed) URL; in local development it also displays the code.
       Posting to request()->fullUrl() keeps the signature intact -- using the
       route() helper here would drop it and the request would be rejected.
     How to use: served by LoginController@showVerify.
     How to extend: swap the input for a segmented OTP component. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Enter your code — {{ config('app.name', 'Tracking') }}</title>
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

                <h1 class="mt-4 text-xl font-semibold text-slate-900">Enter your one-time code</h1>
                <p class="mt-2 text-sm text-slate-500">
                    We emailed you a code. It expires in {{ config('otpz.expiration', 5) }} minutes.
                </p>

                @if ($devCode)
                    <div class="mt-4 rounded-md border border-brand-200 bg-brand-50 p-3 text-sm text-brand-800">
                        Development mode: your code is <span class="font-mono font-semibold">{{ $devCode }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">
                        <span aria-hidden="true">&times;</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="sessionId" value="{{ $sessionId }}">

                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700">Code</label>
                        <input
                            id="code"
                            name="code"
                            type="text"
                            inputmode="text"
                            autocomplete="one-time-code"
                            required
                            autofocus
                            class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 font-mono tracking-widest outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-brand-500 px-4 py-2 font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-200"
                    >
                        Sign in
                    </button>
                </form>

                <a
                    href="{{ route('customer.login') }}"
                    class="mt-4 block text-center text-sm text-slate-500 transition hover:text-brand-600"
                >
                    Use a different email address or request a new code
                </a>
            </div>
        </div>
    </body>
</html>
