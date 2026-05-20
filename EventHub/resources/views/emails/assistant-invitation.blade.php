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
                            <h2 style="margin:0 0 12px;color:#fff;font-size:20px;">مرحباً بك، {{ $assistant->name }}!</h2>
                            <p style="color:#9ca3af;font-size:15px;line-height:1.6;margin:0 0 24px;">
                                لقد تلقيت دعوة من <strong>{{ $manager->name }}</strong> لتكون مساعداً في حدث <strong>"{{ $event->title }}"</strong>.
                            </p>
                            
                            @if($invitationMessage)
                            <!-- Invitation Message Box -->
                            <div style="background:rgba(110,64,242,0.05);border:1px solid rgba(110,64,242,0.2);border-radius:12px;padding:16px;margin-bottom:24px;">
                                <span style="color:#a78bfa;font-size:12px;display:block;margin-bottom:8px;font-weight:600;">رسالة من منظم الحدث:</span>
                                <p style="color:#e5e7eb;font-size:14px;margin:0;line-height:1.5;font-style:italic;">
                                    "{{ $invitationMessage }}"
                                </p>
                            </div>
                            @endif

                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:20px;margin-bottom:24px;">
                                <h3 style="color:#fff;font-size:16px;margin:0 0 12px;">تفاصيل الحدث:</h3>
                                <div style="margin-bottom:8px;">
                                    <span style="color:#6b7280;font-size:12px;display:block;">تاريخ البدء</span>
                                    <strong style="color:#fff;font-size:14px;">{{ \Carbon\Carbon::parse($event->start_time)->format('Y-m-d h:i A') }}</strong>
                                </div>
                                @if($event->venue)
                                <div>
                                    <span style="color:#6b7280;font-size:12px;display:block;">الموقع</span>
                                    <strong style="color:#fff;font-size:14px;">{{ $event->venue->name }}</strong>
                                </div>
                                @endif
                            </div>
                            
                            <p style="color:#9ca3af;font-size:14px;line-height:1.6;margin:0 0 24px;">
                                يمكنك قبول أو رفض هذه الدعوة مباشرة من خلال تطبيق <strong>EventHub</strong> في قسم "الطلبات".
                            </p>

                            <div style="text-align:center;">
                                <a href="#" style="display:inline-block;background:#6e40f2;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;">افتح التطبيق للمراجعة</a>
                            </div>
                            
                            <hr style="border:none;border-top:1px solid rgba(255,255,255,0.06);margin:24px 0;">
                            
                            <p style="color:#6b7280;font-size:12px;line-height:1.5;margin:0;text-align:center;">
                                إذا لم يكن لديك التطبيق، يمكنك تحميله من متجر التطبيقات والبدء فوراً.
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
