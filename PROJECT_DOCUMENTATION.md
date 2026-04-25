# 📋 EventHub - توثيق المشروع الكامل
# EventHub - Complete Project Documentation

> **آخر تحديث:** 2026-04-25
> **التقنيات:** Laravel 11 + Sanctum + Blade + MySQL + DomPDF + Laravel Notifications
> **نوع المشروع:** نظام إدارة فعاليات (Event Management System)

---

## 📑 فهرس المحتويات

1. [نظرة عامة](#-نظرة-عامة)
2. [البنية التقنية](#-البنية-التقنية)
3. [الأدوار والصلاحيات](#-الأدوار-والصلاحيات-roles)
4. [قاعدة البيانات](#-قاعدة-البيانات)
5. [الـ Models والعلاقات](#-الـ-models-والعلاقات)
6. [الـ Controllers والوظائف](#-الـ-controllers-والوظائف)
7. [الـ API Routes](#-الـ-api-routes)
8. [الـ Web Routes](#-الـ-web-routes)
9. [الـ Middleware](#-الـ-middleware)
10. [صفحات الواجهة (Views)](#-صفحات-الواجهة-views)
11. [الميزات الرئيسية](#-الميزات-الرئيسية)
12. [هيكل الملفات](#-هيكل-الملفات)

---

## 🌐 نظرة عامة

**EventHub** هو نظام متكامل لإدارة الفعاليات والمؤتمرات يشمل:
- إنشاء وإدارة الفعاليات
- نظام حجز تذاكر مع QR Code
- نظام رعاية (Sponsorship) ثنائي الاتجاه
- تسجيل حضور عبر مسح QR Code
- لوحات تحكم تحليلية لكل دور
- نظام بروفايلات موحد لجميع المستخدمين
- إنشاء عقود رعاية PDF تلقائياً

---

## 🏗 البنية التقنية

| المكون | التقنية |
|--------|---------|
| **Backend Framework** | Laravel 11 |
| **Authentication** | Laravel Sanctum (Token-based) |
| **Database** | MySQL |
| **Frontend** | Blade Templates + Vanilla JS + CSS |
| **PDF Generation** | Barryvdh DomPDF |
| **File Storage** | Laravel Storage (public disk) |
| **QR Code** | External API (api.qrserver.com) |

---

## 👥 الأدوار والصلاحيات (Roles)

النظام يحتوي على **5 أدوار** رئيسية:

### 1. 🔴 Admin (المدير العام)
| الصلاحية | الوصف |
|-----------|-------|
| لوحة تحكم تحليلية | إحصائيات النظام الشاملة |
| إدارة الفعاليات | الموافقة/الرفض على الفعاليات المعلقة + عرض جميع الفعاليات |
| إدارة المستخدمين | عرض/إنشاء/تعليق/حذف المستخدمين |
| إدارة الأماكن (Venues) | إنشاء/تعديل الأماكن |
| إنشاء مستخدمين | يمكنه إنشاء Event Manager, Sponsor, User |
| إحصائيات الفعاليات | عرض إحصائيات أي فعالية |

**الصفحات:** `/admin/dashboard`, `/admin/events`, `/admin/users`, `/admin/venues`

---

### 2. 🟢 Event Manager (مدير الفعاليات)
| الصلاحية | الوصف |
|-----------|-------|
| إنشاء فعاليات | إنشاء فعالية جديدة (تحتاج موافقة Admin) |
| إدارة المساعدين | إنشاء/حذف حسابات Assistant لفعالياته |
| الرعاية | إرسال/استقبال طلبات رعاية + فتح/إغلاق باب الرعاية |
| الحضور | عرض قائمة الحاضرين لكل فعالية |
| الإحصائيات | لوحة تحليلية لفعالياته + إحصائيات مفصلة |
| لوحة التحكم | إحصائيات عامة عن فعالياته |

**الصفحات:** `/manager/dashboard`, `/manager/events`, `/manager/assistants`, `/manager/attendance`, `/manager/sponsorship`, `/manager/statistics`

---

### 3. 🟡 Sponsor (الراعي)
| الصلاحية | الوصف |
|-----------|-------|
| تصفح الفعاليات | عرض الفعاليات المفتوحة للرعاية |
| طلبات الرعاية | إرسال/استقبال طلبات رعاية |
| التوفر | تفعيل/إيقاف حالة التوفر (is_available) |
| البروفايل | إدارة بيانات الشركة (اسم، لوغو، وصف) |
| لوحة التحكم | عرض طلبات الرعاية والفعاليات المرتبط بها |

**الصفحات:** `/sponsor/dashboard`, `/sponsor/events`, `/sponsor/requests`

---

### 4. 🔵 User (المستخدم العادي / الحاضر)
| الصلاحية | الوصف |
|-----------|-------|
| حجز تذاكر | حجز تذكرة لفعالية معتمدة |
| عرض التذاكر | عرض تذاكره مع QR Code |
| البروفايل | تعديل بياناته الشخصية |

**الصفحات:** `/profile`

---

### 5. 🟣 Assistant (المساعد)
| الصلاحية | الوصف |
|-----------|-------|
| تسجيل الحضور | مسح QR Code لتسجيل حضور الحاضرين |
| عرض المشاركين | عرض قائمة المشاركين في فعاليته |
| مرتبط بفعالية | كل مساعد مرتبط بفعالية محددة عبر `event_id` |

**الصفحات:** `/profile`

---

## 🗄 قاعدة البيانات

### جداول قاعدة البيانات (39 migration)

#### 1. `users` - المستخدمين
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| name | string | الاسم |
| email | string (unique) | البريد الإلكتروني |
| contact_email | string (nullable) | بريد التواصل |
| password | string (hashed) | كلمة المرور |
| role | enum | الدور: Admin, Event Manager, Sponsor, User, Assistant |
| is_active | boolean (default: true) | حالة الحساب |
| event_id | foreign (nullable) | الفعالية المرتبطة (للمساعدين) |
| avatar / image | string (nullable) | صورة المستخدم |
| phone | string (nullable) | رقم الهاتف |
| bio | text (nullable) | نبذة شخصية |
| social_links | json (nullable) | روابط التواصل الاجتماعي |
| verification_status | enum | حالة التحقق: verified, pending, rejected, changes_requested (default: verified للمستخدمين العاديين) |
| verification_document | string (nullable) | مسار وثيقة التحقق (PDF/صورة) |
| verification_notes | text (nullable) | ملاحظات الـ Admin على طلب التحقق |

#### 2. `events` - الفعاليات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| title | string | عنوان الفعالية |
| description | text | وصف الفعالية |
| event_type | enum | النوع: Conference, Workshop, Exhibition, Entertainment, Seminar, Festival, Other |
| venue_id | foreign | المكان |
| created_by | foreign (users) | منشئ الفعالية |
| start_time | datetime | وقت البداية |
| end_time | datetime | وقت النهاية |
| capacity | integer | السعة القصوى |
| status | enum | الحالة: pending, approved, rejected |
| is_sponsorship_open | boolean | هل باب الرعاية مفتوح |
| rejection_reason | text (nullable) | سبب الرفض |
| image | string (nullable) | صورة الفعالية |

> **ملاحظة:** `event_type` أصبح string يدعم القيم العربية: مؤتمر، ندوة، ورشة عمل، دورة تدريبية، ترفيه، ملتقى علمي، رياضة، تقنية، اجتماعية

#### 3. `venues` - الأماكن
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| name | string | اسم المكان |
| location | string (URL) | رابط الموقع (خريطة) |
| capacity | integer | السعة |
| status | string | الحالة |

#### 4. `tickets` - التذاكر
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| user_id | foreign | المستخدم |
| qr_code | string (unique) | رمز QR |
| status | enum | الحالة: unused, used |

#### 5. `attendance_logs` - سجل الحضور
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| ticket_id | foreign | التذكرة |
| scanned_by | foreign (users) | من قام بالمسح |
| scanned_at | datetime | وقت المسح |

#### 6. `profiles` - البروفايلات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| user_id | foreign (unique) | المستخدم |
| profile_type | enum | individual أو company |
| logo | string (nullable) | الشعار |
| bio | text (nullable) | النبذة |
| phone | string (nullable) | الهاتف |
| contact_email | string (nullable) | بريد التواصل |
| website | string (nullable) | الموقع |
| company_name | string (nullable) | اسم الشركة |
| company_description | text (nullable) | وصف الشركة |
| facebook / instagram / linkedin | string (nullable) | وسائل التواصل |
| is_approved | boolean | معتمد من الإدارة |
| is_available | boolean | متوفر للرعاية |

#### 7. `profile_contacts` - جهات اتصال البروفايل
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| profile_id | foreign | البروفايل |
| type | string | نوع التواصل (phone, email, etc.) |
| value | string | القيمة |

#### 8. `event_sponsor` - الربط بين الفعاليات والرعاة (Pivot)
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| sponsor_id | foreign (users) | الراعي |
| tier | enum | المستوى: diamond, gold, silver, bronze |
| contribution_amount | decimal | مبلغ المساهمة |

#### 9. `sponsorship_requests` - طلبات الرعاية
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| sponsor_id | foreign (users) | الراعي |
| event_manager_id | foreign (users) | مدير الفعالية |
| initiator | enum | من بادر: sponsor أو event_manager |
| message | text (nullable) | رسالة الطلب |
| status | enum | الحالة: pending, accepted, rejected |

#### 10. `notifications` - الإشعارات (Laravel Standard)
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | uuid (primary) | المعرف |
| type | string | كلاس الإشعار (SystemNotification) |
| notifiable_type | string | نوع المستلم (App\Models\User) |
| notifiable_id | bigint | معرف المستلم |
| data | text (JSON) | البيانات: title, message, type, icon, action_url, related_id |
| read_at | timestamp (nullable) | وقت القراءة (null = غير مقروء) |

#### 11. `ratings` - تقييمات الفعاليات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| event_id | foreign | الفعالية |
| user_id | foreign | المستخدم المُقيِّم |
| rating | tinyint (1-5) | التقييم من 1 إلى 5 نجوم |
| review_text | text (nullable) | نص المراجعة |

> **قيد:** `UNIQUE(user_id, event_id)` - تقييم واحد لكل مستخدم لكل فعالية (يمكن تحديثه)

---

## 🔗 الـ Models والعلاقات

### User Model
```
User ─→ hasOne Profile
User ─→ belongsTo Event (للمساعدين)
User ─→ hasMany Event (كـ creator/manager)
User ─→ belongsToMany Event (كـ sponsor عبر event_sponsor)
```

### Event Model
```
Event ─→ belongsTo Venue
Event ─→ belongsTo User (creator)
Event ─→ hasMany Ticket
Event ─→ hasMany Rating
Event ─→ belongsToMany User (sponsors عبر event_sponsor)
```
- **Scopes:** `upcoming()`, `live()`, `ended()`
- **Accessors:** `time_status` → (upcoming / live / ended) | `average_rating` → متوسط التقييم (محسوب ديناميكياً)
- **Methods:** `sponsorsWithProfiles()`, `sponsorsByTier($tier)`

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

---

## 🎮 الـ Controllers والوظائف

### 1. AuthController (11 وظيفة)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `register()` | POST | تسجيل مستخدم جديد (دور User فقط) |
| `registerPartner()` | POST | تسجيل شريك (Event Manager أو Sponsor) مع رفع وثيقة تحقق |
| `login()` | POST | تسجيل الدخول + فحص is_active |
| `logout()` | POST | تسجيل الخروج (حذف جميع التوكنات) |
| `getProfile()` | GET | عرض بروفايل المستخدم الحالي |
| `getPublicProfile($id)` | GET | عرض بروفايل مستخدم عام + متوسط تقييم المدير |
| `getPortfolio($id)` | GET | محفظة أعمال: فعاليات المدير أو فعاليات الراعي |
| `updateProfile()` | PUT | تحديث البروفايل (اسم، بريد، صورة، جهات اتصال) |
| `updateAvailability()` | PATCH | تبديل توفر الراعي |
| `createUser()` | POST | إنشاء مستخدم (Admin: User/Admin فقط، Manager: Assistant فقط) |
| `getAssistants()` | GET | قائمة المساعدين التابعين للمدير |
| `deleteAssistant($id)` | DELETE | حذف مساعد |
| `getAvailableSponsors()` | GET | قائمة الرعاة المتوفرين |

### 2. EventController (10 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | قائمة الفعاليات المعتمدة (للسبونسر: يتطلب is_available=true) |
| `show($id)` | GET | تفاصيل فعالية واحدة مع الرعاة |
| `store()` | POST | إنشاء فعالية (Manager) + فحص السعة + فحص تعارض المكان + إشعار Admin |
| `approve($id)` | PUT | الموافقة (Admin) + إشعار المدير |
| `reject($id)` | PUT | الرفض مع السبب (Admin) + إشعار المدير |
| `pending()` | GET | الفعاليات المعلقة (Admin) + auto-reject المنتهية |
| `myEvents()` | GET | فعاليات المدير + auto-reject المنتهية |
| `all()` | GET | جميع الفعاليات (Admin) |
| `toggleSponsorship($id)` | PATCH | فتح/إغلاق الرعاية (Manager) |
| `rate($id)` | POST | تقييم فعالية 1-5 نجوم + نص مراجعة (User لديه تذكرة + الفعالية بدأت) + إشعار المدير |
| `reviews($id)` | GET | عرض كل تقييمات ومراجعات فعالية مع متوسط التقييم |

### 3. VenueController (4 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | قائمة الأماكن (Admin يشوف الكل, غيره يشوف المتاحة فقط) |
| `store()` | POST | إنشاء مكان جديد (Admin) |
| `update($id)` | PUT | تعديل مكان (Admin) |
| `destroy($id)` | DELETE | **معطل** - لحماية السجلات الأرشيفية |

### 4. TicketController (2 وظيفتين)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `store()` | POST | حجز تذكرة (فحص: الفعالية معتمدة + تذكرة واحدة + السعة) + إنشاء QR Code |
| `myTickets()` | GET | عرض تذاكر المستخدم مع QR URLs |

### 5. CheckinController (2 وظيفتين)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `checkin()` | POST | مسح QR Code + تسجيل الحضور (Assistant فقط + فحص Event المرتبط) |
| `eventParticipants($id)` | GET | قائمة المشاركين في فعالية (Assistant/Manager/Admin) |

### 6. SponsorshipController (3 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `store()` | POST | إنشاء طلب رعاية (ثنائي الاتجاه: Manager→Sponsor أو Sponsor→Manager) |
| `index()` | GET | عرض طلبات الرعاية (مرتبة ومجمعة حسب الفعالية للمدير) |
| `update($id)` | PUT | قبول/رفض طلب + تخصيص مستوى الرعاية تلقائياً + إنشاء عقد PDF |
| `updateTier($id)` | PATCH | السماح لمدير الفعالية بتعديل وتصنيف رتبة الراعي (يُقفل عند بدء الفعالية) |

### 7. NotificationController (3 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | آخر 50 إشعار + عدد غير المقروءة |
| `markRead($id)` | PUT | تحديد إشعار واحد كمقروء |
| `markAllRead()` | PUT | تحديد كل الإشعارات كمقروءة |

### 8. VerificationController (5 وظائف) - جديد
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `pendingRequests()` | GET | طلبات التحقق المعلقة (Admin) - verified_status: pending أو changes_requested |
| `approve($id)` | PUT | قبول تحقق شريك + إشعاره (Admin) |
| `reject($id)` | PUT | رفض تحقق مع ملاحظات + إشعار الشريك (Admin) |
| `requestChanges($id)` | PUT | طلب تعديلات على الوثيقة + إشعار الشريك (Admin) |
| `downloadDocument($id)` | GET | تحميل وثيقة التحقق (Admin) |
| `reuploadDocument()` | POST | إعادة رفع الوثيقة بعد طلب التعديل (Partner) |

### 9. ProfileController (4 وظائف) - خاص بـ Web Views
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `show($user)` | GET | عرض بروفايل مستخدم (صفحة عامة) |
| `edit()` | GET | صفحة تعديل البروفايل |
| `updateInformation()` | PUT | تحديث المعلومات الشخصية |
| `updateSecurity()` | PUT | تحديث البريد وكلمة المرور |

### 🔔 SystemNotification (Notification Class)
- **الموقع:** `app/Notifications/SystemNotification.php`
- **القناة:** `database` فقط (يُخزن في جدول notifications)
- **الحقول:** `title`, `message`, `type` (event/sponsorship/ticket/verification/system), `icon` (emoji), `action_url`, `related_id`
- **يُستخدم في:** EventController, SponsorshipController, VerificationController

### 10. AnalyticsController (6 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `system()` | GET | إحصائيات النظام الشاملة (Admin): مستخدمين، فعاليات، تذاكر، حضور، اشتراكات شهرية، أفضل 5 فعاليات |
| `managerOverview()` | GET | ملخص المدير: فعالياته + إحصائيات التذاكر والحضور + متوسط التقييم |
| `event($id)` | GET | إحصائيات فعالية (Admin/Manager/Sponsor المرتبط) |
| `users()` | GET | قائمة المستخدمين الموثقين فقط (Admin) |
| `toggleStatus($id)` | PATCH | تعليق/تفعيل حساب + حذف توكناته (Admin) |
| `deleteUser($id)` | DELETE | حذف مستخدم (Admin) |



---

## 🛣 الـ API Routes

### المسارات العامة (بدون مصادقة)
```
POST   /api/register                    → تسجيل مستخدم عادي (User)
POST   /api/register/partner            → تسجيل شريك (Manager/Sponsor) مع وثيقة تحقق
POST   /api/login                       → تسجيل الدخول
GET    /api/events                      → قائمة الفعاليات المعتمدة
GET    /api/events/{id}                 → تفاصيل فعالية
GET    /api/events/{id}/reviews         → تقييمات ومراجعات فعالية
GET    /api/venues                      → قائمة الأماكن المتاحة
```

### المسارات المحمية (auth:sanctum)
```
── البروفايل والمستخدمين ──
GET    /api/profile                        → بروفايلي
GET    /api/profile/{id}                   → بروفايل مستخدم عام
PUT    /api/profile                        → تحديث بروفايلي
PATCH  /api/profile/availability           → تبديل التوفر (Sponsor)
POST   /api/users                          → إنشاء مستخدم (Admin/Manager)
GET    /api/assistants                     → قائمة مساعديني (Manager)
DELETE /api/assistants/{id}                → حذف مساعد (Manager)
GET    /api/sponsors/available             → الرعاة المتوفرين

── المصادقة ──
POST   /api/logout                         → تسجيل الخروج

── الفعاليات ──
POST   /api/events                         → إنشاء فعالية (Manager)
PUT    /api/events/{id}/approve            → موافقة (Admin)
PUT    /api/events/{id}/reject             → رفض (Admin)
GET    /api/events/list/pending            → الفعاليات المعلقة (Admin)
GET    /api/events/list/my                 → فعالياتي (Manager)
GET    /api/events/list/my/live            → فعالياتي الحية (Manager)
GET    /api/events/list/all               → كل الفعاليات (Admin)
PATCH  /api/events/{id}/toggle-sponsorship → تبديل حالة الرعاية (Manager)

── الأماكن ──
POST   /api/venues                         → إنشاء مكان (Admin)
PUT    /api/venues/{id}                    → تعديل مكان (Admin)
DELETE /api/venues/{id}                    → [معطل] حذف مكان

── التذاكر ──
POST   /api/tickets                        → حجز تذكرة
GET    /api/my-tickets                     → تذاكري

── تسجيل الحضور ──
POST   /api/checkin                        → مسح QR Code (Assistant)
GET    /api/checkin/event/{id}             → قائمة المشاركين (Assistant/Manager/Admin)

── الرعاية ──
POST   /api/sponsorship                    → إنشاء طلب رعاية (Manager/Sponsor)
GET    /api/sponsorship                    → قائمة طلبات الرعاية
PUT    /api/sponsorship/{id}               → قبول/رفض طلب
PATCH  /api/sponsorship/{id}/tier          → تحديث تصنيف/رتبة الممول (Event Manager)

── الإشعارات ──
GET    /api/notifications                  → آخر 50 إشعار + عدد غير المقروء
PUT    /api/notifications/read-all         → تحديد الكل كمقروء
PUT    /api/notifications/{id}/read        → تحديد إشعار كمقروء

── الإحصائيات ──
GET    /api/analytics/system               → إحصائيات النظام (Admin)
GET    /api/analytics/manager              → ملخص المدير
GET    /api/analytics/event/{id}           → إحصائيات فعالية
GET    /api/analytics/users                → قائمة المستخدمين الموثقين (Admin)
PATCH  /api/analytics/users/{id}/status    → تعليق/تفعيل مستخدم (Admin)
DELETE /api/analytics/users/{id}           → حذف مستخدم (Admin)

── التحقق (Verification) ──
GET    /api/verifications/pending              → طلبات التحقق المعلقة (Admin)
PUT    /api/verifications/{id}/approve         → قبول التحقق (Admin)
PUT    /api/verifications/{id}/reject          → رفض التحقق (Admin)
PUT    /api/verifications/{id}/request-changes → طلب تعديلات (Admin)
GET    /api/verifications/{id}/document        → تحميل الوثيقة (Admin)
POST   /api/verifications/reupload             → إعادة رفع الوثيقة (Partner)

── التقييمات ──
POST   /api/events/{id}/rate               → تقييم فعالية (User)
```

---

## 🌐 الـ Web Routes

```
/                                  → يحول لـ /login
/login                             → تسجيل الدخول (Guest)
/register                          → تسجيل مستخدم عادي (Guest)
/register/partner                  → تسجيل شريك بوثيقة تحقق (Guest)
/pending-verification              → صفحة انتظار مراجعة التحقق

── مسارات مشتركة (مصادق) ──
/profile                           → صفحة البروفايل
/profile/edit                      → تعديل البروفايل
/profile/{user}                    → بروفايل مستخدم عام
PUT /profile/information           → تحديث المعلومات
PUT /profile/security              → تحديث الأمان

── مسارات Admin (+ verified_partner middleware) ──
/admin/dashboard                   → لوحة تحكم Admin
/admin/events                      → إدارة الفعاليات (موافقة/رفض + إحصائيات)
/admin/users                       → إدارة المستخدمين
/admin/venues                      → إدارة الأماكن
/admin/verifications               → مراجعة طلبات تحقق الشركاء
/admin/event-stats/{id}            → إحصائيات فعالية محددة

── مسارات Event Manager (+ verified_partner middleware) ──
/manager/dashboard                 → لوحة تحكم المدير
/manager/events                    → إنشاء وإدارة الفعاليات
/manager/assistants                → إدارة المساعدين
/manager/attendance                → سجل الحضور
/manager/sponsorship               → إدارة الرعاية
/manager/event-stats/{id}          → إحصائيات فعالية محددة

── مسارات Sponsor (+ verified_partner middleware) ──
/sponsor/dashboard                 → لوحة تحكم الراعي
/sponsor/events                    → تصفح الفعاليات المتاحة
/sponsor/requests                  → طلبات الرعاية
/sponsor/history                   → سجل الرعايات السابقة
```

---

## 🔒 الـ Middleware

### 1. TokenWebAuthMiddleware (`web.auth`)
- يتحقق من وجود `auth_token` في الـ cookies
- يبحث عن التوكن في `personal_access_tokens`
- يسجل دخول المستخدم في Session Guard
- يفحص الدور ويحول للصفحة المناسبة إذا الدور غير مطابق

### 2. TokenGuestMiddleware (`web.guest`)
- يتحقق إذا المستخدم مسجل دخول عبر الـ cookie
- إذا مسجل، يحوله لصفحة الداشبورد حسب دوره
- يمنع المستخدمين المسجلين من الوصول لصفحات login/register

### 3. RoleMiddleware (`role`)
- يتحقق من دور المستخدم في API Routes
- يدعم أدوار متعددة: `middleware('role:Admin,Event Manager')`

### 4. EnsurePartnerVerified (`verified_partner`) - جديد
- يتحقق أن Event Manager أو Sponsor لديه `verification_status = verified`
- إذا لم يكن موثقاً: API → 403 JSON | Web → redirect `/pending-verification`
- مُطبَّق على جميع مسارات Manager و Sponsor

---

## 📄 صفحات الواجهة (Views)

### صفحات عامة
| الملف | الوصف |
|-------|-------|
| `login.blade.php` | صفحة تسجيل الدخول |
| `register.blade.php` | صفحة التسجيل |
| `welcome.blade.php` | الصفحة الرئيسية (82KB - صفحة كبيرة) |
| `profile.blade.php` | صفحة البروفايل الكاملة (19KB) |
| `user-profile.blade.php` | صفحة بروفايل المستخدم العام |

### صفحات Auth (4 صفحات)
| الملف | الوصف |
|-------|-------|
| `login.blade.php` | تسجيل الدخول |
| `register.blade.php` | تسجيل مستخدم عادي |
| `auth/partner-register.blade.php` | تسجيل شريك مع رفع وثيقة تحقق |
| `auth/pending-verification.blade.php` | صفحة انتظار مراجعة التحقق |

### صفحات Admin (6 صفحات)
| الملف | الوصف |
|-------|-------|
| `admin/dashboard.blade.php` | لوحة التحكم + إحصائيات شاملة |
| `admin/events.blade.php` | إدارة الفعاليات (موافقة/رفض + بحث + modal إحصائيات) |
| `admin/users.blade.php` | إدارة المستخدمين الموثقين (بحث + تعليق + حذف) |
| `admin/venues.blade.php` | إدارة الأماكن (إنشاء + تعديل) |
| `admin/verifications.blade.php` | مراجعة طلبات تحقق الشركاء (قبول/رفض/طلب تعديل) |
| `admin/event-stats.blade.php` | إحصائيات مفصلة لفعالية محددة |

### صفحات Manager (6 صفحات)
| الملف | الوصف |
|-------|-------|
| `manager/dashboard.blade.php` | لوحة التحكم + إحصائيات + متوسط التقييم |
| `manager/events.blade.php` | إنشاء وإدارة الفعاليات مع شارات الرعاة |
| `manager/assistants.blade.php` | إدارة المساعدين |
| `manager/attendance.blade.php` | سجل الحضور |
| `manager/sponsorship.blade.php` | إدارة الرعاية (ثنائي الاتجاه + تصنيف الرعاة) |
| `manager/event-stats.blade.php` | إحصائيات مفصلة: حضور بالساعة + تقييمات + رعاة |

### صفحات Sponsor (4 صفحات)
| الملف | الوصف |
|-------|-------|
| `sponsor/dashboard.blade.php` | لوحة التحكم |
| `sponsor/events.blade.php` | تصفح الفعاليات المفتوحة للرعاية |
| `sponsor/requests.blade.php` | طلبات الرعاية (قبول/رفض) |
| `sponsor/history.blade.php` | سجل الرعايات السابقة |

### صفحات البروفايل (2 صفحتين)
| الملف | الوصف |
|-------|-------|
| `profile/edit.blade.php` | تعديل البروفايل (18KB) |
| `profile/show.blade.php` | عرض البروفايل العام |

### PDF Templates
| الملف | الوصف |
|-------|-------|
| `pdf/agreement.blade.php` | قالب عقد الرعاية (يُنشأ تلقائياً عند قبول طلب) |

---

## ⭐ الميزات الرئيسية

### 1. 🎫 نظام التذاكر و QR Code
- حجز تذكرة واحدة لكل مستخدم لكل فعالية
- إنشاء QR Code فريد: `{RANDOM_10}-{EVENT_ID}-{USER_ID}`
- عرض QR Code عبر API خارجي: `api.qrserver.com`
- فحص السعة قبل الحجز

### 2. ✅ تسجيل الحضور (Check-in)
- المساعد يمسح QR Code
- فحص أن المساعد مرتبط بنفس الفعالية
- تسجيل في `attendance_logs` مع وقت المسح
- منع المسح المتكرر (التذكرة تتحول لـ "used")

### 3. 🤝 نظام الرعاية المتقدم المتكامل
- **الطلب المزدوج:** Manager → Sponsor أو Sponsor → Manager
- **تقييم الرعاة وتصنيفهم (Tiering System):** فرز الرعاة إلى مستويات حصرية (Diamond 💎, Gold 🥇, Silver 🥈, Bronze 🥉).
- **منع التكرار (Unique Rank):** التحقق الاستباقي لضمان عدم تكرار رتبة راعيين في نفس الفعالية.
- **تخصيص وتعديل مرن (Edit Rank):** واجهة مخصصة تتيح لمدير الفعالية تغيير تصنيف الممول في حالة القبول.
- **إغلاق التعديل الآمن (Lock Mechanism):** قفل إمكانية تعديل رتب الممولين فور بدء الحدث (Live) لضمان الشفافية.
- **العقود الـ PDF التلقائية:** إصدار فوري لعقود الرعاية بصيغة PDF فور القبول وتوقيعها.
- **عرض متناسق وجذاب:** شارات (Badges) مُلونة تُظهر مستوى واسم وصورة الراعي الشخصية (Logo) عبر كل النوافذ بتصميم متجاوب Responsive.

### 4. 📊 نظام التحليلات
- **Admin:** إحصائيات شاملة (مستخدمين بالأدوار، فعاليات بالحالة والنوع، اشتراكات شهرية)
- **Manager:** إحصائيات فعالياته (حضور، تذاكر، نسب الإشغال)
- **Event Stats:** جدول الحضور بالساعة، ساعة الذروة، آخر 10 check-ins، إيرادات الرعاة

### 5. 🏢 إدارة الأماكن
- فحص تعارض المواعيد قبل إنشاء فعالية
- فحص أن سعة الفعالية لا تتجاوز سعة المكان
- الحذف معطل للحفاظ على الأرشيف
- Admin يشوف كل الأماكن، غيره يشوف المتاحة فقط

### 6. 👤 نظام البروفايلات الموحد
- بروفايل واحد لكل مستخدم (Profile)
- نوعين: individual (للأفراد) و company (للشركات/الرعاة)
- جهات اتصال ديناميكية (ProfileContact)
- حالة التوفر (is_available) للرعاة

### 7. 🔔 نظام الإشعارات (Laravel Notifications)
- قناة `database` فقط — مُخزّن في جدول `notifications` (UUID)
- كلاس واحد `SystemNotification` بحقول: title, message, type, icon, action_url, related_id
- أنواع الإشعارات: `event` | `sponsorship` | `ticket` | `verification` | `system`
- يُرسَل تلقائياً عند: موافقة/رفض فعالية، قبول/رفض رعاية، تقييم جديد، تحقق الهوية
- دعم `markAllRead` لتحديد كل الإشعارات دفعة واحدة

### 8. 📄 إنشاء PDF تلقائي
- عند قبول طلب الرعاية يتم إنشاء عقد PDF
- يُحفظ في `storage/app/public/agreements/`
- يستخدم قالب `pdf/agreement.blade.php`

### 9. ✅ نظام التحقق من هوية الشركاء (Verification)
- يُطلب من كل Event Manager وSponsor رفع وثيقة تحقق عند التسجيل
- حالات التحقق: `pending` → `verified` أو `rejected` أو `changes_requested`
- Middleware `EnsurePartnerVerified` يحجب الوصول حتى تتم الموافقة
- Admin يراجع الطلبات ويستطيع قبول/رفض/طلب تعديل الوثيقة
- الشريك المرفوض يُبلَّغ ويستطيع رفع وثيقة جديدة (`reupload`)

### 10. ⭐ نظام التقييمات
- User يُقيّم فعالية (1-5 نجوم) + نص مراجعة شرط: لديه تذكرة + الفعالية بدأت
- قيد UNIQUE(user_id, event_id): تقييم واحد قابل للتحديث
- `average_rating` محسوب ديناميكياً على Event model
- إشعار تلقائي للمدير عند كل تقييم جديد

## 📁 هيكل الملفات

```
EventHub/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AnalyticsController.php      # إحصائيات النظام والمدير
│   │   │   ├── AuthController.php           # تسجيل/دخول/بروفايل/مساعدين
│   │   │   ├── CheckinController.php        # مسح QR + سجل الحضور
│   │   │   ├── EventController.php          # CRUD فعاليات + تقييمات
│   │   │   ├── NotificationController.php   # إشعارات (Laravel Notifications)
│   │   │   ├── ProfileController.php        # بروفايل Web Views
│   │   │   ├── SponsorshipController.php    # رعاية ثنائية الاتجاه + PDF
│   │   │   ├── TicketController.php         # حجز تذاكر + QR
│   │   │   ├── VenueController.php          # إدارة الأماكن
│   │   │   └── VerificationController.php  # تحقق هوية الشركاء
│   │   └── Middleware/
│   │       ├── EnsurePartnerVerified.php   # فحص التحقق (Managers/Sponsors)
│   │       ├── RoleMiddleware.php           # فحص الأدوار (API)
│   │       ├── TokenGuestMiddleware.php     # حماية صفحات الضيف
│   │       └── TokenWebAuthMiddleware.php   # مصادقة Blade عبر Token
│   ├── Notifications/
│   │   └── SystemNotification.php          # كلاس الإشعارات (قناة database)
│   └── Models/
│       ├── AttendanceLog.php
│       ├── Event.php                        # + average_rating accessor
│       ├── EventSponsor.php                 # Pivot الرعاية + tier branding
│       ├── Profile.php
│       ├── ProfileContact.php
│       ├── Rating.php                       # تقييمات الفعاليات
│       ├── SponsorshipRequest.php
│       ├── Ticket.php
│       ├── User.php                         # + verification fields
│       └── Venue.php
├── database/
│   └── migrations/                          # 39 migration
├── resources/views/
│   ├── admin/                               # 6 صفحات (+ verifications + event-stats)
│   ├── auth/                                # partner-register + pending-verification
│   ├── manager/                             # 6 صفحات (+ event-stats)
│   ├── sponsor/                             # 4 صفحات (+ history)
│   ├── profile/
│   ├── pdf/                                 # قالب عقد PDF
│   ├── login.blade.php
│   └── register.blade.php
├── routes/
│   ├── api.php                              # API endpoints
│   └── web.php                              # Web routes
└── public/
    ├── css/style.css                        # تصميم موحد للواجهة
    ├── js/
    │   ├── api.js                           # مساعد استدعاء API
    │   ├── auth.js                          # تسجيل/دخول JS
    │   ├── notifications.js                 # نظام الإشعارات realtime-like
    │   └── i18n.js                          # ترجمات العربية/الإنجليزية
    └── storage/ → storage/app/public        # صور + عقود PDF
```

---

## 📊 ملخص الأرقام

| العنصر | العدد |
|--------|-------|
| **Controllers** | 10 |
| **Models** | 11 (+ Rating) |
| **Notification Classes** | 1 (SystemNotification) |
| **Migrations** | 39 |
| **API Endpoints** | ~45 |
| **Web Routes** | ~28 |
| **Middleware** | 4 (+ EnsurePartnerVerified) |
| **Blade Views** | ~24 |
| **أدوار** | 5 (Admin, Event Manager, Sponsor, User, Assistant) |
| **جداول قاعدة البيانات** | 11 جدول رئيسي |

---

> 📝 **ملاحظة:** هذا الملف يمثل التوثيق الكامل للمشروع حتى تاريخ 2026-04-25. أي تعديلات مستقبلية يجب تحديث هذا الملف.

---

## 📱 تطبيق الموبايل (Flutter)

> التطبيق يتصل بنفس الـ Laravel Backend عبر Sanctum Token API.
> Base URL: `http://127.0.0.1:8000/api` (للتطوير عبر USB: `adb reverse tcp:8000 tcp:8000`)

### الأدوار المدعومة في الموبايل
| الدور | الوصول |
|-------|--------|
| User | الشاشة الرئيسية + تذاكر + بروفايل |
| Assistant | شاشة مسح QR فقط |

---

### 📋 واجهات تطبيق المستخدم العادي (User)

#### 1. Splash Screen
- **الملف:** `screens/splash_screen.dart`
- **الوظيفة:** شاشة البداية مع أنيميشن (fade + scale)
- **المنطق:** تحقق من `SharedPreferences` للتوكن → توجيه لـ HomeScreen أو LoginScreen أو ScannerScreen
- **API المستخدم:** لا شيء (محلي فقط)

#### 2. Login Screen
- **الملف:** `screens/auth/login_screen.dart`
- **الحقول:** Email + Password
- **API:** `POST /api/login` → يحفظ token + user في SharedPreferences
- **التوجيه بعد الدخول:** `role == 'Assistant'` → ScannerScreen | غيره → MainNavigation

#### 3. Register Screen
- **الملف:** `screens/auth/register_screen.dart`
- **الحقول:** Full Name + Email + Password + Confirm Password
- **التحقق:** كلمة مرور 8 أحرف على الأقل + تطابق
- **API:** `POST /api/register` (يسجّل بدور User فقط)

#### 4. Main Navigation
- **الملف:** `screens/user/main_navigation.dart`
- **التنقل:** Bottom Navigation Bar بـ 3 تبويبات
  - 🔍 **Explore** → HomeScreen
  - 🎫 **Tickets** → MyTicketsScreen
  - 👤 **Profile** → ProfileScreen

#### 5. Home Screen (Explore)
- **الملف:** `screens/user/home_screen.dart`
- **API:** `GET /api/events`
- **الميزات:**
  - بحث نصي (عنوان + مكان + وصف)
  - فلاتر أفقية: All, Technical, Workshop, Conference, Seminar, Cultural, Other
  - التصنيف يعتمد على تحليل النص (keyword matching)
  - Pull-to-refresh + عدد الفعاليات المعروضة
  - الضغط على فعالية → EventDetailsScreen

#### 6. Event Details Screen
- **الملف:** `screens/user/event_details_screen.dart`
- **البيانات المعروضة:** عنوان + تاريخ + وقت + مكان + سعة + المنظم + وصف + رعاة
- **رعاة الفعالية:** شريط أفقي يعرض اسم + شعار + رتبة (Diamond/Gold/Silver/Bronze) بألوان مميزة
- **API:** `POST /api/tickets` (`event_id`) → حجز تذكرة
- **بعد الحجز الناجح:** رسالة نجاح + العودة للقائمة

#### 7. My Tickets Screen
- **الملف:** `screens/user/my_tickets_screen.dart`
- **API:** `GET /api/my-tickets`
- **الميزات:**
  - تبويبان: Upcoming | History (مُقسَّم حسب تاريخ الفعالية)
  - كارد التذكرة: اسم الفعالية + تاريخ + مكان + رقم التذكرة + حالة (ACTIVE/USED)
  - زر QR Code → QRCodeScreen
  - Pull-to-refresh
  - Offline cache عبر SharedPreferences

#### 8. QR Code Screen
- **الملف:** `screens/user/qr_code_screen.dart`
- **الحزمة:** `qr_flutter`
- **المعروض:** كارد تذكرة بيضاء مع QR Code + شريط ملون علوي + حالة (VALID/USED) + رقم التذكرة
- **البيانات:** qr_code string مباشرة من الـ Backend

#### 9. Profile Screen
- **الملف:** `screens/profile_screen.dart`
- **المعروض:** اسم + دور + بريد + تاريخ التسجيل + إحصائيات (Total Tickets, Attended, Upcoming)
- **الميزات:**
  - 🔔 جرس الإشعارات مع badge عدد غير المقروء → Bottom Sheet للإشعارات
  - ✏️ Edit Profile Dialog: تعديل اسم + بريد + كلمة مرور
  - 🚪 Logout مع تأكيد
- **API:**
  - `GET /api/notifications` → قائمة الإشعارات
  - `PUT /api/notifications/{id}/read` → تحديد كمقروء
  - `PUT /api/profile` → تحديث البروفايل

---

### 📋 واجهة المساعد (Assistant)

#### Scanner Screen
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
- **Logout:** زر في الأعلى مع تأكيد

---

### 🏗 بنية الموبايل التقنية

#### State Management
| Provider | البيانات المُدارة |
|----------|------------------|
| `AuthProvider` | user, token, role, isAuthenticated |
| `EventProvider` | قائمة الفعاليات + حالة التحميل |
| `TicketProvider` | تذاكر المستخدم + upcoming/past/totalAttended |

#### ApiService
- **الملف:** `services/api_service.dart`
- **الطرق:** `get()`, `post()`, `put()`, `delete()`
- **المصادقة:** Bearer Token من SharedPreferences في كل طلب
- **Token Expiry:** isTokenExpired() تتحقق من 401

#### التبعيات (pubspec.yaml)
| الحزمة | الوظيفة |
|--------|--------|
| `http` | HTTP requests |
| `provider` | State management |
| `shared_preferences` | تخزين token + cache |
| `mobile_scanner` | مسح QR Camera |
| `qr_flutter` | عرض QR Code |
| `google_fonts` | خط Inter |

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

### 🔲 الواجهات المطلوب تطويرها (مستقبلاً)

| الواجهة | الدور | API المطلوب |
|---------|-------|------------|
| Rating/Review Screen | User | `POST /api/events/{id}/rate` |
| Notifications Mark All Read | User | `PUT /api/notifications/read-all` |
| Partner Registration | Manager/Sponsor | `POST /api/register/partner` |
| Verification Status Screen | Manager/Sponsor | `GET /api/verifications/pending` |
| Document Re-upload | Manager/Sponsor | `POST /api/verifications/reupload` |

