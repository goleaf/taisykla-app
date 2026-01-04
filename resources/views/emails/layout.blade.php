<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:32px 24px;background-color:#4f46e5;color:#ffffff;text-align:center;">
                            <h1 style="margin:0;font-size:24px;font-weight:700;letter-spacing:-0.025em;">Taisykla</h1>
                            <p style="margin:8px 0 0;font-size:14px;color:#c7d2fe;text-transform:uppercase;letter-spacing:0.05em;">Service Management System</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 32px;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 32px;background-color:#f9fafb;border-top:1px solid #f3f4f6;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.5;">
                                This is an automated notification from Taisykla.<br>
                                Please do not reply directly to this email.
                            </p>
                            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb;">
                                <p style="margin:0;font-size:11px;color:#9ca3af;">
                                    &copy; {{ date('Y') }} Taisykla App. All rights reserved.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
