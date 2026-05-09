# توثيق قواعد البيانات والنماذج (Database Schema & Models Documentation)

هذا الملف يحتوي على وصف شامل لكل الجداول (Migrations) والنماذج (Models) الموجودة في نظام EventHub، بالإضافة إلى العلاقات بينها. تم تجهيز هذا الملف ليكون مرجعاً لأي نموذج ذكاء اصطناعي ليفهم هيكل البيانات بشكل كامل.

---

## 1. جدول المستخدمين (`users`)
**النموذج (Model):** `User`

يحتوي على كافة بيانات المستخدمين باختلاف أدوارهم في النظام.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `name`: اسم المستخدم.
  - `email`: البريد الإلكتروني (فريد).
  - `contact_email`: بريد التواصل (اختياري).
  - `password`: كلمة المرور (مشفرة).
  - `avatar` / `image`: مسار صورة المستخدم.
  - `phone`: رقم الهاتف.
  - `bio`: نبذة تعريفية.
  - `social_links`: روابط التواصل الاجتماعي (JSON).
  - `role`: دور المستخدم ويكون أحد القيم (`Admin`, `Event Manager`, `Sponsor`, `User`, `Assistant`).
  - `event_id`: معرف الفعالية (اختياري - في حال كان المستخدم مخصصاً لفعالية معينة).
  - `is_active`: حالة الحساب (نشط/غير نشط).
- **حقول التحقق (Verification) للمنظمين والرعاة:**
  - `verification_status`: حالة التوثيق (`verified`, `pending`, `rejected`, `changes_requested`).
  - `verification_notes`: ملاحظات الإدارة على طلب التوثيق.
  - `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`: مسارات المستندات الرسمية المرفوعة.
  - `doc_*_status`: حالة كل مستند على حدة (`pending`, `approved`, `rejected`).
  - `doc_*_note`: ملاحظة الرفض لكل مستند.
- **العلاقات (Relationships):**
  - `event()`: ينتمي إلى فعالية `belongsTo(Event::class)` (عبر `event_id`).
  - `profile()`: يمتلك ملفاً شخصياً واحداً `hasOne(Profile::class)`.
  - `managedEvents()`: يمتلك عدة فعاليات (لمدراء الفعاليات) `hasMany(Event::class, 'created_by')`.
  - `sponsoredEvents()`: يرعى عدة فعاليات (للرعاة) `belongsToMany(Event::class)` عبر الجدول الوسيط `event_sponsor`.

---

## 2. جدول الملفات الشخصية (`profiles`)
**النموذج (Model):** `Profile`

يحتوي على تفاصيل الملف الشخصي الموحد لكل مستخدم سواء كان فرداً أو شركة.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `user_id`: معرف المستخدم المرتبط (بميزة الحذف التلقائي Cascade).
  - `profile_type`: نوع الملف (`individual` للأفراد، `company` للشركات والرعاة).
  - `logo`: الشعار.
  - `bio`: نبذة (عني / عن الشركة).
  - `company_name`: اسم الشركة.
  - `company_description`: وصف الشركة.
  - `is_approved`: هل الملف معتمد.
  - `is_available`: هل متاح للرعاية/التواصل.
- **العلاقات (Relationships):**
  - `user()`: ينتمي إلى مستخدم `belongsTo(User::class)`.
  - `contacts()`: يمتلك عدة وسائل تواصل `hasMany(ProfileContact::class)`.

---

## 3. جدول وسائل التواصل للملف الشخصي (`profile_contacts`)
**النموذج (Model):** `ProfileContact`

- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `profile_id`: معرف الملف الشخصي.
  - `type`: نوع الوسيلة (مثلاً: phone, website, linkedin).
  - `value`: قيمة الوسيلة (الرقم أو الرابط).
- **العلاقات (Relationships):**
  - `profile()`: ينتمي إلى ملف شخصي `belongsTo(Profile::class)`.

---

## 4. جدول القاعات / الأماكن (`venues`)
**النموذج (Model):** `Venue`

يمثل الأماكن المتاحة داخل النظام لحجز الفعاليات.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `name`: اسم القاعة.
  - `location`: موقع القاعة.
  - `capacity`: السعة الاستيعابية.
  - `morning_start`, `morning_end`, `evening_start`, `evening_end`: فترات التشغيل الصباحية والمسائية.
  - `status`: حالة القاعة (`available` أو `maintenance`).
