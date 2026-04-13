# 📋 EventHub - توثيق المشروع الكامل
# EventHub - Complete Project Documentation

> **آخر تحديث:** 2026-04-14
> **التقنيات:** Laravel 11 + Sanctum + Blade + MySQL + DomPDF
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

### جداول قاعدة البيانات (31 migration)

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

#### 10. `event_notifications` - الإشعارات
| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف |
| user_id | foreign | المستخدم |
| message | text | محتوى الإشعار |
| is_read | boolean | مقروء أم لا |

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
Event ─→ belongsToMany User (sponsors عبر event_sponsor)
```
- **Scopes:** `upcoming()`, `live()`, `ended()`
- **Accessor:** `time_status` → (upcoming / live / ended)
- **Methods:** `sponsorsWithProfiles()`, `sponsorsByTier($tier)`

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

### 1. AuthController (9 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `register()` | POST | تسجيل مستخدم جديد (دور User فقط) |
| `login()` | POST | تسجيل الدخول + فحص is_active |
| `logout()` | POST | تسجيل الخروج (حذف جميع التوكنات) |
| `getProfile()` | GET | عرض بروفايل المستخدم الحالي |
| `getPublicProfile($id)` | GET | عرض بروفايل مستخدم عام |
| `updateProfile()` | PUT | تحديث البروفايل (اسم، بريد، صورة، جهات اتصال) |
| `updateAvailability()` | PATCH | تبديل توفر الراعي |
| `createUser()` | POST | إنشاء مستخدم (Admin يخلق أي دور, Manager يخلق Assistant فقط) |
| `getAssistants()` | GET | قائمة المساعدين التابعين للمدير |
| `deleteAssistant($id)` | DELETE | حذف مساعد |
| `getAvailableSponsors()` | GET | قائمة الرعاة المتوفرين |

### 2. EventController (8 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `index()` | GET | قائمة الفعاليات المعتمدة والمفتوحة (عام) |
| `show($id)` | GET | تفاصيل فعالية واحدة |
| `store()` | POST | إنشاء فعالية جديدة (Event Manager) + فحص تعارض المكان |
| `approve($id)` | PUT | الموافقة على فعالية (Admin) |
| `reject($id)` | PUT | رفض فعالية مع السبب (Admin) |
| `pending()` | GET | الفعاليات المعلقة (Admin) |
| `myEvents()` | GET | فعاليات المدير الخاصة |
| `liveEvents()` | GET | الفعاليات الحية الآن للمدير |
| `all()` | GET | جميع الفعاليات (Admin) |
| `toggleSponsorship($id)` | PATCH | فتح/إغلاق الرعاية (Manager) |

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
| `index()` | GET | عرض إشعارات المستخدم |
| `store()` | POST | إنشاء إشعار (Admin/Manager) |
| `markRead($id)` | PUT | تحديد إشعار كمقروء |

### 8. AnalyticsController (6 وظائف)
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `system()` | GET | إحصائيات النظام الشاملة (Admin): مستخدمين، فعاليات، تذاكر، حضور، رسوم شهرية، أفضل الفعاليات |
| `managerOverview()` | GET | ملخص المدير: فعالياته + إحصائيات التذاكر والحضور لكل فعالية |
| `event($id)` | GET | إحصائيات فعالية واحدة (مسجلين + حاضرين + نسبة الحضور) |
| `eventStatistics($id)` | GET | إحصائيات مفصلة: جدول زمني للحضور بالساعة + رعاة + إيرادات + آخر 10 check-ins |
| `users()` | GET | قائمة كل المستخدمين (Admin) |
| `toggleStatus($id)` | PATCH | تعليق/تفعيل حساب مستخدم (Admin) |
| `deleteUser($id)` | DELETE | حذف مستخدم (Admin) |

### 9. ProfileController (4 وظائف) - خاص بـ Web Views
| الوظيفة | HTTP Method | الوصف |
|---------|-------------|-------|
| `show($user)` | GET | عرض بروفايل مستخدم (صفحة عامة) |
| `edit()` | GET | صفحة تعديل البروفايل |
| `updateInformation()` | PUT | تحديث المعلومات الشخصية (اسم، صورة، هاتف، سوشال ميديا) |
| `updateSecurity()` | PUT | تحديث البريد وكلمة المرور |

---

## 🛣 الـ API Routes

### المسارات العامة (بدون مصادقة)
```
POST   /api/register          → تسجيل مستخدم جديد
POST   /api/login             → تسجيل الدخول
GET    /api/events             → قائمة الفعاليات المعتمدة
GET    /api/events/{id}        → تفاصيل فعالية
GET    /api/venues             → قائمة الأماكن المتاحة
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
GET    /api/notifications                  → إشعاراتي
POST   /api/notifications                  → إنشاء إشعار (Admin/Manager)
PUT    /api/notifications/{id}/read        → تحديد كمقروء

