# EventHub — Database Entities (كيانات قاعدة البيانات)

> هذا الملف يحتوي على كل جداول قاعدة البيانات وأعمدتها بشكل مُنظّم لتسهيل رسم مخطط الكيانات (ERD).
> كل جدول (Table) مستقل بذاته مع أعمدته وأنواع البيانات والقيود.

---

## 1. `users` — المستخدمين

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `name` | varchar(255) | NOT NULL |
| 3 | `email` | varchar(255) | NOT NULL, UNIQUE |
| 4 | `contact_email` | varchar(255) | NULLABLE |
| 5 | `avatar` | varchar(255) | NULLABLE |
| 6 | `image` | varchar(255) | NULLABLE |
| 7 | `phone` | varchar(255) | NULLABLE |
| 8 | `bio` | text | NULLABLE |
| 9 | `social_links` | json | NULLABLE |
| 10 | `email_verified_at` | timestamp | NULLABLE |
| 11 | `password` | varchar(255) | NOT NULL |
| 12 | `remember_token` | varchar(100) | NULLABLE |
| 13 | `fcm_token` | varchar(255) | NULLABLE |
| 14 | `role` | enum('Admin','Event Manager','Sponsor','User','Assistant','Company') | DEFAULT 'User' |
| 15 | `event_id` | bigint (unsigned) | NULLABLE, FK → events.id (CASCADE) |
| 16 | `is_active` | boolean | DEFAULT true |
| 17 | `verification_status` | enum('verified','pending','rejected','changes_requested') | DEFAULT 'verified' |
| 18 | `verification_notes` | text | NULLABLE |
| 19 | `interests` | json | NULLABLE |
| 20 | `created_at` | timestamp | NULLABLE |
| 21 | `updated_at` | timestamp | NULLABLE |

---

## 2. `profiles` — الملفات الشخصية

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `user_id` | bigint (unsigned) | NOT NULL, UNIQUE, FK → users.id (CASCADE) |
| 3 | `profile_type` | enum('individual','company') | DEFAULT 'individual' |
| 4 | `company_type` | varchar(255) | NULLABLE |
| 5 | `company_type_slug` | varchar(255) | NULLABLE |
| 6 | `logo` | varchar(255) | NULLABLE |
| 7 | `bio` | text | NULLABLE |
| 8 | `company_name` | varchar(255) | NULLABLE |
| 9 | `company_description` | text | NULLABLE |
| 10 | `is_approved` | boolean | DEFAULT false |
| 11 | `is_available` | boolean | DEFAULT true |
| 12 | `created_at` | timestamp | NULLABLE |
| 13 | `updated_at` | timestamp | NULLABLE |

---

## 3. `profile_contacts` — وسائل التواصل للملف الشخصي

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `profile_id` | bigint (unsigned) | NOT NULL, FK → profiles.id (CASCADE) |
| 3 | `type` | varchar(255) | NOT NULL |
| 4 | `value` | varchar(255) | NOT NULL |
| 5 | `created_at` | timestamp | NULLABLE |
| 6 | `updated_at` | timestamp | NULLABLE |

---