- **العلاقات (Relationships):**
  - `events()`: تحتوي على عدة فعاليات `hasMany(Event::class)`.
  - `maintenancePeriods()`: تحتوي على عدة فترات صيانة `hasMany(VenueMaintenancePeriod::class)`.

---

## 5. جدول فترات صيانة القاعات (`venue_maintenance_periods`)
**النموذج (Model):** `VenueMaintenancePeriod`

- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `venue_id`: معرف القاعة.
  - `start_date`, `end_date`: تاريخ بداية ونهاية الصيانة.
  - `reason`: سبب الصيانة.
- **العلاقات (Relationships):**
  - `venue()`: ينتمي إلى قاعة `belongsTo(Venue::class)`.

---

## 6. جدول الفعاليات (`events`)
**النموذج (Model):** `Event`

الجدول المركزي الذي يحتوي على كل تفاصيل الفعالية.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `title`: عنوان الفعالية.
  - `description`: وصف الفعالية.
  - `event_type`: نوع الفعالية (افتراضي: 'مؤتمر').
  - `venue_id`: معرف القاعة الداخلية (اختياري).
  - الحقول الخاصة بالقاعات الخارجية: `external_venue_name`, `external_venue_location`, `booking_proof_path` (مسار إثبات الحجز).
  - `period`: فترة الفعالية.
  - `booking_date`: تاريخ الحجز.
  - `start_time`, `end_time`: وقت بداية ونهاية الفعالية.
  - `capacity`: سعة الفعالية.
  - `image`: صورة الفعالية (بانر).
  - `status`: حالة الفعالية (`pending` وغيرها).
  - `rejection_reason`: سبب رفض الفعالية من قبل الإدارة.
  - `is_sponsorship_open`: هل الفعالية متاحة للرعاية.
  - `created_by`: معرف المستخدم الذي أنشأ الفعالية (مدير الفعالية).
  - `event_objective`: هدف الفعالية.
  - `target_audience`: الجمهور المستهدف.
  - مستندات الوزارة والمراجعة: `ministry_document_path`, `review_message`, `review_fields` (حقول مراجعة بصيغة JSON), `review_status`.
  - الجداول الزمنية: `external_schedule`, `internal_schedule`, `agenda` (كلها بصيغة JSON).
- **العلاقات (Relationships):**
  - `venue()`: تنتمي إلى قاعة `belongsTo(Venue::class)`.
  - `creator()`: تنتمي إلى منشئ الفعالية `belongsTo(User::class, 'created_by')`.
  - `tickets()`: تمتلك عدة تذاكر `hasMany(Ticket::class)`.
  - `ratings()`: تمتلك عدة تقييمات `hasMany(Rating::class)`.
  - `sponsors()`: ترتبط بعدة رعاة (مستخدمين) `belongsToMany(User::class)` عبر الجدول الوسيط `event_sponsor`.

---

## 7. جدول الرعاة للفعاليات (الجدول الوسيط - `event_sponsor`)
**النموذج (Model):** `EventSponsor` (Pivot Model)

يربط بين الفعاليات (`events`) والمستخدمين الرعاة (`users` ذوي دور Sponsor).
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `event_id`: معرف الفعالية.
  - `sponsor_id`: معرف المستخدم (الراعي).
  - `tier`: فئة الرعاية (`diamond`, `gold`, `silver`, `bronze` أو `null` إذا لم يتم التصنيف).
  - `contribution_amount`: مبلغ الرعاية.

---

## 8. جدول التذاكر (`tickets`)
**النموذج (Model):** `Ticket`

- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `event_id`: معرف الفعالية.
  - `user_id`: معرف المستخدم (الزائر).
  - `qr_code`: كود التذكرة (QR Code) (فريد).
  - `status`: حالة التذكرة (`unused`، `used`).
- **العلاقات (Relationships):**
  - `event()`: تنتمي إلى فعالية `belongsTo(Event::class)`.
  - `user()`: تنتمي إلى مستخدم `belongsTo(User::class)`.
  - `attendanceLog()`: تمتلك سجل حضور واحد `hasOne(AttendanceLog::class)`.

