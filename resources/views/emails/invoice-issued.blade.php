@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 16px;font-size:18px;font-weight:600;color:#111827;">New Invoice Issued</h2>

<p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#374151;">
    A new invoice has been generated for your recent service.
</p>

<div style="background-color:#f3f4f6;border-radius:8px;padding:20px;margin-bottom:24px;">
    <h3 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#4b5563;text-transform:uppercase;">Invoice Info</h3>
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding:4px 0;font-size:14px;color:#6b7280;" width="120">Invoice #:</td>
            <td style="padding:4px 0;font-size:14px;color:#111827;font-weight:500;">{{ $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;font-size:14px;color:#6b7280;">Amount Due:</td>
            <td style="padding:4px 0;font-size:14px;color:#111827;font-weight:600;color:#ef4444;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;font-size:14px;color:#6b7280;">Due Date:</td>
            <td style="padding:4px 0;font-size:14px;color:#111827;font-weight:500;">{{ $invoice->due_date->format('M j, Y') }}</td>
        </tr>
    </table>
</div>

<div style="text-align:center;">
    <a href="{{ route('billing.index') }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;">View & Pay Invoice</a>
</div>

<p style="margin:24px 0 0;font-size:14px;color:#6b7280;text-align:center;">
    Thank you for your business!
</p>
@endsection