## 4. `user_documents` — وثائق المستخدم

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `user_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 3 | `document_type` | enum('commercial_register', 'tax_number', 'articles_of_association', 'practice_license') | NOT NULL |
| 4 | `file_path` | varchar(255) | NULLABLE |
| 5 | `status` | varchar(255) | DEFAULT 'pending' — (pending, approved, rejected, pending_update) |
| 6 | `note` | text | NULLABLE |
| 7 | `created_at` | timestamp | NULLABLE |
| 8 | `updated_at` | timestamp | NULLABLE |

> **UNIQUE:** (`user_id`, `document_type`)

---

## 5. `venues` — القاعات / الأماكن

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `name` | varchar(255) | NOT NULL |
| 3 | `location` | varchar(255) | NOT NULL |
| 4 | `capacity` | integer | NOT NULL |
| 5 | `morning_start` | varchar(5) | DEFAULT '09:00' |
| 6 | `morning_end` | varchar(5) | DEFAULT '13:00' |
| 7 | `evening_start` | varchar(5) | DEFAULT '15:00' |
| 8 | `evening_end` | varchar(5) | DEFAULT '19:00' |
| 9 | `status` | enum('available','maintenance') | DEFAULT 'available' |
| 10 | `created_at` | timestamp | NULLABLE |
| 11 | `updated_at` | timestamp | NULLABLE |

---

## 6. `venue_maintenance_periods` — فترات صيانة القاعات

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `venue_id` | bigint (unsigned) | NOT NULL, FK → venues.id (CASCADE) |
| 3 | `start_date` | date | NOT NULL |
| 4 | `end_date` | date | NOT NULL |
| 5 | `reason` | varchar(255) | NULLABLE |
| 6 | `created_at` | timestamp | NULLABLE |
| 7 | `updated_at` | timestamp | NULLABLE |

---

## 7. `events` — الفعاليات

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `title` | varchar(255) | NOT NULL |
| 3 | `description` | text | NOT NULL |
| 4 | `location` | varchar(255) | NULLABLE |
| 5 | `event_type` | varchar(255) | DEFAULT 'مؤتمر' |
| 6 | `venue_id` | bigint (unsigned) | NULLABLE, FK → venues.id (SET NULL) |
| 7 | `start_time` | datetime | NOT NULL |
| 8 | `end_time` | datetime | NOT NULL |
| 9 | `capacity` | integer | NULLABLE |
| 10 | `image` | varchar(255) | NULLABLE |
| 11 | `status` | varchar(255) | DEFAULT 'pending' |
| 12 | `is_sponsorship_open` | boolean | DEFAULT true |
| 13 | `is_tickets_open` | boolean | DEFAULT true |
| 14 | `is_exhibition` | boolean | DEFAULT false |
| 15 | `is_exhibitor_registration_open` | boolean | DEFAULT true |
| 16 | `is_applications_open` | boolean | DEFAULT true |
| 17 | `is_published` | boolean | DEFAULT false |
| 18 | `event_objective` | text | NULLABLE |
| 19 | `target_audience` | varchar(255) | NULLABLE |
| 20 | `created_by` | bigint (unsigned) | NOT NULL, FK → users.id |
| 21 | `created_at` | timestamp | NULLABLE |
| 22 | `updated_at` | timestamp | NULLABLE |

---

## 8. `event_external_venues` — قاعات الفعاليات الخارجية

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, UNIQUE, FK → events.id (CASCADE) |
| 3 | `venue_name` | varchar(255) | NOT NULL |
| 4 | `venue_location` | varchar(255) | NULLABLE |
| 5 | `booking_proof_path` | varchar(255) | NULLABLE |
| 6 | `booking_date` | date | NULLABLE |
| 7 | `period` | varchar(255) | NULLABLE |
| 8 | `created_at` | timestamp | NULLABLE |
| 9 | `updated_at` | timestamp | NULLABLE |

---

## 9. `event_schedules` — جداول وأجندات الفعاليات

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, UNIQUE, FK → events.id (CASCADE) |
| 3 | `ministry_document_path` | varchar(255) | NULLABLE |
| 4 | `external_schedule` | json | NULLABLE |
| 5 | `internal_schedule` | json | NULLABLE |
| 6 | `agenda` | json | NULLABLE |
| 7 | `published_schedule` | json | NULLABLE |
| 8 | `created_at` | timestamp | NULLABLE |
| 9 | `updated_at` | timestamp | NULLABLE |

---

## 10. `event_reviews` — مراجعات الفعاليات والإلغاء

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, UNIQUE, FK → events.id (CASCADE) |
| 3 | `rejection_reason` | text | NULLABLE |
| 4 | `review_message` | text | NULLABLE |
| 5 | `review_fields` | json | NULLABLE |
| 6 | `review_status` | varchar(255) | DEFAULT 'none' — (none, needs_review, reviewed) |
| 7 | `cancellation_reason` | text | NULLABLE |
| 8 | `cancellation_rejection_reason` | text | NULLABLE |
| 9 | `created_at` | timestamp | NULLABLE |
| 10 | `updated_at` | timestamp | NULLABLE |

---

## 11. `event_sponsor` — الجدول الوسيط (الرعاة والفعاليات)

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `sponsor_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 4 | `tier` | varchar(10) | NULLABLE — (diamond, gold, silver, bronze) |
| 5 | `contribution_amount` | decimal(10,2) | NULLABLE |
| 6 | `created_at` | timestamp | NULLABLE |
| 7 | `updated_at` | timestamp | NULLABLE |

