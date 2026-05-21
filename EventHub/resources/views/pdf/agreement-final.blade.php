<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>عقد رعاية نهائي</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; padding: 40px; color: #333; line-height: 1.8; direction: rtl; text-align: right; }
        h1 { text-align: center; color: #1a1a2e; margin-bottom: 10px; font-size: 24px; }
        h2 { color: #2C3E50; font-size: 16px; margin-top: 30px; border-bottom: 2px solid #22d3ee; padding-bottom: 6px; }
        .subtitle { text-align: center; color: #999; font-size: 12px; margin-bottom: 30px; }
        .badge-accepted { display: inline-block; background: #10b981; color: #fff; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .details { margin-bottom: 30px; }
        .details th { text-align: right; padding-right: 10px; color: #7F8C8D; font-size: 12px; padding-bottom: 6px; }
        .details td { padding-bottom: 6px; font-size: 12px; }
        .content { margin-top: 20px; }
        .parties-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .parties-table th, .parties-table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .parties-table th { background: #f5f5f5; color: #2C3E50; font-size: 12px; }
        .timeline { margin: 20px 0; }
        .timeline-item { padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 11px; }
        .timeline-date { color: #999; font-size: 10px; }
        .signatures { margin-top: 60px; width: 100%; display: table; }
        .signature-box { display: table-cell; width: 50%; text-align: center; }
        .line { border-bottom: 1px solid #333; width: 80%; margin: 30px auto 10px; }
        .seal { text-align: center; margin-top: 40px; padding: 20px; border: 2px solid #22d3ee; border-radius: 12px; }
        .seal-text { color: #22d3ee; font-weight: bold; font-size: 14px; }
        .footer { text-align: center; margin-top: 40px; color: #aaa; font-size: 10px; font-style: italic; }
    </style>
</head>
<body>

    <h1>عقد رعاية رسمي — نسخة نهائية</h1>
    <p class="subtitle">
        <span class="badge-accepted">✅ تم الاتفاق والتوقيع</span>
    </p>

    <h2>📋 تفاصيل الاتفاقية</h2>
    <table class="details">
        <tr><th>تاريخ الاتفاقية:</th><td>{{ $date }}</td></tr>
        <tr><th>عنوان الحدث:</th><td><strong>{{ $event->title }}</strong></td></tr>
        <tr><th>نوع الحدث:</th><td>{{ $event->event_type ?? '—' }}</td></tr>
        <tr><th>تاريخ الحدث:</th><td>{{ \Carbon\Carbon::parse($event->start_time)->format('Y/m/d - h:i A') }} — {{ \Carbon\Carbon::parse($event->end_time)->format('Y/m/d - h:i A') }}</td></tr>
        <tr><th>مكان الحدث:</th><td>{{ $event->venue->name ?? ($event->external_venue_name ?? '—') }}</td></tr>
        <tr><th>السعة:</th><td>{{ $event->capacity ? $event->capacity . ' شخص' : 'مفتوح' }}</td></tr>
        <tr><th>هدف الحدث:</th><td>{{ $event->event_objective ?? '—' }}</td></tr>
        <tr><th>الجمهور المستهدف:</th><td>{{ $event->target_audience ?? '—' }}</td></tr>
    </table>

    <h2>👥 أطراف العقد</h2>
    <table class="parties-table">
        <tr>
            <th>الطرف الأول: مدير الحدث</th>
            <th>الطرف الثاني: الراعي</th>
        </tr>
        <tr>
            <td><strong>{{ $manager->name }}</strong><br><span style="color:#999;font-size:11px">{{ $manager->profile?->contacts()->where('type', 'email')->first()?->value ?? $manager->email }}</span></td>
            <td><strong>{{ $sponsor->name }}</strong><br><span style="color:#999;font-size:11px">{{ $sponsor->profile?->contacts()->where('type', 'email')->first()?->value ?? $sponsor->email }}</span></td>
        </tr>
    </table>

    <h2>📝 بنود وشروط العقد</h2>
    <div class="content" style="background:#fafafa; border:1px solid #eee; border-radius:10px; padding:20px; text-align:right;">
        
        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند الأول: نطاق الرعاية</h3>
            <p style="font-size:12px; color:#555; margin:0;">يلتزم الطرف الثاني (الراعي) بتقديم الدعم المالي و/أو العيني للحدث المذكور أعلاه وفقاً للتفاصيل المتفق عليها بين الطرفين.</p>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند الثاني: قيمة الرعاية</h3>
            <p style="font-size:12px; color:#555; margin:0;">يتعهد الراعي بتقديم مبلغ الرعاية المتفق عليه (إن وجد) وفقاً للآلية والمواعيد التي تم الاتفاق عليها.</p>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند الثالث: التزامات الراعي (الطرف الثاني)</h3>
            <ul style="font-size:12px; color:#555; margin:0; padding-right:20px;">
                <li>تقديم الدعم المالي/العيني في المواعيد المحددة.</li>
                <li>توفير المواد الترويجية (شعارات، لافتات) قبل الحدث.</li>
                <li>الالتزام بشروط وأحكام الحدث المتفق عليها.</li>
            </ul>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند الرابع: التزامات المنظم (الطرف الأول)</h3>
            <ul style="font-size:12px; color:#555; margin:0; padding-right:20px;">
                <li>إبراز شعار الراعي في جميع المواد الدعائية والترويجية.</li>
                <li>توفير المساحات والامتيازات المتفق عليها للراعي.</li>
                <li>تسهيل مهام فريق الراعي خلال فترة انعقاد الحدث.</li>
            </ul>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند الخامس: مدة العقد</h3>
            <p style="font-size:12px; color:#555; margin:0;">يسري هذا العقد من تاريخ اعتماده النهائي عبر المنصة وحتى انتهاء الحدث المذكور أعلاه، ما لم يتم الاتفاق على خلاف ذلك.</p>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند السادس: شروط الإلغاء والفسخ</h3>
            <p style="font-size:12px; color:#555; margin:0;">في حالة رغبة أي من الطرفين في إلغاء هذا العقد، يجب إخطار الطرف الآخر كتابياً عبر المنصة أو البريد الإلكتروني الرسمي قبل فترة لا تقل عن أسبوعين من تاريخ الحدث.</p>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند السابع: السرية</h3>
            <p style="font-size:12px; color:#555; margin:0;">يتعهد الطرفان بالحفاظ على سرية جميع المعلومات المالية والتجارية والمراسلات المتبادلة بموجب هذا العقد.</p>
        </div>

        <div style="margin-bottom:15px;">
            <h3 style="color:#2C3E50; font-size:14px; margin-bottom:5px;">البند الثامن: حل النزاعات</h3>
            <p style="font-size:12px; color:#555; margin:0;">في حالة نشوء أي خلاف حول تفسير أو تنفيذ هذا العقد، يتم حله ودياً بين الطرفين أولاً، وفي حالة تعذر ذلك يتم اللجوء إلى الجهات المختصة.</p>
        </div>
    </div>

    <div class="seal">
        <p class="seal-text">✅ تم الاتفاق النهائي بين الطرفين بتاريخ {{ $date }}</p>
        <p style="color: #666; font-size: 11px;">هذا العقد ملزم قانونياً لكلا الطرفين وفقاً للبنود المتفق عليها في ملف العقد النهائي.</p>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <p><strong>الطرف الأول (مدير الحدث)</strong></p>
            <div class="line"></div>
            <p>{{ $manager->name }}</p>
        </div>
        <div class="signature-box">
            <p><strong>الطرف الثاني (الراعي)</strong></p>
            <div class="line"></div>
            <p>{{ $sponsor->name }}</p>
        </div>
    </div>

    <p class="footer">تم إنشاء هذا العقد النهائي بواسطة منصة EventHub — جميع الحقوق محفوظة</p>

</body>
</html>
