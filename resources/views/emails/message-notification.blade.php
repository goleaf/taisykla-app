<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Notification</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f1f5f9;padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px;background:#0f172a;color:#ffffff;">
                            <h1 style="margin:0;font-size:20px;">Taisykla Communication</h1>
                            <p style="margin:6px 0 0;font-size:13px;color:#cbd5f5;">New update from your service team</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;font-size:14px;color:#334155;">{{ $message->sender?->name ?? $message->user?->name ?? 'System' }} sent a message.</p>
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;font-size:14px;color:#0f172a;line-height:1.5;">
                                {!! nl2br(e($body)) !!}
                            </div>
                            <div style="margin-top:16px;font-size:12px;color:#64748b;">
                                <p style="margin:0;">Thread #{{ $message->thread_id }}</p>
                                @if ($message->related_work_order_id)
                                    <p style="margin:4px 0 0;">Work Order #{{ $message->related_work_order_id }}</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px;background:#f8fafc;font-size:11px;color:#64748b;">
                            Manage notification preferences in your account settings. This email is sent for operational updates.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