> **UNIQUE:** (`event_id`, `sponsor_id`)

---

## 12. `tickets` — التذاكر

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `ticket_number` | integer | NULLABLE |
| 4 | `user_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 5 | `qr_code` | varchar(255) | NOT NULL, UNIQUE |
| 6 | `status` | enum('unused','used','cancelled') | DEFAULT 'unused' |
| 7 | `created_at` | timestamp | NULLABLE |
| 8 | `updated_at` | timestamp | NULLABLE |

---

## 13. `attendance_logs` — سجل الحضور

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `ticket_id` | bigint (unsigned) | NOT NULL, FK → tickets.id (CASCADE) |
| 3 | `scanned_by` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 4 | `scanned_at` | timestamp | DEFAULT CURRENT_TIMESTAMP |
| 5 | `created_at` | timestamp | NULLABLE |
| 6 | `updated_at` | timestamp | NULLABLE |

---

## 14. `sponsorship_requests` — طلبات الرعاية

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `sponsor_id` | bigint (unsigned) | NULLABLE, FK → users.id (NULL ON DELETE) |
| 4 | `event_manager_id` | bigint (unsigned) | NULLABLE, FK → users.id (CASCADE) |
| 5 | `initiator` | enum('sponsor','event_manager') | DEFAULT 'sponsor' |
| 6 | `message` | text | NULLABLE |
| 7 | `status` | enum('pending','accepted','rejected','negotiating','cancelled') | DEFAULT 'pending' |
| 8 | `created_at` | timestamp | NULLABLE |
| 9 | `updated_at` | timestamp | NULLABLE |

---

## 15. `event_notifications` — إشعارات الفعاليات

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `user_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 3 | `message` | text | NOT NULL |
| 4 | `is_read` | boolean | DEFAULT false |
| 5 | `created_at` | timestamp | NULLABLE |
| 6 | `updated_at` | timestamp | NULLABLE |

---

## 16. `notifications` — الإشعارات (Laravel Notifications)

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | uuid | PK |
| 2 | `type` | varchar(255) | NOT NULL |
| 3 | `notifiable_type` | varchar(255) | NOT NULL |
| 4 | `notifiable_id` | bigint (unsigned) | NOT NULL |
| 5 | `data` | text | NOT NULL |
| 6 | `read_at` | timestamp | NULLABLE |
| 7 | `created_at` | timestamp | NULLABLE |
| 8 | `updated_at` | timestamp | NULLABLE |

> **INDEX:** (`notifiable_type`, `notifiable_id`)

---

