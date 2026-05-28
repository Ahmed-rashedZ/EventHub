/**
 * EventHub – Internationalization (i18n) System v2
 * Supports English (default) ↔ Arabic with full RTL + live DOM translation.
 * Uses localStorage('lang') to persist the user's language preference.
 *
 * Strategy:
 *   1. applyI18n()     → sets <html dir/lang>, injects RTL CSS & Cairo font instantly.
 *   2. translateDOM()  → walks every text node and replaces known phrases.
 *   3. MutationObserver → re-translates any content injected later by JS.
 */

/* ─────────────────────────────────────────────────────────────────
   Translation Dictionaries
   ───────────────────────────────────────────────────────────────── */
const I18N_EN = {
  'Publish Days': 'Publish Days',
  'published_schedule_desc': 'Select the days you want to display to the public. Unselected days will be considered "Setup Days" and won\'t appear in the public event schedule.',
  'published_schedule_success': 'Draft saved successfully!',
  'Public': 'Public',
  'Setup': 'Setup',
  'Event published successfully! 🚀': 'Event published successfully! 🚀',
  'Event unpublished. It is no longer visible to the public.': 'Event unpublished. It is no longer visible to the public.',
  'Are you sure you want to unpublish this event? It will no longer be visible to the public.': 'Are you sure you want to unpublish this event? It will no longer be visible to the public.',
  'Please select at least one day to publish.': 'Please select at least one day to publish.',
  'Exhibition': 'Exhibition',
  'Company': 'Company',
  'Exhibitor': 'Exhibitor',
  'Available for Exhibitions': 'Available for Exhibitions',
  'Browse Companies': 'Browse Companies',
  'Companies & Exhibitors': 'Companies & Exhibitors',
  'Apply as a company to participate in exhibitions.': 'Apply as a company to participate in exhibitions.',
  /* Manager strings that need EN identity entries */
  'Select your event…': 'Select your event…',
  'Describe sponsorship opportunity…': 'Describe sponsorship opportunity…',
  'sponsor': 'sponsor',
  'Edit Agenda': 'Edit Agenda',
  'Unpublish': 'Unpublish',
  'Save Draft': 'Save Draft',
  'Publish': 'Publish',
  'Published — Visible to public': 'Published — Visible to public',
  'Draft — Not visible to public yet': 'Draft — Not visible to public yet',
  'morning': 'morning',
  'evening': 'evening',
  'afternoon': 'afternoon',
  'full day': 'full day',
  'Change Booth': 'Change Booth',
  'Confirmed': 'Confirmed',
  'Booth': 'Booth',
  'Booth Locked': 'Booth Locked',
  'Exhibition layout is locked (Less than 14 days remaining). You can no longer add, edit, or delete zones and booths.': 'Exhibition layout is locked (Less than 14 days remaining). You can no longer add, edit, or delete zones and booths.',
  'scanned_today': 'Scanned Today',
  'not_scanned_today': 'Not Scanned Today',
  'days_attended': 'Days Attended',
  'Scanned Today': 'Scanned Today',
  'Not Scanned Today': 'Not Scanned Today',
  'Days Attended': 'Days Attended',
};

