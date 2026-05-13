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
                <table width="460" cellpadding="0" cellspacing="0" style="background:#1a1d28;border:1px solid rgba(255,255,255,0.08);border-radius:16px;overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#f59e0b,#ea580c);padding:28px;text-align:center;">
                            <img src="{{ url('/images/logo.jpg') }}" alt="EventHub Logo" style="width: 56px; height: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; margin-bottom: 8px;">
                            <h1 style="margin:0;color:#fff;font-size:22px;font-weight:800;">تحديث حساب المساعد</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 28px;">
                            <h2 style="margin:0 0 12px;color:#fff;font-size:20px;">مرحباً بك، {{ $name }}!</h2>
                            <p style="color:#9ca3af;font-size:15px;line-height:1.6;margin:0 0 24px;">
                                لقد قام مدير الحدث بتحديث بيانات حسابك وتعيينك للعمل في حدث <strong>"{{ $eventName }}"</strong>. إليك بيانات الدخول الحالية الخاصة بك:
                            </p>
                            
                            <!-- Credentials Box -->
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:20px;margin-bottom:24px;">
                                <div style="margin-bottom:12px;">
                                    <span style="color:#6b7280;font-size:12px;display:block;margin-bottom:4px;">البريد الإلكتروني</span>
                                    <strong style="color:#fff;font-size:16px;">{{ $email }}</strong>
                                </div>
                                @if($password)
                                <div>
                                    <span style="color:#6b7280;font-size:12px;display:block;margin-bottom:4px;">كلمة المرور الجديدة</span>
                                    <strong style="color:#f59e0b;font-size:16px;letter-spacing:1px;">{{ $password }}</strong>
                                </div>
                                @else
                                <div>
                                    <span style="color:#6b7280;font-size:12px;display:block;margin-bottom:4px;">كلمة المرور</span>
                                    <strong style="color:#9ca3af;font-size:14px;">لم يتم تغييرها (استخدم كلمتك السابقة)</strong>
                                </div>
                                @endif
                            </div>
                            
                            <h3 style="color:#fff;font-size:16px;margin:0 0 12px;">ماذا يعني هذا؟</h3>
                            <ol style="color:#9ca3af;font-size:14px;line-height:1.8;padding-right:20px;margin:0 0 24px;">
                                <li>لقد تم تحديث تفاصيل حسابك وصلاحيات دخولك في النظام.</li>
                                <li>عند تسجيل الدخول لتطبيق <strong>EventHub</strong>، ستتمكن من مسح التذاكر الخاصة بالحدث الجديد.</li>
                            </ol>

                            <div style="text-align:center;">
                                <a href="#" style="display:inline-block;background:#f59e0b;color:#111;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;">فتح التطبيق</a>
                            </div>
                            
                            <hr style="border:none;border-top:1px solid rgba(255,255,255,0.06);margin:24px 0;">
                            
                            <p style="color:#6b7280;font-size:12px;line-height:1.5;margin:0;text-align:center;">
                                يمكنك دائماً تعديل ملفك الشخصي أو تغيير كلمة مرورك من إعدادات حسابك داخل التطبيق.
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