## 17. `ratings` — التقييمات

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `user_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 4 | `rating` | tinyint (unsigned) | NOT NULL — (1 to 5) |
| 5 | `review_text` | text | NULLABLE |
| 6 | `created_at` | timestamp | NULLABLE |
| 7 | `updated_at` | timestamp | NULLABLE |

> **UNIQUE:** (`user_id`, `event_id`)

---

## 18. `password_reset_codes` — رموز استعادة كلمة المرور

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `email` | varchar(255) | NOT NULL, INDEX |
| 3 | `code` | varchar(255) | NOT NULL — (bcrypt hash) |
| 4 | `attempts` | tinyint (unsigned) | DEFAULT 0 |
| 5 | `reset_token` | varchar(64) | NULLABLE |
| 6 | `created_at` | timestamp | NULLABLE |

---

## 19. `password_reset_tokens` — رموز إعادة تعيين كلمة المرور

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `email` | varchar(255) | PK |
| 2 | `token` | varchar(255) | NOT NULL |
| 3 | `created_at` | timestamp | NULLABLE |

---

## 20. `event_reminders` — تذكيرات الفعاليات

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `reminder_type` | varchar(255) | NOT NULL |
| 4 | `sent_at` | timestamp | NOT NULL |
| 5 | `created_at` | timestamp | NULLABLE |
| 6 | `updated_at` | timestamp | NULLABLE |

> **UNIQUE:** (`event_id`, `reminder_type`)

---

## 21. `agreement_negotiations` — تفاوض العقود

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `sponsorship_request_id` | bigint (unsigned) | NULLABLE, FK → sponsorship_requests.id (CASCADE) |
| 3 | `exhibition_application_id` | bigint (unsigned) | NULLABLE, FK → exhibition_applications.id (CASCADE) |
| 4 | `status` | enum('draft','pending_review','revision_requested','accepted','rejected') | DEFAULT 'draft' |
| 5 | `last_submitted_by` | bigint (unsigned) | NULLABLE, FK → users.id (NULL ON DELETE) |
| 6 | `final_notes` | text | NULLABLE |
| 7 | `created_at` | timestamp | NULLABLE |
| 8 | `updated_at` | timestamp | NULLABLE |

---

## 22. `agreement_versions` — نسخ العقود

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `negotiation_id` | bigint (unsigned) | NOT NULL, FK → agreement_negotiations.id (CASCADE) |
| 3 | `version_number` | int (unsigned) | NOT NULL |
| 4 | `file_path` | varchar(255) | NOT NULL |
| 5 | `uploaded_by` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 6 | `action` | enum('uploaded','accepted','rejected','revision_requested') | NOT NULL |
| 7 | `message` | text | NULLABLE |
| 8 | `created_at` | timestamp | NULLABLE |
| 9 | `updated_at` | timestamp | NULLABLE |

---

## 23. `assistance_requests` — طلبات المساعدة

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `assistant_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 3 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 4 | `manager_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 5 | `status` | enum('pending','accepted','rejected') | DEFAULT 'pending' |
| 6 | `message` | text | NULLABLE |
| 7 | `responded_at` | timestamp | NULLABLE |
| 8 | `created_at` | timestamp | NULLABLE |
| 9 | `updated_at` | timestamp | NULLABLE |

> **UNIQUE:** (`assistant_id`, `event_id`)

---

## 24. `exhibition_applications` — طلبات المعارض

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `company_id` | bigint (unsigned) | NOT NULL, FK → users.id (CASCADE) |
| 4 | `event_manager_id` | bigint (unsigned) | NOT NULL, FK → users.id |
| 5 | `initiator` | enum('company','event_manager') | DEFAULT 'company' |
| 6 | `message` | text | NULLABLE |
| 7 | `status` | enum('pending','accepted','rejected','negotiating') | DEFAULT 'pending' |
| 8 | `booth_preference` | varchar(255) | NULLABLE |
| 9 | `product_category` | varchar(255) | NULLABLE |
| 10 | `booth_number` | varchar(255) | NULLABLE |
| 11 | `booth_size` | varchar(255) | NULLABLE |
| 12 | `created_at` | timestamp | NULLABLE |
| 13 | `updated_at` | timestamp | NULLABLE |

> **UNIQUE:** (`event_id`, `company_id`)

---

## 25. `exhibition_zones` — مناطق المعرض

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `event_id` | bigint (unsigned) | NOT NULL, FK → events.id (CASCADE) |
| 3 | `name` | varchar(255) | NOT NULL |
| 4 | `created_at` | timestamp | NULLABLE |
| 5 | `updated_at` | timestamp | NULLABLE |

---

## 26. `exhibition_booths` — أجنحة المعرض

| # | Column | Type | Constraints |
|---|--------|------|-------------|
| 1 | `id` | bigint (unsigned) | PK, Auto Increment |
| 2 | `exhibition_zone_id` | bigint (unsigned) | NOT NULL, FK → exhibition_zones.id (CASCADE) |
| 3 | `booth_number` | varchar(255) | NOT NULL |
| 4 | `size` | varchar(255) | NULLABLE |
| 5 | `exhibition_application_id` | bigint (unsigned) | NULLABLE, FK → exhibition_applications.id (SET NULL) |
| 6 | `created_at` | timestamp | NULLABLE |
| 7 | `updated_at` | timestamp | NULLABLE |

---

## ملخص عدد الجداول

| التصنيف | الجداول | العدد |
|---------|---------|-------|
| **الإجمالي** | users, profiles, profile_contacts, user_documents, venues, venue_maintenance_periods, events, event_external_venues, event_schedules, event_reviews, event_sponsor, tickets, attendance_logs, sponsorship_requests, event_notifications, notifications, ratings, password_reset_codes, password_reset_tokens, event_reminders, agreement_negotiations, agreement_versions, assistance_requests, exhibition_applications, exhibition_zones, exhibition_booths | **26** |
