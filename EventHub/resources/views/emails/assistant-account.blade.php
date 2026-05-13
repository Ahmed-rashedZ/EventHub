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
                        <td style="background:linear-gradient(135deg,#6e40f2,#4f8cff);padding:28px;text-align:center;">
                            <img src="{{ url('/images/logo.jpg') }}" alt="EventHub Logo" style="width: 56px; height: 56px; object-fit: contain; border-radius: 8px; background: white; padding: 4px; margin-bottom: 8px;">
                            <h1 style="margin:0;color:#fff;font-size:22px;font-weight:800;">EventHub</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 28px;">
                            <h2 style="margin:0 0 12px;color:#fff;font-size:20px;">مرحباً بك، {{ $name }}!</h2>
                            <p style="color:#9ca3af;font-size:15px;line-height:1.6;margin:0 0 24px;">
                                تم اختيارك لتكون مساعداً في حدث <strong>"{{ $eventName }}"</strong>. إليك بيانات الدخول الخاصة بك للبدء في استخدام التطبيق:
                            </p>
                            
                            <!-- Credentials Box -->
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:20px;margin-bottom:24px;">
                                <div style="margin-bottom:12px;">
                                    <span style="color:#6b7280;font-size:12px;display:block;margin-bottom:4px;">البريد الإلكتروني</span>
                                    <strong style="color:#fff;font-size:16px;">{{ $email }}</strong>
                                </div>
                                <div>
                                    <span style="color:#6b7280;font-size:12px;display:block;margin-bottom:4px;">كلمة المرور المؤقتة</span>
                                    <strong style="color:#a78bfa;font-size:16px;letter-spacing:1px;">{{ $password }}</strong>
                                </div>
                            </div>
                            
                            <h3 style="color:#fff;font-size:16px;margin:0 0 12px;">خطوات البدء:</h3>
                            <ol style="color:#9ca3af;font-size:14px;line-height:1.8;padding-right:20px;margin:0 0 24px;">
                                <li>قم بتحميل تطبيق <strong>EventHub</strong> من متجر التطبيقات.</li>
                                <li>قم بتسجيل الدخول باستخدام البريد وكلمة المرور أعلاه.</li>
                                <li>ابدأ بمسح تذاكر الزوار عند بوابات الحدث.</li>
                            </ol>

                            <div style="text-align:center;">
                                <a href="#" style="display:inline-block;background:#6e40f2;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;">تحميل التطبيق الآن</a>
                            </div>
                            
                            <hr style="border:none;border-top:1px solid rgba(255,255,255,0.06);margin:24px 0;">
                            
                            <p style="color:#6b7280;font-size:12px;line-height:1.5;margin:0;text-align:center;">
                                يمكنك تغيير كلمة المرور وتعديل ملفك الشخصي مباشرة من داخل التطبيق.
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
