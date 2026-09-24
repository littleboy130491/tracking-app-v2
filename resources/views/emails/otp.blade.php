{{-- File: resources/views/emails/otp.blade.php
     Responsibility: One-time password email with SAM Group branding.
     What it does: same slots as the package template (greeting, copy, code,
       subcopy, footer) but the header shows the SAM Group logo instead of
       the package mark. The logo URL is absolute so mail clients can load it.
     How to use: referenced by config/otpz.php `template`.
     How to extend: copy a slot block to reword the email. --}}
<x-otpz::template>
<x-slot:logo>
<img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="140" style="display: block; max-width: 140px; height: auto;">
</x-slot>

<x-slot:greeting>
Sign in to {{ config('app.name') }}
</x-slot>

<x-slot:copy>
We received a sign-in request for the account {{ $email }}. Use the code below to sign in.
</x-slot>

<x-slot:code>
{{ $code }}
</x-slot>

<x-slot:subcopy>
If you didn't request this login link, you can safely ignore this email.
</x-slot>

<x-slot:footer>
<strong>Security Reminder:</strong> Fraudulent websites may try to steal your login code. Only enter this code at {{ config('app.url') }}. Never enter this code on any other website or share it with anyone.
</x-slot>
</x-otpz::template>