const I18N_AR = {
  /* ── Page Titles ──────────────────────────────────────── */
  'My Events – EventHub Manager':          'أحداثي – EventHub',
  'My Analytics – EventHub Manager':       'تحليلاتي – EventHub',
  'Edit Profile - EventHub':               'تعديل الملف الشخصي – EventHub',
  'My Profile – EventHub':                 'ملفي الشخصي – EventHub',
  'Events – EventHub Admin':               'الأحداث – الأدمن',
  'Browse Events – EventHub Sponsor':      'تصفح الأحداث – EventHub',
  'Exhibitions – EventHub Manager':        'المعارض – EventHub المدير',
  'Sponsorship – EventHub Manager':        'الرعاية – EventHub المدير',

  /* ── Sidebar – Navigation Labels ─────────────────────── */
  'Overview':          'نظرة عامة',
  'Events':            'الأحداث',
  'My Events':         'أحداثي',
  'Assistants':        'المساعدون',
  'Attendance':        'الحضور',
  'Sponsorship':       'الرعاية',
  'Settings':          'الإعدادات',
  'My Profile':        'ملفي الشخصي',
  'Management':        'الإدارة',
  'Users':             'المستخدمون',
  'Venues':            'القاعات',
  'Opportunities':     'الفرص',
  'Verifications':     'التحقق',
  'Browse Events':     'تصفح الأحداث',
  'Sponsorships':      'الرعايات',
  'Browse Requests':   'الطلبات',
  'History':           'السجل',
  'Dashboard':         'لوحة التحكم',
  'Logout':            'تسجيل الخروج',

  /* ── Page Main Titles & Subtitles ─────────────────────── */
  'Profile Settings':                       'إعدادات الملف الشخصي',
  'Manage your personal information and contact details': 'إدارة معلوماتك الشخصية وبيانات الاتصال',
  'My Analytics':                           'تحليلاتي',
  'Performance overview for your events':   'نظرة عامة على أداء أحداثك',
  'Create and manage your events':          'إنشاء وإدارة أحداثك',
  'Approve, reject and monitor all events': 'اعتماد ورفض ومتابعة جميع الأحداث',
  'Discover opportunities to sponsor upcoming verified events': 'اكتشف فرص رعاية الأحداث القادمة المعتمدة',
  'Update your personal information':       'تحديث معلوماتك الشخصية',

  /* ── Topbar Buttons ───────────────────────────────────── */
  '+ Create Event':  '+ إنشاء حدث',

  /* ── Create Event Modal ─────────────────────────────────── */
  'Create New Event': 'إنشاء حدث جديد',
  'Event Details': 'تفاصيل الحدث',
  'Booking & Settings': 'الحجوزات والإعدادات',
  'Basic Information': 'المعلومات الأساسية',
  'Event Title': 'عنوان الحدث',
  'e.g. Tech Summit 2026': 'مثال: قمة التقنية 2026',
  'Description': 'الوصف',
  'Briefly describe your event…': 'صف الحدث باختصار…',
  'Event Banner Image': 'صورة الغلاف للحدث',
  'Continue →': 'متابعة ←',
  'Venue & Schedule': 'القاعة والجدول الزمني',
  'Event Venue': 'مكان الحدث',
  'Inside Exhibition': 'داخل المعرض',
  'External Venue': 'قاعة خارجية',
  'Select a hall inside the exhibition...': 'اختر قاعة داخل المعرض...',
  'External Hall Name (e.g., Corinthia Hotel)': 'اسم القاعة الخارجية (مثال: فندق كورنثيا)',
  '📅 Select Event Days': '📅 اختر أيام الحدث',
  'Click on days to select them. Max span: 14 days between first and last day.': 'اضغط على الأيام لاختيارها. أقصى مدة: 14 يوماً بين اليوم الأول والأخير.',
  'Google Maps Link': 'رابط خرائط جوجل',
  'https://maps.google.com/...': 'https://maps.google.com/...',
  'Proof of Booking (PDF/Image)': 'إثبات الحجز (ملف PDF أو صورة)',
  'Required. Upload official confirmation of the external booking.': 'مطلوب. يرجى رفع تأكيد رسمي للحجز الخارجي.',
  '⏰ Set Times for Each Day': '⏰ تحديد الأوقات لكل يوم',
  'Additional Settings': 'إعدادات إضافية',
  'Event Objective': 'الهدف من الحدث',
  'What is the main goal of this event?': 'ما هو الهدف الرئيسي من هذا الحدث؟',
  'Target Audience': 'الجمهور المستهدف',
  'e.g. Students, Professionals, General Public': 'مثال: الطلاب، المحترفون، العامة',
  'Competent Authority Approval': 'موافقة الجهة المختصة',
  'Select Role': 'اختر الرول',
  'Event Manager': 'مدير الفعاليات',
  'Sponsor': 'راعي (Sponsor)',
  'Company': 'شركة (عارض)',
  'Exhibitor': 'عارض',
  'Companies & Exhibitors': 'الشركات والجهات العارضة',
  'Participating Companies': 'الشركات المشاركة',
  'Apply as a company to participate in exhibitions.': 'قدم كشركة للمشاركة في المعارض وحجز الأجنحة.',
  'Partner Registration': 'تسجيل الشركاء',
  'Apply as an Event Manager, Sponsor, or Company.': 'قدم كمدير فعاليات، راعي، أو شركة عارضة.',
  'Role / Account Type': 'نوع الحساب / الرول',
  'Select Role': 'اختر الرول',
  'Select Sector': 'اختر القطاع',
  'Technology': 'تقنية',
  'Healthcare': 'صحية',
  'Telecommunications': 'اتصالات',
  'Sports': 'رياضة',
  'Marketing & Media': 'تسويق وإعلام',
  'Education': 'تعليم',
  'Construction & Real Estate': 'إنشاءات وعقارات',
  'Food & Beverage': 'أغذية ومشروبات',
  'Logistics': 'خدمات لوجستية',
  'Finance & Banking': 'تمويل ومصارف',
  'Other': 'أخرى',
  'Company Name / Entity Name': 'اسم الشركة / الجهة',
  'Manager Full Name': 'الاسم الكامل للمدير',
  'Company / Individual Name': 'اسم الشركة أو الفرد',
  'Verification Documents (KYB)': 'وثائق التحقق (KYB)',
  'Click to select file...': 'اضغط لاختيار ملف...',
  'Commercial Register': 'السجل التجاري',
  'Tax Number Certificate': 'شهادة الرقم الضريبي',
  'Articles of Association': 'عقد التأسيس',
  'Practice License': 'رخصة مزاولة المهنة',
  "I declare that all the information and documents provided are authentic, and I agree to EventHub's Partner Terms of Service.": "أقر بأن جميع المعلومات والوثائق المقدمة حقيقية، وأوافق على شروط خدمة الشركاء في EventHub.",
  'Submit Application': 'إرسال الطلب',
  'Already confirmed?': 'لديك حساب بالفعل؟',
  'Sign in': 'تسجيل الدخول',
  'Exhibition': 'معرض',
  'Exhibition features (booths, company applications, and contracts) will be enabled for this event.': 'ستفعل مميزات المعرض (إدارة الأجنحة، طلبات الشركات، والاتفاقيات) لهذا الحدث.',
  'Required. Upload the official approval from the relevant competent authority for this event.': 'مطلوب. يرجى رفع الموافقة الرسمية من الجهة المختصة ذات العلاقة بهذا الحدث.',
  'Event Agenda': 'أجندة الحدث',
  '(Required)': '(مطلوب)',
  'Define the schedule/program for your event. You must add at least one agenda item.': 'حدد البرنامج/الجدول الزمني للحدث. يجب إضافة بند واحد على الأقل في الأجندة.',
  '+ Add Agenda Item': '+ إضافة بند للأجندة',
  '← Back': '→ رجوع',
  'Continue': 'متابعة',
  'Back': 'رجوع',
  'Submit for Approval': 'إرسال للاعتماد',
  'Day': 'اليوم',
  'From': 'من',
  'To': 'إلى',
  'Activity': 'النشاط',
  'e.g. Opening Ceremony': 'مثال: حفل الافتتاح',
  'Description (Optional)': 'الوصف (اختياري)',
  'Brief details about this activity...': 'تفاصيل مختصرة عن هذا النشاط...',
  'Search by event name...': 'ابحث باسم الحدث...',
  'Select Period': 'اختر الفترة',
  'Select a period...': 'اختر فترة...',
  'Morning Period ☀': 'الفترة الصباحية ☀',
  'Evening Period 🌙': 'الفترة المسائية 🌙',
  'Full Day 🗓️': 'يوم كامل 🗓️',
  'Add Agenda Item': 'إضافة بند للأجندة',
  'Add Agenda Item to Selected Day': 'إضافة بند لليوم المحدد',
  'Sun': 'الأحد',
  'Mon': 'الإثنين',
  'Tue': 'الثلاثاء',
  'Wed': 'الأربعاء',
  'Thu': 'الخميس',
  'Fri': 'الجمعة',
  'Sat': 'السبت',
  'Jan': 'يناير',
  'Feb': 'فبراير',
  'Mar': 'مارس',
  'Apr': 'أبريل',
  'Approve, reject and monitor all events': 'الموافقة على الأحداث أو رفضها ومراقبتها',
  'Search by event or manager name...': 'ابحث عن طريق اسم الحدث أو المنظم...',
  'All Status': 'كل الحالات',
  'Soonest First': 'الأقرب أولاً',
  'Farthest First': 'الأبعد أولاً',
  'Alphabetical': 'أبجدياً',
  'Live Now': 'مباشر الآن',
  'Ended': 'انتهى',
  'Awaiting Changes': 'بانتظار التغييرات',
  'Updated': 'تم التحديث',
  'Explain why this event is being rejected': 'اشرح سبب رفض هذا الحدث',
  'Venue capacity mismatch, missing details...': 'مثال: عدم تطابق سعة القاعة، تفاصيل مفقودة...',
  'Confirm Rejection': 'تأكيد الرفض',
  'Send Review': 'إرسال مراجعة',
  'Select fields that need changes': 'اختر الحقول التي تحتاج إلى تغييرات',
  'Ministry Doc': 'وثيقة الوزارة',
  'Booking Proof': 'إثبات الحجز',
  'Event Objective': 'الهدف من الحدث',
  'Target Audience': 'الجمهور المستهدف',
  'Created by': 'أنشئ بواسطة',
  'Full Event Schedule': 'جدول الفعالية الكامل',
  'Full Event Agenda': 'أجندة الفعالية الكاملة',
  'Setup Day': 'يوم تجهيز',
  'Reason for Rejection': 'سبب الرفض',
  'Review Message': 'رسالة المراجعة',
  'Reason for Rejecting Cancellation': 'سبب رفض الإلغاء',
  'Explain why you are rejecting this cancellation request': 'اشرح سبب رفض طلب الإلغاء هذا',
  'The event is already sold out and cannot be cancelled at this stage...': 'الحدث بيعت تذاكره بالكامل ولا يمكن إلغاؤه في هذه المرحلة...',
  'Manage event locations': 'إدارة مواقع الفعاليات',
  'Venue Name': 'اسم القاعة',
  'Google Maps Link': 'رابط خرائط جوجل',
  'Open Google Maps → Select location → Copy share link and paste here': 'افتح خرائط جوجل ← اختر الموقع ← انسخ رابط المشاركة وألصقه هنا',
  'Morning Start': 'بداية الفترة الصباحية',
  'Morning End': 'نهاية الفترة الصباحية',
  'Evening Start': 'بداية الفترة المسائية',
  'Evening End': 'نهاية الفترة المسائية',
  'Maintenance (Full Lockdown)': 'صيانة (إغلاق كامل)',
  'Save Venue': 'حفظ القاعة',
  'Schedule Maintenance': 'جدولة الصيانة',
  'Add Maintenance Period': 'إضافة فترة صيانة',
  'Start Date': 'تاريخ البدء',
  'End Date': 'تاريخ الانتهاء',
  'Select start date...': 'اختر تاريخ البدء...',
  'Select end date...': 'اختر تاريخ الانتهاء...',
  'Booked (Event)': 'محجوز (حدث)',
  'Maintenance': 'صيانة',
  'Today': 'اليوم',
  'Reason (Optional)': 'السبب (اختياري)',
  'Scheduled Maintenance Periods': 'فترات الصيانة المجدولة',
  'No venues yet. Add one!': 'لا توجد قاعات بعد. أضف واحدة!',
  'Full Lockdown': 'إغلاق كامل',
  'Under Maintenance': 'تحت الصيانة',
  'Available': 'متاح',
  'scheduled': 'مجدولة',
  'None': 'لا يوجد',
  'Venue updated!': 'تم تحديث القاعة!',
  'Venue added!': 'تم إضافة القاعة!',
  'No maintenance periods scheduled': 'لا يوجد فترات صيانة مجدولة',
  'ACTIVE NOW': 'نشط الآن',
  'COMPLETED': 'مكتمل',
  'UPCOMING': 'قادم',
  'Cannot Schedule Maintenance': 'لا يمكن جدولة الصيانة',
  'The following events are already booked in this date range:': 'الأحداث التالية محجوزة بالفعل في هذا النطاق الزمني:',
  'Please choose dates that don\'t conflict with existing bookings.': 'يرجى اختيار تواريخ لا تتعارض مع الحجوزات الموجودة.',
  'Approve Cancellation': 'موافقة على الإلغاء',
  'Reject Cancellation': 'رفض الإلغاء',
  'Rejection Reason': 'سبب الرفض',
  'Current Sponsors': 'الرعاة الحاليون',
  'Agreement': 'الاتفاقية',
  'Participating Companies': 'الشركات المشاركة',
  'Event Cancelled': 'تم إلغاء الحدث',
  'Cancellation Pending': 'الإلغاء قيد الانتظار',
  'Cancellation Reason': 'سبب الإلغاء',
  'About this Event': 'حول هذا الحدث',
  'Event has ended': 'الحدث انتهى',
  'Event is live': 'الحدث مباشر الآن',
  'Needs Approval': 'يحتاج إلى موافقة',
  'Event must be approved first': 'يجب الموافقة على الحدث أولاً',
  'Cannot change sponsorship for live/ended events': 'لا يمكن تغيير الرعاية للأحداث المباشرة/المنتهية',
  'Exhibition has ended': 'المعرض انتهى',
  'May': 'مايو',
  'Jun': 'يونيو',
  'Jul': 'يوليو',
  'Aug': 'أغسطس',
  'Sep': 'سبتمبر',
  'Oct': 'أكتوبر',
  'Nov': 'نوفمبر',
  'Dec': 'ديسمبر',

  /* ── Filter / Sort Controls ───────────────────────────── */
  'All Status':  'الكل',
  'Pending':     'قيد الانتظار',
  'Approved':    'معتمد',
  'Rejected':    'مرفوض',
  'Cancellation Requested': 'طلب إلغاء',
  'Cancelled':   'ملغى',

  /* ── Sort Dropdown Options ───────────────────────────────── */
  'Soonest First':   'الأقرب أولا',
  'Farthest First':  'الأبعد أولا',
  'Alphabetical':    'أبجدي',
  'Live Now':        'مباشر الآن',
  'Ended':           'منتهي',

  /* ── Table Headers ────────────────────────────────────── */
  'Title':          'العنوان',
  'Venue':          'القاعة',
  'Manager':        'المدير',
  'Start':          'البداية',
  'End':            'النهاية',
  'Capacity':       'السعة',
  'Status':         'الحالة',
  'Actions':        'الإجراءات',
  'Event Name':     'اسم الحدث',
  'Date':           'التاريخ',
  'Action':         'الإجراء',
  'Name':           'الاسم',
  'Email':          'البريد الإلكتروني',
  'Role':           'الدور',
  'Joined':         'الانضمام',
  'Location':       'المكان',

  /* ── Action Buttons ───────────────────────────────────── */
  'ℹ️ Details':        'ℹ️ التفاصيل',
  '📊 Stats':          '📊 الإحصائيات',
  '✓ Approve':         '✓ اعتماد',
  '✕ Reject':          '✕ رفض',
  'Save Changes':      'حفظ التغييرات',
  'Saving...':         'جاري الحفظ...',
  'Cancel':            'إلغاء',
  'Send Request':      'إرسال الطلب',
  'Sending...':        'جاري الإرسال...',
  'Change Photo':      'تغيير الصورة',
  'Request':           'طلب رعاية',
  'Already Requested': 'تم الطلب مسبقاً',
  '+ Add Link':        '+ إضافة رابط',
  '+ Add Phone':       '+ إضافة رقم',
  '+ Add Venue':       '+ إضافة قاعة',
  'Add Venue':         'إضافة قاعة',
  'Save Venue':        'حفظ القاعة',
  'Edit Venue':        'تعديل القاعة',
  'Confirm Rejection': 'تأكيد الرفض',
  'View Public Profile': 'عرض الملف العام',
  'Save Information':  'حفظ المعلومات',
  'Update Security Settings': 'تحديث إعدادات الأمان',
  '⏸ Suspend':         '⏸ تعليق',
  '▶ Activate':        '▶ تفعيل',
  '🗑 Delete':          '🗑 حذف',
  '📄 Docs':            '📄 المستندات',
  'Request Cancellation': 'طلب إلغاء',
  'Resume Ticket Sales': 'استئناف مبيعات التذاكر',
  'Suspend Ticket Sales': 'تعليق مبيعات التذاكر',
  'Sign Out':          'تسجيل الخروج',
  'Company Dashboard': 'لوحة تحكم الشركة',
  'Manage your exhibition participation and booth bookings': 'إدارة مشاركاتك في المعارض وحجوزات الأجنحة',
  'Browse Exhibitions': 'تصفح المعارض',
  'You are currently hidden from exhibition managers. Turn on "Open to Invitations" to be visible to organizers.': 'أنت حالياً مخفي عن مدراء المعارض. قم بتفعيل "متاح للدعوات" لتظهر للمنظمين.',
  'Open to Invitations': 'متاح للدعوات',
  'You are visible to event managers who may invite you to exhibitions.': 'أنت مرئي لمدراء الأحداث الذين قد يدعونك للمعارض.',
  'Accepted Apps': 'الطلبات المقبولة',
  'Pending Apps': 'الطلبات المعلقة',
  'Acceptance Rate': 'نسبة القبول',
  'Recent Applications': 'آخر الطلبات',
  'View Status': 'عرض الحالة',
  'Exhibition': 'المعرض',
  'Date Selected': 'تاريخ الاختيار',
  'Booth Info': 'معلومات الجناح',
  'Accepted! Contract negotiation started.': 'تم القبول! بدأت مفاوضات العقد.',
  'Invitation rejected.': 'تم رفض الدعوة.',
  'Error processing request': 'خطأ في معالجة الطلب',
  'Visible': 'مرئي',
  'Hidden': 'مخفي',
  'My Applications': 'طلباتي',
  'My Exhibition Applications': 'طلبات المعارض الخاصة بي',
  'Track your requests and participate in confirmed exhibitions': 'تتبع طلباتك وشارك في المعارض المؤكدة',
  'Discover exhibitions': 'اكتشف المعارض',
  'Exhibition Title': 'عنوان المعرض',
  'Booth Detail': 'تفاصيل الجناح',
  'You haven\'t applied to any exhibitions yet.': 'لم تقم بالتقديم لأي معارض بعد.',
  'Applied: ': 'بتاريخ: ',
  'Preliminary acceptance successful! Contract negotiation started. 📝': 'تم القبول المبدئي بنجاح! بدأت مفاوضات العقد. 📝',
  'Review Offer': 'مراجعة العرض',
  'Not assigned yet': 'لم يتم التعيين بعد',
  'Discover opportunities to exhibit at upcoming verified events': 'اكتشف فرص العرض في الأحداث القادمة المعتمدة',
  'Search by name or manager...': 'البحث بالاسم أو المدير...',
  'Soonest First': 'الأقرب أولاً',
  'Farthest First': 'الأبعد أولاً',
  'Alphabetical': 'أبجدياً',
  'Could not load your visibility status from the server. Refresh the page or try again later.': 'تعذر تحميل حالة ظهورك من الخادم. أعد تحميل الصفحة أو حاول مرة أخرى لاحقاً.',
  'You are currently not visible to event managers.': 'أنت غير مرئي لمدراء الأحداث حالياً.',
  'You will remain hidden until you turn this option ON again.': 'ستبقى مخفياً حتى تعيد تفعيل هذا الخيار.',
  'Apply for Exhibition': 'التقديم للمشاركة',
  'Message / Intro': 'رسالة / مقدمة',
  'Introduce your company and specify what kind of booth you are looking for...': 'قدم شركتك وحدد نوع الجناح الذي تبحث عنه...',
  'Apply for Booth': 'تقديم طلب حجز كشك',
  'Already Applied': 'تم التقديم مسبقاً',
  'Turn on Open to Invitations on your dashboard': 'قم بتفعيل "متاح للدعوات" من لوحة التحكم',
  'Application sent directly to Event Manager!': 'تم إرسال الطلب مباشرة لمدير الحدث!',
  'Error sending application': 'خطأ في إرسال الطلب',
  'Closed to Invitations': 'غير متاح للدعوات',
  'Schedule (1 Day)': 'البرنامج (يوم واحد)',
  'Schedule (': 'البرنامج (',
  ' days)': ' أيام)',
  ' day)': ' يوم)',
  'Event Schedule': 'برنامج الحدث',
  'Unlimited': 'غير محدود',

  /* ── AI Attendance Prediction ───────────────────────────── */
  'AI Attendance Prediction': 'توقع الحضور بالذكاء الاصطناعي',
  'Analyzing event data...': 'جاري تحليل بيانات الفعالية...',
  'Expected Attendance:': 'الحضور المتوقع:',
  'Fixed Number': 'عدد محدد',

  /* ── Profile Page Form Labels ─────────────────────────── */
  'Profile Information':    'معلومات الملف الشخصي',
  'Full Name':              'الاسم الكامل',
  'Contact Email':          'البريد الإلكتروني للتواصل',
  'Company Name':           'اسم الشركة',
  'Phone Numbers':          'أرقام الهاتف',
  'Social Profiles & Links':'روابط التواصل الاجتماعي',
  'Bio':                    'نبذة شخصية',
  'Tell us about yourself...': 'أخبرنا عن نفسك...',
  'Security Settings':      'إعدادات الأمان',
  'Login Email':            'البريد الإلكتروني للدخول',
  'This email is used for logging into your account.': 'يُستخدم هذا البريد لتسجيل الدخول إلى حسابك.',
  'New Password (leave blank to keep current)': 'كلمة مرور جديدة (اتركها فارغة للإبقاء على الحالية)',
  'Confirm New Password':   'تأكيد كلمة المرور الجديدة',
  'For public/communication purposes': 'للتواصل العام',
  'Open to Sponsorship':    'متاح للرعاية',
  'Language & Interface / اللغة والواجهة': 'اللغة والواجهة',
  'Choose the interface language for all pages in the system.': 'اختر لغة الواجهة لجميع صفحات النظام.',
  'Changes take effect immediately — التغيير يُطبَّق فوراً': 'التغيير يُطبَّق فوراً',

  /* ── Event Detail Modal ───────────────────────────────── */
  'About this Event':     'عن هذا الحدث',
  'Location':             'الموقع',
  'Tickets Booked':       'التذاكر المحجوزة',
  'Created by':           'أنشئ بواسطة',
  'Event Manager':        'مدير الحدث',
  'Current Sponsors':     'الرعاة الحاليون',
  '👥 Attendee Reviews':  '👥 تقييمات الحضور',
  'No description provided.': 'لا يوجد وصف.',
  '⚠ Rejection Reason':  '⚠ سبب الرفض',
  'Be the first to sponsor this event!': 'كن أول راعٍ لهذا الحدث!',

  /* ── Rejection Modal ──────────────────────────────────── */
  'Reason for Rejection':  'سبب الرفض',
  'Explain why this event is being rejected': 'اشرح سبب رفض هذا الحدث',
  'Sponsor Event':         'رعاية الحدث',
  'Selected Event':        'الحدث المحدد',
  'Message / Intro':       'رسالة / مقدمة',
  'Introduce yourself and state what sponsorship options you are interested in...': 'قدم نفسك واذكر خيارات الرعاية التي تهتم بها...',
  'Apply': 'تقديم',
  'View File': 'عرض الملف',
  'View Document': 'عرض المستند',
  'day': 'يوم',
  'days': 'أيام',
  'View Document ↗': 'عرض المستند ↗',
  'Edit': 'تعديل',
  'Maintenance': 'صيانة',
  'Edit Venue': 'تعديل قاعة/مكان',
  'Add Venue': 'إضافة قاعة/مكان',
  'Maintenance for': 'صيانة لـ',
  'Open in Maps': 'فتح في الخرائط',
  'Open in Maps ↗': 'فتح في الخرائط ↗',
  'Not uploaded': 'لم يتم الرفع',
  'Booking Proof': 'إثبات الحجز',
  'Competent Authority Approval': 'موافقة الجهة المختصة',
  'Event Objective': 'هدف الحدث',
  'Target Audience': 'الجمهور المستهدف',
  'Details': 'تفاصيل',
  'View Details': 'عرض التفاصيل',
  'View Statistics': 'عرض الإحصائيات',
  'Stats': 'إحصائيات',

  /* ── JS Dynamic Strings & Substrings ────────────────────── */
  ' approved · ': ' معتمد · ',
  ' pending': ' قيد الانتظار',
  ' rejected': ' مرفوض',
  ' of all events': ' من إجمالي الأحداث',
  ' checked in': ' حاضر',
  ' checked in out of ': ' حاضر من أصل ',
  ' total tickets': ' إجمالي التذاكر',
  ' of ': ' من ',
  ' attended': ' حضروا',
  ' capacity': ' كسعة قصوى',
  'Based on ': 'بناءً على ',
  ' ratings': ' تقييمات',
  ' rating': ' تقييم',

  /* ── Analytics Stats ──────────────────────────────────── */
  'Tickets Sold':          'التذاكر المباعة',
  'Attendance Rate':       'معدل الحضور',
  'Capacity Usage':        'استخدام السعة',
  'Avg Rating':            'متوسط التقييم',
  'Based on all events':   'بناءً على جميع الأحداث',
  'Event Performance':     'أداء الأحداث',
  'Events by Status':      'الأحداث حسب الحالة',
  'Events by Type':        'الأحداث حسب النوع',
  'Registered':            'مسجلون',
  'Attended':              'حضروا',
  'Fill Rate':             'نسبة الامتلاء',
  'Capacity Utilization':  'استخدام السعة',

  /* ── General ─────────────────────────────────────────── */
  'Platform':              'المنصة',
  'PLATFORM':              'المنصة',
  'Choose Platform...':    'اختر المنصة...',

  /* ── Statuses ────────────────────────────────────────── */
  'Accepted':              'مقبول',
  'ACCEPTED':              'مقبول',
  'Pending':               'قيد الانتظار',
  'PENDING':               'قيد الانتظار',
  'REJECTED':              'مرفوض',
  'Change Booth':          'تغيير الكشك',
  'Confirmed':             'تم التأكيد',
  'Booth':                 'الكشك',
  'Booth Locked':          'تم قفل الكشك',
  'Exhibition layout is locked (Less than 14 days remaining). You can no longer add, edit, or delete zones and booths.': 'تم قفل توزيع المعرض (بقي أقل من 14 يوماً). لم يعد بإمكانك إضافة أو تعديل أو حذف الأجنحة والزونات.',

  /* ── Empty States ─────────────────────────────────────── */
  'No events found':   'لا توجد أحداث',
  'Failed to load':    'فشل التحميل',
  'No events match your selection.': 'لا توجد أحداث تتطابق مع اختيارك.',
  'No upcoming public events available right now.': 'لا توجد أحداث عامة قادمة الآن.',
  'No public events available right now.': 'لا توجد أحداث عامة الآن.',

  /* ── Sponsor Visibility ───────────────────────────────── */
  'You are currently not visible to event managers.': 'أنت غير مرئي لمديري الأحداث حالياً.',
  'You will remain hidden until you turn this option ON again.': 'ستبقى مخفياً حتى تعيد تفعيل هذا الخيار.',
  /* ── Missing Dashboard & Profiles ────────────────────────── */
  'Analytics Dashboard': 'لوحة تحليلات النظام',
  'System-wide insights & performance metrics': 'رؤى ومؤشرات أداء النظام الشاملة',
  'Total Events': 'إجمالي الأحداث',
  'Awaiting review': 'في انتظار المراجعة',
  'Total Tickets': 'إجمالي التذاكر',
  'Total Users': 'إجمالي المستخدمين',
  'All registered users': 'جميع المستخدمين المسجلين',
  'Overall Attendance Rate': 'معدل الحضور العام',
  'Registration Trend (Last 6 Months)': 'اتجاه التسجيل (آخر 6 أشهر)',
  'Top Events by Registrations': 'أفضل الأحداث حسب التسجيل',
  'Event': 'الحدث',
  'Type': 'النوع',
  'Tickets': 'التذاكر',
  'Users by Role': 'المستخدمون حسب الدور',
  'Pending Approvals': 'موافقات قيد الانتظار',
  'No pending events': 'لا توجد أحداث قيد الانتظار',
  'No approved events yet': 'لا توجد أحداث معتمدة بعد',
  'No events yet. Create one!': 'لا توجد أحداث بعد. أنشئ حدثاً!',
  'Public Profile': 'الملف الشخصي العام',
  'Website URL': 'رابط الموقع الإلكتروني',
  'Additional Links / Socials': 'روابط / منصات إضافية',
  'Security': 'الأمان',
  'New Password': 'كلمة المرور الجديدة',
  'Leave blank to keep current password': 'اتركه فارغاً للاحتفاظ بكلمة المرور الحالية',
  'When enabled, your profile will be listed publicly, and Event Managers can send you sponsorship requests. You can also browse and request to sponsor published events.': 'عند التفعيل، سيتم إدراج ملفك الشخصي للعامة، ويمكن لمديري الأحداث إرسال طلبات رعاية إليك. يمكنك أيضًا تصفح طلبات رعاية الأحداث المنشورة.',
  'Profile updated!': 'تم تحديث الملف الشخصي!',

  /* ── Event Stats Page ──────────────────────────────────── */
  'Event Statistics - EventHub Manager': 'إحصائيات الحدث - EventHub',
  '← Back to My Events': '← العودة إلى أحداثي',
  'Max attendees': 'الحد الأقصى للحضور',
  'Checked In': 'تم الحضور',
  'Remaining': 'المتبقي',
  'Not checked in yet': 'لم يحضروا بعد',
  'Registration vs Attendance': 'التسجيل مقابل الحضور',
  'Capacity Breakdown': 'توزيع السعة',
  'Participants': 'المشاركون',
  'Attendee': 'الحاضر',
  'Ticket Code': 'كود التذكرة',
  'Scanned At': 'وقت الفحص',
  'Reviews & Ratings': 'التقييمات والمراجعات',
  'No ratings yet': 'لا توجد تقييمات بعد',
  'No reviews yet for this event': 'لا توجد مراجعات لهذا الحدث بعد',
  'Anonymous': 'مجهول',
  'No written review': 'لا توجد مراجعة نصية',
  'No registrations yet': 'لا توجد تسجيلات بعد',

  /* ── Missing Page Titles & Subtitles ───────────────────── */
  'Verification Requests': 'طلبات التحقق',
  'Review and approve partner applications': 'مراجعة واعتماد طلبات الشركاء',
  'Sponsorship Requests': 'طلبات الرعاية',
  'Browse and respond to open sponsorship opportunities': 'تصفح وتجاوب مع فرص الرعاية المتاحة',
  'Sponsorship History': 'سجل الرعايات',
  'Sponsor Dashboard': 'لوحة تحكم الراعي',
  'Your sponsorship activities at a glance': 'لمحة سريعة عن أنشطة الرعاية الخاصة بك',
  'Request sponsors for your events': 'طلب رعاة لأحداثك',
  'Live Attendance': 'الحضور المباشر',
  'Real-time attendance tracking for your live events': 'تتبع الحضور في الوقت الفعلي لأحداثك',
  'Create accounts for assistants to scan QR codes at your events': 'إنشاء حسابات للمساعدين لفحص رموز QR في أحداثك',
  'Venues': 'القاعات',
  'Manage event locations': 'إدارة مواقع الأحداث',
  'Manage all platform users': 'إدارة جميع مستخدمي المنصة',
  'Verifications': 'التحققات',
  'Admin Review': 'مراجعة الأدمن',
  'Admin\'s Review': 'مراجعة الأدمن',
  'Fields to update:': 'الحقول المطلوب تعديلها:',
  'No pending verification requests.': 'لا توجد طلبات تحقق قيد الانتظار.',
  '🔄 Resubmitted': '🔄 أُعيد تقديمه',
  '✨ New Request': '✨ طلب جديد',
  ' (Waiting for Partner)': ' (بانتظار الشريك)',

  /* ── Sponsorship Page ───────────────────────────────────── */
  'Available Sponsors': 'الرعاة المتاحون',
  'Available Companies': 'الشركات العارضة المتاحة',
  'Browse Companies': 'تصفح الشركات',
  'Invite to Exhibition': 'دعوة للمشاركة',
  'Available for Exhibitions': 'متاح للمشاركة في المعارض',
  'Your Sponsorship Requests': 'طلبات الرعاية الخاصة بك',
  'Sponsor': 'الراعي',
  'Company': 'الشركة',
  'Contact': 'للتواصل',
  'Message': 'الرسالة',
  'Tier': 'المستوى',
  'No sponsorship requests yet': 'لا توجد طلبات رعاية بعد',
  'No available sponsors right now.': 'لا يوجد رعاة متاحين حالياً.',
  '💬 Read Message': '💬 قراءة الرسالة',
  'Open': 'مفتوح',
  '✅ Accept': '✅ قبول',
  '❌ Reject': '❌ رفض',
  '✏️ Edit Tier': '✏️ تعديل المستوى',
  'Locked (Started)': 'مغلق (بدأ الحدث)',
  'No Action': 'لا يوجد إجراء',
  'Awaiting Sponsor': 'بانتظار الراعي',
  'View Profile': 'عرض الملف الشخصي',
  'Select your event…': 'اختر حدثك...',
  'Request Sponsorship': 'طلب رعاية',
  'Target Sponsor': 'الراعي المستهدف',
  'Message to Sponsors': 'رسالة للرعاة',
  'Describe sponsorship opportunity…': 'صف فرصة الرعاية...',
  'Submit Request': 'إرسال الطلب',
  'Sponsorship Message': 'رسالة الرعاية',
  'Close': 'إغلاق',
  'Accept Sponsorship': 'قبول الرعاية',
  'Select Sponsor Tier': 'حدد مستوى الراعي',
  '🏷️ Unranked': '🏷️ غير مصنف',
  '💎 Diamond Sponsor': '💎 راعٍ ماسي',
  '🥇 Gold Sponsor': '🥇 راعٍ ذهبي',
  '🥈 Silver Sponsor': '🥈 راعٍ فضي',
  '🥉 Bronze Sponsor': '🥉 راعٍ برونزي',
  'Choose a tier or leave unranked. Two sponsors cannot have the same tier.': 'اختر مستوى أو دعه غير مصنف. لا يمكن لراعيين مشاركة نفس المستوى.',
  '✅ Confirm Acceptance': '✅ تأكيد القبول',
  'Edit Sponsor Tier': 'تعديل مستوى الراعي',
  'Select New Sponsor Tier': 'حدد مستوى الراعي الجديد',
  'This tier will be updated immediately in all event details.': 'سيتم تحديث هذا المستوى فوراً في تفاصيل الحدث.',
  '💎 Diamond': '💎 ماسي',
  '🥇 Gold': '🥇 ذهبي',
  '🥈 Silver': '🥈 فضي',
  '🥉 Bronze': '🥉 برونزي',

  /* ── Assistants Page ────────────────────────────────────── */
  'Generate Assistant Account': 'إنشاء حساب مساعد',
  'Assistants use their accounts to log in on their mobile devices and scan QR codes for attendance tracking at the venue gates.': 'يستخدم المساعدون حساباتهم لتسجيل الدخول بأجهزتهم المحمولة ومسح رموز QR لتتبع الحضور على بوابات القاعة.',
  'Assistant Name': 'اسم المساعد',
  'Assistant Email (Login ID)': 'البريد الإلكتروني للمساعد (للدخول)',
  'Password': 'كلمة المرور',
  'Assign to Event': 'تعيين لحدث',
  'Select an Event...': 'اختر حدثاً...',
  'Create Assistant': 'إنشاء مساعد',
  'Creating...': 'جاري الإنشاء...',
  '✅ Assistant Created Successfully!': '✅ تم إنشاء المساعد بنجاح!',
  'Please share the email and password with your assistant. They can now log in and access the QR scanner.': 'يرجى مشاركة البريد الإلكتروني وكلمة المرور مع مساعدك. يمكنه الآن تسجيل الدخول والوصول لماسح الـ QR.',
  'Existing Assistants': 'المساعدون الحاليون',
  'Loading assistants...': 'جاري تحميل المساعدين...',
  'Delete': 'حذف',
  'No assistants created yet.': 'لا يوجد مساعدون بعد.',
  'Busy': 'مشغول',

  /* ── Assistants UI (Management) ────────────────────────── */
  'Assistants Management': 'إدارة المساعدين',
  'Invite available assistants to your events and track their performance': 'قم بدعوة المساعدين المتاحين لأحداثك وتتبع أدائهم',
  '📨 Invite Assistants': '📨 دعوة المساعدين',
  'Browse available assistants and send event invitations': 'تصفح المساعدين المتاحين وأرسل دعوات الحدث',
  'Select Event to Invite For': 'اختر الحدث للدعوة إليه',
  'Choose an event...': 'اختر حدثاً...',
  'Search assistants...': 'البحث عن المساعدين...',
  'Select an event first to see available assistants': 'اختر حدثاً أولاً لرؤية المساعدين المتاحين',
  'All': 'الكل',
  'Loading invitations...': 'جاري تحميل الدعوات...',
  'No available assistants found': 'لم يتم العثور على مساعدين متاحين',
  'Joined': 'انضم',
  'Invited': 'تمت دعوته',
  'Invite': 'دعوة',
  'Please select an event first': 'يرجى اختيار حدث أولاً',
  'this event': 'هذا الحدث',
  'Send invitation to ': 'إرسال دعوة إلى ',
  ' for ': ' لـ ',
  'Sending...': 'جاري الإرسال...',
  'Invitation sent to': 'تم إرسال الدعوة إلى',
  'Failed to send invitation': 'فشل إرسال الدعوة',
  'Failed to load invitations': 'فشل تحميل الدعوات',
  'No invitations sent yet. Invite assistants from the left panel.': 'لم يتم إرسال دعوات بعد. قم بالدعوة من القائمة اليسرى.',
  'No pending invitations.': 'لا توجد دعوات قيد الانتظار.',
  'No accepted invitations yet.': 'لا توجد دعوات مقبولة بعد.',
  'No rejected invitations.': 'لا توجد دعوات مرفوضة.',
  'Assistant': 'المساعد',
  'Event': 'الحدث',
  'Scans': 'عمليات المسح',
  'Remove': 'إزالة',
  'Cannot remove from ended event': 'لا يمكن الإزالة من حدث منتهي',
  'Are you sure you want to remove this assistant from the event?': 'هل أنت متأكد من إزالة هذا المساعد من الحدث؟',
  'Cancel this invitation?': 'إلغاء هذه الدعوة؟',
  'Assistant removed': 'تمت إزالة المساعد',
  'Invitation cancelled': 'تم إلغاء الدعوة',
  'Failed to cancel': 'فشل الإلغاء',
  '✅ Accepted': '✅ مقبول',
  '❌ Rejected': '❌ مرفوض',
  '⏳ Pending': '⏳ قيد الانتظار',

  /* ── Search Placeholders ───────────────────────────────── */
  'Search users…': 'البحث في المستخدمين...',
  'Search by event or manager name...': 'البحث باسم الحدث أو مديره...',
  'Search by event name...': 'البحث باسم الحدث...',
  'Search by name or manager...': 'البحث بالاسم أو المدير...',
  'Search by name…': 'البحث بالاسم...',

  /* ── Additional Sponsor Texts ───────────────────────────── */
  'All Requests': 'كافة الطلبات',
  'Open Requests': 'الطلبات المفتوحة',
  'No requests found': 'لا توجد طلبات',
  'Untitled Event': 'حدث غير معنون',
  '⏳ Awaiting Event Manager to respond': '⏳ بانتظار رد مدير الحدث',
  'Awaiting Event Manager to respond': 'بانتظار رد مدير الحدث',
  'Sponsorship accepted! 🎉': 'تم قبول الرعاية! 🎉',
  'Sponsorship accepted!': 'تم قبول الرعاية!',
  'Request declined.': 'تم رفض الطلب.',
  '📥 Received': '📥 مُستلم',
  '📤 Sent': '📤 مُرسل',
  '❌ Decline': '❌ رفض',
  'Closed to Sponsorship': 'مغلق للرعاية',
  'You are currently visible to event managers and can browse opportunities.': 'أنت مرئي حالياً لمديري الأحداث ويمكنك تصفح الفرص.',
  'Available': 'متاح',
  'Not Available': 'غير متاح',
  'My Sponsorships': 'رعاياتي',
  'View All Open': 'عرض المفتوح',
  'No sponsorships yet.': 'لا توجد رعايات بعد.',
  'Browse open requests!': 'تصفح الطلبات المفتوحة!',

  /* ── Sponsorship History Page ──────────────────────────── */
  'Sponsorship History': 'سجل الرعايات',
  'Your portfolio of supported events and their performance': 'محفظتك للأحداث المدعومة وأدائها',
  'Events Sponsored': 'الأحداث المدعومة',
  'Total Attendees': 'إجمالي الحضور',
  'Failed to load data': 'فشل تحميل البيانات',
  "You haven't sponsored any events yet.": 'لم تقم برعاية أي أحداث بعد.',
  'Browse Opportunities': 'تصفح الفرص',
  'Registered': 'المسجلون',
  'Attended': 'الحضور',
  'Rating': 'التقييم',
  'Manager: ': 'المدير: ',
  'Agreement': 'اتفاقية',
  '📄 Agreement': '📄 الاتفاقية',
  'TBA': 'يُحدد لاحقاً',

  /* ── Create Event & Profile ───────────────────────────── */
  'Create New Event': 'إنشاء حدث جديد',
  'Basic Information': 'المعلومات الأساسية',
  'Event Title': 'عنوان الحدث',
  'Tech Summit 2026': 'قمة التقنية 2026',
  'e.g. Tech Summit 2026': 'مثال: قمة التقنية 2026',
  'Description': 'الوصف',
  'Describe your event…': 'صف حدثك...',
  'Briefly describe your event…': 'صف حدثك باختصار...',
  'Event Type': 'نوع الحدث',
  'Fully Booked': 'محجوز بالكامل',
  'Partially Booked': 'محجوز جزئياً',
  'Date & Location Details': 'تفاصيل التاريخ والموقع',
  'Booking Date': 'تاريخ الحجز',
  'Period': 'الفترة',
  'Morning Period ☀': 'الفترة الصباحية ☀',
  'Evening Period 🌙': 'الفترة المسائية 🌙',
  'Full Day 🗓️': 'يوم كامل 🗓️',
  'Select a venue first to see the time': 'اختر قاعة أولاً لرؤية الوقت',
  'Additional Settings': 'إعدادات إضافية',
  'Event Venue': 'مكان الحدث',
  'Select a hall inside the exhibition...': 'اختر قاعة داخل المعرض...',
  '➕ External Hall': '➕ قاعة خارجية',
  'External Hall Name (e.g., Corinthia Hotel)': 'اسم القاعة الخارجية (مثال: فندق كورنثيا)',
  '🏢 Inside Exhibition': '🏢 داخل المعرض',
  'Google Maps Link': 'رابط خرائط جوجل',
  'Proof of Booking (PDF/Image)': 'إثبات الحجز (ملف PDF/صورة)',
  'Required. Upload official confirmation of the external booking.': 'مطلوب. يرجى رفع تأكيد رسمي للحجز الخارجي.',
  'Loading venues…': 'جاري تحميل القاعات...',
  'Start Date & Time': 'تاريخ ووقت البداية',
  'End Date & Time': 'تاريخ ووقت النهاية',
  'Event Banner Image': 'صورة غلاف الحدث',
  'Submit for Approval': 'إرسال للاعتماد',
  'Loading profile...': 'جاري تحميل الملف الشخصي...',
  'About': 'نبذة',
  'Contact Information': 'معلومات التواصل',
  'User Profile – EventHub': 'الملف الشخصي للمستخدم – EventHub',
  '← Back': '← رجوع',
  'All Events': 'جميع الأحداث',
  'Failed to load events': 'فشل تحميل الأحداث',
  'Login Email (Private)': 'البريد الإلكتروني للدخول (خاص)',
  'Phone': 'الهاتف',
  'All Statuses': 'جميع الحالات',
  'No venues available': 'لا توجد قاعات متاحة',
  'Sponsorship is now OPEN for this event.': 'الرعاية الآن مفتوحة لهذا الحدث.',
  'Sponsorship is now CLOSED for this event.': 'الرعاية الآن مغلقة لهذا الحدث.',
  'Error updating status': 'حدث خطأ أثناء تحديث الحالة',
  'Event submitted for approval!': 'تم إرسال الحدث للاعتماد!',
  'Could not fetch event details': 'تعذر استرجاع تفاصيل الحدث',
  'Could not load details': 'تعذر تحميل التفاصيل',
  'No description.': 'لا يوجد وصف.',
  'Average Event Rating': 'متوسط تقييم الأحداث',
  'Available for Sponsorship': 'متاح للرعاية',
  'Currently Not Available': 'غير متوفر حالياً',
  'Application Status': 'حالة الطلب',
  'Booth': 'الجناح',
  'Booth Detail': 'تفاصيل الجناح',
  'Participation confirmed!': 'تم تأكيد المشاركة!',
  'Your application is awaiting manager approval.': 'طلبك ينتظر موافقة مدير المعرض.',
  'The manager invited you to exhibit.': 'دعاك مدير المعرض للمشاركة كعارض.',
  'Agreement offer sent. Please review the contract.': 'تم إرسال عرض الاتفاقية. يرجى مراجعة العقد.',
  'Review Contract': 'مراجعة العقد',
  'Participation confirmed!': 'تم تأكيد المشاركة!',
  'Booking Proof': 'إثبات الحجز',
  'Competent Authority Approval': 'موافقة الجهة المختصة',
  'Event Objective': 'الهدف من الحدث',
  'Target Audience': 'الجمهور المستهدف',
  'LYD': 'د.ل',
  'Unranked': 'غير مصنف',
  'Negotiating': 'قيد التفاوض',
  'Contract': 'العقد',
  'Download Contract': 'تحميل العقد',
  'Download Final Agreement': 'تحميل الاتفاقية النهائية',
  'Booth': 'الجناح',
  'Actions': 'الإجراءات',
  'Total': 'الإجمالي',
  'Past': 'منتهي',
  'Total Fees': 'إجمالي الرسوم',
  'Search company...': 'بحث عن شركة...',
  'All Categories': 'كل الفئات',
  'Category': 'الفئة',
  'Booth saved successfully!': 'تم حفظ الجناح بنجاح!',
  'Error saving booth': 'خطأ في حفظ الجناح',
  'Assign Booth Location': 'تخصيص مكان الجناح',
  'Assigning for:': 'تخصيص لـ:',
  'Booth Number / Location': 'رقم الجناح / المكان',
  'Space Size': 'مساحة الجناح',
  'Save Assignment': 'حفظ التخصيص',
  'Booth assigned successfully!': 'تم تخصيص الجناح بنجاح!',
  'Error assigning booth': 'خطأ في تخصيص الجناح',
  'Select an exhibition event…': 'اختر حدث معرض…',
  'Exhibition Layout': 'تخطيط المعرض',
  'Applications': 'الطلبات',
  'Zones & Booths Configuration': 'إعداد المناطق والأكشاك',
  'Manage exhibition zones and available slots': 'إدارة مناطق المعرض والأماكن المتاحة',
  'Batch Add': 'إضافة دفعة',
  'Booth': 'كشك',
  'Add Zone': 'إضافة منطقة',
  'Loading booths...': 'جاري تحميل الأكشاك...',
  'No zones defined yet.': 'لا توجد مناطق محددة بعد.',
  'Create First Zone': 'إنشاء أول منطقة',
  'Zone created': 'تم إنشاء المنطقة',
  'Zone deleted': 'تم حذف المنطقة',
  'Booths generated': 'تم إنشاء الأكشاك',
  'Enter Zone Name (e.g. Zone A):': 'أدخل اسم المنطقة (مثلاً Area A):',
  'Enter prefix (e.g. A-):': 'أدخل البادئة (مثلاً A-):',
  'How many booths?': 'كم عدد الأكشاك؟',
  'Default size per booth:': 'المساحة الافتراضية لكل كشك:',
  'Update Booth Number:': 'تحديث رقم الكشك:',
  'Update Size:': 'تحديث المساحة:',
  'Are you sure?': 'هل أنت متأكد؟',
  'Search company...': 'البحث عن شركة...',
  'Company': 'الشركة',
  'Category': 'الفئة',
  'Status': 'الحالة',
  'Actions': 'الإجراءات',
  'Exhibitions': 'المعارض',
  'My Exhibitions': 'إدارة المعارض',
  'Assign Booth': 'تخصيص مكان',
  'Select Booth': 'اختر الجناح',
  'Save Assignment': 'حفظ التخصيص',
  'Add New Zone': 'إضافة منطقة جديدة',
  'Zone Name': 'اسم المنطقة',
  'e.g. Zone A, Hall 1': 'مثلاً Zone A، Hall 1',
  'Create Zone': 'إنشاء المنطقة',
  'Add Booth': 'إضافة كشك',
  'Booth Number': 'رقم الكشك',
  'e.g. A-1': 'مثلاً A-1',
  'Size': 'المساحة',
  'e.g. 9 or 3x3': 'مثلاً 9 أو 3x3',
  'Save Booth': 'حفظ الكشك',
  'Batch Generate Booths': 'توليد الأكشاك جماعياً',
  'Prefix': 'البادئة',
  'e.g. A-': 'مثلاً A-',
  'Start Number': 'رقم البداية',
  'e.g. 1': 'مثلاً 1',
  'How many?': 'كم العدد؟',
  'e.g. 10': 'مثلاً 10',
  'Default Size': 'المساحة الافتراضية',
  'Generate Batch': 'توليد دفعة',
  'm²': 'م²',
  'متر': 'متر',

  /* ── Statuses & Badges (Lowercase) ────────────────────── */
  'pending': 'قيد الانتظار',
  'approved': 'معتمد',
  'rejected': 'مرفوض',
  'live': 'مباشر',
  'ended': 'منتهي',
  'upcoming': 'قادم',
  'unused': 'غير مستخدم',
  'used': 'مستخدم',
  'accepted': 'مقبول',
  'available': 'متاح',
  'maintenance': 'صيانة',
  'Admin': 'الأدمن',
  'Event Manager': 'مدير أحداث',
  'Sponsor': 'الراعي',
  'User': 'مستخدم',
  'Assistant': 'مساعد',
  'cancellation_requested': 'طلب إلغاء',
  'cancelled': 'ملغى',

  /* ── Notifications ──────────────────────────────────────── */
  '🔔 Notifications': '🔔 الإشعارات',
  'Mark all read': 'تحديد الكل كمقروء',
  'No notifications yet': 'لا توجد إشعارات بعد',
  'All notifications marked as read': 'تم تحديد جميع الإشعارات كمقروءة',
  'Just now': 'الآن',
  'm ago': 'د مضت',
  'h ago': 'س مضت',
  'd ago': 'ي مضت',
  'New Event Pending': 'حدث جديد قيد الانتظار',
  'Event Approved ✅': 'تم اعتماد الحدث ✅',
  'Event Rejected ❌': 'تم رفض الحدث ❌',
  'New Rating Received': 'تقييم جديد',
  'New Ticket Booked 🎟️': 'تم حجز تذكرة 🎟️',
  'Sponsorship Invitation 💼': 'دعوة رعاية 💼',
  'New Sponsorship Request 🤝': 'طلب رعاية جديد 🤝',
  'Sponsorship accepted ✅': 'تم قبول الرعاية ✅',
  'Sponsorship rejected ❌': 'تم رفض الرعاية ❌',
  'Invitation accepted ✅': 'تم قبول الدعوة ✅',
  'Invitation rejected ❌': 'تم رفض الدعوة ❌',
  'Verification Approved ✅': 'تم قبول التحقق ✅',
  'Verification Rejected ❌': 'تم رفض التحقق ❌',
  'Changes Requested ⚠️': 'مطلوب تعديلات ⚠️',
  'Cancellation Requested ⚠️': 'تم طلب الإلغاء ⚠️',
  'Cancellation Approved 🚫': 'تمت الموافقة على الإلغاء 🚫',
  'Cancellation Rejected ⚠️': 'تم رفض طلب الإلغاء ⚠️',

  /* ── General ──────────────────────────────────────────── */
  '🔄 Refresh': '🔄 تحديث',
  'Refresh': 'تحديث',
  ' Live Events': ' أحداث مباشرة',
  ' Live Event': ' حدث مباشر',
  'Edit Profile': 'تعديل الملف الشخصي',
  'No bio provided yet.': 'لم يتم تقديم نبذة بعد.',
  'End': 'وقت الانتهاء',

  /* ── Event Stats ──────────────────────────────────────── */
  '← Back to My Events': '← الرجوع إلى أحداثي',
  'Capacity': 'السعة',
  'Max attendees': 'الحد الأقصى للحضور',
  'Registered': 'المسجلون',
  'Checked In': 'تم الحضور',
  'Remaining': 'المتبقي',
  'Not checked in yet': 'لم يسجلوا الدخول بعد',
  'Fill Rate': 'معدل الامتلاء',
  'Attendance Rate': 'معدل الحضور',
  'Registration vs Attendance': 'التسجيل مقابل الحضور',
  'Capacity Breakdown': 'تفصيل السعة الاستيعابية',
  'Participants': 'المشاركون',
  'Attendee': 'الحاضر',
  'Ticket Code': 'رمز التذكرة',
  'Scanned At': 'وقت الفحص',
  'No registrations yet': 'لا توجد تسجيلات بعد',
  'Reviews & Ratings': 'التقييمات والمراجعات',
  'No ratings yet': 'لا توجد تقييمات بعد',
  'No reviews yet for this event': 'لا توجد مراجعات لهذا الحدث',
  'Failed to load reviews': 'فشل تحميل التقييمات',
  'Anonymous': 'مجهول',
  'No written review': 'لا توجد مراجعة مكتوبة',
  'Not Scanned': 'لم يتم الفحص',
  'Registered (Not Scanned)': 'مسجل (لم يحضر)',
  'Available Slots': 'الأماكن المتاحة',
  'Request Event Cancellation': 'طلب إلغاء الفعالية',
  'Please provide a reason for cancelling this event. This request will be sent to the administrator for approval.': 'يرجى تقديم سبب لإلغاء هذه الفعالية. سيتم إرسال هذا الطلب إلى المسؤول للموافقة عليه.',
  'Note:': 'ملاحظة:',
  'Ticket sales will be suspended immediately upon submitting this request.': 'سيتم تعليق مبيعات التذاكر فور تقديم هذا الطلب.',
  'CANCELLATION REASON': 'سبب الإلغاء',
  'e.g. Unforeseen circumstances, medical emergency...': 'مثال: ظروف غير متوقعة، حالة طوارئ طبية...',
  '⌛ Cancellation Pending': '⌛ الإلغاء قيد الانتظار',
  'Your request is being reviewed by the administrator.': 'يتم مراجعة طلبك من قبل المسؤول.',
  'Reason:': 'السبب:',
  '🚫 Cancellation Rejected': '🚫 تم رفض الإلغاء',
  'The admin rejected your cancellation request.': 'رفض المسؤول طلب الإلغاء الخاص بك.',
  'Admin Note:': 'ملاحظة المسؤول:',
  '🛑 Close Ticket Sales': '🛑 إغلاق مبيعات التذاكر',
  '🎟️ Open Ticket Sales': '🎟️ فتح مبيعات التذاكر',
  'Cancellation request submitted successfully': 'تم تقديم طلب الإلغاء بنجاح',
  'Please enter a cancellation reason': 'يرجى إدخال سبب الإلغاء',
  'Ticket sales are now OPEN': 'مبيعات التذاكر مفتوحة الآن',
  'Ticket sales are now CLOSED': 'مبيعات التذاكر مغلقة الآن',
  'Reason for Rejecting Cancellation': 'سبب رفض الإلغاء',
  'Explain why you are rejecting this cancellation request': 'اشرح سبب رفضك لطلب الإلغاء هذا',
  'e.g. The event is already sold out and cannot be cancelled at this stage...': 'مثال: الحدث بيعت تذاكره بالكامل ولا يمكن إلغاؤه في هذه المرحلة...',
  'Confirm Rejection': 'تأكيد الرفض',
  'Cancellation request rejected. Event status restored to approved.': 'تم رفض طلب الإلغاء. تم استعادة حالة الفعالية إلى معتمدة.',
  'Are you sure you want to APPROVE this cancellation request? This will permanently cancel the event.': 'هل أنت متأكد من رغبتك في الموافقة على طلب الإلغاء هذا؟ سيؤدي هذا إلى إلغاء الفعالية بشكل نهائي.',
  'Event cancelled successfully.': 'تم إلغاء الفعالية بنجاح.',
  'Cannot request cancellation for events starting in less than 30 days.': 'لا يمكن طلب إلغاء الفعاليات التي تبدأ خلال أقل من 30 يوماً.',
  'Cancellation Requests': 'طلبات الإلغاء',
  'of capacity': 'من السعة',
  'attendance': 'حضور',
  'registered out of': 'مسجلون من أصل',
  'capacity': 'أماكن',
  'checked in out of': 'حاضرون من أصل',
  'registrations': 'تسجيلات',
  'Based on': 'بناءً على',
  'rating(s)': 'تقييم(ات)',
  '✏️ Edit': '✏️ تعديل',
  'Venue Name': 'اسم القاعة',
  'Google Maps Link': 'رابط خرائط جوجل',
  'Open in Maps': 'فتح في الخرائط',
  '📍 Open in Maps': '📍 فتح في الخرائط',
  'No venues yet. Add one!': 'لا توجد قاعات بعد. أضف واحدة!',
  'Create Assistant': 'إنشاء مساعد',
  'Existing Assistants': 'المساعدون الحاليون',
  'Generate Assistant Account': 'إنشاء حساب مساعد',
  'Publish Days': 'أيام النشر',
  'Publish': 'نشر',
  'Unpublish': 'إلغاء النشر',
  'Save Draft': 'حفظ كمسودة',
  'Published — Visible to public': 'منشور — مرئي للجمهور',
  'Draft — Not visible to public yet': 'مسودة — غير مرئي للجمهور بعد',
  'The published schedule field is required.': 'حقل جدول النشر مطلوب.',
  'The published schedule field is required': 'حقل جدول النشر مطلوب',
  'published_schedule_desc': 'حدد الأيام التي تريد عرضها للجمهور. الأيام غير المحددة ستعتبر "أيام تجهيز" ولن تظهر في جدول الفعالية العام.',
  'published_schedule_success': 'تم حفظ المسودة بنجاح!',
  'Public': 'عام',
  'Setup': 'تجهيز',
  'No schedule found for this event.': 'لم يتم العثور على جدول زمني لهذا الحدث.',
  'Event published successfully! 🚀': 'تم نشر الحدث بنجاح! 🚀',
  'Event unpublished. It is no longer visible to the public.': 'تم إلغاء نشر الحدث. لم يعد مرئياً للجمهور.',
  'Are you sure you want to unpublish this event? It will no longer be visible to the public.': 'هل أنت متأكد من إلغاء نشر هذا الحدث؟ لن يكون مرئياً للجمهور بعد الآن.',
  'Please select at least one day to publish.': 'يرجى اختيار يوم واحد على الأقل للنشر.',

  /* ── Event Types (English → Arabic) ────────────────────── */
  'Conference': 'مؤتمر',
  'Seminar': 'ندوة',
  'Workshop': 'ورشة عمل',
  'Training Course': 'دورة تدريبية',
  'Entertainment': 'ترفيه',
  'Scientific Forum': 'ملتقى علمي',
  'Sports': 'رياضة',
  'Technology': 'تقنية',
  'Social': 'اجتماعية',

  /* ── Login / Register / Auth Pages ─────────────────────── */
  'Welcome back': 'مرحباً بعودتك',
  'Welcome back, ': 'مرحباً بعودتك، ',
  'Sign in to your account to continue': 'سجّل الدخول إلى حسابك للمتابعة',
  'Email Address': 'البريد الإلكتروني',
  'Sign In': 'تسجيل الدخول',
  'Signing in…': 'جاري تسجيل الدخول…',
  "Don't have an account?": 'ليس لديك حساب؟',
  'Register': 'تسجيل',
  'Registration Restricted': 'التسجيل مقيّد',
  'To join the EventHub platform:': 'للانضمام إلى منصة EventHub:',
  'Attendees (Users)': 'الحضور (المستخدمون)',
  'Please download the': 'يرجى تحميل',
  'EventHub Mobile App': 'تطبيق EventHub',
  'to create your account and buy tickets.': 'لإنشاء حسابك وشراء التذاكر.',
  'Managers & Sponsors': 'المديرون والرعاة',
  'Accounts for Event Managers and Sponsors must be verified by the System Administrator.': 'يجب التحقق من حسابات مديري الأحداث والرعاة من قبل مدير النظام.',
  'I declare that all the information and documents provided are authentic, and I agree to EventHub\'s Partner Terms of Service.': 'أقر بأن جميع المعلومات والمستندات المقدمة صحيحة، وأوافق على شروط خدمة شركاء EventHub.',
  'No data': 'لا توجد بيانات',
  'Apply as Partner →': 'التقديم كشريك →',
  'Already have an account?': 'لديك حساب بالفعل؟',
  'Sign in here': 'سجّل الدخول هنا',
  'Partner Registration': 'تسجيل الشركاء',
  'Apply as an Event Manager or Sponsor.': 'التقديم كمدير أحداث أو راعٍ.',
  'Role / Account Type': 'الدور / نوع الحساب',
  'Select Role': 'اختر الدور',
  'Company / Individual Name': 'اسم الشركة / الفرد',
  'Business Email Address': 'البريد الإلكتروني للأعمال',
  'Verification Document (KYB / KYC)': 'وثيقة التحقق (KYB / KYC)',
  'Verification Documents (KYB)': 'المستندات القانونية (KYB)',
  'Please upload your Commercial Register, License, or Authorization Letter (PDF/JPG/PNG up to 5MB).': 'يرجى رفع السجل التجاري أو الرخصة أو خطاب التفويض (PDF/JPG/PNG حتى 5 ميجا).',
  'Upload all required documents below (PDF/JPG/PNG, max 5 MB each).': 'يرجى رفع جميع المستندات المطلوبة أدناه (PDF/JPG/PNG, بحد أقصى 5 ميجا لكل ملف).',
  'Click to select file...': 'انقر لاختيار ملف...',
  'Submit Application': 'تقديم الطلب',
  'Submitting...': 'جاري التقديم...',
  'Already confirmed?': 'تم التأكيد بالفعل؟',
  'Sign in': 'تسجيل الدخول',
  'Application Under Review': 'الطلب قيد المراجعة',
  'Your partner application has been received successfully. Our administration team is currently verifying the documents you uploaded. You will receive full access to the platform once your status is approved.': 'تم استلام طلب الشراكة بنجاح. فريق الإدارة يتحقق حالياً من المستندات التي رفعتها. ستحصل على وصول كامل للمنصة بمجرد الموافقة.',
  'Current Status': 'الحالة الحالية',
  'PENDING': 'قيد الانتظار',
  'REJECTED': 'مرفوض',
  'ACTION REQUIRED': 'مطلوب إجراء',
  'Upload Revised Document (PDF/JPG)': 'ارفع المستند المعدّل (PDF/JPG)',
  'Resubmit': 'إعادة التقديم',

  /* ── Venues Page ────────────────────────────────────────── */
  'Open Google Maps → Select location → Copy share link and paste here': 'افتح Google Maps ← اختر الموقع ← انسخ رابط المشاركة والصقه هنا',
  'Venue updated!': 'تم تحديث القاعة!',
  'Venue added!': 'تمت إضافة القاعة!',
  'Available': 'متاح',
  'Maintenance': 'صيانة',

  /* ── Attendance Page ────────────────────────────────────── */
  'No Live Events': 'لا توجد أحداث مباشرة',
  "You don't have any events that are currently live.": 'ليس لديك أي أحداث مباشرة حالياً.',
  'Events will appear here during their scheduled time.': 'ستظهر الأحداث هنا خلال وقتها المحدد.',
  'Attendance Progress': 'تقدم الحضور',
  'View Participants': 'عرض المشاركين',
  'not scanned': 'لم يُفحص',
  'full': 'ممتلئ',
  'No registrations': 'لا توجد تسجيلات',
  'Failed to load events': 'فشل تحميل الأحداث',
  'Please try again': 'يرجى المحاولة مجدداً',

  /* ── Verification Page ──────────────────────────────────── */
  'Document Review': 'مراجعة المستند',
  'Review Application': 'مراجعة الطلب',
  'This user has uploaded a verification document. Click below to securely download and review it.': 'قام المستخدم برفع مستند تحقق. انقر أدناه لتحميله ومراجعته بأمان.',
  'Previous Feedback Requested': 'الملاحظات السابقة المطلوبة',
  'Download Document': 'تحميل المستند',
  '⬇️ Download Document': '⬇️ تحميل المستند',
  '✓ Approve': '✓ اعتماد',
  '🔄 Request Changes': '🔄 طلب تعديلات',
  '✕ Reject': '✕ رفض',
  'Feedback Notes': 'ملاحظات التقييم',
  'Explain what changes are needed or why it was rejected...': 'اشرح التعديلات المطلوبة أو سبب الرفض...',
  'Confirm Rejection': 'تأكيد الرفض',
  'Confirm Changes Request': 'تأكيد طلب التعديلات',
  'Rejection Reason': 'سبب الرفض',
  'Changes Needed': 'تعديلات مطلوبة',
  'Please provide a reason': 'يرجى تقديم السبب',
  'Please describe what changes are needed': 'يرجى وصف التعديلات المطلوبة',
  'Partner Approved!': 'تمت الموافقة على الشريك!',
  'Partner Rejected': 'تم رفض الشريك',
  'Changes Requested': 'تم طلب التعديلات',
  'Downloading document...': 'جاري تحميل المستند...',
  'Download complete': 'اكتمل التحميل',
  'Error downloading file': 'خطأ في تحميل الملف',
  'Failed to load requests.': 'فشل تحميل الطلبات.',
  'Commercial Register': 'السجل التجاري',
  'Tax Number Certificate': 'شهادة الرقم الضريبي',
  'Articles of Association': 'عقد التأسيس',
  'Practice License': 'إذن المزاولة',
  'Click to select file...': 'انقر لاختيار ملف...',
  'Click to select new file...': 'انقر لاختيار ملف جديد...',
  'Reason:': 'السبب:',
  'Please select at least one document to re-upload.': 'يرجى اختيار مستند واحد على الأقل لإعادة الرفع.',
  'Documents submitted successfully!': 'تم إرسال المستندات بنجاح!',
  'Error re-uploading documents.': 'خطأ في إعادة رفع المستندات.',
  'Submit Review': 'إرسال التقييم',
  'Reject Entirely': 'رفض بالكامل',
  '🚫 Reject Entirely': '🚫 رفض بالكامل',
  'Reason for rejecting this entire application...': 'سبب رفض هذا الطلب بالكامل...',
  'Application rejected.': 'تم رفض الطلب.',
  'Error rejecting application.': 'خطأ في رفض الطلب.',
  'Are you sure you want to reject this entire application?': 'هل أنت متأكد أنك تريد رفض هذا الطلب بالكامل؟',
  'Please provide a rejection reason.': 'يرجى تقديم سبب للرفض.',
  'Please review "': 'يرجى مراجعة "',
  '" before submitting.': '" قبل الإرسال.',
  'Please provide a rejection reason for "': 'يرجى تقديم سبب رفض لـ "',
  'Review submitted!': 'تم إرسال التقييم!',
  'Error submitting review': 'خطأ في إرسال التقييم',
  'Previous note: ': 'الملاحظة السابقة: ',
  'Reason for rejecting this document...': 'سبب رفض هذا المستند...',
  '✅ Accept': '✅ قبول',
  '❌ Reject': '❌ رفض',
  '⬇️ Download': '⬇️ تحميل',
  'No file': 'لا يوجد ملف',
  '📤 Submit Review': '📤 إرسال التقييم',
  'Action Required': 'الإجراء المطلوب',
  'Some of your documents need to be revised. Please re-upload the rejected documents below.': 'تحتاج بعض مستنداتك إلى المراجعة. يرجى إعادة رفع المستندات المرفوضة أدناه.',
  'Your partner application has been received successfully. Our administration team is currently verifying the documents you uploaded.': 'تم استلام طلب الشراكة الخاص بك بنجاح. يقوم فريق الإدارة لدينا حالياً بالتحقق من المستندات التي قمت برفعها.',
  'CHANGES REQUESTED': 'تعديلات مطلوبة',
  'Document Status': 'حالة المستندات',
  'Resubmit Documents': 'إعادة تقديم المستندات',
  'Documents': 'المستندات',
  'Verification Documents': 'مستندات التحقق',
  'View and update your verification documents. Your account remains active while updates are reviewed.': 'عرض وتحديث مستندات التحقق الخاصة بك. يبقى حسابك نشطاً أثناء مراجعة التحديثات.',
  'Upload New': 'رفع جديد',
  'Submit Updated Documents': 'تقديم المستندات المحدثة',
  'Submit New Documents': 'تقديم المستندات الجديدة',
  'Documents submitted for review.': 'تم تقديم المستندات للمراجعة.',
  'Pending Review': 'قيد المراجعة',
  'pending_update': 'قيد المراجعة',
  'Document Update': 'تحديث مستند',
  'Review Update': 'مراجعة التحديث',
  'Review Document Update': 'مراجعة تحديث المستند',
  'Review Application': 'مراجعة الطلب',
  'Could not load documents.': 'تعذر تحميل المستندات.',
  'Error loading documents.': 'خطأ في تحميل المستندات.',
  'Error loading document': 'خطأ في تحميل المستند',
  'Selected:': 'تم اختيار:',
  'Documents for ': 'مستندات ',
  'Download': 'تحميل',
  '📤 Resubmit Documents': '📤 إعادة تقديم المستندات',
  'Submitting...': 'جاري الإرسال...',
  '🚫 Rejection Reason': '🚫 سبب الرفض',
  'Are you sure you want to suspend user "': 'هل أنت متأكد من رغبتك في إيقاف المستخدم "',
  'Are you sure you want to activate user "': 'هل أنت متأكد من رغبتك في تفعيل المستخدم "',
  '"?': '"؟',
  'Suspended users will be logged out and unable to log back in.': 'سيتم تسجيل خروج المستخدمين الموقوفين ولن يتمكنوا من تسجيل الدخول مرة أخرى.',
  'User suspended successfully.': 'تم إيقاف المستخدم بنجاح.',
  'User activated successfully.': 'تم تفعيل المستخدم بنجاح.',
  'Error updating status': 'خطأ في تحديث الحالة',
  'Delete user "': 'حذف المستخدم "',
  '"? This cannot be undone.': '"؟ لا يمكن التراجع عن هذا الإجراء.',
  'User deleted': 'تم حذف المستخدم',
  'Error deleting user': 'خطأ في حذف المستخدم',
  'Downloading documents...': 'جاري تحميل المستندات...',
  'Downloads complete': 'اكتملت التحميلات',
  'Account Status': 'حالة الحساب',
  'Accept': 'قبول',
  'Reject': 'رفض',
  'Reason for rejection...': 'سبب الرفض...',
  'Submit Review': 'إرسال المراجعة',
  'Review submitted!': 'تم إرسال المراجعة!',
  'Error submitting review': 'خطأ في إرسال المراجعة',
  'Please make a decision on at least one document.': 'يرجى اتخاذ قرار بشأن مستند واحد على الأقل.',
  'Please provide a rejection reason for the document.': 'يرجى تقديم سبب رفض للمستند.',
  'Review verification documents. Accept or reject individual documents — the partner will be notified instantly.': 'راجع مستندات التحقق. يمكنك قبول أو رفض المستندات بشكل فردي — سيتم إخطار الشريك فوراً.',
  'No file': 'لا يوجد ملف',
  'Commercial Register': 'السجل التجاري',
  'Tax Number Certificate': 'شهادة الرقم الضريبي',
  'Articles of Association': 'عقد التأسيس',
  'Practice License': 'رخصة مزاولة المهنة',
  'approved': 'مقبول',
  'rejected': 'مرفوض',
  'pending': 'قيد الانتظار',
  'changes_requested': 'مطلوب تعديلات',
  'verified': 'موثق',
  'Pending Review': 'قيد المراجعة',
  'Verified': 'موثق',
  'Pending': 'قيد الانتظار',
  'Changes Requested': 'مطلوب تعديلات',
  'Rejected': 'مرفوض',
  'Account Status': 'حالة الحساب',
  'Accept': 'قبول',
  'Reject': 'رفض',
  'Reason for rejection...': 'سبب الرفض...',
  'Submit Review': 'إرسال المراجعة',
  'Review submitted!': 'تم إرسال المراجعة!',
  'Error submitting review': 'خطأ في إرسال المراجعة',
  'Please make a decision on at least one document.': 'يرجى اتخاذ قرار بشأن مستند واحد على الأقل.',
  'Please provide a rejection reason for the document.': 'يرجى تقديم سبب رفض للمستند.',
  'Review verification documents. Accept or reject individual documents — the partner will be notified instantly.': 'راجع مستندات التحقق. يمكنك قبول أو رفض المستندات بشكل فردي — سيتم إخطار الشريك فوراً.',
  'No file': 'لا يوجد ملف',

  /* ── Agreement Negotiation ─────────────────────────────── */
  'Contract Negotiation': 'تفاوض العقد',
  'Negotiate terms with the other party': 'تفاوض على البنود مع الطرف الآخر',
  'Contract': 'العقد',
  'No contract generated yet. Generate the initial agreement to start negotiation.': 'لم يتم إنشاء عقد بعد. أنشئ العقد الأولي لبدء التفاوض.',
  'Generate Contract': 'إنشاء العقد',
  'Download Contract': 'تحميل العقد',
  'Download Final Agreement': 'تحميل الاتفاقية النهائية',
  'Upload Modified Contract': 'رفع العقد المعدل',
  'Add a message describing your changes...': 'أضف رسالة توضح التعديلات...',
  'Upload & Send for Review': 'رفع وإرسال للمراجعة',
  'Respond to Contract': 'الرد على العقد',
  'Add feedback or comments...': 'أضف ملاحظاتك أو تعليقاتك...',
  'Accept Contract': 'قبول العقد',
  'Request Revision': 'طلب تعديل',
  'Reject Contract': 'رفض العقد',
  'Waiting for the other party to review your submission...': 'بانتظار مراجعة الطرف الآخر لتقديمك...',
  'Negotiation History': 'سجل التفاوض',
  'No history yet': 'لا يوجد سجل بعد',
  'Contract generated successfully!': 'تم إنشاء العقد بنجاح!',
  'Contract uploaded and sent for review!': 'تم رفع العقد وإرساله للمراجعة!',
  'Please select a file': 'يرجى اختيار ملف',
  'Please provide feedback for your revision request': 'يرجى تقديم ملاحظاتك لطلب التعديل',
  'Are you sure you want to accept this contract?': 'هل أنت متأكد من رغبتك في قبول هذا العقد؟',
  'Are you sure you want to reject this contract?': 'هل أنت متأكد من رغبتك في رفض هذا العقد؟',
  'Send revision request with your feedback?': 'إرسال طلب تعديل مع ملاحظاتك؟',
  'Contract accepted!': 'تم قبول العقد!',
  'Contract rejected': 'تم رفض العقد',
  'Revision requested': 'تم طلب التعديل',
  'Draft': 'مسودة',
  'Pending Review': 'قيد المراجعة',
  'Revision Requested': 'مطلوب تعديل',
  'Uploaded new version': 'تم رفع نسخة جديدة',
  'Download failed': 'فشل التحميل',
  'Download complete': 'تم التحميل',
  'negotiating': 'قيد التفاوض',
  'Negotiating': 'قيد التفاوض',
  'Contract Negotiation': 'تفاوض العقد',
  'Final Agreement': 'الاتفاقية النهائية',
  'Failed to load': 'فشل في التحميل',
  'Cancel Agreement': 'إلغاء الاتفاقية',
  'This will cancel the preliminary agreement. Please provide a reason for cancellation.': 'سيؤدي هذا إلى إلغاء الاتفاقية المبدئية. يرجى تقديم سبب للإلغاء.',
  'This action cannot be undone.': 'هذا الإجراء لا يمكن التراجع عنه.',
  'Reason for cancellation (required)...': 'سبب الإلغاء (مطلوب)...',
  'Go Back': 'تراجع',
  'Confirm Cancellation': 'تأكيد الإلغاء',
  'Please provide a cancellation reason (at least 5 characters)': 'يرجى تقديم سبب للإلغاء (5 أحرف على الأقل)',
  'Agreement cancelled successfully': 'تم إلغاء الاتفاقية بنجاح',
  'Choose file': 'اختر ملف',
  'Cancel': 'إلغاء',
  'cancelled': 'ملغي',
  'Cancelled': 'ملغي',

  /* ── Forgot Password ───────────────────────────────────── */
  'Forgot Password?': 'هل نسيت كلمة المرور؟',
  'Enter your email and we\'ll send you a verification code.': 'أدخل بريدك الإلكتروني وسنرسل لك رمز تحقق.',
  'Email Address': 'البريد الإلكتروني',
  'Send Verification Code': 'إرسال رمز التحقق',
  'Remember your password?': 'تتذكر كلمة المرور؟',
  'Sign In': 'تسجيل الدخول',
  'Enter Verification Code': 'أدخل رمز التحقق',
  'We sent a 6-digit code to': 'أرسلنا رمزاً من 6 أرقام إلى',
  'New Password': 'كلمة المرور الجديدة',
  'Confirm Password': 'تأكيد كلمة المرور',
  'Reset Password': 'إعادة تعيين كلمة المرور',
  'Didn\'t receive the code? Resend': 'لم يصلك الرمز؟ إعادة الإرسال',
  '← Back': '→ رجوع',
  'Password Changed!': 'تم تغيير كلمة المرور!',
  'Your password has been reset successfully. You can now sign in with your new password.': 'تم إعادة تعيين كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.',
  'Go to Sign In': 'الذهاب لتسجيل الدخول',
  'Sending...': 'جاري الإرسال...',
  'Verification code sent to your email!': 'تم إرسال رمز التحقق إلى بريدك!',
  'Error sending code': 'خطأ في إرسال الرمز',
  'Please enter the full 6-digit code.': 'يرجى إدخال رمز التحقق الكامل (6 أرقام).',
  'Passwords do not match.': 'كلمتا المرور غير متطابقتين.',
  'Password must be at least 8 characters.': 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
  'Resetting...': 'جاري إعادة التعيين...',
  'Error resetting password': 'خطأ في إعادة تعيين كلمة المرور',
  'New code sent!': 'تم إرسال رمز جديد!',
  'Error resending code': 'خطأ في إعادة إرسال الرمز',
  'Code expired. Please request a new one.': 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.',
  'Verify Code': 'تأكيد الرمز',
  'Verifying...': 'جاري التحقق...',
  'Code verified!': 'تم التحقق من الرمز!',
  'Invalid code': 'رمز غير صالح',
  'Set New Password': 'تعيين كلمة مرور جديدة',
  'Your code was verified. Now set a new password for your account.': 'تم التحقق من الرمز. الآن قم بتعيين كلمة مرور جديدة لحسابك.',

  /* ── Toast / Confirm Messages ───────────────────────────── */
  'Approved!': 'تمت الموافقة!',
  'Rejected.': 'تم الرفض.',
  'Event approved!': 'تم اعتماد الحدث!',
  'Event rejected.': 'تم رفض الحدث.',
  'Event submitted for approval!': 'تم إرسال الحدث للاعتماد!',
  'Open to Sponsorship': 'متاح للرعاية',
  'Available for Sponsorship': 'متاح للرعاية',
  'Available for Exhibitions': 'متاح للمعارض',
  'Currently Not Available': 'غير متاح حالياً',
  'Not Available for Exhibitions': 'غير متاح للمعارض',
  'Request submitted!': 'تم إرسال الطلب!',
  'Assistant created!': 'تم إنشاء المساعد!',
  'Assistant deleted!': 'تم حذف المساعد!',
  'Failed to load events': 'فشل تحميل الأحداث',
  'Failed to delete assistant': 'فشل حذف المساعد',
  'Error creating assistant': 'خطأ في إنشاء المساعد',
  'User deleted': 'تم حذف المستخدم',
  'All notifications marked as read': 'تم تحديد جميع الإشعارات كمقروءة',
  'Registration successful! Redirecting...': 'تم التسجيل بنجاح! جاري التحويل...',
  'Document submitted successfully!': 'تم تقديم المستند بنجاح!',
  'Error re-uploading document.': 'خطأ في إعادة رفع المستند.',
  'Please select a document first.': 'يرجى اختيار مستند أولاً.',
  'No users found': 'لا يوجد مستخدمون',
  'Failed to load users': 'فشل تحميل المستخدمين',
  'View Verification File': 'عرض ملف التحقق',
  'N/A': 'غير متوفر',
  'Sponsor tier updated beautifully! ✨': 'تم تحديث مستوى الراعي بنجاح! ✨',
  'Error updating tier': 'خطأ في تحديث المستوى',
  'Company Name / Entity Name': 'اسم الشركة / الجهة',
  'Manager Full Name': 'اسم المدير الكامل',
  'No written comment': 'لا يوجد تعليق مكتوب',
  'Notifications': 'الإشعارات',
  'Mark all read': 'تحديد الكل كمقروء',
  'Just now': 'الآن',
  'm ago': 'دقيقة مضت',
  'h ago': 'ساعة مضت',
  'd ago': 'يوم مضى',
  'Exhibitions': 'المعارض',
  'Exhibition Management': 'إدارة المعارض',
  'Invite companies to showcase at your exhibitions': 'ادعُ الشركات للعرض في معارضك',
  'Available Companies': 'الشركات المتاحة',
  'Exhibition Applications': 'طلبات المعارض',
  'Invite to Exhibition': 'دعوة للمشاركة في معرض',
  'Select Exhibition Event': 'اختر فعالية المعرض',
  'Target Company': 'الشركة المستهدفة',
  'Invitation Message': 'رسالة الدعوة',
  'Tell the company why they should exhibit...': 'أخبر الشركة لماذا يجب أن تشارك كعارض...',
  'Invitation sent successfully!': 'تم إرسال الدعوة بنجاح!',
  'Send Invitation': 'إرسال الدعوة',
  'rejected': 'مرفوض',
  'of all events': 'من جميع الأحداث',
  'checked in': 'تم تسجيل دخولهم',
  'checked in out of': 'تم تسجيل دخولهم من أصل',
  'total tickets': 'إجمالي التذاكر',
  'Login failed': 'فشل تسجيل الدخول',
  'Network error': 'خطأ في الشبكة',

  /* ── Manager – Exhibition Page ───────────────────────────── */
  'Manage exhibitions, companies, booths & rankings': 'إدارة المعارض والشركات والمناصات والتصنيفات',
  'Available Companies': 'الشركات المتاحة',
  'Exhibitions': 'المعارض',
  'Exhibition Management': 'إدارة المعارض',
  'Applications': 'الطلبات',
  'Exhibition Layout': 'مخطط المعرض',
  'Zones & Booths Configuration': 'إعداد المناطق والمناصات',
  'Manage exhibition zones and available slots': 'إدارة مناطق المعرض والفتحات المتاحة',
  'Add Zone': 'إضافة منطقة',
  'Add New Zone': 'إضافة منطقة جديدة',
  'Zone Name': 'اسم المنطقة',
  'Create Zone': 'إنشاء المنطقة',
  'Assign Booth': 'تعيين منصة',
  'Assign Booth Location': 'تخصيص مكان المنصة',
  'Select Booth': 'اختر المنصة',
  'Save Assignment': 'حفظ التخصيص',
  'Add Booth': 'إضافة منصة',
  'Edit Booth': 'تعديل منصة',
  'Booth Number': 'رقم المنصة',
  'Batch Add': 'إضافة بالجملة',
  'Batch Generate Booths': 'توليد منصات بالجملة',
  'How many?': 'كم العدد؟',
  'Default Size': 'الحجم الافتراضي',
  'Generate Batch': 'توليد بالجملة',
  'No zones defined yet.': 'لا توجد مناطق محددة بعد.',
  'Create First Zone': 'إنشاء أول منطقة',
  'No exhibition applications yet': 'لا توجد طلبات معارض بعد',
  'Invite companies from the list above to get started.': 'ادعُ الشركات من القائمة أعلاه للبدء.',
  'No available companies right now.': 'لا توجد شركات متاحة الآن.',
  'Search company...': 'بحث عن شركة...',
  'Category': 'الفئة',
  'Booth': 'المنصة',
  'Past': 'منتهي',
  'Awaiting Response': 'بانتظار الرد',
  'Invite': 'دعوة',
  'Select an exhibition event…': 'اختر فعالية معرض...',
  'Invitation Message': 'رسالة الدعوة',
  'Tell the company why they should exhibit...': 'أخبر الشركة لماذا يجب أن تشارك كعارض...',
  'Invitation sent successfully!': 'تم إرسال الدعوة بنجاح!',
  'Send Invitation': 'إرسال الدعوة',
  'Invite to Exhibition': 'دعوة للمشاركة في معرض',
  'Invitation Message': 'رسالة الدعوة',
  'Assigning for:': 'التعيين لـ:',
  'Error sending invitation': 'خطأ في إرسال الدعوة',
  'Please select an exhibition event': 'يرجى اختيار فعالية معرض',
  'Preliminary acceptance successful! Contract negotiation started.': 'تم القبول المبدئي بنجاح! بدأت مفاوضات العقد.',
  'Zone deleted': 'تم حذف المنطقة',
  'Failed to load layout': 'فشل تحميل المخطط',

  /* ── Manager – Sponsorship Page ──────────────────────────── */
  'Sponsorship Requests': 'طلبات الرعاية',
  'Request sponsors for your events': 'طلب رعاة لفعالياتك',
  'Available Sponsors': 'الرعاة المتاحون',
  'Your Sponsorship Requests': 'طلبات الرعاية الخاصة بك',
  'Sponsor (Sector)': 'الراعي (القطاع)',
  'Tier': 'المستوى',
  'Sector': 'القطاع',
  'Request Sponsorship': 'طلب رعاية',
  'Target Sponsor': 'الراعي المستهدف',
  'Message to Sponsors': 'رسالة للرعاة',
  'Describe sponsorship opportunity…': 'صف فرصة الرعاية...',
  'Submit Request': 'إرسال الطلب',
  'Accept Sponsorship': 'قبول الرعاية',
  'Select Sponsor Tier': 'اختر مستوى الراعي',
  'Edit Sponsor Tier': 'تعديل مستوى الراعي',
  'Select New Sponsor Tier': 'اختر مستوى الراعي الجديد',
  'Unranked': 'غير مصنّف',
  'Diamond Sponsor': 'راعي ماسي',
  'Gold Sponsor': 'راعي ذهبي',
  'Silver Sponsor': 'راعي فضي',
  'Bronze Sponsor': 'راعي برونزي',
  '✅ Confirm Acceptance': '✅ تأكيد القبول',
  'Two sponsors cannot have the same tier.': 'لا يمكن لراعيين أن يكون لهما نفس المستوى.',
  'Choose a tier or leave unranked. Two sponsors cannot have the same tier.': 'اختر مستوى أو اتركه غير مصنّف. لا يمكن لراعيين أن يكون لهما نفس المستوى.',
  'This tier will be updated immediately in all event details.': 'سيتم تحديث هذا المستوى فوراً في جميع تفاصيل الفعالية.',
  'Locked': 'مغلق',
  'Edit Tier': 'تعديل المستوى',
  'Request': 'طلب',
  'No sponsorship requests yet': 'لا توجد طلبات رعاية بعد',
  'No available sponsors right now.': 'لا يوجد رعاة متاحون الآن.',
  'Preliminary acceptance successful! Contract negotiation started. 📝': 'تم القبول المبدئي بنجاح! بدأت مفاوضات العقد. 📝',
  'Request rejected.': 'تم رفض الطلب.',
  'Please select an event': 'يرجى اختيار فعالية',
  'Sponsorship request submitted!': 'تم إرسال طلب الرعاية!',
  'Error sending invitation': 'خطأ في إرسال الدعوة',
  'Error sending sponsorship request': 'خطأ في إرسال طلب الرعاية',
  'sponsor': 'راعي',
  'Sponsor': 'الراعي',
  'Select your event…': 'اختر فعاليتك...',
  'Sponsorship Message': 'رسالة الرعاية',
  'Contract Negotiation': 'تفاوض العقد',
  'Negotiate terms with the company': 'تفاوض على البنود مع الشركة',
  'Negotiate terms with the sponsor': 'تفاوض على البنود مع الراعي',
  'Review Offer': 'مراجعة العرض',
  'View Profile': 'عرض الملف',
  'General': 'عام',

  /* ── Dynamic Notifications ──────────────────────────────── */
  'New Event Pending': 'حدث جديد قيد الانتظار',
  'Event "': 'الحدث "',
  '" was submitted by ': '" تم إرساله بواسطة ',
  ' and needs your approval.': ' ويحتاج إلى موافقتك.',
  'Event Approved ✅': 'تم اعتماد الحدث ✅',
  'Your event "': 'الحدث الخاص بك "',
  '" has been approved and is now live!': '" تم اعتماده وهو الآن متاح للعامة!',
  'Event Rejected ❌': 'تم رفض الحدث ❌',
  '" has been rejected': '" تم رفضه',
  'New Rating Received': 'تم تلقي تقييم جديد',
  '" received a ': '" حصل على تقييم ',
  '-star rating ': ' نجوم ',
  ' has applied as a Partner (': ' قدم كشريك (',
  ') and requires document verification.': ') ويحتاج إلى التحقق من المستندات.',
  'Your Partner Account has been APPROVED! 🎉': 'تمت الموافقة على حساب الشريك الخاص بك! 🎉',
  'Your Partner application has been REJECTED.': 'تم رفض طلب الشريك الخاص بك.',
  'Your Partner application requires changes.': 'طلب الشريك الخاص بك يحتاج إلى تعديلات.',
  'New ticket purchase for "': 'شراء تذكرة جديدة للحدث "',
  '" (': '" (',
  ' tickets).': ' تذاكر).',
  'Sponsorship Request from ': 'طلب رعاية من ',
  'Sponsorship Approved! 🎉': 'تمت الموافقة على الرعاية! 🎉',
  'Sponsorship Request Denied': 'تم رفض طلب الرعاية',
  'Sponsorship Tier Assigned: ': 'تم تخصيص مستوى الرعاية: ',
  'Sponsorship Cancelled': 'تم إلغاء الرعاية',
  'You received a sponsorship invitation for "': 'تلقيت دعوة رعاية للحدث "',
  '" from ': '" من ',
  ' sent a sponsorship request for "': ' أرسل طلب رعاية للحدث "',
  'Your sponsorship request for "': 'طلب الرعاية الخاص بك للحدث "',
  '" has been accepted.': '" تم قبوله.',
  '" has been rejected.': '" تم رفضه.',
  ' has accepted your sponsorship invitation for "': ' قام بقبول دعوة الرعاية الخاصة بك للحدث "',
  ' has rejected your sponsorship invitation for "': ' قام برفض دعوة الرعاية الخاصة بك للحدث "',
  'Your verification was approved!': 'تمت الموافقة على التحقق الخاص بك!',
  'Your verification was rejected: ': 'تم رفض التحقق الخاص بك: ',
  'The admin requested changes on your verification: ': 'طلب الإدارة تعديلات على التحقق الخاص بك: ',
  '-star rating from ': ' نجوم من ',
  '".': '".',

  /* ── Event Type Tooltips ────────────────────────────────── */
  'Business Conference\nTech Conference\nMedical Conference\nAcademic Conference\nStartup Conference': 'مؤتمر أعمال\nمؤتمر تقني\nمؤتمر طبي\nمؤتمر أكاديمي\nمؤتمر الشركات الناشئة',
  'Educational Seminar\nScientific Seminar\nCultural Seminar\nFinancial Seminar\nManagement Seminar': 'ندوة تعليمية\nندوة علمية\nندوة ثقافية\nندوة مالية\nندوة إدارية',
  'Programming Workshop\nDesign Workshop\nWriting Workshop\nArt Workshop\nStrategy Workshop': 'ورشة برمجة\nورشة تصميم\nورشة كتابة\nورشة فنون\nورشة تخطيط استراتيجي',
  'Language Course\nLeadership Course\nMarketing Course\nHR Course\nIT Course': 'دورة لغات\nدورة قيادة\nدورة تسويق\nدورة موارد بشرية\nدورة تقنية معلومات',
  'Music Concerts\nFestivals\nStand-up Comedy\nMagic Shows\nGaming Events': 'حفلات موسيقية\nمهرجانات\nكوميديا ارتجالية\nعروض سحرية\nفعاليات ألعاب',
  'Research Forum\nPhysics Forum\nMedical Forum\nEngineering Forum\nAI Forum': 'ملتقى أبحاث\nملتقى فيزياء\nملتقى طبي\nملتقى هندسي\nملتقى ذكاء اصطناعي',
  'Football Tournaments\nMarathons\nE-Sports\nYoga Classes\nMartial Arts': 'بطولات كرة قدم\nماراثون\nرياضات إلكترونية\nجلسات يوغا\nفنون قتالية',
  'Hackathons\nTech Expos\nCloud Computing\nCybersecurity\nDeveloper Meetups': 'هاكاثون\nمعارض تقنية\nحوسبة سحابية\nأمن سيبراني\nملتقيات المطورين',
  'Networking Events\nCharity Events\nCommunity Gatherings\nAlumni Meetings\nGala Dinners': 'لقاءات تعارف\nفعاليات خيرية\nتجمعات مجتمعية\nاجتماعات خريجين\nحفلات عشاء',

  /* ── Review Modal & Buttons ─────────────────────────────── */
  'Review': 'مراجعة',
  'Send Review': 'إرسال مراجعة',
  'Select fields that need changes': 'حدد الحقول التي تحتاج إلى تعديل',
  'Review Message': 'رسالة المراجعة',
  'e.g. Please upload a clearer competent authority document and increase the capacity...': 'مثال: يرجى رفع مستند موافقة الجهة المختصة بشكل أكثر وضوحاً وزيادة السعة القصوى...',
  'Failed to load dashboard data': 'فشل في تحميل بيانات لوحة التحكم',
  'Banner': 'الغلاف',
  'Ministry Doc': 'مستند الوزارة',
  'Sponsored': 'المدعومة',
  'Sponsored Events History': 'سجل الأحداث المدعومة',

  /* ── Notification Translations ───────────────────────────── */
  'New Partner Registration 🎉': 'طلب تسجيل شريك جديد 🎉',
  'A new Company ': 'تم تسجيل شركة جديدة ',
  'A new Sponsor ': 'تم تسجيل راعٍ جديد ',
  'A new Event Manager ': 'تم تسجيل مدير فعاليات جديد ',
  ' has registered and is waiting for verification.': ' وبانتظار التحقق.',
  'Verification Document Updated': 'تم تحديث وثائق التحقق',
  'Document Update Request 📄': 'طلب تحديث الوثائق 📄',
  'Partner ': 'الشريك ',
  ' has resubmitted verification documents and requires review.': ' قام بإعادة تقديم وثائق التحقق وهي بانتظار المراجعة.',
  ' has submitted updated documents for review: ': ' قام بتقديم وثائق محدثة للمراجعة: ',
  'scanned_today': 'تم المسح اليوم',
  'not_scanned_today': 'لم يمسح اليوم',
  'days_attended': 'أيام الحضور',
  'Scanned Today': 'تم المسح اليوم',
  'Not Scanned Today': 'لم يمسح اليوم',
  'Days Attended': 'أيام الحضور',

  /* ── AI Description Suggestion ──────────────────────────── */
  'AI Assistant': 'مساعد ذكي',
  'Want AI to generate a description based on your title?': 'هل تريد أن يكتب الذكاء الاصطناعي وصفاً بناءً على العنوان؟',
  '✨ Generate': '✨ توليد',
  'No thanks': 'لا شكراً',
  'Generating description...': 'جاري توليد الوصف...',
  'Description Generated!': 'تم توليد الوصف!',
  'The description has been filled in below. Feel free to edit it.': 'تم تعبئة الوصف أدناه. يمكنك تعديله كما تشاء.',
};

