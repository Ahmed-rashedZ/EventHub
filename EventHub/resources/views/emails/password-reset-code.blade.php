<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#0f1117;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f1117;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="420" cellpadding="0" cellspacing="0" style="background:#1a1d28;border:1px solid rgba(255,255,255,0.08);border-radius:16px;overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#6e40f2,#4f8cff);padding:28px;text-align:center;">
                            <div style="font-size:32px;margin-bottom:8px;">🎯</div>
                            <h1 style="margin:0;color:#fff;font-size:22px;font-weight:800;">EventHub</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 28px;">
                            <h2 style="margin:0 0 8px;color:#fff;font-size:18px;">مرحباً {{ $userName }}،</h2>
                            <p style="color:#9ca3af;font-size:14px;line-height:1.6;margin:0 0 24px;">
                                لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك. استخدم الرمز التالي:
                            </p>
                            
                            <!-- OTP Code -->
                            <div style="background:rgba(110,64,242,0.1);border:2px dashed rgba(110,64,242,0.4);border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;">
                                <div style="font-size:36px;font-weight:800;letter-spacing:12px;color:#a78bfa;font-family:monospace;">
                                    {{ $code }}
                                </div>
                            </div>
                            
                            <p style="color:#ef4444;font-size:13px;margin:0 0 16px;text-align:center;">
                                ⏱ هذا الرمز صالح لمدة <strong>5 دقائق</strong> فقط
                            </p>
                            
                            <hr style="border:none;border-top:1px solid rgba(255,255,255,0.06);margin:20px 0;">
                            
                            <p style="color:#6b7280;font-size:12px;line-height:1.5;margin:0;">
                                إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة بأمان.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:16px 28px;border-top:1px solid rgba(255,255,255,0.06);text-align:center;">
                            <p style="color:#4b5563;font-size:11px;margin:0;">© {{ date('Y') }} EventHub. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
