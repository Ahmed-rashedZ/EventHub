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

  /* ── Empty States ─────────────────────────────────────── */
  'No events found':   'لا توجد أحداث',
  'Failed to load':    'فشل التحميل',
  'No events match your selection.': 'لا توجد أحداث تتطابق مع اختيارك.',
  'No upcoming public events available right now.': 'لا توجد أحداث عامة قادمة الآن.',
  'No public events available right now.': 'لا توجد أحداث عامة الآن.',

  /* ── Sponsor Visibility ───────────────────────────────── */
  'You are currently not visible to event managers.': 'أنت غير مرئي لمديري الأحداث حالياً.',
  'You will remain hidden until you turn this option ON again.': 'ستبقى مخفياً حتى تعيد تفعيل هذا الخيار.',
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
       GLOBAL
    ════════════════════════════════════════════════════ */
    [dir="rtl"] body,
    [dir="rtl"] *         { font-family: 'Cairo', 'Segoe UI', sans-serif !important; letter-spacing: 0 !important; }

    /* ════════════════════════════════════════════════════
       LAYOUT  — sidebar flips to right, content on left
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .app-layout       { flex-direction: row-reverse; }
    [dir="rtl"] .sidebar          { border-right: none !important; border-left: 1px solid var(--border) !important; }
    [dir="rtl"] .main-content     { margin-left: 0 !important; margin-right: var(--sidebar-w) !important; }

    /* ════════════════════════════════════════════════════
       SIDEBAR internals
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .sidebar-logo     { flex-direction: row-reverse; }
    [dir="rtl"] .nav-section-label { text-align: right; display: block; }
    [dir="rtl"] .nav-item         { flex-direction: row-reverse; }
    [dir="rtl"] .nav-item .nav-icon { margin-right: 0 !important; margin-left: 10px !important; }
    [dir="rtl"] .sidebar-user     { flex-direction: row-reverse; }
    [dir="rtl"] .sidebar-user > div:last-child { text-align: right; }

    /* ════════════════════════════════════════════════════
       TOPBAR
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .topbar           { flex-direction: row-reverse; }
    [dir="rtl"] .topbar > div:first-child { text-align: right; }
    [dir="rtl"] .topbar-actions   { flex-direction: row-reverse; }
    [dir="rtl"] .page-title,
    [dir="rtl"] .page-subtitle    { text-align: right; }

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
    /* search bar padding/icon flip */
    [dir="rtl"] input[style*="padding-left"] { padding-right: 36px !important; padding-left: 12px !important; }
    [dir="rtl"] [style*="left:10px"]         { left: auto !important; right: 10px !important; }

    /* ════════════════════════════════════════════════════
       CARDS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .card,
    [dir="rtl"] .profile-card     { text-align: right; }
    [dir="rtl"] .avatar-upload    { text-align: center !important; }

    /* ════════════════════════════════════════════════════
       TABLES
    ════════════════════════════════════════════════════ */
    [dir="rtl"] table             { direction: rtl; }
    [dir="rtl"] th, [dir="rtl"] td { text-align: right; }

    /* ════════════════════════════════════════════════════
       MODALS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .modal-header     { flex-direction: row-reverse; }
    [dir="rtl"] .modal-body       { text-align: right; }
    [dir="rtl"] .modal-footer     { flex-direction: row-reverse; justify-content: flex-start; }
    [dir="rtl"] .ed-title-row     { flex-direction: row-reverse; text-align: right; }
    [dir="rtl"] .ed-info-card     { flex-direction: row-reverse; text-align: right; }
    [dir="rtl"] .ed-footer        { flex-direction: row-reverse; }
    [dir="rtl"] .ed-badges        { justify-content: flex-end; }

    /* ════════════════════════════════════════════════════
       EVENT CARDS (card grid)
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .event-card-header,
    [dir="rtl"] .event-card-footer,
    [dir="rtl"] .event-card-body  { text-align: right; }
    [dir="rtl"] .ev-badges        { justify-content: flex-end; }

    /* ════════════════════════════════════════════════════
       ANALYTICS STATS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .an-stat-label,
    [dir="rtl"] .an-stat-value,
    [dir="rtl"] .an-stat-sub      { text-align: right; }
    [dir="rtl"] .an-ev-header,
    [dir="rtl"] .an-rate-card     { flex-direction: row-reverse; }

    /* ════════════════════════════════════════════════════
       SORT / FILTER BAR
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .filter-bar,
    [dir="rtl"] .sort-bar         { flex-direction: row-reverse; }

    /* ════════════════════════════════════════════════════
       TOASTS
    ════════════════════════════════════════════════════ */
    [dir="rtl"] #toast-container  { left: auto !important; right: 20px !important; }
    [dir="rtl"] .toast            { direction: rtl; text-align: right; }

    /* ════════════════════════════════════════════════════
       BADGES  — keep numbers LTR inside RTL layout
    ════════════════════════════════════════════════════ */
    [dir="rtl"] .badge,
    [dir="rtl"] .status-badge     { direction: ltr; display: inline-block; }

    /* ════════════════════════════════════════════════════
       MISC FLEX FIXES from inline styles
    ════════════════════════════════════════════════════ */
    [dir="rtl"] [style*="justify-content: flex-end"]  { justify-content: flex-start !important; }
    [dir="rtl"] [style*="text-align: right"]          { text-align: left !important; }
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
  for (const [en, ar] of Object.entries(I18N_AR)) {
    if (updated.includes(en)) {
      updated = updated.replace(en, ar);
      changed = true;
    }
  }
  if (changed) {
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

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    injectRTLStyles();
    translateDOM();
    startObserver();
  });
} else {
  injectRTLStyles();
  translateDOM();
  startObserver();
}