── الإحصائيات ──
GET    /api/analytics/system               → إحصائيات النظام (Admin)
GET    /api/analytics/manager              → ملخص المدير
GET    /api/analytics/event/{id}           → إحصائيات فعالية
GET    /api/analytics/event/{id}/statistics → إحصائيات مفصلة
GET    /api/analytics/users                → قائمة المستخدمين (Admin)
PATCH  /api/analytics/users/{id}/status    → تعليق/تفعيل مستخدم (Admin)
DELETE /api/analytics/users/{id}           → حذف مستخدم (Admin)
```

---

## 🌐 الـ Web Routes

```
/                              → يحول لـ /login
/login                         → صفحة تسجيل الدخول (Guest)
/register                      → صفحة التسجيل (Guest)

── مسارات مشتركة (مصادق) ──
/profile                       → صفحة البروفايل
/profile/edit                  → تعديل البروفايل
/profile/{user}                → بروفايل مستخدم عام
PUT /profile/information       → تحديث المعلومات
PUT /profile/security          → تحديث الأمان

── مسارات Admin ──
/admin/dashboard               → لوحة تحكم المدير العام
/admin/events                  → إدارة الفعاليات
/admin/users                   → إدارة المستخدمين
/admin/venues                  → إدارة الأماكن

── مسارات Event Manager ──
/manager/dashboard             → لوحة تحكم مدير الفعاليات
/manager/events                → إدارة فعالياتي
/manager/assistants            → إدارة المساعدين
/manager/attendance            → سجل الحضور
/manager/sponsorship           → الرعاية
/manager/statistics            → الإحصائيات