---

## 9. جدول سجل الحضور (`attendance_logs`)
**النموذج (Model):** `AttendanceLog`

- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `ticket_id`: معرف التذكرة.
  - `scanned_by`: معرف المستخدم (الذي قام بمسح التذكرة).
  - `scanned_at`: وقت المسح.
- **العلاقات (Relationships):**
  - `ticket()`: تنتمي إلى تذكرة `belongsTo(Ticket::class)`.
  - `scannedBy()`: تنتمي إلى المستخدم الماسح `belongsTo(User::class, 'scanned_by')`.

---

## 10. جدول طلبات الرعاية (`sponsorship_requests`)
**النموذج (Model):** `SponsorshipRequest`

يدير طلبات الرعاية سواء المبادرة من الراعي للمنظم، أو من المنظم للراعي.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `event_id`: معرف الفعالية.
  - `sponsor_id`: معرف المستخدم الراعي.
  - `event_manager_id`: معرف مدير الفعالية.
  - `initiator`: الطرف المبادر بالطلب (`sponsor` أو `event_manager`).
  - `message`: نص الطلب/الرسالة.
  - `status`: حالة الطلب (`pending`, `accepted`, `rejected`).
- **العلاقات (Relationships):**
  - `event()`: تنتمي إلى فعالية `belongsTo(Event::class)`.
  - `sponsor()`: تنتمي إلى المستخدم الراعي `belongsTo(User::class, 'sponsor_id')`.
  - `manager()`: تنتمي إلى مدير الفعالية `belongsTo(User::class, 'event_manager_id')`.

---

## 11. جدول الإشعارات (`event_notifications` / `notifications`)
**النموذج (Model):** `EventNotification`

- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `user_id`: معرف المستخدم المستقبل للإشعار.
  - `message`: نص الإشعار.
  - `is_read`: حالة القراءة (مقروء/غير مقروء).
- **العلاقات (Relationships):**
  - `user()`: ينتمي إلى مستخدم `belongsTo(User::class)`.

---

## 12. جدول التقييمات (`ratings`)
**النموذج (Model):** `Rating`

تقييمات المستخدمين للفعاليات.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `event_id`: معرف الفعالية.
  - `user_id`: معرف المستخدم.
  - `rating`: درجة التقييم (1 إلى 5).
  - `review_text`: النص المكتوب للتقييم.
- **العلاقات (Relationships):**
  - `event()`: تنتمي إلى فعالية `belongsTo(Event::class)`.
  - `user()`: تنتمي إلى مستخدم `belongsTo(User::class)`.

---

## 13. جدول رموز استعادة كلمة المرور (`password_reset_codes`)
**النموذج (Model):** `PasswordResetCode`

يستخدم لإدارة رموز التحقق (OTP) لاستعادة كلمات المرور بدلاً من الروابط التقليدية.
- **الحقول الأساسية:**
  - `id`: المعرف الأساسي.
  - `email`: البريد الإلكتروني.
  - `code`: رمز التحقق (مخزن كـ Hash).
  - `attempts`: عدد المحاولات الخاطئة.
  - `reset_token`: رمز استعادة لإكمال العملية بعد إدخال الكود بنجاح.
  - `created_at`: وقت الإنشاء لانتهاء الصلاحية.

---

### ملاحظات إضافية لنماذج الذكاء الاصطناعي:
- كافة العلاقات مبنية باستخدام إطار عمل `Laravel Eloquent ORM`.
- تم استخدام حقول من نوع `JSON` مثل (`social_links`, `external_schedule`, `internal_schedule`, `agenda`, `review_fields`) لإعطاء مرونة في تخزين البيانات المهيكلة.
- الجداول تعتمد على مفاتيح أجنبية (Foreign Keys) بخاصية `Cascade On Delete` في أغلب العلاقات لضمان نظافة قواعد البيانات.
- يتم الاعتماد على جدول `users` بصورة مركزية لكافة أنواع الحسابات، ويتم التمييز بينها بحقل `role`، بينما يحصل كل مستخدم على تفاصيل أوسع عبر جدول `profiles` التابع له.
