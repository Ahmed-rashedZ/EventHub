<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Carbon\Carbon;

class AgreementWordService
{
    /**
     * Generate a Word (.docx) agreement document in Arabic.
     *
     * @param  object  $sponsorshipRequest  (loaded with event.venue, sponsor, manager)
     * @return string  The file path (relative to storage/app/public)
     */
    public static function generate($sponsorshipRequest): string
    {
        $event   = $sponsorshipRequest->event;
        $sponsor = $sponsorshipRequest->sponsor;
        $manager = $sponsorshipRequest->manager;
        $date    = now()->format('Y-m-d');

        $phpWord = new PhpWord();

        // ── Global Settings ──────────────────────────────────────
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);
        $phpWord->setDefaultParagraphStyle(['alignment' => Jc::END, 'bidi' => true]);

        // ── Styles ───────────────────────────────────────────────
        $phpWord->addParagraphStyle('RTL', [
            'alignment'  => Jc::END,
            'bidi'       => true,
            'spaceAfter' => 120,
        ]);

        $phpWord->addParagraphStyle('RTLCenter', [
            'alignment' => Jc::CENTER,
            'bidi'      => true,
            'spaceAfter' => 120,
        ]);

        $phpWord->addFontStyle('TitleFont', [
            'name'  => 'Arial',
            'size'  => 22,
            'bold'  => true,
            'color' => '1a1a2e',
        ]);

        $phpWord->addFontStyle('SectionFont', [
            'name'  => 'Arial',
            'size'  => 14,
            'bold'  => true,
            'color' => '2C3E50',
        ]);

        $phpWord->addFontStyle('LabelFont', [
            'name'  => 'Arial',
            'size'  => 12,
            'bold'  => true,
            'color' => '555555',
        ]);

        $phpWord->addFontStyle('ValueFont', [
            'name'  => 'Arial',
            'size'  => 12,
            'color' => '333333',
        ]);

        $phpWord->addFontStyle('ClauseFont', [
            'name'  => 'Arial',
            'size'  => 12,
            'color' => '333333',
        ]);

        $phpWord->addFontStyle('PlaceholderFont', [
            'name'  => 'Arial',
            'size'  => 12,
            'color' => '999999',
            'italic' => true,
        ]);

        // ── Section ──────────────────────────────────────────────
        $section = $phpWord->addSection([
            'marginTop'    => 1200,
            'marginBottom' => 1200,
            'marginLeft'   => 1200,
            'marginRight'  => 1200,
        ]);

        // ─── TITLE ──────────────────────────────────────────────
        $section->addText('عقد رعاية رسمي', 'TitleFont', 'RTLCenter');
        $section->addText('Official Sponsorship Agreement', [
            'name' => 'Arial', 'size' => 11, 'color' => '999999', 'italic' => true
        ], 'RTLCenter');

        // Decorative line
        $section->addText(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
            ['name' => 'Arial', 'size' => 8, 'color' => 'CCCCCC'],
            'RTLCenter'
        );
        $section->addTextBreak(1);

        // ─── AGREEMENT DETAILS ──────────────────────────────────
        $section->addText('📋 تفاصيل الاتفاقية', 'SectionFont', 'RTL');

        $detailsTable = $section->addTable([
            'borderSize'  => 0,
            'cellMargin'  => 80,
            'alignment'   => Jc::END,
        ]);

        $details = [
            ['تاريخ الاتفاقية', $date],
            ['رقم الطلب', '#' . $sponsorshipRequest->id],
            ['عنوان الحدث', $event->title ?? '—'],
            ['نوع الحدث', $event->event_type ?? '—'],
            ['تاريخ بداية الحدث', $event->start_time ? Carbon::parse($event->start_time)->format('Y/m/d - h:i A') : '—'],
            ['تاريخ نهاية الحدث', $event->end_time ? Carbon::parse($event->end_time)->format('Y/m/d - h:i A') : '—'],
            ['مكان الحدث', $event->venue->name ?? ($event->external_venue_name ?? '—')],
            ['موقع الحدث', $event->venue->location ?? ($event->external_venue_location ?? '—')],
            ['سعة الحدث', ($event->capacity ?? '—') . ' شخص'],
            ['هدف الحدث', $event->event_objective ?? '—'],
            ['الجمهور المستهدف', $event->target_audience ?? '—'],
        ];