/* ─────────────────────────────────────────────────────────────────
   Reverse Dictionary (Arabic → English) – auto-built from I18N_AR
   ───────────────────────────────────────────────────────────────── */
for (const [en, ar] of Object.entries(I18N_AR)) {
  I18N_EN[ar] = en;
}

/* ─────────────────────────────────────────────────────────────────
   Event Type display helper
   Maps Arabic DB values to English display names.
   ───────────────────────────────────────────────────────────────── */
const EVENT_TYPE_MAP = {
  'مؤتمر':        { en: 'Conference',       icon: '🎙️' },
  'ندوة':         { en: 'Seminar',          icon: '📖' },
  'ورشة عمل':     { en: 'Workshop',         icon: '🔧' },
  'دورة تدريبية': { en: 'Training Course',  icon: '🎓' },
  'ترفيه':        { en: 'Entertainment',     icon: '🎭' },
  'ملتقى علمي':   { en: 'Scientific Forum',  icon: '🔬' },
  'رياضة':        { en: 'Sports',           icon: '⚽' },
  'تقنية':        { en: 'Technology',        icon: '💻' },
  'اجتماعية':     { en: 'Social',           icon: '🤝' },
};

/**
 * Translate an event type (stored as Arabic in DB) to the current language.
 * tType('مؤتمر') → 'Conference' (en) or 'مؤتمر' (ar)
 */
