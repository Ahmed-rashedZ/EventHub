-- EventHub Database Backup
-- Date: 2026-05-01 22:10:57
-- Database: eventhub

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- --------------------------------------------------------
-- Table: attendance_logs
-- --------------------------------------------------------

DROP TABLE IF EXISTS `attendance_logs`;
CREATE TABLE `attendance_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `scanned_by` bigint(20) unsigned NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_logs_ticket_id_foreign` (`ticket_id`),
  KEY `attendance_logs_scanned_by_foreign` (`scanned_by`),
  CONSTRAINT `attendance_logs_scanned_by_foreign` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: cache
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: cache_locks
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: event_notifications
-- --------------------------------------------------------

DROP TABLE IF EXISTS `event_notifications`;
CREATE TABLE `event_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `event_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: event_sponsor
-- --------------------------------------------------------

DROP TABLE IF EXISTS `event_sponsor`;
CREATE TABLE `event_sponsor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `sponsor_id` bigint(20) unsigned NOT NULL,
  `tier` varchar(10) DEFAULT NULL,
  `contribution_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_sponsor_event_id_sponsor_id_unique` (`event_id`,`sponsor_id`),
  KEY `event_sponsor_sponsor_id_index` (`sponsor_id`),
  CONSTRAINT `event_sponsor_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_sponsor_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('1', '17', '10', 'silver', '0.00', '2026-04-13 21:02:22', '2026-04-13 22:24:09');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('2', '11', '6', 'diamond', NULL, NULL, NULL);
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('3', '11', '10', 'bronze', '0.00', '2026-04-13 21:25:34', '2026-04-13 21:25:34');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('4', '14', '10', 'gold', '0.00', '2026-04-13 21:25:36', '2026-04-13 22:45:42');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('5', '13', '10', 'bronze', '0.00', '2026-04-13 21:55:35', '2026-04-13 21:55:35');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('6', '9', '21', 'bronze', '0.00', '2026-04-13 22:00:54', '2026-04-13 22:00:54');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('7', '17', '21', 'bronze', '0.00', '2026-04-13 22:01:15', '2026-04-13 22:25:59');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('8', '7', '21', 'bronze', '0.00', '2026-04-13 22:12:27', '2026-04-13 22:12:27');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('9', '14', '21', 'diamond', '0.00', '2026-04-13 22:13:58', '2026-04-13 22:46:05');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('10', '18', '21', 'silver', '0.00', '2026-04-13 22:33:25', '2026-04-13 22:41:18');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('11', '22', '10', 'diamond', '0.00', '2026-04-21 19:08:13', '2026-04-21 19:11:25');
INSERT INTO `event_sponsor` (`id`, `event_id`, `sponsor_id`, `tier`, `contribution_amount`, `created_at`, `updated_at`) VALUES ('12', '23', '10', 'diamond', '0.00', '2026-04-24 00:28:06', '2026-04-24 20:07:05');

