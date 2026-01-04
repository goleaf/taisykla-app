@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#111827;">Welcome to Taisykla!</h2>

<p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
    Your account has been successfully created. We're excited to have you on board.
</p>

<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#374151;">
    To get started, you'll need to set up your password by clicking the button below:
</p>

<div style="text-align:center;margin-bottom:32px;">
    <a href="{{ $resetUrl }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;">Set Up Your Password</a>
</div>

<div style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:16px;margin-bottom:24px;">
    <p style="margin:0;font-size:14px;color:#92400e;line-height:1.5;">
        <strong>Login details:</strong><br>
        Email: {{ $user->email }}<br>
        Organization: {{ $user->organization?->name ?? 'None' }}
    </p>
</div>

<p style="margin:0;font-size:14px;color:#6b7280;line-height:1.6;">
    If you have any questions, please contact our support team or visit our knowledge base.
</p>
@endsection