function tType(arabicType) {
  if (!arabicType) return arabicType;
  const entry = EVENT_TYPE_MAP[arabicType];
  if (!entry) return arabicType;
  return getLang() === 'ar' ? arabicType : entry.en;
}

/* ─────────────────────────────────────────────────────────────────
   Core API
   ───────────────────────────────────────────────────────────────── */

let cachedSortedAR = null;
let cachedSortedEN = null;

function getLang() {
  return localStorage.getItem('lang') || 'en';
}

/**
 * Translate a key. Returns Arabic if lang=ar, otherwise the original key.
 */
function t(key) {
  const lang = getLang();
  const dict = lang === 'ar' ? I18N_AR : I18N_EN;
  return (dict && dict[key]) ? dict[key] : key;
}

/**
 * Translate an arbitrary string by applying exact and partial dictionary matches.
 */
function translateText(text) {
  if (!text || typeof text !== 'string') return text;
  const lang = getLang();
  
  const dict = lang === 'ar' ? I18N_AR : I18N_EN;
  if (!dict) return text;

  // Exact match first
  if (dict[text]) return dict[text];

  // If English and the key wasn't in dict, don't do partial replacement unless we have custom EN rules
  if (lang === 'en' && Object.keys(I18N_EN).length === 0) return text;

  let updated = text;
  let changed = false;

  // Protect email addresses from translation
  const emailRegex = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
  const emails = [];
  const updatedWithNoEmails = updated.replace(emailRegex, (match) => {
    emails.push(match);
    return `__EMAIL_TOKEN_${emails.length - 1}__`;
  });
  const hasEmails = emails.length > 0;
  if (hasEmails) {
    updated = updatedWithNoEmails;
  }

  const hasEventHub = updated.includes('EventHub');
  if (hasEventHub) {
    updated = updated.split('EventHub').join('__EHUB_TOKEN__');
  }

  // Use cached sorted entries for performance
  let entries;
  if (lang === 'ar') {
    if (!cachedSortedAR) cachedSortedAR = Object.entries(I18N_AR).sort((a, b) => b[0].length - a[0].length);
    entries = cachedSortedAR;
  } else {
    if (!cachedSortedEN) cachedSortedEN = Object.entries(I18N_EN).sort((a, b) => b[0].length - a[0].length);
    entries = cachedSortedEN;
  }

  for (let i = 0; i < entries.length; i++) {
    const [key, val] = entries[i];
    if (updated.includes(key)) {
      updated = updated.split(key).join(val);
      changed = true;
    }
  }

  if (hasEventHub) {
    updated = updated.split('__EHUB_TOKEN__').join('EventHub');
  }

  // Restore protected email addresses
  if (hasEmails) {
    for (let i = 0; i < emails.length; i++) {
      updated = updated.split(`__EMAIL_TOKEN_${i}__`).join(emails[i]);
    }
  }

  return changed ? updated : text;
}

