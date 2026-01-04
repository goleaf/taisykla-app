@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#111827;">Verification Code</h2>

<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#374151;">
    Use the following code to complete your sign-in process. This code is valid for a limited time.
</p>

<div style="text-align:center;margin-bottom:32px;">
    <div style="display:inline-block;background-color:#f3f4f6;color:#111827;padding:16px 32px;border-radius:12px;font-family:monospace;font-size:32px;font-weight:700;letter-spacing:0.2em;border:1px solid #e5e7eb;">
        {{ $code }}
    </div>
</div>

<p style="margin:0;font-size:14px;color:#6b7280;text-align:center;line-height:1.6;">
    This code will expire in approximately {{ $expiresIn }}.<br>
    If you did not attempt to sign in, please secure your account immediately.
</p>
@endsection