-- --------------------------------------------------------
-- Table: events
-- --------------------------------------------------------

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_type` varchar(255) NOT NULL DEFAULT 'مؤتمر',
  `location` varchar(255) DEFAULT NULL,
  `venue_id` bigint(20) unsigned DEFAULT NULL,
  `external_venue_name` varchar(255) DEFAULT NULL,
  `external_venue_location` varchar(255) DEFAULT NULL,
  `booking_proof_path` varchar(255) DEFAULT NULL,
  `period` varchar(255) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `capacity` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `is_sponsorship_open` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_created_by_foreign` (`created_by`),
  KEY `events_venue_id_foreign` (`venue_id`),
  CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `events_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('1', 'valo', 'valo', 'مؤتمر', NULL, '2', NULL, NULL, NULL, NULL, NULL, '2026-03-30 03:18:00', '2026-03-31 03:18:00', '200', NULL, 'approved', NULL, '0', '6', '2026-03-29 01:18:56', '2026-04-24 22:16:32');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('2', 'fort', 'sdfsdfsdfs', 'مؤتمر', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-03-30 03:19:00', '2026-03-31 03:19:00', '500', NULL, 'approved', NULL, '0', '6', '2026-03-29 01:19:28', '2026-04-13 22:44:06');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('3', 'ffffff', 'fff', 'مؤتمر', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-03-30 03:33:00', '2026-03-31 03:33:00', '1', NULL, 'approved', NULL, '0', '6', '2026-03-29 01:34:04', '2026-04-13 22:44:05');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('4', 'gffgfg', 'fgfgfgf', 'مؤتمر', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-03-30 03:34:00', '2026-03-31 03:34:00', '22', NULL, 'approved', NULL, '0', '6', '2026-03-29 01:34:43', '2026-04-13 22:44:06');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('5', 'jjjjjjjjjjjj', 'jjjjjjjjj', 'مؤتمر', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-04-03 03:42:00', '2026-04-08 03:42:00', '11', NULL, 'approved', NULL, '0', '6', '2026-03-29 01:42:25', '2026-04-24 22:16:31');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('6', 'werwe', 'werwrwrrwe', 'مؤتمر', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-03-31 07:59:00', '2026-04-01 03:56:00', '44', NULL, 'approved', NULL, '0', '13', '2026-03-29 01:56:56', '2026-04-13 21:54:08');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('7', 'gaming', 'zkjdhfbglkzjdfnvkljzdf', 'مؤتمر', NULL, '2', NULL, NULL, NULL, NULL, NULL, '2026-04-02 17:45:00', '2026-05-08 17:45:00', '120', NULL, 'approved', NULL, '0', '6', '2026-03-29 15:46:28', '2026-04-13 22:44:07');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('8', 'fortnite event', 'eeeeeeeeeeeeee', 'مؤتمر', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-05 04:22:00', '2026-04-07 04:22:00', '50', NULL, 'approved', NULL, '0', '13', '2026-04-04 02:22:50', '2026-04-13 21:54:09');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('9', 'CCCCCC', 'CCCCCCC', 'مؤتمر', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-04-16 02:14:00', '2026-04-23 02:14:00', '12', NULL, 'approved', NULL, '1', '6', '2026-04-07 00:14:33', '2026-04-13 22:44:20');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('10', 'sdf', 'sdf', 'مؤتمر', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-08 02:31:00', '2026-04-09 02:31:00', '3', NULL, 'rejected', 'iadrnhidjfgndjfgnidjfgnaioo', '0', '6', '2026-04-07 00:31:24', '2026-04-13 22:44:10');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('11', 'رررررررررر', 'vvvvvvvvvvvvv', 'ترفيه', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-14 03:07:00', '2026-04-15 03:07:00', '3', 'events/UxTqPKrdWaiLTyAWAlgONSvCfPEd3FT5yevYqmOL.png', 'approved', NULL, '0', '13', '2026-04-07 01:07:43', '2026-04-13 21:54:06');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('12', 'kkkk', 'kkk', 'ورشة عمل', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-17 03:14:00', '2026-04-18 03:14:00', '2', 'events/xB6ISW3vqOm3hGfIxkpxWCuyvFs4LBxgMvUsypDu.jpg', 'approved', NULL, '0', '13', '2026-04-07 01:14:40', '2026-04-13 21:54:09');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('13', 'j', 'jjj', 'ترفيه', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-15 03:16:00', '2026-04-15 07:16:00', '1', 'events/o9PZhaGNcoaczYrAc5bku00YWYwOEUoxQa0kDf52.webp', 'approved', NULL, '0', '13', '2026-04-07 01:16:20', '2026-04-13 21:54:10');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('14', 'yyy', 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'ملتقى علمي', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-25 03:17:00', '2026-04-26 03:17:00', '1', 'events/f43kFBVIUSvW4M4Omoo7G7MC6P0hXar06SGDRo5c.png', 'approved', NULL, '0', '6', '2026-04-07 01:17:44', '2026-04-13 22:44:23');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('15', 'مممممممممممممممم', 'ممممممممممممممممممممم', 'مؤتمر', NULL, '3', NULL, NULL, NULL, NULL, NULL, '2026-04-12 00:58:00', '2026-04-13 00:58:00', '5', 'events/MsMA6sBF7i4AWpeX26hiLtUeRyXBFUTJviVtRhzp.png', 'approved', NULL, '0', '6', '2026-04-11 22:58:55', '2026-04-13 22:44:09');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('16', 'تت', 'تت', 'ورشة عمل', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-04-12 01:00:00', '2026-04-14 01:00:00', '6', 'events/er7P9jZ0rI58WnTemhMJd9r0rsazjNFdXELiK8ob.jpg', 'approved', NULL, '0', '6', '2026-04-11 23:00:29', '2026-04-13 22:44:11');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('17', 'GGGGG', 'GGGGGG', 'ورشة عمل', NULL, '4', NULL, NULL, NULL, NULL, NULL, '2026-04-12 01:03:00', '2026-04-14 01:02:00', '34', 'events/ehelWfhajeuqSvQbMFQeDr6766G9EKjU7qpYPXag.jpg', 'approved', NULL, '0', '6', '2026-04-11 23:02:43', '2026-04-13 22:44:11');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('18', 'جديد', 'خخخ', 'ندوة', NULL, '4', NULL, NULL, NULL, NULL, NULL, '2026-04-24 00:31:00', '2026-04-25 00:31:00', '44', 'events/nbmQpn2OzhQpCVgTR1c3eF3Gyvv5a3KE1ONVQyLL.png', 'approved', NULL, '1', '13', '2026-04-13 22:32:05', '2026-04-13 22:32:27');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('19', 'jjjjjjjjjjjjjjjjjjjjjj', 'jjjjjjjjjjjjjjjjj', 'jjjjj', NULL, '4', NULL, NULL, NULL, NULL, NULL, '2026-04-18 00:26:00', '2026-04-18 17:26:00', '8', 'events/oEu73sXqKUEcOeCd97zfNagmnG11X1jwTUrmTUd8.jpg', 'approved', NULL, '1', '6', '2026-04-17 22:26:41', '2026-04-17 23:12:15');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('20', 'يبا', 'يبليب', 'اجتماعية', NULL, '4', NULL, NULL, NULL, NULL, NULL, '2026-05-08 01:15:00', '2026-05-09 01:15:00', '5', 'events/paA2Ap9NPm71CArEzPUw83KFkhb6h5IIzszMWtrH.png', 'approved', NULL, '1', '6', '2026-04-19 23:15:25', '2026-04-19 23:15:49');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('21', 'لا', 'بلا', 'مؤتمر', NULL, '4', NULL, NULL, NULL, NULL, NULL, '2026-04-30 02:12:00', '2026-05-01 02:12:00', '5', 'events/i8khtMLbEMPqLYWMWr9TfqqBnrfxGlALYx4756Uv.jpg', 'approved', NULL, '1', '24', '2026-04-20 00:13:06', '2026-04-20 00:54:42');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('22', 'ltt', 'aaaaaaa', 'دورة تدريبية', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-05-07 20:56:00', '2026-05-08 21:00:00', '5', 'events/zwxAFcv1FJGEbPGueqgqqDVnPPVGuyZ8VPLmSZNe.png', 'approved', NULL, '1', '6', '2026-04-21 19:00:27', '2026-04-21 19:01:29');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('23', 'oooooooooo', 'ooooooooooo', 'ملتقى علمي', NULL, '5', NULL, NULL, NULL, NULL, NULL, '2026-04-25 02:24:00', '2026-04-26 02:24:00', '6', 'events/Y8QUCBjWoZm0wOAbSMKvUHIihc2dwEOiFyXrmkF9.jpg', 'approved', NULL, '1', '6', '2026-04-24 00:25:20', '2026-04-24 00:27:23');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('24', 'aaaaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaaaaaa', 'تقنية', NULL, '5', NULL, NULL, NULL, NULL, NULL, '2026-04-27 02:30:00', '2026-04-28 02:30:00', '1666', 'events/XSXVkTaYoNmy23cEaXE6CiRC6Ssf93Rr2j2WIucN.png', 'rejected', 'uuuuuuuuuuuuuuuuuuuuuu', '1', '6', '2026-04-24 00:30:12', '2026-04-24 00:31:14');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('25', 'AGMED', 'AHMED', 'رياضة', NULL, '2', NULL, NULL, NULL, NULL, NULL, '2026-05-28 00:12:00', '2026-06-29 00:12:00', '4', 'events/GPJqbYHu6XH6DIPDgx2Be4gW1YAyUoKpfTF4VtT5.png', 'pending', NULL, '1', '6', '2026-04-24 22:13:23', '2026-04-24 22:13:23');
INSERT INTO `events` (`id`, `title`, `description`, `event_type`, `location`, `venue_id`, `external_venue_name`, `external_venue_location`, `booking_proof_path`, `period`, `booking_date`, `start_time`, `end_time`, `capacity`, `image`, `status`, `rejection_reason`, `is_sponsorship_open`, `created_by`, `created_at`, `updated_at`) VALUES ('26', 'kkkkk', 'jjjj', 'ندوة', NULL, NULL, 'kkkkk', 'http://127.0.0.1:8000/manager/events', 'proofs/Avo6A5l8FdvNzmUOLTNR153TMrueP1p7OiuJAZQ2.jpg', NULL, NULL, '2026-05-01 21:50:00', '2026-05-02 21:50:00', '44', 'events/DXB7K5xgx8gBEVjAIx0UZLDIJ7K1TlVBLokxWudC.jpg', 'approved', NULL, '1', '37', '2026-05-01 19:50:55', '2026-05-01 19:52:22');

-- --------------------------------------------------------
-- Table: failed_jobs
-- --------------------------------------------------------

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: job_batches
-- --------------------------------------------------------

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: jobs
-- --------------------------------------------------------

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: migrations
-- --------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2026_03_28_213634_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_03_28_214425_add_role_to_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2026_03_28_215335_create_venues_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2026_03_28_215705_create_events_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2026_03_29_000001_create_tickets_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2026_03_29_000002_create_attendance_logs_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2026_03_29_000003_create_sponsorship_requests_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2026_03_29_000004_create_event_notifications_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2026_03_29_000004_make_sponsor_id_nullable_in_sponsorship_requests_table', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2026_03_29_155505_add_event_id_to_users_table', '4');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2026_04_03_000001_create_sponsors_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2026_04_03_000002_create_event_sponsor_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2026_04_03_000003_add_location_to_events_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2026_04_03_000004_drop_event_sponsor_and_sponsors_tables', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2026_04_03_000005_create_profiles_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('19', '2026_04_03_000006_create_event_sponsor_table_v2', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('20', '2026_04_03_000007_create_profile_contacts_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('21', '2026_04_03_000008_remove_contact_columns_from_profiles_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('22', '2026_04_03_000009_add_is_available_to_profiles_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('23', '2026_04_03_000010_update_sponsorship_requests_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('24', '2026_04_03_210416_add_is_sponsorship_open_to_events_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('25', '2026_04_04_123822_add_profile_fields_to_users_table_v2', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('26', '2026_04_05_211932_add_image_to_users_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('27', '2026_04_06_001404_drop_password_plain_from_users_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('28', '2026_04_06_223636_add_contact_email_to_users_table', '7');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('29', '2026_04_06_233008_add_is_active_to_users_table', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('30', '2026_04_07_002356_add_rejection_reason_to_events_table', '9');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('31', '2026_04_07_025500_add_image_to_events_table', '10');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('32', '2026_04_07_010551_add_event_type_to_events_table', '11');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('33', '2026_04_14_105304_create_ratings_table', '12');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('34', '2026_04_17_200641_change_event_type_to_string_in_events_table', '12');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('35', '2026_04_17_225223_add_review_text_to_ratings_table', '13');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('36', '2026_04_19_000001_allow_null_tier_in_event_sponsor', '14');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('37', '2026_04_19_231013_update_event_types_in_events_table', '14');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('38', '2026_04_19_235038_add_verification_columns_to_users_table', '15');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('39', '2026_04_20_002101_update_verification_status_enum_in_users', '16');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('40', '2026_04_23_235616_create_notifications_table', '17');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('41', '2026_04_27_200221_add_verification_documents_to_users_table', '18');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('42', '2026_04_28_002340_create_password_reset_codes_table', '19');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('43', '2026_04_28_133143_add_reset_token_to_password_reset_codes_table', '20');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('44', '2026_04_29_200000_add_periods_to_venues_table', '21');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('45', '2026_04_29_210000_update_events_for_external_venues', '21');

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('0a59cc82-8792-485d-9281-9ac06531138e', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Partner Registration \\ud83c\\udf89\",\"message\":\"A new Event Manager \\\"{\\u0642\\u0639\\u0648\\u062f}\\\" has registered and is waiting for verification.\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 15:03:51', '2026-04-28 15:03:34', '2026-04-28 15:03:51');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('0d7bd96e-15d1-4f26-be8a-7930cd6cfd21', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '39', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nTax Number Certificate: dfgdf\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', '2026-04-28 15:12:12', '2026-04-28 15:05:12', '2026-04-28 15:12:12');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('13eb2bc6-ec6a-461c-85b6-acb494fd8970', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0627\\u062d\\u0645\\u062f has submitted updated documents for review: Articles of Association\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-27 23:53:29', '2026-04-27 23:44:16', '2026-04-27 23:53:29');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('1940f5e4-084a-4dae-a9b3-5c9dd0770ca5', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Verification Document Updated\",\"message\":\"Partner \\u0642\\u0639\\u0648\\u062f has resubmitted verification documents and requires review.\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 15:06:11', '2026-04-28 15:05:59', '2026-04-28 15:06:11');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('1b49fac5-0a9c-4a93-aa69-c3204e9bb6dd', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0639\\u0628\\u0633\\u064a has submitted updated documents for review: Commercial Register, Tax Number Certificate, Articles of Association, Practice License\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 00:06:24', '2026-04-28 00:06:14', '2026-04-28 00:06:24');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('1ca09152-33f1-41ec-8859-84ae69a19ab5', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '34', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nArticles of Association: g\\nPractice License: g\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:16:23', '2026-04-27 22:16:23');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('1d45c06f-d998-4356-96a9-ba2b5b1ea4e2', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Articles of Association\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 15:01:46', '2026-04-28 00:01:42', '2026-04-28 15:01:46');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('1e344293-e14f-43cf-9a54-938acd739a79', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/dashboard\",\"related_id\":null}', '2026-04-27 23:36:36', '2026-04-27 23:32:48', '2026-04-27 23:36:36');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('1e515212-77fb-4f88-b82f-cabecb9691a2', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Partner Registration \\ud83c\\udf89\",\"message\":\"A new Event Manager \\\"{\\u0627\\u062d\\u0645\\u062f}\\\" has registered and is waiting for verification.\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-27 23:32:42', '2026-04-27 23:32:31', '2026-04-27 23:32:42');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('21efa271-8d4b-486d-9ed9-1cf09e998feb', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Tax Number Certificate, Articles of Association\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:01:05', '2026-04-27 23:53:25', '2026-04-28 00:01:05');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('22b2c57c-958b-4258-a16a-cc7057b14f35', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Document Update Rejected \\u274c\",\"message\":\"Your updated documents were rejected:\\nArticles of Association: \\u064a\\u0628\",\"type\":\"verification\",\"icon\":\"\\u274c\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:01:05', '2026-04-28 00:00:42', '2026-04-28 00:01:05');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('230ad082-59ce-43b6-bbd9-eb2cd5e28840', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '39', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Tax Number Certificate\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 15:12:12', '2026-04-28 15:09:11', '2026-04-28 15:12:12');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('29d27069-cd0c-4f35-be9a-cdac36e9a3fa', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Event Pending\",\"message\":\"Event \\\"AGMED\\\" was submitted by \\u0639\\u0628\\u0633\\u064a and needs your approval.\",\"type\":\"event\",\"icon\":\"\\ud83d\\udccb\",\"action_url\":\"\\/admin\\/events\",\"related_id\":25}', '2026-04-27 20:41:01', '2026-04-24 22:13:23', '2026-04-27 20:41:01');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('306573e0-3164-475b-bf3c-a179b4c878ed', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Event Approved \\u2705\",\"message\":\"Your event \\\"kkkkk\\\" has been approved and is now live!\",\"type\":\"event\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/events\",\"related_id\":26}', '2026-05-01 19:54:33', '2026-05-01 19:52:22', '2026-05-01 19:54:33');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('31957f66-810d-48e7-b4c1-4b10da0c71c7', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '35', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nCommercial Register: \\u0627\\u0627\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:43:59', '2026-04-27 22:43:59');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('343c5fd1-aa53-4442-a143-7c17f6a6b58f', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '24', '{\"title\":\"New Ticket Booked \\ud83c\\udf9f\\ufe0f\",\"message\":\"Ahmed Rashed booked a ticket for \\\"\\u0644\\u0627\\\" (1\\/5)\",\"type\":\"ticket\",\"icon\":\"\\ud83c\\udf9f\\ufe0f\",\"action_url\":\"\\/manager\\/event-stats\\/21\",\"related_id\":21}', '2026-04-27 23:07:34', '2026-04-25 12:34:26', '2026-04-27 23:07:34');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('3e6286a1-233d-4a08-ba5e-8977e2a0d09c', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '34', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nTax Number Certificate: \\u0621\\u0621\\u0621\\u0621\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:20:29', '2026-04-27 22:20:29');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('5321f9d9-d2c2-4c03-9894-8207b45af312', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '33', '{\"title\":\"Verification Rejected \\u274c\",\"message\":\"Your verification was rejected: \\u064a\\u0628\\u0644\\u064a\\u0628\",\"type\":\"verification\",\"icon\":\"\\u274c\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:50:12', '2026-04-27 22:50:12');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('59dc70af-fd51-4dbd-a55f-82baba27fda9', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0627\\u062d\\u0645\\u062f has submitted updated documents for review: Articles of Association\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 00:01:26', '2026-04-28 00:01:16', '2026-04-28 00:01:26');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('5f025e90-61ef-4936-a761-d21ce55f857f', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Commercial Register, Tax Number Certificate, Articles of Association, Practice License\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', NULL, '2026-04-28 00:06:30', '2026-04-28 00:06:30');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('62c0dbe5-c9d9-4acb-a9b6-70ae579a5d0e', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '10', '{\"title\":\"Sponsorship Invitation \\ud83d\\udcbc\",\"message\":\"You received a sponsorship invitation for \\\"oooooooooo\\\" from \\u0639\\u0628\\u0633\\u064a.\",\"type\":\"sponsorship\",\"icon\":\"\\ud83d\\udcbc\",\"action_url\":\"\\/sponsor\\/requests\",\"related_id\":23}', '2026-04-24 00:28:02', '2026-04-24 00:27:50', '2026-04-24 00:28:02');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('645fe7a8-aada-43f7-9ced-541575aecc36', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '30', '{\"title\":\"Verification Rejected \\u274c\",\"message\":\"Your verification was rejected: \\u0644\",\"type\":\"verification\",\"icon\":\"\\u274c\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 20:56:22', '2026-04-27 20:56:22');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('6efe88eb-1d99-482d-9791-e0ef2e96f520', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '35', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/sponsor\\/dashboard\",\"related_id\":null}', NULL, '2026-04-27 22:45:24', '2026-04-27 22:45:24');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('7a8af275-0017-421b-9476-aa0b4c48af90', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Event Pending\",\"message\":\"Event \\\"oooooooooo\\\" was submitted by \\u0639\\u0628\\u0633\\u064a and needs your approval.\",\"type\":\"event\",\"icon\":\"\\ud83d\\udccb\",\"action_url\":\"\\/admin\\/events\",\"related_id\":23}', '2026-04-24 00:25:34', '2026-04-24 00:25:22', '2026-04-24 00:25:34');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('7b3a7318-d7d8-4563-baa8-cf86baf2c6f6', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '24', '{\"title\":\"New Sponsorship Request \\ud83e\\udd1d\",\"message\":\"ltt sent a sponsorship request for \\\"\\u0644\\u0627\\\".\",\"type\":\"sponsorship\",\"icon\":\"\\ud83e\\udd1d\",\"action_url\":\"\\/manager\\/sponsorship\",\"related_id\":21}', '2026-04-27 23:07:34', '2026-04-24 21:37:20', '2026-04-27 23:07:34');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('81aa4598-d20b-4639-85fb-bd5414585eb1', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"Invitation accepted \\u2705\",\"message\":\"ltt has accepted your sponsorship invitation for \\\"oooooooooo\\\".\",\"type\":\"sponsorship\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/sponsorship\",\"related_id\":23}', '2026-04-24 00:29:51', '2026-04-24 00:28:08', '2026-04-24 00:29:51');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('8c426a60-ad96-4c3e-a057-6266e8a5ecff', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '39', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/dashboard\",\"related_id\":null}', '2026-04-28 15:12:12', '2026-04-28 15:06:24', '2026-04-28 15:12:12');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('8ef3ad7c-658d-4ec8-bbe7-c750373aba89', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0627\\u062d\\u0645\\u062f has submitted updated documents for review: Commercial Register\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-27 23:42:40', '2026-04-27 23:37:39', '2026-04-27 23:42:40');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('90a2e1ea-a5f3-4e35-91cc-6f484bbb18a0', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Partner Registration \\ud83c\\udf89\",\"message\":\"A new Sponsor \\\"{\\u0634\\u0633\\u0634\\u0633}\\\" has registered and is waiting for verification.\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 00:03:07', '2026-04-28 00:02:55', '2026-04-28 00:03:07');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('95edfc5f-2ceb-407a-b778-db2558b24872', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0627\\u062d\\u0645\\u062f has submitted updated documents for review: Tax Number Certificate, Articles of Association\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-27 23:53:20', '2026-04-27 23:53:08', '2026-04-27 23:53:20');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('979cf279-811b-4235-ae7f-2da7e0a545a6', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Tax Number Certificate\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:01:05', '2026-04-27 23:58:11', '2026-04-28 00:01:05');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('99f10784-489d-4895-ada9-9c3c6bfadb57', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0627\\u062d\\u0645\\u062f has submitted updated documents for review: Tax Number Certificate\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-05-01 19:51:49', '2026-05-01 19:49:53', '2026-05-01 19:51:49');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('9b821605-a5f7-454d-8d98-89f6aca0b888', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Event Pending\",\"message\":\"Event \\\"kkkkk\\\" was submitted by \\u0627\\u062d\\u0645\\u062f and needs your approval.\",\"type\":\"event\",\"icon\":\"\\ud83d\\udccb\",\"action_url\":\"\\/admin\\/events\",\"related_id\":26}', '2026-05-01 19:51:43', '2026-05-01 19:50:55', '2026-05-01 19:51:43');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('9e923256-e42e-44f9-ac14-9cc65c0c565a', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '34', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nArticles of Association: gsesdf\\nPractice License: guu\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:17:25', '2026-04-27 22:17:25');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('a884bad3-67c4-462f-a1fb-ee00aface379', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '34', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nPractice License: guuxxxx\\nArticles of Association: gsesdfxxxx\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:17:36', '2026-04-27 22:17:36');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('aa883abb-2efa-426d-b40a-6f074c5f121d', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"Event Rejected \\u274c\",\"message\":\"Your event \\\"aaaaaaaaaaaaaaaaa\\\" has been rejected: uuuuuuuuuuuuuuuuuuuuuu\",\"type\":\"event\",\"icon\":\"\\u274c\",\"action_url\":\"\\/manager\\/events\",\"related_id\":24}', '2026-04-24 00:31:28', '2026-04-24 00:31:14', '2026-04-24 00:31:28');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('ab371aa4-f3be-4d47-8065-891557939c19', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"New Event Pending\",\"message\":\"Event \\\"aaaaaaaaaaaaaaaaa\\\" was submitted by \\u0639\\u0628\\u0633\\u064a and needs your approval.\",\"type\":\"event\",\"icon\":\"\\ud83d\\udccb\",\"action_url\":\"\\/admin\\/events\",\"related_id\":24}', '2026-04-24 00:30:35', '2026-04-24 00:30:12', '2026-04-24 00:30:35');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('ac942686-041c-45f5-894f-febaeeb7cbf7', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Document Update Rejected \\u274c\",\"message\":\"Your updated documents were rejected:\\nArticles of Association: \\u0628\\u0644\",\"type\":\"verification\",\"icon\":\"\\u274c\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:01:05', '2026-04-27 23:44:33', '2026-04-28 00:01:05');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('ad3cdb28-89ad-4aec-bf4c-c279f5ba2bab', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '32', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/dashboard\",\"related_id\":null}', NULL, '2026-04-27 20:43:03', '2026-04-27 20:43:03');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('af49b25f-bb68-43ff-864d-5766ba6bc11b', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '37', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Commercial Register\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:01:05', '2026-04-27 23:42:47', '2026-04-28 00:01:05');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('b4f13b3d-cfeb-446a-a343-f626d582a315', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '34', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nTax Number Certificate: \\u0633\\u064a\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 22:27:54', '2026-04-27 22:27:54');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('b79d6205-5f0a-42c2-9ed1-a3dd97e263ae', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '38', '{\"title\":\"Document Update Rejected \\u274c\",\"message\":\"Your updated documents were rejected:\\nTax Number Certificate: \\u0626\\u0621\\u0624\",\"type\":\"verification\",\"icon\":\"\\u274c\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:03:39', '2026-04-28 00:03:24', '2026-04-28 00:03:39');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('b8ba018b-ac6c-4206-b4d1-44a06ee54b2b', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"New Ticket Booked \\ud83c\\udf9f\\ufe0f\",\"message\":\"Ahmed Rashed booked a ticket for \\\"\\u064a\\u0628\\u0627\\\" (1\\/5)\",\"type\":\"ticket\",\"icon\":\"\\ud83c\\udf9f\\ufe0f\",\"action_url\":\"\\/manager\\/event-stats\\/20\",\"related_id\":20}', '2026-04-25 22:43:13', '2026-04-25 22:41:51', '2026-04-25 22:43:13');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('bbe14555-fc8b-4331-9666-6c348a6f753a', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"New Ticket Booked \\ud83c\\udf9f\\ufe0f\",\"message\":\"Ahmed Rashed booked a ticket for \\\"ltt\\\" (2\\/5)\",\"type\":\"ticket\",\"icon\":\"\\ud83c\\udf9f\\ufe0f\",\"action_url\":\"\\/manager\\/event-stats\\/22\",\"related_id\":22}', '2026-04-25 23:46:58', '2026-04-25 12:30:23', '2026-04-25 23:46:58');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('be3139db-306d-4ad3-a2a4-55ce1af4bc44', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '38', '{\"title\":\"Document Update Approved \\u2705\",\"message\":\"Your updated documents have been approved: Tax Number Certificate\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 00:05:33', '2026-04-28 00:04:14', '2026-04-28 00:05:33');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('c0d508c9-251e-4543-8da9-3830adf76025', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '39', '{\"title\":\"Document Update Rejected \\u274c\",\"message\":\"Your updated documents were rejected:\\nTax Number Certificate: jashgdfbhdgf\",\"type\":\"verification\",\"icon\":\"\\u274c\",\"action_url\":\"\\/profile\",\"related_id\":null}', '2026-04-28 15:07:44', '2026-04-28 15:07:06', '2026-04-28 15:07:44');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('c34a787c-7fe7-406d-852f-e05f494835d7', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '32', '{\"title\":\"Documents Need Revision \\u26a0\\ufe0f\",\"message\":\"Some documents were rejected:\\nTax Number Certificate: \\u0647\\u0643\\u064a\\nPractice License: \\u0647\\u0647\\u0647\\u0647\\u0647\\u0647\",\"type\":\"verification\",\"icon\":\"\\u26a0\\ufe0f\",\"action_url\":\"\\/pending-verification\",\"related_id\":null}', NULL, '2026-04-27 20:39:43', '2026-04-27 20:39:43');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('c3fafad5-b453-4ca8-b375-96659f965e50', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0634\\u0633\\u0634\\u0633 has submitted updated documents for review: Tax Number Certificate\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 00:04:09', '2026-04-28 00:03:52', '2026-04-28 00:04:09');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('c9527f16-6c1a-4907-8309-613fa1de170b', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '38', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/sponsor\\/dashboard\",\"related_id\":null}', '2026-04-28 00:03:39', '2026-04-28 00:03:13', '2026-04-28 00:03:39');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('d076e75b-e322-422a-b324-877f72a58578', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"New Ticket Booked \\ud83c\\udf9f\\ufe0f\",\"message\":\"\\u0627\\u062d\\u0645\\u062f \\u064a\\u0648\\u0632\\u0631 booked a ticket for \\\"ltt\\\" (1\\/5)\",\"type\":\"ticket\",\"icon\":\"\\ud83c\\udf9f\\ufe0f\",\"action_url\":\"\\/manager\\/event-stats\\/22\",\"related_id\":22}', '2026-04-25 12:24:04', '2026-04-25 12:12:17', '2026-04-25 12:24:04');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('d098e1f1-c890-42b4-8370-a7eba0a62a40', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '6', '{\"title\":\"Event Approved \\u2705\",\"message\":\"Your event \\\"oooooooooo\\\" has been approved and is now live!\",\"type\":\"event\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/events\",\"related_id\":23}', '2026-04-24 00:27:35', '2026-04-24 00:27:23', '2026-04-24 00:27:35');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('e4a007f8-b42f-4fe7-92e9-bd15d2342d6a', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '36', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/dashboard\",\"related_id\":null}', NULL, '2026-04-27 23:32:05', '2026-04-27 23:32:05');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('f2fcbe7c-1784-4781-9be9-74e834156056', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '34', '{\"title\":\"Verification Approved \\u2705\",\"message\":\"All your documents have been approved! You now have full access to the platform.\",\"type\":\"verification\",\"icon\":\"\\u2705\",\"action_url\":\"\\/manager\\/dashboard\",\"related_id\":null}', NULL, '2026-04-27 22:31:24', '2026-04-27 22:31:24');
INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES ('fde98226-0346-40d4-9d76-53e7f3e0bc2d', 'App\\Notifications\\SystemNotification', 'App\\Models\\User', '7', '{\"title\":\"Document Update Request \\ud83d\\udcc4\",\"message\":\"Partner \\u0642\\u0639\\u0648\\u062f has submitted updated documents for review: Tax Number Certificate\",\"type\":\"verification\",\"icon\":\"\\ud83d\\udee1\\ufe0f\",\"action_url\":\"\\/admin\\/verifications\",\"related_id\":null}', '2026-04-28 15:09:00', '2026-04-28 15:08:46', '2026-04-28 15:09:00');

-- --------------------------------------------------------
-- Table: password_reset_codes
-- --------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_codes`;
CREATE TABLE `password_reset_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_codes_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: password_reset_tokens
-- --------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: personal_access_tokens
-- --------------------------------------------------------

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=411 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('1', 'App\\Models\\User', '2', 'auth_token', '33865f1ccba7ea549675e9ff2f74d80929d4cfa0680263a8aab5627b000637f3', '[\"*\"]', NULL, NULL, '2026-03-29 00:34:31', '2026-03-29 00:34:31');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('79', 'App\\Models\\User', '16', 'auth_token', '8ad078078738407bc3a5e5288bac46d1b48d9df6177572938484379290e9decf', '[\"*\"]', NULL, NULL, '2026-04-04 12:07:13', '2026-04-04 12:07:13');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('330', 'App\\Models\\User', '8', 'auth_token', '91c1c8b1894b3e1653df93d1e9c1bdee106de224c7f6e22f4720ffdcf721ced2', '[\"*\"]', '2026-04-25 12:35:00', NULL, '2026-04-25 12:33:03', '2026-04-25 12:35:00');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('331', 'App\\Models\\User', '8', 'auth_token', 'c7b9f966edf30d0bfd585c84a2e5d67b980b36e009b2ca7ee9d9364b33c603f0', '[\"*\"]', '2026-04-25 22:44:21', NULL, '2026-04-25 22:40:39', '2026-04-25 22:44:21');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES ('410', 'App\\Models\\User', '37', 'auth_token', '77e2e870aac5ec326e8280c068842f6e75d122306192964ecd8c56dca06e69be', '[\"*\"]', '2026-05-01 20:10:47', NULL, '2026-05-01 19:52:48', '2026-05-01 20:10:47');

-- --------------------------------------------------------
-- Table: profile_contacts
-- --------------------------------------------------------

DROP TABLE IF EXISTS `profile_contacts`;
CREATE TABLE `profile_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_contacts_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_contacts_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `profile_contacts` (`id`, `profile_id`, `type`, `value`, `created_at`, `updated_at`) VALUES ('1', '1', 'phone', '1234567899', '2026-04-04 12:16:12', '2026-04-04 12:16:12');
INSERT INTO `profile_contacts` (`id`, `profile_id`, `type`, `value`, `created_at`, `updated_at`) VALUES ('2', '1', 'email', 'ahmed.zz@gmail.com', '2026-04-04 12:16:12', '2026-04-04 12:16:12');