/**
 * Change language and reload.
 */
function setLanguage(lang) {
  localStorage.setItem('lang', lang);
  location.reload();
}

/* ─────────────────────────────────────────────────────────────────
   Direction + Font application (run IMMEDIATELY, before render)
   ───────────────────────────────────────────────────────────────── */
function applyDirection() {
  const lang = getLang();
  const html = document.documentElement;
  if (lang === 'ar') {
    html.setAttribute('lang', 'ar');
    html.setAttribute('dir', 'rtl');
  } else {
    html.setAttribute('lang', 'en');
    html.removeAttribute('dir');
  }
}

function injectRTLStyles() {
  if (getLang() !== 'ar') return;
  if (document.getElementById('i18n-rtl')) return;

  /* ── Load Cairo font ─────────────────────────────── */
  if (!document.getElementById('cairo-font')) {
    const link  = document.createElement('link');
    link.id     = 'cairo-font';
    link.rel    = 'stylesheet';
    link.href   = 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap';
    document.head.appendChild(link);
  }

  const s = document.createElement('style');
  s.id = 'i18n-rtl';
  s.textContent = `
    /* ════════════════════════════════════════════════════
       GLOBAL & LAYOUT
    ════════════════════════════════════════════════════ */
    html[dir="rtl"], [dir="rtl"] body { 
        direction: rtl !important; 
        font-family: 'Cairo', 'Segoe UI', sans-serif !important; 
        text-align: right !important;
    }
    [dir="rtl"] * { font-family: 'Cairo', 'Segoe UI', sans-serif !important; }

    [dir="rtl"] .sidebar { 
        left: auto !important; 
        right: 0 !important; 
        border-right: none !important; 
        border-left: 1px solid var(--border) !important; 
        direction: rtl !important;
    }
    [dir="rtl"] .main-content { 
        margin-left: 0 !important; 
        margin-right: var(--sidebar-w) !important; 
        direction: rtl !important;
    }

    /* ════════════════════════════════════════════════════
       SIDEBAR INTERNALS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .nav-section-label { text-align: right !important; padding-right: 20px !important; }
    [dir="rtl"] .nav-item { 
        flex-direction: row !important; /* Keep order, RTL will handle placement */
        justify-content: flex-start !important; 
        text-align: right !important;
        gap: 12px !important;
    }
    [dir="rtl"] .nav-item .nav-icon { 
        margin-right: 0 !important; 
        margin-left: 0 !important; 
    }
    
    /* Specific fix for the injected language toggle */
    [dir="rtl"] #injected-lang-toggle {
        flex-direction: row !important;
    }
    [dir="rtl"] #injected-lang-toggle > div {
        flex-direction: row !important;
        gap: 12px !important;
    }

    [dir="rtl"] .sidebar-user { flex-direction: row !important; }
    [dir="rtl"] .user-info { text-align: right !important; margin-right: 10px !important; margin-left: 0 !important; }
    
    [dir="rtl"] .topbar { flex-direction: row !important; }
    [dir="rtl"] .page-title, [dir="rtl"] .page-subtitle { text-align: right !important; }

    /* ════════════════════════════════════════════════════
       FORMS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .form-group       { text-align: right; }
    [dir="rtl"] .form-label       { display: block; text-align: right; }
    [dir="rtl"] label             { display: block; text-align: right; }
    [dir="rtl"] input:not([type="checkbox"]):not([type="radio"]),
    [dir="rtl"] textarea,
    [dir="rtl"] select            { text-align: right; direction: rtl; }
    [dir="rtl"] input::placeholder,
    [dir="rtl"] textarea::placeholder { text-align: right; }
    /* Fix inline search/select bars */
    [dir="rtl"] input[style*="padding-left"], 
    [dir="rtl"] select[style*="padding-left"] { padding-right: 36px !important; padding-left: 12px !important; }
    [dir="rtl"] select[style*="padding-left:32px"] { padding-right: 32px !important; padding-left: 12px !important; }
    [dir="rtl"] [style*="left:10px"]         { left: auto !important; right: 10px !important; }
    [dir="rtl"] [style*="left: 10px"]        { left: auto !important; right: 10px !important; }
    [dir="rtl"] [style*="padding-right: 20px"] { padding-right: 0 !important; padding-left: 20px !important; }

    /* ════════════════════════════════════════════════════
       CARDS & TABLES
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .card,
    [dir="rtl"] .profile-card     { text-align: right; }
    [dir="rtl"] .avatar-upload    { text-align: center !important; }
    [dir="rtl"] table             { direction: rtl; }
    [dir="rtl"] th, [dir="rtl"] td { text-align: right; }

    /* ════════════════════════════════════════════════════
       MODALS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .modal-body       { text-align: right; }
    [dir="rtl"] .ed-title-row     { text-align: right; }
    [dir="rtl"] .ed-info-card     { text-align: right; }
    [dir="rtl"] .ed-close-btn     { left: 14px; right: auto; }

    /* ════════════════════════════════════════════════════
       EVENT CARDS (card grid)
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .event-card-header,
    [dir="rtl"] .event-card-footer,
    [dir="rtl"] .event-card-body  { text-align: right; }

    /* ════════════════════════════════════════════════════
       ANALYTICS STATS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .an-stat-label,
    [dir="rtl"] .an-stat-value,
    [dir="rtl"] .an-stat-sub      { text-align: right; }
    [dir="rtl"] .an-top-table th  { text-align: right; }
    [dir="rtl"] .an-pending-item  { text-align: right; }
    [dir="rtl"] .an-rate-info h4,
    [dir="rtl"] .an-rate-info p   { text-align: right; }

    /* ════════════════════════════════════════════════════
       TOASTS & BADGES
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .toast            { direction: rtl; text-align: right; }
    [dir="rtl"] .badge,
    [dir="rtl"] .status-badge     { direction: ltr; display: inline-block; }

    /* ════════════════════════════════════════════════════
       MISC INLINE STYLE FLIPS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] [style*="text-align: right"] { text-align: left !important; }
  `;
  document.head.appendChild(s);
}