── مسارات Sponsor ──
/sponsor/dashboard             → لوحة تحكم الراعي
/sponsor/events                → استعراض الفعاليات
/sponsor/requests              → طلبات الرعاية
```

---

## 🔒 الـ Middleware

### 1. TokenWebAuthMiddleware (`web.auth`)
- يتحقق من وجود `auth_token` في الـ cookies
- يبحث عن التوكن في `personal_access_tokens`
- يسجل دخول المستخدم في Session Guard
- يفحص الدور ويحول لصفحة المناسبة إذا الدور غير مطابق

### 2. TokenGuestMiddleware (`web.guest`)
- يتحقق إذا المستخدم مسجل دخول عبر الـ cookie
- إذا مسجل، يحوله لصفحة الداشبورد حسب دوره
- يمنع المستخدمين المسجلين من الوصول لصفحات login/register

### 3. RoleMiddleware (`role`)
- يتحقق من دور المستخدم
- يستخدم في API Routes
- يدعم أدوار متعددة: `middleware('role:Admin,Event Manager')`

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

### صفحات Admin (4 صفحات)
| الملف | الوصف |
|-------|-------|
| `admin/dashboard.blade.php` | لوحة التحكم + الإحصائيات |
| `admin/events.blade.php` | إدارة الفعاليات (موافقة/رفض + بحث + إحصائيات) |
| `admin/users.blade.php` | إدارة المستخدمين (بحث + تعليق + حذف) |
| `admin/venues.blade.php` | إدارة الأماكن (إنشاء + تعديل) |

### صفحات Manager (6 صفحات)
| الملف | الوصف |
|-------|-------|
| `manager/dashboard.blade.php` | لوحة التحكم + إحصائيات |
| `manager/events.blade.php` | إنشاء وإدارة الفعاليات (30KB - الأكبر) |
| `manager/assistants.blade.php` | إدارة المساعدين |
| `manager/attendance.blade.php` | سجل الحضور |
| `manager/sponsorship.blade.php` | إدارة الرعاية |
| `manager/statistics.blade.php` | إحصائيات مفصلة (35KB - أكبر ملف) |

### صفحات Sponsor (3 صفحات)
| الملف | الوصف |
|-------|-------|
| `sponsor/dashboard.blade.php` | لوحة التحكم |
| `sponsor/events.blade.php` | تصفح الفعاليات المتاحة |
| `sponsor/requests.blade.php` | طلبات الرعاية |

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

### 7. 🔔 نظام الإشعارات
- إشعارات داخلية للمستخدمين
- يمكن للـ Admin و Manager إرسال إشعارات
- تحديد كمقروء

### 8. 📄 إنشاء PDF تلقائي
- عند قبول طلب الرعاية يتم إنشاء عقد PDF
- يُحفظ في `storage/app/public/agreements/`
- يستخدم قالب `pdf/agreement.blade.php`

---

## 📁 هيكل الملفات

```
EventHub/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AnalyticsController.php      # إحصائيات النظام والمدير
│   │   │   ├── AuthController.php           # تسجيل/دخول/مستخدمين/بروفايل API
│   │   │   ├── CheckinController.php        # مسح QR + سجل الحضور
│   │   │   ├── EventController.php          # CRUD فعاليات + موافقة/رفض
│   │   │   ├── NotificationController.php   # إشعارات
│   │   │   ├── ProfileController.php        # بروفايل Web Views
│   │   │   ├── SponsorshipController.php    # طلبات الرعاية ثنائية الاتجاه
│   │   │   ├── TicketController.php         # حجز تذاكر + QR
│   │   │   └── VenueController.php          # إدارة الأماكن
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php           # فحص الأدوار (API)
│   │       ├── TokenGuestMiddleware.php     # حماية صفحات الضيف
│   │       └── TokenWebAuthMiddleware.php   # مصادقة Blade عبر Token
│   └── Models/
│       ├── AttendanceLog.php                # سجل الحضور
│       ├── Event.php                        # الفعالية + scopes + sponsors
│       ├── EventNotification.php            # الإشعارات
│       ├── EventSponsor.php                 # Pivot الرعاية + tier branding
│       ├── Profile.php                      # البروفايل الموحد
│       ├── ProfileContact.php              # جهات اتصال
│       ├── SponsorshipRequest.php           # طلبات الرعاية
│       ├── Ticket.php                       # التذاكر
│       ├── User.php                         # المستخدم + العلاقات
│       └── Venue.php                        # المكان
├── database/
│   └── migrations/                          # 31 migration
├── resources/views/
│   ├── admin/                               # 4 صفحات لوحة Admin
│   ├── manager/                             # 6 صفحات لوحة Manager
│   ├── sponsor/                             # 3 صفحات لوحة Sponsor
│   ├── profile/                             # 2 صفحة بروفايل
│   ├── pdf/                                 # قالب عقد PDF
│   ├── login.blade.php                      # تسجيل الدخول
│   ├── register.blade.php                   # التسجيل
│   └── welcome.blade.php                    # الصفحة الرئيسية
├── routes/
│   ├── api.php                              # API endpoints (81 سطر)
│   └── web.php                              # Web routes (118 سطر)
└── public/
    └── storage/ → storage/app/public        # الملفات العامة (صور + عقود)
```

---

## 📊 ملخص الأرقام

| العنصر | العدد |
|--------|-------|
| **Controllers** | 9 (+ Controller base) |
| **Models** | 10 |
| **Migrations** | 31 |
| **API Endpoints** | ~30 |
| **Web Routes** | ~20 |
| **Middleware** | 3 |
| **Blade Views** | ~20 |
| **أدوار** | 5 (Admin, Event Manager, Sponsor, User, Assistant) |
| **جداول قاعدة البيانات** | ~10 جداول رئيسية |

---

> 📝 **ملاحظة:** هذا الملف يمثل التوثيق الكامل للمشروع حتى تاريخ 2026-04-14. أي تعديلات مستقبلية يجب تحديث هذا الملف.