-- --------------------------------------------------------
-- Table: profiles
-- --------------------------------------------------------

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `profile_type` enum('individual','company') NOT NULL DEFAULT 'individual',
  `logo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_description` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profiles_user_id_unique` (`user_id`),
  KEY `profiles_profile_type_index` (`profile_type`),
  CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('1', '10', 'company', NULL, 'zzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'ltt', NULL, '0', '1', '2026-04-04 12:14:24', '2026-04-21 19:04:48');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('2', '6', 'individual', NULL, 'gfdfgdf1شششششششششششششششششششش', NULL, NULL, '0', '1', '2026-04-06 22:10:58', '2026-04-06 22:48:48');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('3', '7', 'individual', NULL, 'ghfgh', NULL, NULL, '0', '1', '2026-04-06 23:00:19', '2026-04-06 23:00:19');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('4', '13', 'individual', NULL, 'بلابلابلا', NULL, NULL, '0', '1', '2026-04-06 23:10:59', '2026-04-06 23:13:00');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('5', '21', 'company', NULL, NULL, NULL, NULL, '0', '1', '2026-04-13 21:59:18', '2026-04-13 21:59:18');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('7', '28', 'company', NULL, NULL, NULL, NULL, '0', '1', '2026-04-20 00:39:37', '2026-04-20 00:39:37');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('8', '30', 'company', NULL, NULL, NULL, NULL, '0', '1', '2026-04-24 00:28:48', '2026-04-24 00:28:48');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('10', '38', 'company', NULL, NULL, NULL, NULL, '0', '1', '2026-04-28 00:02:55', '2026-04-28 00:02:55');
INSERT INTO `profiles` (`id`, `user_id`, `profile_type`, `logo`, `bio`, `company_name`, `company_description`, `is_approved`, `is_available`, `created_at`, `updated_at`) VALUES ('11', '39', 'individual', NULL, NULL, NULL, NULL, '0', '1', '2026-04-28 15:11:48', '2026-04-28 15:11:48');

-- --------------------------------------------------------
-- Table: ratings
-- --------------------------------------------------------

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL COMMENT '1 to 5',
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratings_user_id_event_id_unique` (`user_id`,`event_id`),
  KEY `ratings_event_id_foreign` (`event_id`),
  CONSTRAINT `ratings_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ratings` (`id`, `event_id`, `user_id`, `rating`, `review_text`, `created_at`, `updated_at`) VALUES ('2', '2', '13', '1', 'fghfghfghgfhgfhfghgfhfghgf', NULL, NULL);
