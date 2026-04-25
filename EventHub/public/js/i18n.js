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
   Translation Dictionary  (English key → Arabic value)
   ───────────────────────────────────────────────────────────────── */
const I18N_AR = {
  /* ── Page Titles ──────────────────────────────────────── */
  'My Events – EventHub Manager':     'أحداثي – EventHub',
  'My Analytics – EventHub Manager':  'تحليلاتي – EventHub',
  'Edit Profile - EventHub':          'تعديل الملف الشخصي – EventHub',
  'My Profile – EventHub':            'ملفي الشخصي – EventHub',
  'Events – EventHub Admin':          'الأحداث – الأدمن',
  'Browse Events – EventHub Sponsor': 'تصفح الأحداث – EventHub',

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

  /* ── Filter / Sort Controls ───────────────────────────── */
  'All Status':  'الكل',
  'Pending':     'قيد الانتظار',
  'Approved':    'معتمد',
  'Rejected':    'مرفوض',

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
  'Sign Out':          'تسجيل الخروج',


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
  'Rejected':              'مرفوض',
  'REJECTED':              'مرفوض',

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
  'No pending verification requests.': 'لا توجد طلبات تحقق قيد الانتظار.',
  '🔄 Resubmitted': '🔄 أُعيد تقديمه',
  '✨ New Request': '✨ طلب جديد',
  ' (Waiting for Partner)': ' (بانتظار الشريك)',

  /* ── Sponsorship Page ───────────────────────────────────── */
  'Available Sponsors': 'الرعاة المتاحون',
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
  '📄 Agreement': '📄 الاتفاقية',
  'TBA': 'يُحدد لاحقاً',

  /* ── Create Event & Profile ───────────────────────────── */
  'Create New Event': 'إنشاء حدث جديد',
  'Event Title': 'عنوان الحدث',
  'Tech Summit 2026': 'قمة التقنية 2026',
  'Description': 'الوصف',
  'Describe your event…': 'صف حدثك...',
  'Event Type': 'نوع الحدث',
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
  'Please upload your Commercial Register, License, or Authorization Letter (PDF/JPG/PNG up to 5MB).': 'يرجى رفع السجل التجاري أو الرخصة أو خطاب التفويض (PDF/JPG/PNG حتى 5 ميجا).',
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

  /* ── Toast / Confirm Messages ───────────────────────────── */
  'Approved!': 'تمت الموافقة!',
  'Rejected.': 'تم الرفض.',
  'Event approved!': 'تم اعتماد الحدث!',
  'Event rejected.': 'تم رفض الحدث.',
  'Event submitted for approval!': 'تم إرسال الحدث للاعتماد!',
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
  'rejected': 'مرفوض',
  'of all events': 'من جميع الأحداث',
  'checked in': 'تم تسجيل دخولهم',
  'checked in out of': 'تم تسجيل دخولهم من أصل',
  'total tickets': 'إجمالي التذاكر',
  'Login failed': 'فشل تسجيل الدخول',
  'Network error': 'خطأ في الشبكة',

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
};

/* ─────────────────────────────────────────────────────────────────
   Reverse Dictionary (Arabic → English) – auto-built from I18N_AR
   ───────────────────────────────────────────────────────────────── */
const I18N_EN = {};
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

function getLang() {
  return localStorage.getItem('lang') || 'en';
}

/**
 * Translate a key. Returns Arabic if lang=ar, otherwise the original key.
 * Also handles Arabic→English reverse lookup when lang=en.
 * Use: t('My Events') → 'أحداثي'
 */
function t(key) {
  const lang = getLang();
  if (lang === 'ar' && I18N_AR[key]) return I18N_AR[key];
  if (lang === 'en' && I18N_EN[key]) return I18N_EN[key];
  return key;
}

/**
 * Translate an arbitrary string by applying exact and partial dictionary matches.
 * Useful for dynamic messages containing variables (e.g. "Your event X is approved").
 */
function translateText(text) {
  if (!text) return text;
  const lang = getLang();
  const dict = lang === 'ar' ? I18N_AR : I18N_EN;

  if (dict[text]) return dict[text];

  let updated = text;
  let changed = false;

  const hasEventHub = updated.includes('EventHub');
  if (hasEventHub) {
    updated = updated.split('EventHub').join('__EHUB_TOKEN__');
  }

  const entries = Object.entries(dict).sort((a, b) => b[0].length - a[0].length);
  for (const [key, val] of entries) {
    if (updated.includes(key)) {
      updated = updated.split(key).join(val);
      changed = true;
    }
  }

  if (hasEventHub) {
    updated = updated.split('__EHUB_TOKEN__').join('EventHub');
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
  const original = node.textContent;
  const trimmed  = original.trim();
  if (!trimmed) return;

  const lang = getLang();
  const dict = lang === 'ar' ? I18N_AR : I18N_EN;

  /* 1️⃣  Try exact match first */
  if (dict[trimmed]) {
    node.textContent = original.replace(trimmed, dict[trimmed]);
    return;
  }

  /* 2️⃣  Try replacing any known key that appears inside the text.
          This handles "📊 Dashboard" → "📊 لوحة التحكم"           */
  let updated = trimmed;
  let changed  = false;

  // Protect the project name "EventHub" from partial dictionary matches (like 'Event')
  const hasEventHub = updated.includes('EventHub');
  if (hasEventHub) {
    updated = updated.split('EventHub').join('__EHUB_TOKEN__');
  }

  // Sort entries by length descending to prevent partial translations
  const entries = Object.entries(dict).sort((a, b) => b[0].length - a[0].length);
  for (const [key, val] of entries) {
    if (updated.includes(key)) {
      updated = updated.split(key).join(val); // Replace all occurrences
      changed = true;
    }
  }

  if (hasEventHub) {
    updated = updated.split('__EHUB_TOKEN__').join('EventHub');
    changed = true;
  }

  if (changed && updated !== trimmed) {
    node.textContent = original.replace(trimmed, updated);
  }
}

/** Translate placeholder / title attributes on a single element */
function translateAttrs(el) {
  const lang = getLang();
  const dict = lang === 'ar' ? I18N_AR : I18N_EN;
  ['placeholder', 'title'].forEach(attr => {
    const val = el.getAttribute(attr);
    if (val && dict[val]) el.setAttribute(attr, dict[val]);
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
        } else if (node.nodeType === Node.ELEMENT_NODE && !SKIP_TAGS.has(node.tagName)) {
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
