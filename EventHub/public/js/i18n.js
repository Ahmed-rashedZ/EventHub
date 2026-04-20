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
  'Soonest First':   'الأقرب زمناً',
  'Farthest First':  'الأبعد زمناً',
  'Alphabetical':    'ترتيب أبجدي',
  'Live Now':        'الحالية الآن',
  'Ended':           'المنتهية',

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
  'Confirm Rejection': 'تأكيد الرفض',
  'View Public Profile': 'عرض الملف العام',
  'Save Information':  'حفظ المعلومات',
  'Update Security Settings': 'تحديث إعدادات الأمان',

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
  '📥 Received': '📥 مُستلم',
  '📤 Sent': '📤 مُرسل',
  '❌ Decline': '❌ رفض',
  '⏳ Awaiting Event Manager to respond': '⏳ بانتظار رد مدير الحدث',
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
};

/* ─────────────────────────────────────────────────────────────────
   Core API
   ───────────────────────────────────────────────────────────────── */

function getLang() {
  return localStorage.getItem('lang') || 'en';
}

/**
 * Translate a key. Returns Arabic if lang=ar, otherwise the original key.
 * Use: t('My Events') → 'أحداثي'
 */
function t(key) {
  return (getLang() === 'ar' && I18N_AR[key]) ? I18N_AR[key] : key;
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
    /* Fix inline search bars */
    [dir="rtl"] input[style*="padding-left"] { padding-right: 36px !important; padding-left: 12px !important; }
    [dir="rtl"] [style*="left:10px"]         { left: auto !important; right: 10px !important; }
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

  /* 1️⃣  Try exact match first */
  if (I18N_AR[trimmed]) {
    node.textContent = original.replace(trimmed, I18N_AR[trimmed]);
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
  const entries = Object.entries(I18N_AR).sort((a, b) => b[0].length - a[0].length);
  for (const [en, ar] of entries) {
    if (updated.includes(en)) {
      updated = updated.split(en).join(ar); // Replace all occurrences
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
  ['placeholder', 'title'].forEach(attr => {
    const val = el.getAttribute(attr);
    if (val && I18N_AR[val]) el.setAttribute(attr, I18N_AR[val]);
  });
}

const SKIP_TAGS = new Set(['SCRIPT','STYLE','NOSCRIPT','CODE','PRE','TEMPLATE']);

/**
 * Walk through a subtree and translate all text nodes + attributes.
 */
function translateSubtree(root) {
  if (getLang() !== 'ar') return;

  const walker = document.createTreeWalker(
    root,
    NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT,
    {
      acceptNode(node) {
        if (node.nodeType === Node.ELEMENT_NODE) {
          if (SKIP_TAGS.has(node.tagName)) return NodeFilter.FILTER_REJECT;
          return NodeFilter.FILTER_SKIP;
        }
        const parent = node.parentElement;
        if (!parent || SKIP_TAGS.has(parent.tagName)) return NodeFilter.FILTER_REJECT;
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

/** Full DOM pass on document.body */
function translateDOM() {
  if (getLang() !== 'ar') return;
  translateSubtree(document.body);
}

/* ─────────────────────────────────────────────────────────────────
   MutationObserver – catch dynamically injected content
   ───────────────────────────────────────────────────────────────── */
let _observer = null;

function startObserver() {
  if (getLang() !== 'ar') return;
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
  const nav = document.querySelector('.sidebar-nav');
  if (nav && !document.getElementById('injected-lang-toggle')) {
      const langToggle = document.createElement('a');
      langToggle.id = 'injected-lang-toggle';
      langToggle.className = 'nav-item';
      langToggle.href = 'javascript:void(0)';
      langToggle.onclick = () => setLanguage(getLang() === 'ar' ? 'en' : 'ar');
      langToggle.style.cssText = 'display: flex; justify-content: space-between; align-items: center; cursor: pointer; text-decoration: none; padding: 9px 12px;';
      
      const l = getLang() === 'ar' ? 'العربية' : 'English';
      const label = getLang() === 'ar' ? 'اللغة' : 'Language';
      
      langToggle.innerHTML = `
          <div style="display:flex; align-items:center; gap:12px; color:#fff;">
              <span class="nav-icon" style="font-size:1.1rem; line-height:1;">🌐</span>
              <span style="font-weight:600; color:#ffffff;">${label}</span>
          </div>
          <span style="color:rgba(255,255,255,0.4); font-size:0.75rem; font-weight:500;">${l}</span>
      `;
      nav.appendChild(langToggle);
  }
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