        foreach ($details as $row) {
            $tableRow = $detailsTable->addRow();
            $cell1 = $tableRow->addCell(3200);
            $cell1->addText($row[0] . ':', 'LabelFont', 'RTL');
            $cell2 = $tableRow->addCell(6800);
            $cell2->addText($row[1], 'ValueFont', 'RTL');
        }

        $section->addTextBreak(1);

        // ─── PARTIES ────────────────────────────────────────────
        $section->addText('👥 أطراف العقد', 'SectionFont', 'RTL');

        $partiesTable = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => 'E0E0E0',
            'cellMargin'  => 100,
            'alignment'   => Jc::CENTER,
        ]);

        // Header row
        $headerRow = $partiesTable->addRow(400);
        $headerRow->addCell(5000, ['bgColor' => 'F5F5F5'])->addText(
            'الطرف الأول: مدير الحدث',
            ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '2C3E50'],
            'RTLCenter'
        );
        $headerRow->addCell(5000, ['bgColor' => 'F5F5F5'])->addText(
            'الطرف الثاني: الراعي',
            ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '2C3E50'],
            'RTLCenter'
        );

        // Values row
        $valRow = $partiesTable->addRow(400);
        $valRow->addCell(5000)->addText(
            $manager->name ?? '—',
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            'RTLCenter'
        );
        $valRow->addCell(5000)->addText(
            $sponsor->name ?? '—',
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            'RTLCenter'
        );

        $managerEmail = $manager->profile?->contacts()->where('type', 'email')->first()?->value ?? $manager->email ?? '—';
        $sponsorEmail = $sponsor->profile?->contacts()->where('type', 'email')->first()?->value ?? $sponsor->email ?? '—';

        // Contact row
        $contactRow = $partiesTable->addRow(300);
        $contactRow->addCell(5000)->addText(
            $managerEmail,
            ['name' => 'Arial', 'size' => 10, 'color' => '777777'],
            'RTLCenter'
        );
        $contactRow->addCell(5000)->addText(
            $sponsorEmail,
            ['name' => 'Arial', 'size' => 10, 'color' => '777777'],
            'RTLCenter'
        );

        $section->addTextBreak(1);

        // ─── TERMS & CONDITIONS ─────────────────────────────────
        $section->addText('📝 بنود وشروط العقد', 'SectionFont', 'RTL');
        $section->addText(
            'يرجى من الطرفين مراجعة وتعديل البنود التالية وفقاً للاتفاق المشترك:',
            ['name' => 'Arial', 'size' => 10, 'color' => '888888', 'italic' => true],
            'RTL'
        );
        $section->addTextBreak(1);

        $clauses = [
            [
                'title'   => 'البند الأول: نطاق الرعاية',
                'content' => 'يلتزم الطرف الثاني (الراعي) بتقديم الدعم المالي و/أو العيني للحدث المذكور أعلاه وفقاً للتفاصيل المتفق عليها بين الطرفين.',
                'editable' => 'حدد نوع الدعم المقدم (مالي / عيني / كلاهما) والتفاصيل:',
            ],
            [
                'title'   => 'البند الثاني: قيمة الرعاية',
                'content' => 'يتعهد الراعي بتقديم مبلغ الرعاية المتفق عليه وفقاً للآتي:',
                'editable' => 'حدد المبلغ الإجمالي وطريقة الدفع والمواعيد:',
            ],
            [
                'title'   => 'البند الثالث: التزامات الراعي (الطرف الثاني)',
                'content' => '',
                'editable' => "١. تقديم الدعم المالي/العيني في المواعيد المحددة.\n٢. توفير المواد الترويجية (شعارات، لافتات) قبل الحدث.\n٣. _______________________________________________\n٤. _______________________________________________",
            ],
            [
                'title'   => 'البند الرابع: التزامات المنظم (الطرف الأول)',
                'content' => '',
                'editable' => "١. إبراز شعار الراعي في جميع المواد الدعائية.\n٢. توفير مساحة عرض للراعي في مكان الحدث.\n٣. ذكر الراعي في جميع الإعلانات والبيانات الصحفية.\n٤. _______________________________________________\n٥. _______________________________________________",
            ],
            [
                'title'   => 'البند الخامس: مدة العقد',
                'content' => 'يسري هذا العقد من تاريخ توقيعه وحتى انتهاء الحدث المذكور أعلاه، ما لم يتم الاتفاق على خلاف ذلك.',
                'editable' => 'تعديلات على مدة العقد (إن وجدت):',
            ],
            [
                'title'   => 'البند السادس: الحقوق الترويجية',
                'content' => '',
                'editable' => "حدد الحقوق الترويجية الممنوحة للراعي:\n١. _______________________________________________\n٢. _______________________________________________\n٣. _______________________________________________",
            ],
            [
                'title'   => 'البند السابع: شروط الإلغاء والفسخ',
                'content' => 'في حالة رغبة أي من الطرفين في إلغاء هذا العقد، يجب إخطار الطرف الآخر كتابياً قبل فترة لا تقل عن:',
                'editable' => 'حدد فترة الإخطار والشروط المالية للإلغاء:',
            ],
            [
                'title'   => 'البند الثامن: السرية',
                'content' => 'يتعهد الطرفان بالحفاظ على سرية جميع المعلومات المالية والتجارية المتبادلة بموجب هذا العقد.',
                'editable' => 'إضافات على بند السرية (إن وجدت):',
            ],
            [
                'title'   => 'البند التاسع: حل النزاعات',
                'content' => 'في حالة نشوء أي خلاف حول تفسير أو تنفيذ هذا العقد، يتم حله ودياً بين الطرفين أولاً، وفي حالة تعذر ذلك يتم اللجوء إلى الجهات القضائية المختصة.',
                'editable' => '',
            ],
            [
                'title'   => 'البند العاشر: بنود إضافية',
                'content' => '',
                'editable' => "أضف أي بنود إضافية متفق عليها بين الطرفين:\n_______________________________________________\n_______________________________________________\n_______________________________________________",
            ],
        ];

        foreach ($clauses as $i => $clause) {
            // Clause title
            $section->addText(
                $clause['title'],
                ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '2C3E50'],
                'RTL'
            );

            // Clause content (if any)
            if (!empty($clause['content'])) {
                $section->addText($clause['content'], 'ClauseFont', 'RTL');
            }

            // Editable placeholder
            if (!empty($clause['editable'])) {
                $lines = explode("\n", $clause['editable']);
                foreach ($lines as $line) {
                    $section->addText($line, 'PlaceholderFont', 'RTL');
                }
            }

            $section->addTextBreak(1);
        }

        // ─── SIGNATURES ─────────────────────────────────────────
        $section->addText(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
            ['name' => 'Arial', 'size' => 8, 'color' => 'CCCCCC'],
            'RTLCenter'
        );
        $section->addTextBreak(1);

        $section->addText('✍️ التوقيعات', 'SectionFont', 'RTLCenter');
        $section->addTextBreak(1);

        $sigTable = $section->addTable([
            'borderSize'  => 0,
            'cellMargin'  => 100,
            'alignment'   => Jc::CENTER,
        ]);

        $sigRow = $sigTable->addRow(600);

        // Manager signature
        $cell1 = $sigRow->addCell(5000);
        $cell1->addText('الطرف الأول (مدير الحدث)', 'LabelFont', 'RTLCenter');
        $cell1->addText('الاسم: ' . ($manager->name ?? '—'), 'ValueFont', 'RTLCenter');
        $cell1->addTextBreak(2);
        $cell1->addText('________________________________', ['name' => 'Arial', 'size' => 10, 'color' => 'AAAAAA'], 'RTLCenter');
        $cell1->addText('التوقيع', ['name' => 'Arial', 'size' => 9, 'color' => 'AAAAAA'], 'RTLCenter');

        // Sponsor signature
        $cell2 = $sigRow->addCell(5000);
        $cell2->addText('الطرف الثاني (الراعي)', 'LabelFont', 'RTLCenter');
        $cell2->addText('الاسم: ' . ($sponsor->name ?? '—'), 'ValueFont', 'RTLCenter');
        $cell2->addTextBreak(2);
        $cell2->addText('________________________________', ['name' => 'Arial', 'size' => 10, 'color' => 'AAAAAA'], 'RTLCenter');
        $cell2->addText('التوقيع', ['name' => 'Arial', 'size' => 9, 'color' => 'AAAAAA'], 'RTLCenter');

        // ── Footer ──────────────────────────────────────────────
        $section->addTextBreak(2);
        $section->addText(
            'تم إنشاء هذا العقد تلقائياً بواسطة منصة EventHub بتاريخ ' . $date,
            ['name' => 'Arial', 'size' => 9, 'color' => 'AAAAAA', 'italic' => true],
            'RTLCenter'
        );

        // ── Save ────────────────────────────────────────────────
        $dir = 'agreements';
        $storagePath = storage_path('app/public/' . $dir);
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $filename = $dir . '/agreement_' . $sponsorshipRequest->id . '_v1.docx';
        $fullPath = storage_path('app/public/' . $filename);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        return $filename;
    }

    /**
     * Generate a Word (.docx) agreement for exhibition participation.
     *
     * @param  object  $exhibitionApplication  (loaded with event.venue, company, manager)
     * @return string  The file path (relative to storage/app/public)
     */
    public static function generateExhibition($exhibitionApplication): string
    {
        $event   = $exhibitionApplication->event;
        $company = $exhibitionApplication->company;
        $manager = $exhibitionApplication->manager;
        $date    = now()->format('Y-m-d');

        $phpWord = new PhpWord();

        // ── Global Settings ──────────────────────────────────────
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);
        $phpWord->setDefaultParagraphStyle(['alignment' => Jc::END, 'bidi' => true]);

        // ── Styles ───────────────────────────────────────────────
        $phpWord->addParagraphStyle('RTL', [
            'alignment'  => Jc::END,
            'bidi'       => true,
            'spaceAfter' => 120,
        ]);

        $phpWord->addParagraphStyle('RTLCenter', [
            'alignment' => Jc::CENTER,
            'bidi'      => true,
            'spaceAfter' => 120,
        ]);

        $phpWord->addFontStyle('TitleFont', [
            'name'  => 'Arial', 'size'  => 22, 'bold'  => true, 'color' => '1a1a2e',
        ]);

        $phpWord->addFontStyle('SectionFont', [
            'name'  => 'Arial', 'size'  => 14, 'bold'  => true, 'color' => '2C3E50',
        ]);

        $phpWord->addFontStyle('LabelFont', [
            'name'  => 'Arial', 'size'  => 12, 'bold'  => true, 'color' => '555555',
        ]);

        $phpWord->addFontStyle('ValueFont', [
            'name'  => 'Arial', 'size'  => 12, 'color' => '333333',
        ]);

        $phpWord->addFontStyle('ClauseFont', [
            'name'  => 'Arial', 'size'  => 12, 'color' => '333333',
        ]);

        $phpWord->addFontStyle('PlaceholderFont', [
            'name'  => 'Arial', 'size'  => 12, 'color' => '999999', 'italic' => true,
        ]);

        // ── Section ──────────────────────────────────────────────
        $section = $phpWord->addSection([
            'marginTop' => 1200, 'marginBottom' => 1200,
            'marginLeft' => 1200, 'marginRight' => 1200,
        ]);

        // ─── TITLE ──────────────────────────────────────────────
        $section->addText('عقد مشاركة في المعرض', 'TitleFont', 'RTLCenter');
        $section->addText('Exhibition Participation Agreement', [
            'name' => 'Arial', 'size' => 11, 'color' => '999999', 'italic' => true
        ], 'RTLCenter');

        $section->addText(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━',
            ['name' => 'Arial', 'size' => 8, 'color' => 'CCCCCC'],
            'RTLCenter'
        );
        $section->addTextBreak(1);

        // ─── AGREEMENT DETAILS ──────────────────────────────────
        $section->addText('📋 تفاصيل الاتفاقية', 'SectionFont', 'RTL');

        $detailsTable = $section->addTable([
            'borderSize' => 0, 'cellMargin' => 80, 'alignment' => Jc::END,
        ]);

        $details = [
            ['تاريخ الاتفاقية', $date],
            ['رقم الطلب', '#EX-' . $exhibitionApplication->id],
            ['عنوان المعرض', $event->title ?? '—'],
            ['نوع الحدث', $event->event_type ?? '—'],
            ['تاريخ بداية المعرض', $event->start_time ? Carbon::parse($event->start_time)->format('Y/m/d - h:i A') : '—'],
            ['تاريخ نهاية المعرض', $event->end_time ? Carbon::parse($event->end_time)->format('Y/m/d - h:i A') : '—'],
            ['مكان المعرض', $event->venue->name ?? ($event->external_venue_name ?? '—')],
            ['موقع المعرض', $event->venue->location ?? ($event->external_venue_location ?? '—')],
            ['فئة المنتج/الخدمة', $exhibitionApplication->product_category ?? '—'],
        ];


        foreach ($details as $row) {
            $tableRow = $detailsTable->addRow();
            $cell1 = $tableRow->addCell(3200);
            $cell1->addText($row[0] . ':', 'LabelFont', 'RTL');
            $cell2 = $tableRow->addCell(6800);
            $cell2->addText($row[1], 'ValueFont', 'RTL');
        }

        $section->addTextBreak(1);

        // ─── PARTIES ────────────────────────────────────────────
        $section->addText('👥 أطراف العقد', 'SectionFont', 'RTL');

        $partiesTable = $section->addTable([
            'borderSize' => 6, 'borderColor' => 'E0E0E0',
            'cellMargin' => 100, 'alignment' => Jc::CENTER,
        ]);

        $headerRow = $partiesTable->addRow(400);
        $headerRow->addCell(5000, ['bgColor' => 'F5F5F5'])->addText(
            'الطرف الأول: مدير المعرض',
            ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '2C3E50'],
            'RTLCenter'
        );
        $headerRow->addCell(5000, ['bgColor' => 'F5F5F5'])->addText(
            'الطرف الثاني: الشركة العارضة',
            ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '2C3E50'],
            'RTLCenter'
        );

        $valRow = $partiesTable->addRow(400);
        $valRow->addCell(5000)->addText($manager->name ?? '—', ['name' => 'Arial', 'size' => 12, 'bold' => true], 'RTLCenter');
        $valRow->addCell(5000)->addText($company->name ?? '—', ['name' => 'Arial', 'size' => 12, 'bold' => true], 'RTLCenter');

        $managerEmail = $manager->profile?->contacts()->where('type', 'email')->first()?->value ?? $manager->email ?? '—';
        $companyEmail = $company->profile?->contacts()->where('type', 'email')->first()?->value ?? $company->email ?? '—';

        $contactRow = $partiesTable->addRow(300);
        $contactRow->addCell(5000)->addText($managerEmail, ['name' => 'Arial', 'size' => 10, 'color' => '777777'], 'RTLCenter');
        $contactRow->addCell(5000)->addText($companyEmail, ['name' => 'Arial', 'size' => 10, 'color' => '777777'], 'RTLCenter');

        $section->addTextBreak(1);

        // ─── TERMS & CONDITIONS ─────────────────────────────────
        $section->addText('📝 بنود وشروط العقد', 'SectionFont', 'RTL');
        $section->addText(
            'يرجى من الطرفين مراجعة وتعديل البنود التالية وفقاً للاتفاق المشترك:',
            ['name' => 'Arial', 'size' => 10, 'color' => '888888', 'italic' => true],
            'RTL'
        );
        $section->addTextBreak(1);

        $clauses = [
            ['title' => 'البند الأول: نطاق المشاركة', 'content' => 'يلتزم الطرف الثاني (الشركة العارضة) بالمشاركة في المعرض المذكور أعلاه من خلال عرض منتجاته/خدماته في الجناح المخصص.', 'editable' => 'حدد نوع المنتجات/الخدمات المعروضة وتفاصيل المشاركة:'],
            ['title' => 'البند الثاني: رسوم المشاركة', 'content' => 'يتعهد الطرف الثاني بدفع رسوم المشاركة المتفق عليها وفقاً للآتي:', 'editable' => 'حدد المبلغ الإجمالي وطريقة الدفع والمواعيد:'],
            ['title' => 'البند الثالث: تجهيزات المشاركة', 'content' => '', 'editable' => "١. التجهيزات المقدمة من المنظم:\n_______________________________________________\n٢. التجهيزات المطلوبة من الشركة:\n_______________________________________________\n٣. _______________________________________________"],
            ['title' => 'البند الرابع: التزامات الشركة العارضة', 'content' => '', 'editable' => "١. الالتزام بمواعيد التواجد.\n٢. توفير المواد الترويجية والعينات.\n٣. الالتزام بالأنظمة واللوائح المعمول بها.\n٤. _______________________________________________"],
            ['title' => 'البند الخامس: التزامات المنظم', 'content' => '', 'editable' => "١. توفير مساحة المشاركة المخصصة بالمواصفات المتفق عليها.\n٢. توفير الخدمات الأساسية (كهرباء، إنترنت، تنظيف).\n٣. إدراج الشركة في الدليل الرسمي للمعرض.\n٤. _______________________________________________"],
            ['title' => 'البند السادس: مدة العقد', 'content' => 'يسري هذا العقد من تاريخ توقيعه وحتى انتهاء المعرض المذكور أعلاه.', 'editable' => 'تعديلات على مدة العقد (إن وجدت):'],
            ['title' => 'البند السابع: شروط الإلغاء', 'content' => 'في حالة رغبة أي من الطرفين في إلغاء المشاركة، يجب إخطار الطرف الآخر كتابياً قبل:', 'editable' => 'حدد فترة الإخطار والشروط المالية للإلغاء:'],
            ['title' => 'البند الثامن: بنود إضافية', 'content' => '', 'editable' => "أضف أي بنود إضافية:\n_______________________________________________\n_______________________________________________"],
        ];

        foreach ($clauses as $clause) {
            $section->addText($clause['title'], ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '2C3E50'], 'RTL');
            if (!empty($clause['content'])) {
                $section->addText($clause['content'], 'ClauseFont', 'RTL');
            }
            if (!empty($clause['editable'])) {
                foreach (explode("\n", $clause['editable']) as $line) {
                    $section->addText($line, 'PlaceholderFont', 'RTL');
                }
            }
            $section->addTextBreak(1);
        }

        // ─── SIGNATURES ─────────────────────────────────────────
        $section->addText('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', ['name' => 'Arial', 'size' => 8, 'color' => 'CCCCCC'], 'RTLCenter');
        $section->addTextBreak(1);
        $section->addText('✍️ التوقيعات', 'SectionFont', 'RTLCenter');
        $section->addTextBreak(1);

        $sigTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 100, 'alignment' => Jc::CENTER]);
        $sigRow = $sigTable->addRow(600);

        $cell1 = $sigRow->addCell(5000);
        $cell1->addText('الطرف الأول (مدير المعرض)', 'LabelFont', 'RTLCenter');
        $cell1->addText('الاسم: ' . ($manager->name ?? '—'), 'ValueFont', 'RTLCenter');
        $cell1->addTextBreak(2);
        $cell1->addText('________________________________', ['name' => 'Arial', 'size' => 10, 'color' => 'AAAAAA'], 'RTLCenter');
        $cell1->addText('التوقيع', ['name' => 'Arial', 'size' => 9, 'color' => 'AAAAAA'], 'RTLCenter');

        $cell2 = $sigRow->addCell(5000);
        $cell2->addText('الطرف الثاني (الشركة العارضة)', 'LabelFont', 'RTLCenter');
        $cell2->addText('الاسم: ' . ($company->name ?? '—'), 'ValueFont', 'RTLCenter');
        $cell2->addTextBreak(2);
        $cell2->addText('________________________________', ['name' => 'Arial', 'size' => 10, 'color' => 'AAAAAA'], 'RTLCenter');
        $cell2->addText('التوقيع', ['name' => 'Arial', 'size' => 9, 'color' => 'AAAAAA'], 'RTLCenter');

        // ── Footer ──────────────────────────────────────────────
        $section->addTextBreak(2);
        $section->addText(
            'تم إنشاء هذا العقد تلقائياً بواسطة منصة EventHub بتاريخ ' . $date,
            ['name' => 'Arial', 'size' => 9, 'color' => 'AAAAAA', 'italic' => true],
            'RTLCenter'
        );

        // ── Save ────────────────────────────────────────────────
        $dir = 'agreements';
        $storagePath = storage_path('app/public/' . $dir);
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $filename = $dir . '/exhibition_agreement_' . $exhibitionApplication->id . '_v1.docx';
        $fullPath = storage_path('app/public/' . $filename);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        return $filename;
    }

    /**
     * Generate a final PDF from the accepted Word agreement.
     * Uses DomPDF with a special view that summarizes the final agreement.
     */
    public static function generateFinalPdf($sponsorshipRequest, $negotiation): string
    {
        return self::generateGenericFinalPdf($sponsorshipRequest, $negotiation, 'sponsor');
    }

    public static function generateExhibitionFinalPdf($exhibitionApplication, $negotiation): string
    {
        return self::generateGenericFinalPdf($exhibitionApplication, $negotiation, 'exhibition');
    }

    private static function generateGenericFinalPdf($target, $negotiation, $type): string
    {
        $target->load(['event.venue', 'event.externalVenue', 'manager', ($type === 'exhibition' ? 'company' : 'sponsor')]);

        $lastVersion = $negotiation->versions()
            ->where('action', 'uploaded')
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->reorder()
            ->orderByDesc('version_number')
            ->first();

        $prefix = ($type === 'exhibition' ? 'exhib_' : '');
        $filename = 'agreements/agreement_' . $prefix . $target->id . '_final.pdf';
        $fullPdfPath = storage_path('app/public/' . $filename);
        
        $pdfGenerated = false;

        if ($lastVersion && \Illuminate\Support\Facades\Storage::disk('public')->exists($lastVersion->file_path)) {
            $uploadedFilePath = storage_path('app/public/' . $lastVersion->file_path);
            
            if (strtolower(pathinfo($uploadedFilePath, PATHINFO_EXTENSION)) === 'pdf') {
                copy($uploadedFilePath, $fullPdfPath);
                $pdfGenerated = true;
            } else {
                $vbsScript = '
Set objWord = CreateObject("Word.Application")
objWord.Visible = False
objWord.DisplayAlerts = 0
On Error Resume Next
Set objDoc = objWord.Documents.Open("' . str_replace('/', '\\', $uploadedFilePath) . '")
If Err.Number <> 0 Then
    WScript.Quit 1
End If
objDoc.SaveAs "' . str_replace('/', '\\', $fullPdfPath) . '", 17
If Err.Number <> 0 Then
    objDoc.Close False
    objWord.Quit
    WScript.Quit 1
End If
objDoc.Close False
objWord.Quit
WScript.Quit 0
';
                $vbsPath = storage_path('app/public/temp_convert_' . uniqid() . '.vbs');
                file_put_contents($vbsPath, $vbsScript);
                exec('cscript //nologo "' . $vbsPath . '"', $output, $returnVar);
                @unlink($vbsPath);
                
                if (file_exists($fullPdfPath) && filesize($fullPdfPath) > 0) {
                    $pdfGenerated = true;
                }
            }
        }
        
        if (!$pdfGenerated) {
            if ($lastVersion && \Illuminate\Support\Facades\Storage::disk('public')->exists($lastVersion->file_path)) {
                $uploadedFilePath = storage_path('app/public/' . $lastVersion->file_path);
                copy($uploadedFilePath, $fullPdfPath);
            } else {
                $pdfData = [
                    'event'       => $target->event,
                    'partner'     => ($type === 'exhibition' ? $target->company : $target->sponsor),
                    'manager'     => $target->manager,
                    'date'        => now()->format('Y-m-d'),
                    'negotiation' => $negotiation,
                    'versions'    => $negotiation->versions()->with('uploader')->get(),
                    'type'        => $type,
                ];
                $html = view('pdf.agreement-final', $pdfData)->render();
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $pdf->output());
            }
        }

        return $filename;
    }
}
