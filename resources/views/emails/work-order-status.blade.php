@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#111827;">Work Order Update</h2>

<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#374151;">
    Your work order <strong>#{{ $workOrder->id }}</strong> has been updated to <strong>{{ ucfirst($workOrder->status) }}</strong>.
</p>

<div style="background-color:#f3f4f6;border-radius:8px;padding:20px;margin-bottom:24px;">
    <h3 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#4b5563;text-transform:uppercase;">Details</h3>
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding:4px 0;font-size:14px;color:#6b7280;" width="100">Subject:</td>
            <td style="padding:4px 0;font-size:14px;color:#111827;font-weight:500;">{{ $workOrder->subject }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;font-size:14px;color:#6b7280;">Priority:</td>
            <td style="padding:4px 0;font-size:14px;color:#111827;font-weight:500;">{{ ucfirst($workOrder->priority) }}</td>
        </tr>
        @if($workOrder->scheduled_start_at)
        <tr>
            <td style="padding:4px 0;font-size:14px;color:#6b7280;">Scheduled:</td>
            <td style="padding:4px 0;font-size:14px;color:#111827;font-weight:500;">{{ $workOrder->scheduled_start_at->format('M j, Y H:i') }}</td>
        </tr>
        @endif
    </table>
</div>

<div style="text-align:center;">
    <a href="{{ route('work-orders.show', $workOrder->id) }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;">View Work Order</a>
</div>
@endsection
