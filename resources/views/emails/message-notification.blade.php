@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#111827;">New Message Received</h2>

<p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
    {{ $message->sender?->name ?? $message->user?->name ?? 'System' }} has sent you a message regarding 
    <strong>{{ $message->related_work_order_id ? 'Work Order #' . $message->related_work_order_id : 'a communication' }}</strong>.
</p>

<div style="background-color:#f9fafb;border-left:4px solid #4f46e5;padding:16px 20px;margin:24px 0;font-size:15px;color:#1f2937;line-height:1.6;font-style:italic;">
    "{!! nl2br(e($body)) !!}"
</div>

<div style="text-align:center;">
    <a href="{{ route('messages.index') }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;">Reply to Message</a>
</div>

<p style="margin:24px 0 0;font-size:12px;color:#9ca3af;text-align:center;">
    Thread ID: #{{ $message->thread_id }}
</p>
@endsection