/* ─────────────────────────────────────────────────────────────────
   Text Translation
   ───────────────────────────────────────────────────────────────── */

/**
 * Translate a single DOM text node if it matches a dictionary entry.
 * Handles full-string matches and trims surrounding whitespace.
 */
function translateNode(node) {
  if (!node || !node.textContent) return;
  
  // Skip if inside an excluded container
  let p = node.parentElement;
  while (p) {
    if (p.classList.contains('i18n-skip') || p.hasAttribute('data-no-translate') || SKIP_TAGS.has(p.tagName)) return;
    p = p.parentElement;
  }

  const original = node.textContent;
  const trimmed  = original.trim();
  if (!trimmed) return;

  // Normalize whitespace (replace newlines and multiple spaces with a single space) for key matching
  const normalized = trimmed.replace(/\s+/g, ' ');
  const translated = translateText(normalized);
  if (translated !== normalized) {
    node.textContent = original.replace(trimmed, translated);
  } else {
    // Fallback to exact match on trimmed text if normalized lookup fails
    const translatedTrimmed = translateText(trimmed);
    if (translatedTrimmed !== trimmed) {
      node.textContent = original.replace(trimmed, translatedTrimmed);
    }
  }
}

/** Translate placeholder / title attributes on a single element */
function translateAttrs(el) {
  const lang = getLang();
  const dict = lang === 'ar' ? I18N_AR : I18N_EN;
  if (!dict) return;
  ['placeholder', 'title'].forEach(attr => {
    const val = el.getAttribute(attr);
    if (val) {
      const trans = translateText(val);
      if (trans !== val) el.setAttribute(attr, trans);
    }
  });
}