INSERT INTO `ratings` (`id`, `event_id`, `user_id`, `rating`, `review_text`, `created_at`, `updated_at`) VALUES ('3', '2', '21', '0', 'يببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببب', NULL, NULL);
INSERT INTO `ratings` (`id`, `event_id`, `user_id`, `rating`, `review_text`, `created_at`, `updated_at`) VALUES ('4', '7', '8', '4', 'fghfghf', NULL, NULL);
INSERT INTO `ratings` (`id`, `event_id`, `user_id`, `rating`, `review_text`, `created_at`, `updated_at`) VALUES ('5', '7', '7', '4', 'ghfg', NULL, NULL);

-- --------------------------------------------------------
-- Table: sessions
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('L6IF4yjg1J2SEOOagUB4FFeRD2FQdm0jv1LdQaEC', '37', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZXY4S010SWp4elBuRkxqR1ZUaExMWFpsY09mTVdmYmVmTHVZdjEwViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYW5hZ2VyL2V2ZW50cyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzc7fQ==', '1777665170');

-- --------------------------------------------------------
-- Table: sponsorship_requests
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sponsorship_requests`;
CREATE TABLE `sponsorship_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `sponsor_id` bigint(20) unsigned DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `event_manager_id` bigint(20) unsigned DEFAULT NULL,
  `initiator` enum('sponsor','event_manager') NOT NULL DEFAULT 'sponsor',
  PRIMARY KEY (`id`),
  KEY `sponsorship_requests_event_id_foreign` (`event_id`),
  KEY `sponsorship_requests_sponsor_id_foreign` (`sponsor_id`),
  KEY `sponsorship_requests_event_manager_id_foreign` (`event_manager_id`),
  CONSTRAINT `sponsorship_requests_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sponsorship_requests_event_manager_id_foreign` FOREIGN KEY (`event_manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sponsorship_requests_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('7', '1', '10', 'احبك', 'accepted', '2026-03-29 01:51:40', '2026-03-29 01:52:33', NULL, 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('8', '2', '10', 'سيبسيبس', 'rejected', '2026-03-29 01:54:45', '2026-03-29 01:57:28', NULL, 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('9', '5', '10', 'سيبسيب', 'rejected', '2026-03-29 01:54:50', '2026-03-29 01:57:29', NULL, 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('10', '6', '10', 'sdfsfsd', 'accepted', '2026-03-29 01:57:04', '2026-03-29 01:57:26', NULL, 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('11', '7', '10', 'uhgdfuhgdufhgdfuh', 'accepted', '2026-03-29 15:49:34', '2026-03-29 15:51:15', NULL, 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('12', '12', '10', 'vbn', 'rejected', '2026-04-13 20:54:34', '2026-04-13 21:55:33', '13', 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('13', '17', '10', 'hhhhhhhhhhhhhhhhhhhhhhhh', 'accepted', '2026-04-13 20:59:27', '2026-04-13 21:02:25', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('14', '14', '10', 'asdas', 'accepted', '2026-04-13 21:03:23', '2026-04-13 21:25:36', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('15', '11', '10', 'بلالبا', 'accepted', '2026-04-13 21:25:17', '2026-04-13 21:25:34', '13', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('16', '13', '10', 'تت', 'accepted', '2026-04-13 21:52:24', '2026-04-13 21:55:35', '13', 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('17', '9', '21', 'nnnnnnnnnnnnnnnn', 'accepted', '2026-04-13 21:59:53', '2026-04-13 22:00:54', '6', 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('18', '17', '21', 'gggggg', 'accepted', '2026-04-13 22:00:40', '2026-04-13 22:01:15', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('19', '7', '21', 'ييي', 'accepted', '2026-04-13 22:12:14', '2026-04-13 22:12:27', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('20', '14', '21', NULL, 'accepted', '2026-04-13 22:13:38', '2026-04-13 22:13:58', '6', 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('21', '18', '21', 'هههههههههههههههههههههههههههههههههههههههههههههه', 'accepted', '2026-04-13 22:32:44', '2026-04-13 22:33:27', '13', 'sponsor');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('22', '14', '28', 'لا', 'rejected', '2026-04-20 01:02:51', '2026-04-25 12:26:39', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('23', '22', '10', 'hdfbhdbfhdfb', 'accepted', '2026-04-21 19:03:14', '2026-04-21 19:08:15', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('24', '23', '10', '\'', 'accepted', '2026-04-24 00:27:50', '2026-04-24 00:28:08', '6', 'event_manager');
INSERT INTO `sponsorship_requests` (`id`, `event_id`, `sponsor_id`, `message`, `status`, `created_at`, `updated_at`, `event_manager_id`, `initiator`) VALUES ('25', '21', '10', NULL, 'pending', '2026-04-24 21:37:19', '2026-04-24 21:37:19', '24', 'sponsor');

-- --------------------------------------------------------
-- Table: tickets
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `status` enum('unused','used') NOT NULL DEFAULT 'unused',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_qr_code_unique` (`qr_code`),
  KEY `tickets_event_id_foreign` (`event_id`),
  KEY `tickets_user_id_foreign` (`user_id`),
  CONSTRAINT `tickets_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('1', '8', '8', '', 'unused', NULL, NULL);
INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('3', '2', '6', 'werw33', 'unused', NULL, NULL);
INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('4', '17', '8', 'fghf', 'used', NULL, NULL);
INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('5', '22', '31', 'JHNE5TJNB2-22-31', 'unused', '2026-04-25 12:12:09', '2026-04-25 12:12:09');
INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('6', '22', '8', 'VAWMUQZHWQ-22-8', 'unused', '2026-04-25 12:30:23', '2026-04-25 12:30:23');
INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('7', '21', '8', 'PKTY6IB0PM-21-8', 'unused', '2026-04-25 12:34:26', '2026-04-25 12:34:26');
INSERT INTO `tickets` (`id`, `event_id`, `user_id`, `qr_code`, `status`, `created_at`, `updated_at`) VALUES ('8', '20', '8', 'HDCIGFNJYW-20-8', 'unused', '2026-04-25 22:41:50', '2026-04-25 22:41:50');

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('Admin','Event Manager','Sponsor','User','Assistant') NOT NULL DEFAULT 'User',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `verification_status` enum('verified','pending','rejected','changes_requested') DEFAULT 'verified',
  `verification_notes` text DEFAULT NULL,
  `doc_commercial_register` varchar(255) DEFAULT NULL,
  `doc_tax_number` varchar(255) DEFAULT NULL,
  `doc_articles_of_association` varchar(255) DEFAULT NULL,
  `doc_practice_license` varchar(255) DEFAULT NULL,
  `doc_commercial_register_status` varchar(255) NOT NULL DEFAULT 'pending',
  `doc_tax_number_status` varchar(255) NOT NULL DEFAULT 'pending',
  `doc_articles_of_association_status` varchar(255) NOT NULL DEFAULT 'pending',
  `doc_practice_license_status` varchar(255) NOT NULL DEFAULT 'pending',
  `doc_commercial_register_note` text DEFAULT NULL,
  `doc_tax_number_note` text DEFAULT NULL,
  `doc_articles_of_association_note` text DEFAULT NULL,
  `doc_practice_license_note` text DEFAULT NULL,
  `event_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_event_id_foreign` (`event_id`),
  CONSTRAINT `users_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('6', 'عبسي', 'MANAGER@test.com', 'ahmed.ghjg5763234@gmail.com', 'avatars/Ixuj6KV3r82uSdx4cBPf7donqKL9oqV7pj9s5uhy.jpg', 'avatars/Ixuj6KV3r82uSdx4cBPf7donqKL9oqV7pj9s5uhy.jpg', '09345345555', 'gfdfgdf1شششششششششششششششششششش', '[]', NULL, '$2y$12$uGqtH7iKCUpI2sJFvKmR2OOxH8v.jyDdzjo41WDPFLVgZeLsYklz.', NULL, '2026-03-29 01:02:00', '2026-04-28 00:06:30', 'Event Manager', '1', 'verified', NULL, 'verifications/nTT6ZjC53yJgFxbLFagtUSXBlW745RWhmUULMkKr.pdf', 'verifications/nhZqsbiuDNpaKQDa1ZbKNrtA5WN8Y9HmbqceFsRQ.pdf', 'verifications/GretQ80k4Dva0tV6Oi7aAcUDAR3uZ6kTOirZLIer.pdf', 'verifications/Tsq4hkte4Wf5MLAunHUshuOiFYxuQJD0xRBbUBvm.jpg', 'approved', 'approved', 'approved', 'approved', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('7', 'admin', 'admin@admin.com', 'ahmed.ahmed5763234@gmail.com', 'avatars/B0QiPhpVqpH8A4VkwQNpRhWgeX0xj9OccbfOVjBB.jpg', 'avatars/B0QiPhpVqpH8A4VkwQNpRhWgeX0xj9OccbfOVjBB.jpg', '456456', 'ghfgh', '{\"facebook\":\"http:\\/\\/localhost\\/phpmyadmin\\/index.php?route=\\/sql&db=eventhub&table=users&pos=0\"}', NULL, '$2y$12$VlAKi4T.zims7G/Y79Jdc.0etabd6.Jtq1McPoSaTsTRX7Ng.35Zy', NULL, '2026-03-29 01:07:01', '2026-04-06 23:00:41', 'Admin', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('8', 'Ahmed Rashed', 'user@user.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$7kEvSNqBH0HY5SZ0sOn2cOhIgAobv.FnawX/glb9.FbsbqOQMxbDu', NULL, '2026-03-29 01:22:49', '2026-04-06 23:35:21', 'User', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('10', 'sponsor', 'sponsor@sponsor.com', 'ahmed.ahmed5763234@gmail.com', 'avatars/bkVDv9R61UbDzeBCLs0NRTa3QxlUu27BrOf5JIyB.png', 'avatars/bkVDv9R61UbDzeBCLs0NRTa3QxlUu27BrOf5JIyB.png', '765765765765765765', 'zzzzzzzzzzzzzzzzzzzzzzzzzzzz', '[]', NULL, '$2y$12$HmG6nq822V3k.Yr5kTuzke1BnbpIZxd0RR5y999dUnBSpV0ldI9GS', NULL, '2026-03-29 01:32:23', '2026-04-24 22:09:48', 'Sponsor', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('13', 'manager222', 'MANAGER222@test.com', 'moo.xx@gmail.com', 'avatars/5hdVVFhxLDNJsIcysKroP7RN6yjXhZj5ETQb4w8u.jpg', 'avatars/5hdVVFhxLDNJsIcysKroP7RN6yjXhZj5ETQb4w8u.jpg', '11122223333', 'بلابلابلا', '[]', NULL, '$2y$12$O1oC5TWVQgdP76PBVlwaUeLOy1nq4Z9YvkHMdzfwV0RgBZbzdN.ke', NULL, '2026-03-29 01:55:24', '2026-04-06 23:17:26', 'Event Manager', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('21', 'sp2', 'sponsor222@sponsor.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$WeB.XeGZeyR.44x/7cIRDu7z6c/Pz4mF5LoK7fL7qhhhR422pkjg.', NULL, '2026-04-13 21:59:18', '2026-04-13 21:59:18', 'Sponsor', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('24', 'احمد', 'MANAGERahmed@test.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$6vn4EsQaqRS1WoU9xVIuB.eZho.5MeGquiV47Z6qpuxBK/PFVYJyK', NULL, '2026-04-20 00:07:08', '2026-04-20 00:55:30', 'Event Manager', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('28', 'سبون', 'sponsorahmed@test.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$gc57RQMCq6SGulkGsX9I8.ap0Dcr6j5tLWZz.Wl0b9PlzKOc1J9P2', NULL, '2026-04-20 00:39:37', '2026-04-20 00:40:33', 'Sponsor', '1', 'rejected', 'هههههههههههههههههههه', NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('29', 'احمد راشد', 'MANAGERAHM@test.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$p8UE86vyAW164TKRNWIFAuqSVi7UQo1Fgjx/alfd8pvvt81Wu6eby', NULL, '2026-04-21 18:33:46', '2026-04-21 18:35:38', 'Event Manager', '1', 'rejected', 'GGG', NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('30', 'jjj', 'MANAGERjjjjj@test.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$i52QNBY8BAsD38Nj7WTTUeJCehtqWYMfM3XNUjKg7jbcgh.QNgF1.', NULL, '2026-04-24 00:28:48', '2026-04-27 20:56:22', 'Sponsor', '1', 'rejected', 'ل', NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('31', 'احمد يوزر', 'USERnew@USER.COM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$NfbB6TQYB/2OYKcm56/2BeJJgrZ3dbdzbJbleRWIj//uoL87cICXq', NULL, '2026-04-25 12:09:20', '2026-04-25 12:09:20', 'User', '1', 'verified', NULL, NULL, NULL, NULL, NULL, 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('33', 'قوء', 'MANAGERy@test.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$kYLqs56KRrq213EwFwmYyOXNuDc6GdF1hrDsJZkWXwohR8iB//gcG', NULL, '2026-04-27 20:51:29', '2026-04-27 22:50:12', 'Event Manager', '1', 'rejected', 'يبليب', 'verifications/ESNUz0FgK1fMn8eiDGzE8RERAhFDpcKbpePBlugm.jpg', 'verifications/GwPZN9cScyGclNTrENE4aAAnJzBFFLq71itC3q75.jpg', 'verifications/lSc8MHb1kcFMgQUy3xO2YL7zYUk4dK45IyvZp0tA.jpg', 'verifications/tjO3Oe6qInraqD1iOQ181x8y4lEdVrFkAAC35rHi.jpg', 'pending', 'pending', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('37', 'احمد', 'MANAGERahmedz@test.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$m8IlaqI8O.cuqV53Oj795uO5aovEVUTQONwdk42woPhFX/6YzVUva', NULL, '2026-04-27 23:32:31', '2026-05-01 19:49:52', 'Event Manager', '1', 'verified', NULL, 'verifications/dwOpq3Bei3MwlfvOfDTIvllIlAYen8XLXEdEyJ9j.pdf', 'verifications/WXbi4taro9NHnFjhYSAAQ0AdEjPliZrjyv77o4GO.png', 'verifications/kmBP9sjGAWPjzc0uGlGQBjedPf1H2VVqXCOJdDKp.jpg', 'verifications/MZ3sfMPLU8dZj0I7G5OsrR0mCmUmAjlW8QzBLh2f.jpg', 'approved', 'pending_update', 'approved', 'approved', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('38', 'شسشس', 'sponsorzz@sponsor.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$uuAjtrTrsKWKz5UGujvLLumhIJZvp0SQ8dYeEGRY7b9whmWK8jORC', NULL, '2026-04-28 00:02:55', '2026-04-28 00:04:14', 'Sponsor', '1', 'verified', NULL, 'verifications/D9eA5uD3LEcO6o4KoTlfN85Tt4rAvM8OJfSsNXIg.pdf', 'verifications/Qdw9ZeFOtPPBddjQ1kMDKScFcIXAqYa5HKjTNCXh.jpg', NULL, NULL, 'approved', 'approved', 'pending', 'pending', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `contact_email`, `avatar`, `image`, `phone`, `bio`, `social_links`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `verification_status`, `verification_notes`, `doc_commercial_register`, `doc_tax_number`, `doc_articles_of_association`, `doc_practice_license`, `doc_commercial_register_status`, `doc_tax_number_status`, `doc_articles_of_association_status`, `doc_practice_license_status`, `doc_commercial_register_note`, `doc_tax_number_note`, `doc_articles_of_association_note`, `doc_practice_license_note`, `event_id`) VALUES ('39', 'قعود', 'MANAGERaaaaa@test.com', NULL, NULL, NULL, '0934534555', NULL, '[]', NULL, '$2y$12$E3w11jyQch9KONUo/YiXBOjHJ0WSE50AomTSRIsPEuw./UBSjp56.', NULL, '2026-04-28 15:03:32', '2026-04-28 15:11:48', 'Event Manager', '1', 'verified', NULL, 'verifications/5D9JipbJqhByV03vLBfqSlkA8CEIsTagZIJYoGbB.jpg', 'verifications/2O4j4mWL4yxNUFT8yqdxcHqALfCrH3LDz6tE3mK3.pdf', 'verifications/rhjJs9QX2FVGvKvjPSWSqYxHz1aMvY246Z41PUas.pdf', 'verifications/e0Pmcqt5X1YqUxCzXavbx5jCvfZxq3dPMbObXrQn.pdf', 'approved', 'approved', 'approved', 'approved', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Table: venues
-- --------------------------------------------------------

DROP TABLE IF EXISTS `venues`;
CREATE TABLE `venues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `morning_start` varchar(5) NOT NULL DEFAULT '09:00',
  `morning_end` varchar(5) NOT NULL DEFAULT '13:00',
  `evening_start` varchar(5) NOT NULL DEFAULT '15:00',
  `evening_end` varchar(5) NOT NULL DEFAULT '19:00',
  `status` enum('available','maintenance') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `venues` (`id`, `name`, `location`, `capacity`, `morning_start`, `morning_end`, `evening_start`, `evening_end`, `status`, `created_at`, `updated_at`) VALUES ('1', 'tripoly', 'tr', '155', '09:00', '13:00', '15:00', '19:00', 'available', '2026-03-29 01:09:19', '2026-03-29 01:44:23');
INSERT INTO `venues` (`id`, `name`, `location`, `capacity`, `morning_start`, `morning_end`, `evening_start`, `evening_end`, `status`, `created_at`, `updated_at`) VALUES ('2', 'benghazi', 'http://127.0.0.1:8000/admin/venues', '120', '09:00', '13:00', '15:00', '19:00', 'available', '2026-03-29 01:09:43', '2026-04-24 20:18:06');
INSERT INTO `venues` (`id`, `name`, `location`, `capacity`, `morning_start`, `morning_end`, `evening_start`, `evening_end`, `status`, `created_at`, `updated_at`) VALUES ('3', 'nyc', 'aziziza', '53', '09:00', '13:00', '15:00', '19:00', 'available', '2026-03-29 15:57:25', '2026-03-29 15:58:10');
INSERT INTO `venues` (`id`, `name`, `location`, `capacity`, `morning_start`, `morning_end`, `evening_start`, `evening_end`, `status`, `created_at`, `updated_at`) VALUES ('4', 'TEST', 'http://127.0.0.1:8000/admin/venues', '55', '09:00', '13:00', '15:00', '19:00', 'available', '2026-04-11 23:02:02', '2026-04-11 23:02:02');
INSERT INTO `venues` (`id`, `name`, `location`, `capacity`, `morning_start`, `morning_end`, `evening_start`, `evening_end`, `status`, `created_at`, `updated_at`) VALUES ('5', 'testz', 'http://127.0.0.1:8000/admin/venues', '8888', '09:00', '13:00', '15:00', '19:00', 'available', '2026-04-24 00:24:28', '2026-04-24 00:24:28');
INSERT INTO `venues` (`id`, `name`, `location`, `capacity`, `morning_start`, `morning_end`, `evening_start`, `evening_end`, `status`, `created_at`, `updated_at`) VALUES ('6', 'camp nou', 'http://127.0.0.1:8000/admin/venues', '200', '01:00', '13:00', '15:00', '19:00', 'available', '2026-05-01 19:49:02', '2026-05-01 19:49:02');

SET FOREIGN_KEY_CHECKS=1;
