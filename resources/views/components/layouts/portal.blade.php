{{-- File: resources/views/components/layouts/portal.blade.php
     Responsibility: Shell layout for the customer portal pages.
     What it does: renders the brand header (logo, app name, user, logout) and
       the page slot using the compiled Tailwind stylesheet and brand font.
       Shows the impersonation banner (with leave link) while staff impersonate.
     How to use: applied via the #[Layout('components.layouts.portal')] attribute
       on the portal Livewire components.
     How to extend: add navigation links as more portal pages appear. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Tracking') }} — Portal</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
        <x-impersonate::banner />
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3">
                    <x-brand-logo class="h-9" />
                    <span class="hidden text-lg font-semibold text-slate-900 sm:inline">
                        {{ config('app.name', 'Tracking') }}
                    </span>
                </a>

                @auth
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-slate-500">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('customer.logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                            >
                                Sign out
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
            <div class="h-1 bg-gradient-to-r from-brand-400 via-brand-500 to-accent-500"></div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
