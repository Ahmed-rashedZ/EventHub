# 📋 EventHub - توثيق المشروع الكامل
# EventHub - Complete Project Documentation

> **آخر تحديث:** 2026-05-21
> **التقنيات:** Laravel 11 + Sanctum + Blade + MySQL + PhpWord + DomPDF + Laravel Notifications + Firebase Cloud Messaging
> **نوع المشروع:** نظام إدارة فعاليات ومعارض (Event & Exhibition Management System)

---

## 📑 فهرس المحتويات

1. [نظرة عامة](#-نظرة-عامة)
2. [البنية التقنية](#-البنية-التقنية)
3. [الأدوار والصلاحيات](#-الأدوار-والصلاحيات-roles)
4. [قاعدة البيانات](#-قاعدة-البيانات)
5. [الـ Models والعلاقات](#-الـ-models-والعلاقات)
6. [الـ Controllers والوظائف](#-الـ-controllers-والوظائف)
7. [الـ Services](#-الـ-services)
8. [الـ Mail Classes](#-الـ-mail-classes)
9. [الـ Console Commands](#-الـ-console-commands)
10. [الـ API Routes](#-الـ-api-routes)
11. [الـ Web Routes](#-الـ-web-routes)
12. [الـ Middleware](#-الـ-middleware)
13. [صفحات الواجهة (Views)](#-صفحات-الواجهة-views)
14. [الميزات الرئيسية](#-الميزات-الرئيسية)
15. [تطبيق الموبايل (Flutter)](#-تطبيق-الموبايل-flutter)
16. [هيكل الملفات](#-هيكل-الملفات)
17. [ملخص الأرقام](#-ملخص-الأرقام)

---

## 🌐 نظرة عامة

**EventHub** هو نظام متكامل لإدارة الفعاليات والمعارض والمؤتمرات يشمل:
- إنشاء وإدارة الفعاليات (داخلية وخارجية) مع نظام جدولة متعدد الأيام
- نظام حجز تذاكر مع QR Code ورقم تذكرة تسلسلي
- نظام رعاية (Sponsorship) ثنائي الاتجاه مع تفاوض عقود
- نظام معارض (Exhibition) كامل مع إدارة أجنحة ومناطق
- تسجيل حضور عبر مسح QR Code
- نظام دعوات مساعدين (Assistant Invitation System)
- لوحات تحكم تحليلية لكل دور
- نظام بروفايلات موحد لجميع المستخدمين
- إنشاء عقود رعاية/معارض Word + PDF تلقائياً مع نظام تفاوض متعدد النسخ
- نظام تحقق من هوية الشركاء بوثائق متعددة (4 وثائق)
- نظام إلغاء فعاليات (Cancellation Request → Admin Approval)
- نظام مراجعة فعاليات (Admin Review → Manager Edit)
- نظام إشعارات فورية (Firebase Cloud Messaging + Database)
- نظام نسيان كلمة المرور عبر OTP (6 أرقام) + إيميل
- نظام صيانة الأماكن (Venue Maintenance Periods)
- نظام اهتمامات المستخدمين (Interests) مع إشعارات ذكية
- جدولة منشورة (Published Schedule) لعرض تقويم الفعالية للجمهور
- نظام تحليل وتنبؤ ذكي مدعوم بالذكاء الاصطناعي (FastAPI Microservice) للتنبؤ بنسب حضور الفعاليات وتوليد الأوصاف التسويقية تلقائياً

---

## 🏗 البنية التقنية

| المكون | التقنية |
|--------|---------|
| **Backend Framework** | Laravel 11 |
| **Authentication** | Laravel Sanctum (Token-based) |
| **Database** | MySQL |
| **Frontend** | Blade Templates + Vanilla JS + CSS |
| **PDF Generation** | Barryvdh DomPDF |
| **Word Generation** | PhpOffice PhpWord |
| **File Storage** | Laravel Storage (public disk) |
| **QR Code** | External API (api.qrserver.com) |
| **Push Notifications** | Firebase Cloud Messaging (FCM) |
| **Email** | Laravel Mail (OTP + Invitations) |
| **Mobile App** | Flutter (Dart) |
| **AI Microservice** | FastAPI (Python 3) |
| **Machine Learning** | Scikit-Learn (Gradient Boosting Regressor) + Joblib |
| **LLM Integration** | Groq API (Llama 3.3 70B Model) |

---

## 👥 الأدوار والصلاحيات (Roles)

النظام يحتوي على **6 أدوار** رئيسية:

### 1. 🔴 Admin (المدير العام)
| الصلاحية | الوصف |
|-----------|-------|
| لوحة تحكم تحليلية | إحصائيات النظام الشاملة + إحصائيات المعارض |
| إدارة الفعاليات | الموافقة/الرفض/المراجعة على الفعاليات المعلقة + عرض جميع الفعاليات |
| إدارة المستخدمين | عرض/إنشاء/تعليق/حذف المستخدمين |
| إدارة الأماكن (Venues) | إنشاء/تعديل الأماكن + إدارة فترات الصيانة |
| تحقق الشركاء | مراجعة وثائق التحقق (4 وثائق) + قبول/رفض/طلب تعديل لكل وثيقة |
| إدارة الإلغاء | موافقة/رفض طلبات إلغاء الفعاليات |
| إنشاء مستخدمين | يمكنه إنشاء User و Admin فقط |
| إحصائيات الفعاليات | عرض إحصائيات أي فعالية |

**الصفحات:** `/admin/dashboard`, `/admin/events`, `/admin/users`, `/admin/venues`, `/admin/verifications`, `/admin/event-stats/{id}`

---

### 2. 🟢 Event Manager (مدير الفعاليات)
| الصلاحية | الوصف |
|-----------|-------|
| إنشاء فعاليات | إنشاء فعالية جديدة (داخلية/خارجية) مع جدولة متعددة الأيام |
| إدارة المساعدين | دعوة مساعدين عبر نظام الدعوات الجديد + إنشاء/حذف حسابات Assistant |
| الرعاية | إرسال/استقبال طلبات رعاية + فتح/إغلاق باب الرعاية + تفاوض عقود |
| المعارض | إدارة طلبات المعارض + دعوة شركات + إدارة الأجنحة والمناطق |
| الحضور | عرض قائمة الحاضرين لكل فعالية |
| الإحصائيات | لوحة تحليلية لفعالياته + إحصائيات مفصلة + إحصائيات المساعدين |
| إلغاء الفعاليات | طلب إلغاء فعالية (30 يوم قبل البدء) |
| الجدول المنشور | إدارة الجدول المنشور للفعالية |
| تبديل التذاكر | فتح/إغلاق بيع التذاكر |
| تحديث السعة | تعديل سعة الفعالية |

**الصفحات:** `/manager/dashboard`, `/manager/events`, `/manager/assistants`, `/manager/assistants/{id}/stats`, `/manager/attendance`, `/manager/sponsorship`, `/manager/exhibition`, `/manager/event-stats/{id}`

---

### 3. 🟡 Sponsor (الراعي)
| الصلاحية | الوصف |
|-----------|-------|
| تصفح الفعاليات | عرض الفعاليات المفتوحة للرعاية |
| طلبات الرعاية | إرسال/استقبال طلبات رعاية + تفاوض عقود |
| التوفر | تفعيل/إيقاف حالة التوفر (is_available) |
| البروفايل | إدارة بيانات الشركة (اسم، لوغو، وصف، نوع الشركة) |
| لوحة التحكم | عرض طلبات الرعاية والفعاليات المرتبط بها |
| إحصائيات الفعاليات | عرض إحصائيات الفعاليات التي يرعاها |
| إدارة الوثائق | رفع/تحديث وثائق التحقق |

**الصفحات:** `/sponsor/dashboard`, `/sponsor/events`, `/sponsor/requests`, `/sponsor/history`, `/sponsor/event-stats/{id}`

---

### 4. 🟠 Company (شركة المعارض)
| الصلاحية | الوصف |
|-----------|-------|
| تصفح المعارض | عرض المعارض المفتوحة للتسجيل |
| طلبات المشاركة | إرسال/استقبال طلبات مشاركة في المعارض + تفاوض عقود |
| التوفر | تفعيل/إيقاف حالة التوفر (is_available) |
| البروفايل | إدارة بيانات الشركة |
| لوحة التحكم | إحصائيات المعارض + نسبة إكمال البروفايل |
| إدارة الوثائق | رفع/تحديث وثائق التحقق |

**الصفحات:** `/company/dashboard`, `/company/exhibitions`, `/company/events`, `/company/event-stats/{id}`

---

### 5. 🔵 User (المستخدم العادي / الحاضر)
| الصلاحية | الوصف |
|-----------|-------|
| حجز تذاكر | حجز تذكرة لفعالية معتمدة |
| عرض التذاكر | عرض تذاكره مع QR Code |
| تقييم الفعاليات | تقييم فعالية حضرها (1-5 نجوم + نص) |
| البروفايل | تعديل بياناته الشخصية |
| الاهتمامات | اختيار اهتمامات لتلقي إشعارات الفعاليات الجديدة |

**الصفحات:** `/profile`

---

### 6. 🟣 Assistant (المساعد)
| الصلاحية | الوصف |
|-----------|-------|
| تسجيل الحضور | مسح QR Code لتسجيل حضور الحاضرين |
| عرض المشاركين | عرض قائمة المشاركين في فعاليته |
| قبول/رفض دعوات | إدارة دعوات المدراء للعمل في فعالياتهم |
| التوفر | تفعيل/إيقاف حالة التوفر لاستقبال دعوات جديدة |
| سجل العمل | عرض الفعاليات الحالية والسابقة مع إحصائيات المسح |
| إحصائيات مفصلة | عرض تفاصيل المسح لكل فعالية |

**الصفحات:** `/profile` (الويب) | تطبيق الموبايل

---

## 🗄 قاعدة البيانات

### جداول قاعدة البيانات (47 migration)

#### 1. `users` - المستخدمين
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| name | string | الاسم |
| email | string (unique) | البريد الإلكتروني |
| contact_email | string (nullable) | بريد التواصل |
| password | string (hashed) | كلمة المرور |
| role | enum | الدور: Admin, Event Manager, Sponsor, Company, User, Assistant |
| is_active | boolean (default: true) | حالة الحساب |
| event_id | foreign (nullable) | الفعالية المرتبطة (للمساعدين - النظام القديم) |
| avatar / image | string (nullable) | صورة المستخدم |
| phone | string (nullable) | رقم الهاتف |
| bio | text (nullable) | نبذة شخصية |
| social_links | json (nullable) | روابط التواصل الاجتماعي |
| interests | json (nullable) | اهتمامات المستخدم (أنواع الفعاليات) |
| verification_status | enum | حالة التحقق: verified, pending, rejected, changes_requested |
| verification_notes | text (nullable) | ملاحظات الـ Admin على طلب التحقق |
| doc_commercial_register | string (nullable) | مسار وثيقة السجل التجاري |
| doc_tax_number | string (nullable) | مسار شهادة الرقم الضريبي |
| doc_articles_of_association | string (nullable) | مسار عقد التأسيس (Manager فقط) |
| doc_practice_license | string (nullable) | مسار رخصة الممارسة (Manager فقط) |
| doc_commercial_register_status | string (nullable) | حالة وثيقة السجل التجاري |
| doc_tax_number_status | string (nullable) | حالة وثيقة الرقم الضريبي |
| doc_articles_of_association_status | string (nullable) | حالة عقد التأسيس |
| doc_practice_license_status | string (nullable) | حالة رخصة الممارسة |
| doc_commercial_register_note | text (nullable) | ملاحظة على السجل التجاري |
| doc_tax_number_note | text (nullable) | ملاحظة على الرقم الضريبي |
| doc_articles_of_association_note | text (nullable) | ملاحظة على عقد التأسيس |
| doc_practice_license_note | text (nullable) | ملاحظة على رخصة الممارسة |
| fcm_token | string (nullable) | توكن Firebase Cloud Messaging |

#### 2. `events` - الفعاليات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| title | string | عنوان الفعالية |
| description | text | وصف الفعالية |
| event_type | string | النوع: مؤتمر، ندوة، ورشة عمل، دورة تدريبية، ترفيه، ملتقى علمي، رياضة، تقنية، اجتماعية، معرض |
| venue_id | foreign (nullable) | المكان (للداخلية) |
| external_venue_name | string (nullable) | اسم المكان الخارجي |
| external_venue_location | string (nullable) | رابط موقع المكان الخارجي |
| booking_proof_path | string (nullable) | مسار وثيقة إثبات الحجز (خارجي) |
| ministry_document_path | string (nullable) | مسار وثيقة الوزارة |
| created_by | foreign (users) | منشئ الفعالية |
| start_time | datetime | وقت البداية (الكلي) |
| end_time | datetime | وقت النهاية (الكلي) |
| booking_date | date (nullable) | تاريخ الحجز (نظام قديم) |
| period | string (nullable) | الفترة (نظام قديم) |
| capacity | integer (nullable) | السعة القصوى |
| status | enum | الحالة: pending, approved, rejected, cancellation_requested, cancelled |
| is_sponsorship_open | boolean | هل باب الرعاية مفتوح |
| is_tickets_open | boolean | هل بيع التذاكر مفتوح |
| is_exhibition | boolean | هل الفعالية معرض |
| is_applications_open | boolean | هل باب الطلبات مفتوح |
| is_published | boolean | هل الفعالية منشورة |
| is_exhibitor_registration_open | boolean | هل تسجيل العارضين مفتوح |
| rejection_reason | text (nullable) | سبب الرفض |
| cancellation_reason | text (nullable) | سبب طلب الإلغاء |
| cancellation_rejection_reason | text (nullable) | سبب رفض الإلغاء |
| review_message | text (nullable) | رسالة المراجعة من Admin |
| review_fields | json (nullable) | الحقول المطلوب تعديلها |
| review_status | string (nullable) | حالة المراجعة: needs_review, reviewed |
| image | string (nullable) | صورة الفعالية |
| event_objective | text (nullable) | هدف الفعالية |
| target_audience | text (nullable) | الجمهور المستهدف |
| external_schedule | json (nullable) | جدول المكان الخارجي (متعدد الأيام) |
| internal_schedule | json (nullable) | جدول المكان الداخلي (متعدد الأيام) |
| agenda | json (nullable) | أجندة الفعالية (حسب اليوم) |
| published_schedule | json (nullable) | الجدول المنشور للجمهور |

#### 3. `venues` - الأماكن
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| name | string | اسم المكان |
| location | string (URL) | رابط الموقع (خريطة) |
| capacity | integer | السعة |
| status | string | الحالة: available, maintenance |
| morning_start | time | بداية الفترة الصباحية |
| morning_end | time | نهاية الفترة الصباحية |
| evening_start | time | بداية الفترة المسائية |
| evening_end | time | نهاية الفترة المسائية |

#### 4. `venue_maintenance_periods` - فترات صيانة الأماكن
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| venue_id | foreign | المكان |
| start_date | date | تاريخ بداية الصيانة |
| end_date | date | تاريخ نهاية الصيانة |
| reason | string (nullable) | سبب الصيانة |

#### 5. `tickets` - التذاكر
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| user_id | foreign | المستخدم |
| qr_code | string (unique) | رمز QR |
| status | enum | الحالة: unused, used, cancelled |
| ticket_number | integer (nullable) | رقم التذكرة التسلسلي |

#### 6. `attendance_logs` - سجل الحضور
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| ticket_id | foreign | التذكرة |
| scanned_by | foreign (users) | من قام بالمسح |
| scanned_at | datetime | وقت المسح |

#### 7. `profiles` - البروفايلات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| user_id | foreign (unique) | المستخدم |
| profile_type | enum | individual أو company |
| company_type | string (nullable) | نوع الشركة |
| logo | string (nullable) | الشعار |
| bio | text (nullable) | النبذة |
| company_description | text (nullable) | وصف الشركة |
| is_approved | boolean | معتمد من الإدارة |
| is_available | boolean | متوفر للرعاية/الدعوات |

#### 8. `profile_contacts` - جهات اتصال البروفايل
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| profile_id | foreign | البروفايل |
| type | string | نوع التواصل (phone, email, etc.) |
| value | string | القيمة |

#### 9. `event_sponsor` - الربط بين الفعاليات والرعاة (Pivot)
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| sponsor_id | foreign (users) | الراعي |
| tier | enum (nullable) | المستوى: diamond, gold, silver, bronze |
| contribution_amount | decimal | مبلغ المساهمة |

#### 10. `sponsorship_requests` - طلبات الرعاية
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| sponsor_id | foreign (users) | الراعي |
| event_manager_id | foreign (users) | مدير الفعالية |
| initiator | enum | من بادر: sponsor أو event_manager |
| message | text (nullable) | رسالة الطلب |
| status | enum | الحالة: pending, accepted, rejected, negotiating, cancelled |

#### 11. `agreement_negotiations` - مفاوضات العقود
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| sponsorship_request_id | foreign (nullable) | طلب الرعاية |
| exhibition_application_id | foreign (nullable) | طلب المعرض |
| status | enum | الحالة: draft, pending_review, accepted, rejected, revision_requested |
| last_submitted_by | foreign (users) | آخر من قدّم نسخة |
| final_notes | text (nullable) | ملاحظات نهائية |

#### 12. `agreement_versions` - نسخ العقود
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| negotiation_id | foreign | المفاوضة |
| version_number | integer | رقم النسخة |
| file_path | string | مسار الملف (Word/PDF) |
| uploaded_by | foreign (users) | من رفع الملف |
| action | enum | الإجراء: uploaded, accepted, rejected, revision_requested |
| message | text (nullable) | رسالة مرفقة |

#### 13. `notifications` - الإشعارات (Laravel Standard)
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | uuid (primary) | المعرف |
| type | string | كلاس الإشعار (SystemNotification) |
| notifiable_type | string | نوع المستلم (App\Models\User) |
| notifiable_id | bigint | معرف المستلم |
| data | text (JSON) | البيانات: title, message, type, icon, action_url, related_id |
| read_at | timestamp (nullable) | وقت القراءة (null = غير مقروء) |

#### 14. `ratings` - تقييمات الفعاليات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| user_id | foreign | المستخدم المُقيِّم |
| rating | tinyint (1-5) | التقييم من 1 إلى 5 نجوم |
| review_text | text (nullable) | نص المراجعة |

> **قيد:** `UNIQUE(user_id, event_id)` - تقييم واحد لكل مستخدم لكل فعالية (يمكن تحديثه)

#### 15. `assistance_requests` - طلبات/دعوات المساعدين
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| assistant_id | foreign (users) | المساعد |
| event_id | foreign (events) | الفعالية |
| manager_id | foreign (users) | المدير الداعي |
| status | enum | الحالة: pending, accepted, rejected |
| message | text (nullable) | رسالة الدعوة |
| responded_at | datetime (nullable) | وقت الرد |

#### 16. `exhibition_applications` - طلبات المشاركة في المعارض
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign (events) | المعرض/الفعالية |
| company_id | foreign (users) | الشركة |
| event_manager_id | foreign (users) | مدير المعرض |
| initiator | enum | من بادر: company أو event_manager |
| message | text (nullable) | رسالة الطلب |
| status | enum | الحالة: pending, accepted, rejected, negotiating, cancelled |
| product_category | string (nullable) | تصنيف المنتج |
| booth_number | string (nullable) | رقم الجناح (Legacy) |
| booth_size | string (nullable) | حجم الجناح (Legacy) |

#### 17. `exhibition_zones` - مناطق المعرض
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign (events) | المعرض |
| name | string | اسم المنطقة |

#### 18. `exhibition_booths` - أجنحة المعرض
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| exhibition_zone_id | foreign | المنطقة |
| booth_number | string | رقم الجناح |
| size | string (nullable) | الحجم |
| exhibition_application_id | foreign (nullable) | طلب المشاركة المخصص له |

#### 19. `password_reset_codes` - أكواد إعادة تعيين كلمة المرور
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| email | string | البريد الإلكتروني |
| code | string | كود OTP (6 أرقام) |
| attempts | integer | عدد المحاولات |
| reset_token | string (nullable) | توكن إعادة التعيين |
| created_at | timestamp | وقت الإنشاء |

#### 20. `event_notifications` - إشعارات الفعاليات (Legacy)
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| user_id | foreign | المستخدم |
| message | text | الرسالة |
| is_read | boolean | مقروء |

#### 21. `event_reminders` - تذكيرات الفعاليات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| reminder_type | string | نوع التذكير |
| sent_at | datetime (nullable) | وقت الإرسال |

#### 22. `reports` - التقارير
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| timestamps | - | أوقات الإنشاء/التعديل |

#### جداول النظام الأساسية
- `cache` - ذاكرة التخزين المؤقت
- `jobs` - الأعمال المجدولة
- `personal_access_tokens` - توكنات Sanctum

---

## 🔗 الـ Models والعلاقات

### User Model
```
User ─→ hasOne Profile
User ─→ belongsTo Event (للمساعدين - النظام القديم)
User ─→ hasMany Event (كـ creator/manager عبر created_by)
User ─→ belongsToMany Event (كـ sponsor عبر event_sponsor)
User ─→ hasMany AttendanceLog (كـ scanned_by)
User ─→ hasMany AssistanceRequest (كـ assistant_id)
User ─→ hasMany ExhibitionApplication (كـ company_id)
User ─→ hasMany ExhibitionBooth (كـ company_id)
User ─→ belongsToMany Event (عبر assistance_requests كـ assistedEvents)
```
- **Methods:** `hasAccessToEvent($eventId)` → يتحقق من صلاحية المساعد (نظام جديد + قديم)

### Event Model
```
Event ─→ belongsTo Venue
Event ─→ belongsTo User (creator عبر created_by)
Event ─→ hasMany Ticket
Event ─→ hasMany Rating
Event ─→ belongsToMany User (sponsors عبر event_sponsor)
Event ─→ hasMany ExhibitionApplication
Event ─→ hasMany ExhibitionBooth
```
- **Scopes:** `upcoming()`, `live()`, `ended()`
- **Accessors:** `time_status` → (upcoming / live / ended) | `average_rating` → متوسط التقييم (محسوب ديناميكياً)
- **Methods:** `sponsorsWithProfiles()`, `sponsorsByTier($tier)`, `canAcceptExhibitorApplications()`, `exhibitors()`
- **Casts:** `internal_schedule`, `external_schedule`, `agenda`, `published_schedule` → array | `is_sponsorship_open`, `is_tickets_open`, `is_exhibition`, `is_applications_open`, `is_published`, `is_exhibitor_registration_open` → boolean

### Rating Model
```
Rating ─→ belongsTo Event
Rating ─→ belongsTo User
```
- قيد UNIQUE على (user_id + event_id) — تقييم واحد لكل مستخدم قابل للتحديث

### Ticket Model
```
Ticket ─→ belongsTo Event
Ticket ─→ belongsTo User
Ticket ─→ hasOne AttendanceLog
```

### AttendanceLog Model
```
AttendanceLog ─→ belongsTo Ticket
AttendanceLog ─→ belongsTo User (scanner عبر scanned_by)
```

### Profile Model
```
Profile ─→ belongsTo User
Profile ─→ hasMany ProfileContact
```
- **Scopes:** `companies()`, `individuals()`, `approved()`, `available()`
- **Methods:** `tierLabel($tier)`, `displayName()`

### EventSponsor (Pivot Model)
```
EventSponsor ─→ belongsTo Event
EventSponsor ─→ belongsTo User (sponsor)
```
- **Methods:** `tierLabel()`, `isDiamondTier()`

### SponsorshipRequest Model
```
SponsorshipRequest ─→ belongsTo Event
SponsorshipRequest ─→ belongsTo User (sponsor عبر sponsor_id)
SponsorshipRequest ─→ belongsTo User (manager عبر event_manager_id)
SponsorshipRequest ─→ hasOne AgreementNegotiation
```

### AgreementNegotiation Model
```
AgreementNegotiation ─→ belongsTo SponsorshipRequest
AgreementNegotiation ─→ belongsTo ExhibitionApplication
AgreementNegotiation ─→ hasMany AgreementVersion (عبر negotiation_id)
AgreementNegotiation ─→ hasOne AgreementVersion (latestVersion)
AgreementNegotiation ─→ belongsTo User (lastSubmitter عبر last_submitted_by)
```

### AgreementVersion Model
```
AgreementVersion ─→ belongsTo AgreementNegotiation (عبر negotiation_id)
AgreementVersion ─→ belongsTo User (uploader عبر uploaded_by)
```

### AssistanceRequest Model
```
AssistanceRequest ─→ belongsTo User (assistant عبر assistant_id)
AssistanceRequest ─→ belongsTo Event
AssistanceRequest ─→ belongsTo User (manager عبر manager_id)
```
- **Scopes:** `pending()`, `accepted()`

### ExhibitionApplication Model
```
ExhibitionApplication ─→ belongsTo Event
ExhibitionApplication ─→ belongsTo User (company عبر company_id)
ExhibitionApplication ─→ belongsTo User (manager عبر event_manager_id)
ExhibitionApplication ─→ hasOne AgreementNegotiation
ExhibitionApplication ─→ hasOne ExhibitionBooth
```
- **Scopes:** `pending()`, `accepted()`

### ExhibitionZone Model
```
ExhibitionZone ─→ belongsTo Event
ExhibitionZone ─→ hasMany ExhibitionBooth
```

### ExhibitionBooth Model
```
ExhibitionBooth ─→ belongsTo ExhibitionZone
ExhibitionBooth ─→ belongsTo ExhibitionApplication
```

### Venue Model
```
Venue ─→ hasMany Event
Venue ─→ hasMany VenueMaintenancePeriod
```
- **Methods:** `isUnderMaintenance($date)`, `getMaintenanceDates()`

### VenueMaintenancePeriod Model
```
VenueMaintenancePeriod ─→ belongsTo Venue
```

### PasswordResetCode Model
```
PasswordResetCode (standalone)
```
- **Constants:** `TTL_MINUTES = 5`, `MAX_ATTEMPTS = 3`, `RATE_LIMIT_SECONDS = 60`, `MAX_SENDS_PER_HOUR = 5`
- **Scopes:** `valid()`, `unlocked()`
- **Methods:** `isExpired()`, `isLocked()`, `remainingAttempts()`

### EventReminder Model
```
EventReminder ─→ belongsTo Event
```

### EventNotification Model (Legacy)
```
EventNotification ─→ belongsTo User
```

### Report Model
```
Report (empty - placeholder)
```

---

## 🎮 الـ Controllers والوظائف

### 1. AuthController (19 وظيفة)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `register()` | POST | تسجيل مستخدم جديد (User أو Assistant) |
| `registerPartner()` | POST | تسجيل شريك (Event Manager, Sponsor, Company) مع رفع وثائق تحقق (2-4 وثائق) |
| `login()` | POST | تسجيل الدخول + فحص is_active + فحص المنصة (web/mobile) + فحص التحقق |
| `logout()` | POST | تسجيل الخروج (حذف جميع التوكنات) |
| `getProfile()` | GET | عرض بروفايل المستخدم الحالي |
| `getPublicProfile($id)` | GET | عرض بروفايل مستخدم عام + متوسط تقييم المدير |
| `getPortfolio($id)` | GET | محفظة أعمال: فعاليات المدير أو فعاليات الراعي |
| `updateProfile()` | PUT | تحديث البروفايل (اسم، بريد، صورة، جهات اتصال، اهتمامات) |
| `updateAvailability()` | PATCH | تبديل توفر الراعي/الشركة |
| `createUser()` | POST | إنشاء مستخدم (Admin: User/Admin فقط، Manager: Assistant فقط) |
| `getAssistants()` | GET | قائمة المساعدين التابعين للمدير مع إحصائيات المسح |
| `patchAssistantStatus($id)` | PATCH | تعليق/تفعيل مساعد |
| `updateAssistant($id)` | PUT | تعديل بيانات مساعد + إرسال إيميل |
| `deleteAssistant($id)` | DELETE | حذف مساعد |
| `getAvailableSponsors()` | GET | قائمة الرعاة المتوفرين |
| `getAvailableCompanies()` | GET | قائمة الشركات المتوفرة (Company role) |
| `forgotPassword()` | POST | إرسال كود OTP لإعادة تعيين كلمة المرور |
| `verifyCode()` | POST | التحقق من كود OTP + إرجاع reset_token |
| `resetPassword()` | POST | إعادة تعيين كلمة المرور باستخدام reset_token |

### 2. EventController (22 وظيفة)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | قائمة الفعاليات المعتمدة والمنشورة |
| `show($id)` | GET | تفاصيل فعالية واحدة مع الرعاة والمساعدين والعارضين |
| `store()` | POST | إنشاء فعالية (Manager) مع جدولة متعددة الأيام + فحص التعارض |
| `approve($id)` | PUT | الموافقة (Admin) + إشعار المدير + إشعار المهتمين |
| `reject($id)` | PUT | الرفض مع السبب (Admin) + إشعار المدير |
| `pending()` | GET | الفعاليات المعلقة (Admin) |
| `myEvents()` | GET | فعاليات المدير |
| `all()` | GET | جميع الفعاليات (Admin) |
| `toggleSponsorship($id)` | PATCH | فتح/إغلاق الرعاية (Manager) |
| `toggleExhibitorRegistration($id)` | PATCH | فتح/إغلاق تسجيل العارضين (Manager) |
| `toggleApplications($id)` | PATCH | فتح/إغلاق الطلبات (Manager) |
| `rate($id)` | POST | تقييم فعالية 1-5 نجوم (User حضر + التذكرة used) |
| `deleteRating($id)` | DELETE | حذف تقييم المستخدم |
| `reviews($id)` | GET | عرض تقييمات ومراجعات فعالية |
| `sendReview($id)` | PUT | Admin يرسل مراجعة مع حقول مطلوب تعديلها |
| `updatePending($id)` | POST | Manager يعدّل الفعالية المعلقة بعد المراجعة |
| `destroy($id)` | DELETE | Manager يحذف فعالية معلقة |
| `updateAgenda($id)` | PUT | Manager يعدّل أجندة الفعالية |
| `downloadDocument($id, $type)` | GET | تحميل وثائق الفعالية (وزارة/إثبات حجز) |
| `requestCancellation($id)` | POST | Manager يطلب إلغاء فعالية (30 يوم قبل البدء) |
| `approveCancellation($id)` | PUT | Admin يوافق على الإلغاء + إشعار جميع الأطراف |
| `rejectCancellation($id)` | PUT | Admin يرفض الإلغاء |
| `toggleTickets($id)` | PATCH | فتح/إغلاق بيع التذاكر |
| `updatePublishedSchedule($id)` | PUT | تحديث الجدول المنشور + نشر الفعالية |
| `updateCapacity($id)` | PATCH | تحديث سعة الفعالية |
| `categories()` | GET | قائمة أنواع الفعاليات المتاحة |

### 3. VenueController (7 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | قائمة الأماكن (Admin يشوف الكل, غيره المتاحة فقط) |
| `store()` | POST | إنشاء مكان جديد (Admin) مع أوقات الفترات |
| `update($id)` | PUT | تعديل مكان (Admin) |
| `destroy($id)` | DELETE | **معطل** - لحماية السجلات الأرشيفية |
| `bookings($id)` | GET | الحجوزات + فترات الصيانة لمكان محدد |
| `getMaintenancePeriods($id)` | GET | فترات صيانة مكان |
| `addMaintenancePeriod($id)` | POST | إضافة فترة صيانة (Admin) مع فحص التعارض |
| `deleteMaintenancePeriod($id, $periodId)` | DELETE | حذف فترة صيانة |

### 4. TicketController (2 وظيفتين)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `store()` | POST | حجز تذكرة (فحص: معتمدة + تذاكر مفتوحة + تذكرة واحدة + السعة) + رقم تسلسلي |
| `myTickets()` | GET | عرض تذاكر المستخدم مع QR URLs |

### 5. CheckinController (2 وظيفتين)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `checkin()` | POST | مسح QR Code + تسجيل الحضور (Assistant فقط + فحص Event المرتبط) |
| `eventParticipants($id)` | GET | قائمة المشاركين في فعالية (Assistant/Manager/Admin) |

### 6. SponsorshipController (4 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `store()` | POST | إنشاء طلب رعاية (ثنائي الاتجاه: Manager→Sponsor أو Sponsor→Manager) |
| `index()` | GET | عرض طلبات الرعاية مع بيانات المفاوضات |
| `update($id)` | PUT | قبول/رفض طلب → إنشاء عقد Word تلقائياً + بدء التفاوض |
| `updateTier($id)` | PATCH | تحديث تصنيف/رتبة الراعي (Manager) |

### 7. AgreementController (6 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `show($id)` | GET | عرض تفاصيل العقد مع جميع النسخ |
| `generate($id)` | POST | توليد عقد Word أولي تلقائياً (رعاية أو معرض) |
| `download($id, $version?)` | GET | تحميل نسخة محددة أو آخر نسخة |
| `downloadFinal($id)` | GET | تحميل العقد النهائي بعد القبول |
| `upload($id)` | POST | رفع نسخة عقد جديدة (docx/doc/pdf) |
| `respond($id)` | PUT | قبول/رفض/طلب تعديل العقد + إصدار عقد نهائي PDF |
| `cancel($id)` | PUT | إلغاء المفاوضة مع سبب |

### 8. ExhibitionController (5 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `store()` | POST | إنشاء طلب مشاركة (ثنائي الاتجاه: Company←→Manager) |
| `index()` | GET | عرض طلبات المعرض حسب الدور |
| `show($id)` | GET | تفاصيل طلب واحد |
| `update($id)` | PUT | قبول/رفض طلب → إنشاء عقد تلقائياً + بدء التفاوض |
| `assignBooth($id)` | PATCH | تخصيص جناح لشركة (Manager) مع قفل قبل 14 يوم |

### 9. ExhibitionInventoryController (7 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index($eventId)` | GET | عرض مناطق وأجنحة المعرض |
| `storeZone($eventId)` | POST | إنشاء منطقة |
| `destroyZone($id)` | DELETE | حذف منطقة |
| `storeBooth($zoneId)` | POST | إنشاء جناح |
| `updateBooth($id)` | PUT | تعديل جناح |
| `destroyBooth($id)` | DELETE | حذف جناح (غير مخصص فقط) |
| `batchGenerateBooths($zoneId)` | POST | إنشاء أجنحة دفعة واحدة |

### 10. NotificationController (4 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | آخر 50 إشعار + عدد غير المقروءة |
| `markRead($id)` | PUT | تحديد إشعار واحد كمقروء |
| `markAllRead()` | PUT | تحديد كل الإشعارات كمقروءة |
| `saveFcmToken()` | POST | حفظ توكن Firebase Cloud Messaging |

### 11. VerificationController (7 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `pendingRequests()` | GET | طلبات التحقق المعلقة + الموثقين مع تحديثات (Admin) |
| `reviewDocuments($id)` | PUT | مراجعة وثائق فردياً (قبول/رفض لكل وثيقة) + دعم تحديث الموثقين |
| `reject($id)` | PUT | رفض التحقق بالكامل |
| `downloadDocument($id, $type)` | GET | تحميل وثيقة تحقق (Admin أو المالك) |
| `viewMyDocument($type)` | GET | عرض وثيقة المستخدم الحالي (Web Session Auth) |
| `reuploadDocument()` | POST | إعادة رفع/تحديث وثائق (Partner) |
| `myDocuments()` | GET | حالات وثائق المستخدم الحالي |

### 12. AssistantController (10 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| **واجهات المساعد:** | | |
| `getRequests()` | GET | الدعوات المعلقة للمساعد |
| `respondToRequest($id)` | POST | قبول/رفض دعوة |
| `toggleAvailability()` | PATCH | تبديل التوفر |
| `getAcceptedEvents()` | GET | الفعاليات المقبولة (حالية + قادمة) مع إحصائيات |
| `getEventWorkDetails($id)` | GET | تفاصيل العمل في فعالية + قائمة المشاركين |
| `getHistory()` | GET | سجل الفعاليات السابقة مع بحث |
| `getEventStats($id)` | GET | إحصائيات مفصلة لفعالية سابقة |
| **واجهات المدير:** | | |
| `getAvailableAssistants()` | GET | المساعدين المتوفرين مع فحص التعارض الزمني |
| `sendInvitation()` | POST | إرسال دعوة لمساعد + فحص التعارض + إيميل |
| `getManagerInvitations()` | GET | جميع الدعوات المرسلة مع إحصائيات المسح |
| `cancelInvitation($id)` | DELETE | إلغاء دعوة (معلقة أو مقبولة) |

### 13. AssistantAnalyticsController (2 وظيفتين)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `getHistory($id)` | GET | سجل المسح المفصل لمساعد (Manager) |
| `getStats($id)` | GET | إحصائيات مجمعة: مسح حسب الفعالية + توزيع ساعي |

### 14. CompanyAnalyticsController (2 وظيفتين)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `overview()` | GET | إحصائيات الشركة: طلبات، نسبة قبول، إكمال بروفايل |
| `myExhibitions()` | GET | معارض الشركة مع حالات المفاوضات |

### 15. AnalyticsController (6 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `system()` | GET | إحصائيات النظام الشاملة (Admin) + إحصائيات المعارض |
| `managerOverview()` | GET | ملخص المدير + إحصائيات المعارض |
| `event($id)` | GET | إحصائيات فعالية (Admin/Manager/Sponsor المرتبط) |
| `users()` | GET | قائمة المستخدمين الموثقين (Admin) |
| `toggleStatus($id)` | PATCH | تعليق/تفعيل حساب + حذف توكناته (Admin) |
| `deleteUser($id)` | DELETE | حذف مستخدم (Admin) |

### 16. ProfileController (4 وظائف) - خاص بـ Web Views
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `show($user)` | GET | عرض بروفايل مستخدم (صفحة عامة) |
| `edit()` | GET | صفحة تعديل البروفايل |
| `updateInformation()` | PUT | تحديث المعلومات الشخصية |
| `updateSecurity()` | PUT | تحديث البريد وكلمة المرور |

### 🔔 SystemNotification (Notification Class)
- **الموقع:** `app/Notifications/SystemNotification.php`
- **القناة:** `database` فقط (يُخزن في جدول notifications)
- **الحقول:** `title`, `message`, `type` (event/sponsorship/ticket/verification/agreement/exhibition/system), `icon` (emoji), `action_url`, `related_id`
- **يُستخدم في:** EventController, SponsorshipController, AgreementController, ExhibitionController, VerificationController, AssistantController, VenueController, TicketController

---

## 🔧 الـ Services

### 1. AgreementWordService
- **الموقع:** `app/Services/AgreementWordService.php`
- **الوظائف:**
  - `generate($sponsorshipRequest)` → توليد عقد رعاية Word
  - `generateExhibition($exhibitionApplication)` → توليد عقد معرض Word
  - `generateFinalPdf($sponsorshipRequest, $negotiation)` → توليد عقد نهائي PDF (رعاية)
  - `generateExhibitionFinalPdf($exhibitionApplication, $negotiation)` → توليد عقد نهائي PDF (معرض)

### 2. OtpService
- **الموقع:** `app/Services/OtpService.php`
- **الوظائف:**
  - `send($email)` → إرسال كود OTP عبر الإيميل
  - `verify($email, $code)` → التحقق من الكود + إرجاع reset_token
  - `validateResetToken($email, $token)` → التحقق من صلاحية reset_token

### 3. FcmService
- **الموقع:** `app/Services/FcmService.php`
- **الوظيفة:** إرسال إشعارات عبر Firebase Cloud Messaging

---

## ✉️ الـ Mail Classes

| الكلاس | الوصف |
|--------|-------|
| `AssistantAccountCreated` | إيميل للمساعد الجديد بالبيانات (اسم، بريد، كلمة مرور، فعالية) |
| `AssistantAccountUpdated` | إيميل للمساعد عند تحديث بياناته |
| `AssistantInvitation` | إيميل دعوة مساعد للعمل في فعالية |
| `PasswordResetCode` | إيميل كود OTP لإعادة تعيين كلمة المرور |

### قوالب الإيميل (Views)
| الملف | الوصف |
|-------|-------|
| `emails/assistant-account.blade.php` | قالب إيميل إنشاء حساب مساعد |
| `emails/assistant-account-updated.blade.php` | قالب إيميل تحديث حساب مساعد |
| `emails/assistant-invitation.blade.php` | قالب إيميل دعوة مساعد |
| `emails/password-reset-code.blade.php` | قالب إيميل كود OTP |

---

## ⏰ الـ Console Commands

| الأمر | الوصف |
|-------|-------|
| `AutoRejectExpiredExhibitionApplications` | رفض تلقائي لطلبات المعارض المنتهية |
| `PruneRejectedPartners` | تنظيف حسابات الشركاء المرفوضين |
| `SendEventReminders` | إرسال تذكيرات الفعاليات |

---

## 🛣 الـ API Routes

### المسارات العامة (بدون مصادقة)
```
POST   /api/register                    → تسجيل مستخدم عادي (User/Assistant)
POST   /api/register/partner            → تسجيل شريك (Manager/Sponsor/Company)
POST   /api/login                       → تسجيل الدخول
POST   /api/password/forgot             → إرسال كود OTP
POST   /api/password/verify-code        → التحقق من كود OTP
POST   /api/password/reset              → إعادة تعيين كلمة المرور

GET    /api/events                      → قائمة الفعاليات المعتمدة والمنشورة
GET    /api/events/{id}                 → تفاصيل فعالية
GET    /api/events/{id}/reviews         → تقييمات ومراجعات فعالية
GET    /api/categories                  → أنواع الفعاليات المتاحة

GET    /api/venues                      → قائمة الأماكن المتاحة
GET    /api/venues/{id}/bookings        → حجوزات وصيانة مكان
```

### المسارات المحمية (auth:sanctum)
```
── البروفايل والمستخدمين ──
GET    /api/profile                        → بروفايلي
GET    /api/profile/{id}                   → بروفايل مستخدم عام
GET    /api/profile/{id}/portfolio         → محفظة أعمال مستخدم
PUT    /api/profile                        → تحديث بروفايلي
PATCH  /api/profile/availability           → تبديل التوفر (Sponsor/Company)
POST   /api/users                          → إنشاء مستخدم (Admin/Manager)
GET    /api/assistants                     → قائمة مساعديني (Manager)
GET    /api/assistants/{id}/history        → سجل مسح مساعد (Manager)
GET    /api/assistants/{id}/stats          → إحصائيات مساعد (Manager)
PATCH  /api/assistants/{id}/status         → تعليق/تفعيل مساعد
PUT    /api/assistants/{id}                → تعديل مساعد
DELETE /api/assistants/{id}                → حذف مساعد (Manager)
GET    /api/sponsors/available             → الرعاة المتوفرين
GET    /api/companies/available            → الشركات المتوفرة

── المصادقة ──
POST   /api/logout                         → تسجيل الخروج

── الفعاليات ──
POST   /api/events                         → إنشاء فعالية (Manager)
PUT    /api/events/{id}/approve            → موافقة (Admin)
PUT    /api/events/{id}/reject             → رفض (Admin)
GET    /api/events/list/pending            → الفعاليات المعلقة (Admin)
GET    /api/events/list/my                 → فعالياتي (Manager)
GET    /api/events/list/all               → كل الفعاليات (Admin)
PATCH  /api/events/{id}/toggle-sponsorship → تبديل حالة الرعاية (Manager)
PATCH  /api/events/{id}/toggle-applications → تبديل حالة الطلبات (Manager)
PATCH  /api/events/{id}/toggle-exhibitor-registration → تبديل تسجيل العارضين
POST   /api/events/{id}/rate              → تقييم فعالية (User)
DELETE /api/events/{id}/rate              → حذف تقييم
PUT    /api/events/{id}/review            → إرسال مراجعة (Admin)
POST   /api/events/{id}/update-pending    → تعديل فعالية معلقة بعد المراجعة (Manager)
DELETE /api/events/{id}                    → حذف فعالية معلقة (Manager)
PUT    /api/events/{id}/agenda            → تعديل أجندة (Manager)
GET    /api/events/{id}/download-document/{type} → تحميل وثيقة فعالية
POST   /api/events/{id}/request-cancellation → طلب إلغاء (Manager)
PUT    /api/events/{id}/approve-cancellation → موافقة إلغاء (Admin)
PUT    /api/events/{id}/reject-cancellation  → رفض إلغاء (Admin)
PATCH  /api/events/{id}/toggle-tickets     → فتح/إغلاق التذاكر (Manager)
PUT    /api/events/{id}/published-schedule → تحديث الجدول المنشور
PATCH  /api/events/{id}/capacity           → تحديث السعة
POST   /api/events/predict-attendance      → التنبؤ بنسبة الحضور بالذكاء الاصطناعي (FastAPI)
POST   /api/events/generate-description    → توليد وصف تسويقي بالذكاء الاصطناعي (Groq Llama 3.3)

── الأماكن (Admin) ──
POST   /api/venues                         → إنشاء مكان
PUT    /api/venues/{id}                    → تعديل مكان
DELETE /api/venues/{id}                    → [معطل] حذف مكان

── صيانة الأماكن (Admin) ──
GET    /api/venues/{id}/maintenance            → فترات صيانة مكان
POST   /api/venues/{id}/maintenance            → إضافة فترة صيانة
DELETE /api/venues/{id}/maintenance/{periodId}  → حذف فترة صيانة

── التذاكر ──
POST   /api/tickets                        → حجز تذكرة
GET    /api/my-tickets                     → تذاكري

── تسجيل الحضور ──
POST   /api/checkin                        → مسح QR Code (Assistant)
GET    /api/checkin/event/{id}             → قائمة المشاركين

── الرعاية ──
POST   /api/sponsorship                    → إنشاء طلب رعاية (Manager/Sponsor)
GET    /api/sponsorship                    → قائمة طلبات الرعاية
PUT    /api/sponsorship/{id}               → قبول/رفض طلب + إنشاء عقد تلقائياً
PATCH  /api/sponsorship/{id}/tier          → تحديث تصنيف الراعي

── المعارض ──
POST   /api/exhibition                     → إنشاء طلب مشاركة (Company/Manager)
GET    /api/exhibition                     → قائمة طلبات المعرض
GET    /api/exhibition/{id}                → تفاصيل طلب
PUT    /api/exhibition/{id}                → قبول/رفض طلب + إنشاء عقد تلقائياً
PATCH  /api/exhibition/{id}/booth          → تخصيص جناح

── مخزون المعارض (أجنحة ومناطق) ──
GET    /api/exhibition/inventory/{eventId}           → مناطق وأجنحة المعرض
POST   /api/exhibition/inventory/{eventId}/zones     → إنشاء منطقة
DELETE /api/exhibition/inventory/zones/{id}           → حذف منطقة
POST   /api/exhibition/inventory/zones/{zoneId}/booths → إنشاء جناح
POST   /api/exhibition/inventory/zones/{zoneId}/booths/batch → إنشاء أجنحة دفعة
PUT    /api/exhibition/inventory/booths/{id}          → تعديل جناح
DELETE /api/exhibition/inventory/booths/{id}          → حذف جناح

── العقود ──
POST   /api/agreements/{id}/generate           → توليد عقد أولي
GET    /api/agreements/{id}                    → تفاصيل العقد
GET    /api/agreements/{id}/download/{version?} → تحميل نسخة
GET    /api/agreements/{id}/download-final     → تحميل العقد النهائي
POST   /api/agreements/{id}/upload             → رفع نسخة جديدة
PUT    /api/agreements/{id}/respond            → قبول/رفض/طلب تعديل
PUT    /api/agreements/{id}/cancel             → إلغاء المفاوضة

── الإشعارات ──
GET    /api/notifications                  → آخر 50 إشعار + عدد غير المقروء
PUT    /api/notifications/read-all         → تحديد الكل كمقروء
PUT    /api/notifications/{id}/read        → تحديد إشعار كمقروء
POST   /api/fcm-token                      → حفظ توكن FCM

── الإحصائيات ──
GET    /api/analytics/system               → إحصائيات النظام (Admin)
GET    /api/analytics/manager              → ملخص المدير
GET    /api/analytics/event/{id}           → إحصائيات فعالية
GET    /api/analytics/users                → قائمة المستخدمين الموثقين (Admin)
PATCH  /api/analytics/users/{id}/status    → تعليق/تفعيل مستخدم (Admin)
DELETE /api/analytics/users/{id}           → حذف مستخدم (Admin)

── التحقق (Admin) ──
GET    /api/verifications/pending              → طلبات التحقق المعلقة
PUT    /api/verifications/{id}/review          → مراجعة وثائق (قبول/رفض لكل وثيقة)
PUT    /api/verifications/{id}/reject          → رفض التحقق بالكامل
GET    /api/verifications/{id}/document/{type} → تحميل وثيقة
POST   /api/verifications/reupload             → إعادة رفع الوثائق (Partner)
GET    /api/verifications/my-documents         → حالات وثائقي

── نظام دعوات المساعدين (Manager) ──
GET    /api/manager/available-assistants       → المساعدين المتوفرين
POST   /api/manager/invite-assistant           → إرسال دعوة
GET    /api/manager/invitations                → جميع الدعوات المرسلة
DELETE /api/manager/invitations/{id}           → إلغاء دعوة

── واجهات المساعد ──
GET    /api/assistant/requests                 → الدعوات المعلقة
POST   /api/assistant/requests/{id}/respond    → قبول/رفض دعوة
PUT|PATCH /api/assistant/availability          → تبديل التوفر
GET    /api/assistant/work                     → الفعاليات المقبولة (حالية + قادمة)
GET    /api/assistant/work/{id}                → تفاصيل العمل في فعالية
GET    /api/assistant/history                  → سجل الفعاليات السابقة
GET    /api/assistant/history/{id}/stats       → إحصائيات فعالية سابقة

── إحصائيات الشركات ──
GET    /api/company/analytics                  → إحصائيات الشركة
GET    /api/company/exhibitions                → معارض الشركة
```

### مسار Storage Proxy
```
GET    /api/storage-proxy/{path}               → بروكسي تخزين مع CORS (Flutter Web Fix)
```

---

## 🌐 الـ Web Routes

```
/                                  → يحول لـ /login
/login                             → تسجيل الدخول (Guest)
/register                          → تسجيل مستخدم عادي (Guest)
/register/partner                  → تسجيل شريك بوثائق تحقق (Guest)
/forgot-password                   → نسيان كلمة المرور (Guest)
/pending-verification              → صفحة انتظار مراجعة التحقق

── مسارات مشتركة (مصادق) ──
/profile                           → صفحة البروفايل
/profile/edit                      → تعديل البروفايل
/profile/{user}                    → بروفايل مستخدم عام
/user-profile                      → صفحة بروفايل المستخدم
/my-document/{type}                → عرض وثيقة التحقق (Session Auth)
PUT /profile/information           → تحديث المعلومات
PUT /profile/security              → تحديث الأمان

── مسارات Admin ──
/admin/dashboard                   → لوحة تحكم Admin
/admin/events                      → إدارة الفعاليات
/admin/users                       → إدارة المستخدمين
/admin/venues                      → إدارة الأماكن
/admin/verifications               → مراجعة طلبات تحقق الشركاء
/admin/event-stats/{id}            → إحصائيات فعالية محددة

── مسارات Event Manager (+ verified_partner middleware) ──
/manager/dashboard                 → لوحة تحكم المدير
/manager/events                    → إنشاء وإدارة الفعاليات
/manager/assistants                → إدارة المساعدين والدعوات
/manager/assistants/{id}/stats     → إحصائيات مساعد
/manager/attendance                → سجل الحضور
/manager/sponsorship               → إدارة الرعاية
/manager/exhibition                → إدارة المعارض
/manager/event-stats/{id}          → إحصائيات فعالية محددة

── مسارات Sponsor/Company المشتركة (+ verified_partner middleware) ──
/sponsor/dashboard                 → لوحة تحكم الراعي
/sponsor/events                    → تصفح الفعاليات المتاحة
/sponsor/requests                  → طلبات الرعاية
/sponsor/history                   → سجل الرعايات السابقة
/sponsor/event-stats/{id}          → إحصائيات فعالية

── مسارات Company (+ verified_partner middleware) ──
/company/dashboard                 → لوحة تحكم الشركة
/company/exhibitions               → إدارة المعارض
/company/events                    → تصفح الفعاليات
/company/event-stats/{id}          → إحصائيات فعالية
```

---

## 🔒 الـ Middleware

### 1. TokenWebAuthMiddleware (`web.auth`)
- يتحقق من وجود `auth_token` في الـ cookies
- يبحث عن التوكن في `personal_access_tokens`
- يسجل دخول المستخدم في Session Guard
- يفحص الدور ويحول للصفحة المناسبة إذا الدور غير مطابق
- يدعم أدوار متعددة: `web.auth:Admin` أو `web.auth:Sponsor,Company`

### 2. TokenGuestMiddleware (`web.guest`)
- يتحقق إذا المستخدم مسجل دخول عبر الـ cookie
- إذا مسجل، يحوله لصفحة الداشبورد حسب دوره
- يمنع المستخدمين المسجلين من الوصول لصفحات login/register

### 3. RoleMiddleware (`role`)
- يتحقق من دور المستخدم في API Routes
- يدعم أدوار متعددة: `middleware('role:Admin,Event Manager')`

### 4. EnsurePartnerVerified (`verified_partner`)
- يتحقق أن Event Manager أو Sponsor أو Company لديه `verification_status = verified`
- إذا لم يكن موثقاً: API → 403 JSON | Web → redirect `/pending-verification`
- مُطبَّق على جميع مسارات Manager و Sponsor و Company

---

## 📄 صفحات الواجهة (Views)

### صفحات عامة
| الملف | الوصف |
|-------|-------|
| `login.blade.php` | صفحة تسجيل الدخول |
| `register.blade.php` | صفحة التسجيل |
| `welcome.blade.php` | الصفحة الرئيسية (82KB) |
| `profile.blade.php` | صفحة البروفايل الكاملة |
| `user-profile.blade.php` | صفحة بروفايل المستخدم العام (45KB) |

### صفحات Auth (3 صفحات)
| الملف | الوصف |
|-------|-------|
| `auth/partner-register.blade.php` | تسجيل شريك مع رفع وثائق تحقق (4 وثائق) |
| `auth/pending-verification.blade.php` | صفحة انتظار مراجعة التحقق |
| `auth/forgot-password.blade.php` | نسيان كلمة المرور (OTP) |

### صفحات Admin (6 صفحات)
| الملف | الوصف |
|-------|-------|
| `admin/dashboard.blade.php` | لوحة التحكم + إحصائيات شاملة |
| `admin/events.blade.php` | إدارة الفعاليات (موافقة/رفض/مراجعة + إلغاء) |
| `admin/users.blade.php` | إدارة المستخدمين الموثقين |
| `admin/venues.blade.php` | إدارة الأماكن + فترات الصيانة |
| `admin/verifications.blade.php` | مراجعة طلبات تحقق الشركاء (قبول/رفض لكل وثيقة) |
| `admin/event-stats.blade.php` | إحصائيات مفصلة لفعالية محددة |

### صفحات Manager (9 صفحات)
| الملف | الوصف |
|-------|-------|
| `manager/dashboard.blade.php` | لوحة التحكم + إحصائيات |
| `manager/events.blade.php` | إنشاء وإدارة الفعاليات (172KB - صفحة ضخمة) |
| `manager/events_backup.blade.php` | نسخة احتياطية من صفحة الفعاليات |
| `manager/assistants.blade.php` | إدارة المساعدين والدعوات |
| `manager/assistant-stats.blade.php` | إحصائيات مساعد مفصلة |
| `manager/attendance.blade.php` | سجل الحضور |
| `manager/sponsorship.blade.php` | إدارة الرعاية (ثنائي الاتجاه + تفاوض عقود) |
| `manager/exhibition.blade.php` | إدارة المعارض (طلبات + أجنحة + مناطق) |
| `manager/event-stats.blade.php` | إحصائيات مفصلة: حضور + تقييمات + رعاة |

### صفحات Sponsor (5 صفحات)
| الملف | الوصف |
|-------|-------|
| `sponsor/dashboard.blade.php` | لوحة التحكم |
| `sponsor/events.blade.php` | تصفح الفعاليات المفتوحة للرعاية |
| `sponsor/requests.blade.php` | طلبات الرعاية (قبول/رفض + تفاوض عقود) |
| `sponsor/history.blade.php` | سجل الرعايات السابقة |
| `sponsor/event-stats.blade.php` | إحصائيات فعالية |

### صفحات Company (3 صفحات)
| الملف | الوصف |
|-------|-------|
| `company/dashboard.blade.php` | لوحة تحكم الشركة + إحصائيات المعارض |
| `company/events.blade.php` | تصفح الفعاليات/المعارض |
| `company/exhibitions.blade.php` | إدارة طلبات المعارض + تفاوض عقود |

### صفحات البروفايل (2 صفحتين)
| الملف | الوصف |
|-------|-------|
| `profile/edit.blade.php` | تعديل البروفايل (27KB) |
| `profile/show.blade.php` | عرض البروفايل العام |

### Partials
| الملف | الوصف |
|-------|-------|
| `partials/_sidebar-footer.blade.php` | فوتر الشريط الجانبي |

### PDF Templates
| الملف | الوصف |
|-------|-------|
| `pdf/agreement.blade.php` | قالب عقد الرعاية (PDF) |
| `pdf/agreement-final.blade.php` | قالب العقد النهائي (PDF) |

### Email Templates (4 قوالب)
| الملف | الوصف |
|-------|-------|
| `emails/assistant-account.blade.php` | إنشاء حساب مساعد |
| `emails/assistant-account-updated.blade.php` | تحديث حساب مساعد |
| `emails/assistant-invitation.blade.php` | دعوة مساعد لفعالية |
| `emails/password-reset-code.blade.php` | كود OTP |

---

## ⭐ الميزات الرئيسية

### 1. 🎫 نظام التذاكر و QR Code
- حجز تذكرة واحدة لكل مستخدم لكل فعالية
- إنشاء QR Code فريد: `{RANDOM_10}-{EVENT_ID}-{USER_ID}`
- رقم تذكرة تسلسلي لكل فعالية (`ticket_number`)
- عرض QR Code عبر API خارجي: `api.qrserver.com`
- فحص السعة قبل الحجز (nullable capacity)
- فحص `is_tickets_open` قبل الحجز
- حالات التذكرة: `unused`, `used`, `cancelled`
- **دعم كامل للفعاليات متعددة الأيام:** تسمح التذكرة الواحدة والـ QR Code الفريد للحاضر بتسجيل حضوره مرة واحدة يومياً طوال أيام الفعالية.

### 2. ✅ تسجيل الحضور (Check-in)
- المساعد يمسح QR Code
- فحص أن المساعد مرتبط بنفس الفعالية (نظام جديد + قديم)
- تسجيل في `attendance_logs` مع وقت المسح
- **منع المسح المتكرر في نفس اليوم:** يقوم النظام بالتحقق الاستباقي لمنع تسجيل حضور التذكرة أكثر من مرة خلال نفس اليوم. في حال محاولة المسح مجدداً في نفس اليوم، يتم إرجاع خطأ 422 ("تم تسجيل حضور هذه التذكرة اليوم بالفعل").
- **دعم الحضور متعدد الأيام:** يتم حفظ كل عملية مسح يومية كعنصر مستقل في جدول `attendance_logs` يحتوي على معرف التذكرة، ومعرف المساعد الماسح، ووقت المسح الدقيق.
- **تحديث حالة التذكرة:** تتحول حالة التذكرة إلى `used` عند أول مسح ناجح وتبقى كذلك لأغراض التقييم والتحليلات.
- **إحصائيات تفصيلية ديناميكية للمشاركين:** ترجع واجهة المشاركين تفاصيل تشمل:
  - `scanned_today`: قيمة منطقية (Boolean) تحدد ما إذا تم تسجيل حضور المشارك في اليوم الحالي.
  - `total_days_attended`: عدد الأيام الفريدة الكلي التي قام فيها المشارك بتسجيل الحضور للفعالية حتى الآن.

### 3. 🤝 نظام الرعاية المتقدم المتكامل
- **الطلب المزدوج:** Manager → Sponsor أو Sponsor → Manager
- **تقييم الرعاة وتصنيفهم (Tiering System):** فرز الرعاة إلى مستويات حصرية (Diamond 💎, Gold 🥇, Silver 🥈, Bronze 🥉)
- **منع التكرار (Unique Rank):** التحقق الاستباقي لضمان عدم تكرار رتبة راعيين في نفس الفعالية
- **نظام تفاوض العقود:** قبول أولي → حالة `negotiating` → توليد عقد Word تلقائياً → تبادل نسخ → قبول نهائي → إصدار PDF
- **تخصيص وتعديل مرن (Edit Rank):** واجهة مخصصة تتيح لمدير الفعالية تغيير تصنيف الممول
- **الرفض التلقائي:** رفض تلقائي للطلبات المعلقة عند بدء الفعالية

### 4. 🏛️ نظام المعارض (Exhibition System)
- **أنواع الفعاليات:** الفعالية تصبح معرضاً تلقائياً عند اختيار نوع "معرض"
- **الطلب المزدوج:** Company ← → Event Manager
- **إدارة الأجنحة:** مناطق (Zones) + أجنحة (Booths) مع تخصيص ديناميكي
- **إنشاء دفعي:** إنشاء عدة أجنحة دفعة واحدة بـ prefix
- **قفل قبل 14 يوم:** لا يمكن تعديل المخطط أو تغيير الأجنحة قبل 14 يوم من الفعالية
- **تسجيل عارضين:** فتح/إغلاق تسجيل العارضين مع قفل تلقائي قبل 30/60 يوم
- **عقود تلقائية:** نفس نظام تفاوض العقود المستخدم في الرعاية

### 5. 📝 نظام العقود والتفاوض (Agreement Negotiation)
- **توليد تلقائي:** عقد Word يُنشأ تلقائياً عند قبول طلب رعاية أو معرض
- **تبادل النسخ:** رفع نسخ متعددة (docx/doc/pdf) مع رسائل
- **إجراءات الرد:** قبول / رفض / طلب تعديل
- **حماية:** لا يمكن للطرف المُقدِّم قبول طلبه الخاص
- **العقد النهائي:** إصدار PDF نهائي عند القبول
- **حالات:** draft → pending_review → accepted / rejected / revision_requested
- **إلغاء:** إمكانية إلغاء المفاوضة مع سبب
- **إشعارات:** إشعار تلقائي لكل إجراء

### 6. 📊 نظام التحليلات
- **Admin:** إحصائيات شاملة (مستخدمين بالأدوار، فعاليات بالحالة والنوع، اشتراكات شهرية) + إحصائيات المعارض
- **Manager:** إحصائيات فعالياته (حضور، تذاكر، نسب الإشغال) + إحصائيات المعارض
- **Company:** إحصائيات المعارض + نسبة قبول + إكمال البروفايل
- **Event Stats:** جدول الحضور بالساعة، ساعة الذروة، آخر 10 check-ins، إيرادات الرعاة

### 7. 🏢 إدارة الأماكن
- نظام جدولة متقدم: فترة صباحية + مسائية + يوم كامل
- فحص تعارض المواعيد بالدقة (slot-level overlap detection)
- فحص أن سعة الفعالية لا تتجاوز سعة المكان
- نظام صيانة الأماكن (Maintenance Periods) مع فحص التعارض
- الحذف معطل للحفاظ على الأرشيف
- Admin يشوف كل الأماكن، غيره يشوف المتاحة فقط
- دعم الأماكن الخارجية (External Venues) مع إثبات حجز

### 8. 👤 نظام البروفايلات الموحد
- بروفايل واحد لكل مستخدم (Profile)
- نوعين: individual (للأفراد) و company (للشركات/الرعاة)
- جهات اتصال ديناميكية (ProfileContact)
- حالة التوفر (is_available) للرعاة والشركات والمساعدين
- نوع الشركة (company_type) للرعاة والشركات

### 9. 🔔 نظام الإشعارات (Laravel Notifications + FCM)
- قناة `database` — مُخزّن في جدول `notifications` (UUID)
- كلاس واحد `SystemNotification` بحقول: title, message, type, icon, action_url, related_id
- أنواع الإشعارات: `event` | `sponsorship` | `ticket` | `verification` | `agreement` | `exhibition` | `system`
- يُرسَل تلقائياً عند: موافقة/رفض فعالية، قبول/رفض رعاية، تقييم جديد، تحقق الهوية، عقد جديد، دعوة مساعد، حجز تذكرة
- دعم `markAllRead` لتحديد كل الإشعارات دفعة واحدة
- دعم Firebase Cloud Messaging (FCM) للإشعارات الفورية على الموبايل
- حفظ `fcm_token` لكل مستخدم

### 10. 📄 نظام العقود والوثائق
- عند قبول طلب رعاية/معرض يتم إنشاء عقد Word تلقائياً
- يُحفظ في `storage/app/public/agreements/`
- يستخدم قوالب `pdf/agreement.blade.php` و `pdf/agreement-final.blade.php`
- دعم تحميل وثائق الوزارة وإثبات الحجز

### 11. ✅ نظام التحقق من هوية الشركاء (Verification)
- يُطلب من كل Event Manager, Sponsor, Company رفع وثائق تحقق عند التسجيل
- **4 وثائق (Event Manager):** سجل تجاري، شهادة ضريبية، عقد تأسيس، رخصة ممارسة
- **2 وثائق (Sponsor/Company):** سجل تجاري، شهادة ضريبية
- **مراجعة فردية:** Admin يراجع كل وثيقة على حدة (قبول/رفض مع ملاحظة)
- **تحديث الوثائق:** الموثقون يمكنهم تحديث وثائقهم → تظهر للـ Admin كـ `pending_update`
- Middleware `EnsurePartnerVerified` يحجب الوصول حتى تتم الموافقة
- حالات التحقق: `pending` → `verified` أو `rejected` أو `changes_requested`

### 12. ⭐ نظام التقييمات
- User يُقيّم فعالية (1-5 نجوم) + نص مراجعة شرط: لديه تذكرة used + الفعالية بدأت
- قيد UNIQUE(user_id, event_id): تقييم واحد قابل للتحديث والحذف
- `average_rating` محسوب ديناميكياً على Event model (مع دعم preloaded aggregate)
- إشعار تلقائي للمدير عند كل تقييم جديد

### 13. 🗓 نظام الجدولة المتقدمة
- **جدولة متعددة الأيام:** كل فعالية لها جدول تفصيلي (تاريخ + فترة + وقت بداية/نهاية)
- **أماكن داخلية:** فترات صباحية/مسائية/يوم كامل مع فحص التعارض
- **أماكن خارجية:** جدول حر مع إثبات حجز
- **الأجندة:** جدول تفصيلي لكل يوم مع فحص التداخل + حدود الوقت
- **الجدول المنشور:** جدول مُعدّل يُعرض للجمهور مع فلترة الأجندة
- **حجز مسبق:** 60 يوم على الأقل قبل الفعالية

### 14. 🚫 نظام الإلغاء
- Manager يطلب إلغاء فعالية معتمدة (30 يوم قبل البدء)
- تعليق فوري لبيع التذاكر والرعاية
- Admin يوافق أو يرفض الإلغاء
- عند الموافقة: إلغاء جميع التذاكر + إلغاء جميع طلبات الرعاية + إشعار جميع الأطراف

### 15. 👥 نظام دعوات المساعدين
- **Manager:** يبحث عن مساعدين متوفرين + يرسل دعوات مع فحص التعارض الزمني
- **Assistant:** يقبل أو يرفض الدعوات + يتبدل التوفر
- **فحص التعارض:** يتحقق من أن المساعد ليس مشغولاً بفعالية أخرى في نفس الوقت
- **إيميل الدعوة:** إرسال إيميل للمساعد عند الدعوة
- **إلغاء:** المدير يمكنه إلغاء دعوة معلقة أو مقبولة (مع إشعار)

### 16. 🔑 نظام نسيان كلمة المرور (OTP)
- 3 خطوات: إرسال كود → تحقق → تغيير كلمة المرور
- كود OTP مكون من 6 أرقام، صالح لمدة 5 دقائق
- 3 محاولات كحد أقصى للتحقق
- Rate limiting: 60 ثانية بين كل إرسال + 5 إرسالات بالساعة
- إلغاء جميع التوكنات بعد تغيير كلمة المرور

### 17. 🔍 نظام مراجعة الفعاليات (Admin Review)
- Admin يرسل مراجعة مع تحديد الحقول المطلوب تعديلها
- Manager يعدّل الحقول المحددة فقط
- الحقول القابلة للمراجعة: title, description, event_type, capacity, image, ministry_document, booking_proof, venue, dates, agenda
- إشعار تلقائي عند إرسال المراجعة وعند التعديل

### 18. 📱 نظام الاهتمامات (Interests)
- المستخدم يختار أنواع الفعاليات المهتم بها
- عند نشر فعالية جديدة تتطابق مع اهتماماته، يتلقى إشعاراً تلقائياً

### 19. 🔐 نظام فصل المنصات (Platform Separation)
- **تسجيل الدخول:** يتحقق من المنصة (web/mobile)
- **أدوار الموبايل:** User, Assistant
- **أدوار الويب:** Admin, Event Manager, Sponsor, Company
- حساب الموبايل لا يمكنه تسجيل الدخول عبر الويب والعكس

### 20. 🤖 نظام التحليل والتنبؤ الذكي بالذكاء الاصطناعي (AI Microservice)
- **خدمة منفصلة (Microservice):** مبنية بالكامل باستخدام لغة بايثون وإطار FastAPI وتعمل بشكل مستقل على المنفذ `8001`.
- **التنبؤ بنسب الحضور المتوقعة (Attendance Prediction Model):**
  - يعتمد على خوارزمية **Gradient Boosting Regressor** متطورة للتنبؤ الفعلي والدقيق بالحضور.
  - يحلل مجموعة من الميزات الأساسية: نوع الفعالية (Event Type)، مدة الفعالية بالأيام (Total Days)، تضمنها لعطلة نهاية الأسبوع (Includes Weekend)، والفترة اليومية (Time Period: صباحي أو مسائي).
  - يوفر تقديرات مرنة بـ **نطاق ثقة تنبؤي (Prediction Intervals)** تشمل الحد الأدنى المتوقع (15% Quantile) والحد الأقصى المتوقع (85% Quantile) عبر التدريب الموازي لـ Quantile Regression.
- **التعلّم المستمر والتدريب التلقائي (Continuous Learning & Auto-Retraining):**
  - تتيح واجهة البرمجة `POST /retrain` استقبال بيانات الفعاليات المنتهية وإرسال حضورها الفعلي لإضافته لقاعدة البيانات المحلية وتحديثها فورياً.
  - تتم عملية إعادة التدريب للنماذج الثلاثة (المتوسط، والأدنى، والأقصى) تلقائياً مع تحديث النسخ المحملة بالذاكرة بنظام آمن ومتوافق خيطياً (Thread-safe Lock) لمنع أي تعارض أثناء الخدمة المستمرة (Hot-Swapping).
- **التوليد التلقائي للوصف التسويقي (AI Description Generation):**
  - تكامل متطور وسريع مع **Groq API** باستخدام النموذج اللغوي الضخم المتفوق **Llama 3.3 70B Versatile**.
  - توليد أوصاف جذابة واحترافية للفعاليات باللغة العربية الفصحى بناءً على العنوان ونوع الفعالية، في حدود 2 إلى 4 جمل تسويقية محفزة للفئة المستهدفة.
  - نظام ذكي لتنظيف المخرجات من الرموز والبادئات غير المرغوبة، مع تمرير ذكي لأخطاء الاستهلاك ومعدل الطلبات (Rate Limit Pass-through - 429).
- **التكامل مع Laravel (Backend Integration):**
  - يستقبل `EventController` الطلبات ويقوم ديناميكياً بترجمة وتحويل تصنيفات الفعاليات من العربية للإنجليزية لتتوافق مع نموذج الذكاء الاصطناعي.
  - التخاطب داخلياً عبر `Http::timeout` مع التوجيه التلقائي للمنفذ المستهدف المحدد في ملف التكوين عبر المتغير `EVENTHUB_AI_URL`.

---

## 📱 تطبيق الموبايل (Flutter)

> التطبيق يتصل بنفس الـ Laravel Backend عبر Sanctum Token API.
> Base URL: `http://127.0.0.1:8000/api` (للتطوير عبر USB: `adb reverse tcp:8000 tcp:8000`)

### الأدوار المدعومة في الموبايل
| الدور | الوصول |
|-------|--------|
| User | الشاشة الرئيسية + تذاكر + بروفايل + تقييمات + إشعارات + بحث |
| Assistant | شاشة دعوات + عمل + مسح QR + سجل + إحصائيات |

---

### 📋 واجهات تطبيق المستخدم العادي (User)

#### 1. Splash Screen
- **الملف:** `screens/splash_screen.dart`
- **الوظيفة:** شاشة البداية مع أنيميشن (fade + scale)
- **المنطق:** تحقق من `SharedPreferences` للتوكن → توجيه لـ HomeScreen أو LoginScreen أو AssistantMainNavigation
- **API المستخدم:** لا شيء (محلي فقط)

#### 2. Login Screen
- **الملف:** `screens/auth/login_screen.dart`
- **الحقول:** Email + Password
- **API:** `POST /api/login` (مع platform=mobile) → يحفظ token + user في SharedPreferences
- **التوجيه بعد الدخول:** `role == 'Assistant'` → AssistantMainNavigation | غيره → MainNavigation

#### 3. Register Screen
- **الملف:** `screens/auth/register_screen.dart`
- **الحقول:** Full Name + Email + Password + Confirm Password + Role (User/Assistant)
- **التحقق:** كلمة مرور 8 أحرف على الأقل + تطابق
- **API:** `POST /api/register` (يسجّل بدور User أو Assistant)

#### 4. Forgot Password Screen
- **الملف:** `screens/auth/forgot_password_screen.dart`
- **الحقل:** Email
- **API:** `POST /api/password/forgot` → إرسال كود OTP

#### 5. Verify Code Screen
- **الملف:** `screens/auth/verify_code_screen.dart`
- **الحقل:** 6-digit OTP code
- **API:** `POST /api/password/verify-code` → reset_token

#### 6. Reset Password Screen
- **الملف:** `screens/auth/reset_password_screen.dart`
- **الحقول:** New Password + Confirm Password
- **API:** `POST /api/password/reset` (مع reset_token)

#### 7. Main Navigation
- **الملف:** `screens/user/main_navigation.dart`
- **التنقل:** Bottom Navigation Bar بـ 3 تبويبات
  - 🔍 **Explore** → HomeScreen
  - 🎫 **Tickets** → MyTicketsScreen
  - 👤 **Profile** → ProfileScreen

#### 8. Home Screen (Explore)
- **الملف:** `screens/user/home_screen.dart`
- **API:** `GET /api/events`
- **الميزات:**
  - بحث نصي (عنوان + مكان + وصف)
  - فلاتر أفقية: All, Technical, Workshop, Conference, Seminar, Cultural, Other
  - التصنيف يعتمد على تحليل النص (keyword matching)
  - Pull-to-refresh + عدد الفعاليات المعروضة
  - الضغط على فعالية → EventDetailsScreen

#### 9. Search Screen
- **الملف:** `screens/user/search_screen.dart`
- **الوظيفة:** بحث متقدم في الفعاليات مع فلاتر

#### 10. Event Details Screen
- **الملف:** `screens/user/event_details_screen.dart`
- **البيانات المعروضة:** عنوان + تاريخ + وقت + مكان + سعة + المنظم + وصف + رعاة + عارضين + أجندة
- **رعاة الفعالية:** شريط أفقي يعرض اسم + شعار + رتبة (Diamond/Gold/Silver/Bronze) بألوان مميزة
- **API:** `POST /api/tickets` (`event_id`) → حجز تذكرة
- **بعد الحجز الناجح:** رسالة نجاح + العودة للقائمة
- **التقييم:** Bottom sheet لتقييم الفعالية (1-5 نجوم + نص)

#### 11. Reviews List Screen
- **الملف:** `screens/user/reviews_list_screen.dart`
- **API:** `GET /api/events/{id}/reviews`
- **الوظيفة:** عرض جميع تقييمات ومراجعات فعالية مع متوسط التقييم

#### 12. My Tickets Screen
- **الملف:** `screens/user/my_tickets_screen.dart`
- **API:** `GET /api/my-tickets`
- **الميزات:**
  - تبويبان: Upcoming | History (مُقسَّم حسب تاريخ الفعالية)
  - كارد التذكرة: اسم الفعالية + تاريخ + مكان + رقم التذكرة + حالة (ACTIVE/USED/CANCELLED)
  - زر QR Code → QRCodeScreen
  - Pull-to-refresh
  - Offline cache عبر SharedPreferences

#### 13. QR Code Screen
- **الملف:** `screens/user/qr_code_screen.dart`
- **الحزمة:** `qr_flutter`
- **المعروض:** كارد تذكرة بيضاء مع QR Code + شريط ملون علوي + حالة (VALID/USED) + رقم التذكرة
- **البيانات:** qr_code string مباشرة من الـ Backend

#### 14. Profile Screen
- **الملف:** `screens/profile_screen.dart` (38KB)
- **المعروض:** اسم + دور + بريد + تاريخ التسجيل + إحصائيات (Total Tickets, Attended, Upcoming)
- **الميزات:**
  - 🔔 جرس الإشعارات مع badge عدد غير المقروء → NotificationsScreen
  - ✏️ Edit Profile Dialog: تعديل اسم + بريد + كلمة مرور + اهتمامات
  - ⚙️ Settings Screen → إعدادات
  - 🚪 Logout مع تأكيد
- **API:**
  - `PUT /api/profile` → تحديث البروفايل

#### 15. Notifications Screen
- **الملف:** `screens/user/notifications_screen.dart`
- **API:**
  - `GET /api/notifications` → قائمة الإشعارات
  - `PUT /api/notifications/{id}/read` → تحديد كمقروء
  - `PUT /api/notifications/read-all` → تحديد الكل كمقروء

#### 16. Public Profile Screen
- **الملف:** `screens/user/public_profile_screen.dart`
- **API:** `GET /api/profile/{id}`
- **الوظيفة:** عرض بروفايل مستخدم عام (منظم أو راعي)

#### 17. Settings Screen
- **الملف:** `screens/user/settings_screen.dart`
- **الوظيفة:** إعدادات التطبيق (لغة + إشعارات + حذف الحساب)

---

### 📋 واجهات المساعد (Assistant)

#### 1. Assistant Main Navigation
- **الملف:** `screens/assistant/assistant_main_navigation.dart`
- **التنقل:** Bottom Navigation Bar بـ 4 تبويبات
  - 📨 Requests → AssistantRequestsScreen
  - 💼 Work → AssistantWorkScreen
  - 📜 History → AssistantHistoryScreen
  - 👤 Profile → ProfileScreen

#### 2. Assistant Requests Screen
- **الملف:** `screens/assistant/assistant_requests_screen.dart`
- **API:** `GET /api/assistant/requests`
- **الوظيفة:** عرض دعوات المدراء المعلقة + قبول/رفض

#### 3. Assistant Work Screen
- **الملف:** `screens/assistant/assistant_work_screen.dart`
- **API:** `GET /api/assistant/work`
- **الوظيفة:** عرض الفعاليات المقبولة (حالية + قادمة) مع إحصائيات

#### 4. Assistant Event Work Screen
- **الملف:** `screens/assistant/assistant_event_work_screen.dart`
- **API:** `GET /api/assistant/work/{id}`
- **الوظيفة:** تفاصيل العمل في فعالية + قائمة المشاركين + إحصائيات المسح

#### 5. Assistant Event Details Screen
- **الملف:** `screens/assistant/assistant_event_details_screen.dart`
- **الوظيفة:** تفاصيل فعالية كاملة من منظور المساعد

#### 6. Scanner Screen
- **الملف:** `screens/assistant/scanner_screen.dart`
- **الحزمة:** `mobile_scanner`
- **الوظيفة:** كاميرا مستمرة مع overlay مخصص لمسح QR Codes
- **API:** `POST /api/checkin` (`{ qr_code: "..." }`)
- **نتائج المسح:**
  | الكود | النتيجة |
  |-------|--------|
  | 200 | ✅ Entry Allowed |
  | 422 | ⚠️ Already Entered (تذكرة مُستخدمة) |
  | 404 | ❌ Invalid Ticket |
  | 403 | 🚫 Not Your Event |
- **حماية:** cooldown 2 ثانية بعد كل مسح لمنع التكرار

#### 7. Event Participants Screen
- **الملف:** `screens/assistant/event_participants_screen.dart`
- **API:** `GET /api/checkin/event/{id}`
- **الوظيفة:** قائمة المشاركين في فعالية مع حالة المسح

#### 8. Assistant History Screen
- **الملف:** `screens/assistant/assistant_history_screen.dart`
- **API:** `GET /api/assistant/history`
- **الوظيفة:** سجل الفعاليات السابقة مع بحث + إحصائيات المسح

---

### 🏗 بنية الموبايل التقنية

#### State Management (Providers)
| Provider | البيانات المُدارة |
|----------|------------------|
| `AuthProvider` | user, token, role, isAuthenticated |
| `EventProvider` | قائمة الفعاليات + حالة التحميل |
| `TicketProvider` | تذاكر المستخدم + upcoming/past/totalAttended |
| `NotificationProvider` | الإشعارات + عدد غير المقروء |
| `AssistantProvider` | دعوات + فعاليات + سجل |
| `LanguageProvider` | اللغة الحالية + ترجمات (عربي/إنجليزي) |

#### ApiService
- **الملف:** `services/api_service.dart`
- **الطرق:** `get()`, `post()`, `put()`, `delete()`
- **المصادقة:** Bearer Token من SharedPreferences في كل طلب
- **Token Expiry:** isTokenExpired() تتحقق من 401

#### FcmService
- **الملف:** `services/fcm_service.dart`
- **الوظيفة:** إدارة إشعارات Firebase Cloud Messaging + Local Notifications

#### Widgets
| الملف | الوصف |
|-------|-------|
| `widgets/event_card.dart` | كارد فعالية قابل لإعادة الاستخدام (15KB) |
| `widgets/gradient_button.dart` | زر متدرج الألوان |
| `widgets/rate_event_bottom_sheet.dart` | Bottom Sheet لتقييم فعالية |

#### Utils
| الملف | الوصف |
|-------|-------|
| `utils/constants.dart` | ثوابت التطبيق (API URL, ألوان, أنماط) |

#### التبعيات (pubspec.yaml)
| الحزمة | الوظيفة |
|--------|--------|
| `http` | HTTP requests |
| `provider` | State management |
| `shared_preferences` | تخزين token + cache |
| `mobile_scanner` | مسح QR Camera |
| `qr_flutter` | عرض QR Code |
| `google_fonts` | خط Inter |
| `intl` | تنسيق التواريخ والأرقام |
| `url_launcher` | فتح روابط خارجية |
| `image_picker` | اختيار صور |
| `firebase_core` | Firebase Core |
| `firebase_messaging` | Firebase Cloud Messaging |
| `flutter_local_notifications` | إشعارات محلية |
| `share_plus` | مشاركة المحتوى |

#### تصميم التطبيق (AppColors)
| اللون | القيمة | الاستخدام |
|-------|---------|----------|
| bgDark | #0D1117 | خلفية رئيسية |
| bgCard | #161B22 | كروت |
| accent | #6E40F2 | لون رئيسي (بنفسجي) |
| accent2 | #22D3EE | لون ثانوي (سماوي) |
| success | #22C55E | نجاح |
| danger | #EF4444 | خطأ |
| warning | #F59E0B | تحذير |

---

## 📁 هيكل الملفات

```
EventHub_AI/
├── model/                                # المجلد المخصص لحفظ النماذج المدربة
│   ├── gb_model.joblib                   # النموذج الرئيسي للتنبؤ النقطي
│   ├── gb_lower.joblib                   # نموذج الحد الأدنى (Quantile 0.15)
│   ├── gb_upper.joblib                   # نموذج الحد الأقصى (Quantile 0.85)
│   └── model_columns.joblib              # أعمدة النموذج وهيكل البيانات المتوافقة
├── main.py                               # التطبيق الرئيسي (FastAPI v2) والمسارات
├── train.py                              # سكربت تدريب النموذج الأولي
├── requirements.txt                      # التبعيات البرمجية لبايثون
├── .env                                  # إعدادات البيئة (GROQ_API_KEY)
├── events_dataset.csv                    # مجموعة البيانات المصغرة
└── synthetic_events_dataset.csv          # مجموعة البيانات الاصطناعية المجمعة للتدريب

EventHub/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── AutoRejectExpiredExhibitionApplications.php
│   │       ├── PruneRejectedPartners.php
│   │       └── SendEventReminders.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AgreementController.php       # عقود + تفاوض (6 وظائف)
│   │   │   ├── AnalyticsController.php       # إحصائيات النظام والمدير
│   │   │   ├── AssistantAnalyticsController.php # إحصائيات المساعدين
│   │   │   ├── AssistantController.php       # نظام دعوات المساعدين (10 وظائف)
│   │   │   ├── AuthController.php            # تسجيل/دخول/بروفايل/OTP (19 وظيفة)
│   │   │   ├── CheckinController.php         # مسح QR + سجل الحضور
│   │   │   ├── CompanyAnalyticsController.php # إحصائيات الشركات
│   │   │   ├── EventController.php           # CRUD فعاليات + تقييمات + إلغاء (22 وظيفة)
│   │   │   ├── ExhibitionController.php      # نظام المعارض (5 وظائف)
│   │   │   ├── ExhibitionInventoryController.php # أجنحة ومناطق (7 وظائف)
│   │   │   ├── NotificationController.php    # إشعارات + FCM
│   │   │   ├── ProfileController.php         # بروفايل Web Views
│   │   │   ├── SponsorshipController.php     # رعاية ثنائية + عقود (4 وظائف)
│   │   │   ├── TicketController.php          # حجز تذاكر + QR
│   │   │   ├── VenueController.php           # إدارة الأماكن + صيانة (7 وظائف)
│   │   │   └── VerificationController.php    # تحقق هوية (7 وظائف)
│   │   └── Middleware/
│   │       ├── EnsurePartnerVerified.php      # فحص التحقق
│   │       ├── RoleMiddleware.php             # فحص الأدوار (API)
│   │       ├── TokenGuestMiddleware.php       # حماية صفحات الضيف
│   │       └── TokenWebAuthMiddleware.php     # مصادقة Blade عبر Token
│   ├── Mail/
│   │   ├── AssistantAccountCreated.php        # إيميل إنشاء حساب مساعد
│   │   ├── AssistantAccountUpdated.php        # إيميل تحديث حساب مساعد
│   │   ├── AssistantInvitation.php            # إيميل دعوة مساعد
│   │   └── PasswordResetCode.php              # إيميل كود OTP
│   ├── Notifications/
│   │   └── SystemNotification.php             # كلاس الإشعارات (قناة database)
│   ├── Services/
│   │   ├── AgreementWordService.php           # توليد عقود Word + PDF
│   │   ├── FcmService.php                     # Firebase Cloud Messaging
│   │   └── OtpService.php                     # نظام OTP (إرسال + تحقق)
│   └── Models/
│       ├── AgreementNegotiation.php           # مفاوضات العقود
│       ├── AgreementVersion.php               # نسخ العقود
│       ├── AssistanceRequest.php              # دعوات المساعدين
│       ├── AttendanceLog.php                  # سجل الحضور
│       ├── Event.php                          # الفعاليات (22 حقل + scopes + accessors)
│       ├── EventNotification.php              # إشعارات الفعاليات (Legacy)
│       ├── EventReminder.php                  # تذكيرات الفعاليات
│       ├── EventSponsor.php                   # Pivot الرعاية + tier branding
│       ├── ExhibitionApplication.php          # طلبات المعارض
│       ├── ExhibitionBooth.php                # أجنحة المعرض
│       ├── ExhibitionZone.php                 # مناطق المعرض
│       ├── PasswordResetCode.php              # أكواد OTP
│       ├── Profile.php                        # بروفايلات موحدة
│       ├── ProfileContact.php                 # جهات اتصال
│       ├── Rating.php                         # تقييمات الفعاليات
│       ├── Report.php                         # تقارير (placeholder)
│       ├── SponsorshipRequest.php             # طلبات الرعاية
│       ├── Ticket.php                         # التذاكر
│       ├── User.php                           # المستخدمين (6 أدوار)
│       ├── Venue.php                          # الأماكن + صيانة
│       └── VenueMaintenancePeriod.php         # فترات الصيانة
├── database/
│   └── migrations/                            # 47 migration
├── resources/views/
│   ├── admin/                                 # 6 صفحات
│   ├── auth/                                  # 3 صفحات
│   ├── company/                               # 3 صفحات
│   ├── emails/                                # 4 قوالب إيميل
│   ├── manager/                               # 9 صفحات (+ exhibition + assistant-stats)
│   ├── partials/                              # 1 partial
│   ├── sponsor/                               # 5 صفحات
│   ├── profile/                               # 2 صفحتين
│   ├── pdf/                                   # 2 قالب PDF
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── profile.blade.php
│   ├── user-profile.blade.php
│   └── welcome.blade.php
├── routes/
│   ├── api.php                                # API endpoints (~90 route)
│   └── web.php                                # Web routes (~35 route)
└── public/
    ├── css/style.css                          # تصميم موحد للواجهة
    ├── js/
    │   ├── agreement-v2.js                    # نظام تفاوض العقود
    │   ├── api.js                             # مساعد استدعاء API
    │   ├── auth.js                            # تسجيل/دخول JS
    │   ├── flatpickr-custom.js                # تقويم مخصص
    │   ├── i18n.js                            # ترجمات العربية/الإنجليزية (119KB)
    │   └── notifications.js                   # نظام الإشعارات realtime-like
    └── storage/ → storage/app/public          # صور + عقود PDF/Word
```

### هيكل تطبيق الموبايل (Flutter)
```
EventHub_Mobile/eventhub/
├── lib/
│   ├── main.dart                              # Entry point + Providers setup
│   ├── providers/
│   │   ├── auth_provider.dart                 # إدارة المصادقة
│   │   ├── event_provider.dart                # إدارة الفعاليات
│   │   ├── ticket_provider.dart               # إدارة التذاكر
│   │   ├── notification_provider.dart         # إدارة الإشعارات
│   │   ├── assistant_provider.dart            # إدارة بيانات المساعد
│   │   └── language_provider.dart             # إدارة اللغة + الترجمات (26KB)
│   ├── screens/
│   │   ├── splash_screen.dart                 # شاشة البداية
│   │   ├── profile_screen.dart                # البروفايل (38KB)
│   │   ├── auth/
│   │   │   ├── login_screen.dart              # تسجيل الدخول
│   │   │   ├── register_screen.dart           # التسجيل
│   │   │   ├── forgot_password_screen.dart    # نسيان كلمة المرور
│   │   │   ├── verify_code_screen.dart        # تحقق OTP
│   │   │   └── reset_password_screen.dart     # إعادة تعيين كلمة المرور
│   │   ├── user/
│   │   │   ├── main_navigation.dart           # التنقل الرئيسي
│   │   │   ├── home_screen.dart               # الصفحة الرئيسية (36KB)
│   │   │   ├── search_screen.dart             # البحث المتقدم
│   │   │   ├── event_details_screen.dart      # تفاصيل فعالية (50KB)
│   │   │   ├── my_tickets_screen.dart         # تذاكري
│   │   │   ├── qr_code_screen.dart            # عرض QR Code
│   │   │   ├── reviews_list_screen.dart       # قائمة التقييمات
│   │   │   ├── notifications_screen.dart      # الإشعارات
│   │   │   ├── public_profile_screen.dart     # بروفايل عام
│   │   │   └── settings_screen.dart           # الإعدادات
│   │   └── assistant/
│   │       ├── assistant_main_navigation.dart  # التنقل الرئيسي للمساعد
│   │       ├── assistant_requests_screen.dart  # الدعوات
│   │       ├── assistant_work_screen.dart      # الفعاليات الحالية
│   │       ├── assistant_event_work_screen.dart # تفاصيل العمل
│   │       ├── assistant_event_details_screen.dart # تفاصيل فعالية
│   │       ├── scanner_screen.dart            # مسح QR Code
│   │       ├── event_participants_screen.dart  # قائمة المشاركين
│   │       └── assistant_history_screen.dart   # سجل العمل
│   ├── services/
│   │   ├── api_service.dart                   # HTTP API helper
│   │   └── fcm_service.dart                   # Firebase Cloud Messaging
│   ├── utils/
│   │   └── constants.dart                     # ثوابت التطبيق
│   └── widgets/
│       ├── event_card.dart                    # كارد فعالية
│       ├── gradient_button.dart               # زر متدرج
│       └── rate_event_bottom_sheet.dart        # Bottom Sheet تقييم
├── assets/
│   └── images/logo.png                        # شعار التطبيق
└── pubspec.yaml                               # التبعيات (12 حزمة)
```

---

## 📊 ملخص الأرقام

| العنصر | العدد |
|--------|-------|
| **Controllers** | 16 |
| **Models** | 21 |
| **Notification Classes** | 1 (SystemNotification) |
| **Mail Classes** | 4 |
| **Services** | 3 |
| **Console Commands** | 3 |
| **Migrations** | 47 |
| **API Endpoints (Laravel)** | ~92 |
| **Web Routes** | ~35 |
| **Middleware** | 4 |
| **Blade Views** | ~35 |
| **PDF Templates** | 2 |
| **Email Templates** | 4 |
| **أدوار** | 6 (Admin, Event Manager, Sponsor, Company, User, Assistant) |
| **جداول قاعدة البيانات** | 22 جدول رئيسي |
| **Flutter Screens** | 23 شاشة |
| **Flutter Providers** | 6 |
| **Flutter Widgets** | 3 |
| **Flutter Services** | 2 |
| **JS Files** | 6 |
| **خدمات الذكاء الاصطناعي (FastAPI)** | 1 (Microservice) |
| **مسارات الذكاء الاصطناعي (FastAPI)** | 4 (predict, retrain, generate-description, health) |
| **ملفات بايثون الذكية (Python AI Scripts)** | 2 (main.py, train.py) |
| **النماذج المدربة المخزنة (AI Models)** | 3 (gb_model, gb_lower, gb_upper) |
| **قواعد البيانات المحلية الذكية (Datasets)** | 2 (CSV files) |

---

> 📝 **ملاحظة:** هذا الملف يمثل التوثيق الكامل للمشروع حتى تاريخ 2026-05-25. أي تعديلات مستقبلية يجب تحديث هذا الملف.