const SKIP_TAGS = new Set(['SCRIPT','STYLE','NOSCRIPT','CODE','PRE','TEMPLATE']);

/**
 * Walk through a subtree and translate all text nodes + attributes.
 */
function translateSubtree(root) {
  const walker = document.createTreeWalker(
    root,
    NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT,
    {
      acceptNode(node) {
        if (node.nodeType === Node.ELEMENT_NODE) {
          if (SKIP_TAGS.has(node.tagName)) return NodeFilter.FILTER_REJECT;
          if (node.classList.contains('i18n-skip') || node.hasAttribute('data-no-translate')) return NodeFilter.FILTER_REJECT;
          return NodeFilter.FILTER_SKIP;
        }
        const parent = node.parentElement;
        if (!parent || SKIP_TAGS.has(parent.tagName)) return NodeFilter.FILTER_REJECT;
        if (parent.classList.contains('i18n-skip') || parent.hasAttribute('data-no-translate')) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    }
  );

  const textNodes = [];
  let current;
  while ((current = walker.nextNode())) textNodes.push(current);
  textNodes.forEach(translateNode);

  /* Also handle element attributes */
  const allEls = root.nodeType === Node.ELEMENT_NODE ? [root, ...root.querySelectorAll('*')] : root.querySelectorAll ? [...root.querySelectorAll('*')] : [];
  allEls.forEach(translateAttrs);
}

/** Full DOM pass on document.body + title */
function translateDOM() {
  translateSubtree(document.body);
  
  // Also translate page title
  const tTitle = t(document.title);
  if (tTitle !== document.title) document.title = tTitle;
}

/* ─────────────────────────────────────────────────────────────────
   MutationObserver – catch dynamically injected content
   ───────────────────────────────────────────────────────────────── */
let _observer = null;

function startObserver() {
  if (_observer) return;

  _observer = new MutationObserver(mutations => {
    mutations.forEach(m => {
      m.addedNodes.forEach(node => {
        if (node.nodeType === Node.TEXT_NODE) {
          translateNode(node);
        } else if (node.nodeType === Node.ELEMENT_NODE) {
          if (SKIP_TAGS.has(node.tagName)) return;
          if (node.classList.contains('i18n-skip') || node.hasAttribute('data-no-translate')) return;
          translateSubtree(node);
        }
      });
    });
  });

  _observer.observe(document.body, { childList: true, subtree: true });
}

/* ─────────────────────────────────────────────────────────────────
   Main Entry Point
   ───────────────────────────────────────────────────────────────── */
applyDirection();   // ← runs synchronously, before any render

function injectLanguageToggle() {
  if (document.getElementById('injected-lang-toggle')) return;

  const sidebarLogo = document.querySelector('.sidebar-logo');
  const authCard = document.querySelector('.auth-card');
  
  if (!sidebarLogo && !authCard) return;

  const isAr = getLang() === 'ar';
  const toggleBtn = document.createElement('button');
  toggleBtn.id = 'injected-lang-toggle';
  toggleBtn.onclick = () => setLanguage(isAr ? 'en' : 'ar');
  toggleBtn.title = isAr ? 'Switch Language' : 'تبديل اللغة';
  
  // Style for the button
  const style = document.createElement('style');
  style.textContent = `
    #injected-lang-toggle {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      color: rgba(255, 255, 255, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
      padding: 0;
      flex-shrink: 0;
      z-index: 1000;
    }
    #injected-lang-toggle:hover {
      background: rgba(110, 64, 242, 0.15);
      border-color: rgba(110, 64, 242, 0.3);
      color: var(--accent2);
      transform: scale(1.05);
    }
    #injected-lang-toggle svg {
      width: 18px;
      height: 18px;
    }
    ${sidebarLogo ? `
      #injected-lang-toggle { margin-left: auto; }
    ` : ''}
    ${authCard ? `
      #injected-lang-toggle {
        position: absolute;
        top: 20px;
        right: 20px;
      }
    ` : ''}
  `;
  document.head.appendChild(style);

  toggleBtn.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20"/><path d="M12 2a14.5 14.5 0 0 1 0 20"/><path d="M2 12h20"/>
    </svg>
  `;
  
  if (sidebarLogo) sidebarLogo.appendChild(toggleBtn);
  else if (authCard) authCard.appendChild(toggleBtn);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    injectLanguageToggle();
    injectRTLStyles();
    translateDOM();
    startObserver();
  });
} else {
  injectLanguageToggle();
  injectRTLStyles();
  translateDOM();
  startObserver();
}
