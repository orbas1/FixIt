
ALTER TABLE `addresses`
ADD COLUMN `street_address` VARCHAR(255) NULL AFTER `address`;

ALTER TABLE users DROP INDEX users_email_unique;

INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
VALUES
    (115, 3),
    (116, 3),
    (117, 3),
    (118, 3);

ALTER TABLE `bids` CHANGE `status` `status` ENUM('rejected','accepted','requested') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `time_slots` ADD `is_active` BOOLEAN NOT NULL DEFAULT TRUE AFTER `serviceman_id`;

--
-- Table structure for table `video_consultations`
--

CREATE TABLE `video_consultations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `meeting_id` varchar(255) DEFAULT NULL,
  `agenda` varchar(191) DEFAULT NULL,
  `topic` varchar(191) DEFAULT NULL,
  `platform` enum('google_meet','zoom') DEFAULT 'zoom',
  `type` enum('instant','scheduled','recurring') DEFAULT NULL,
  `duration` varchar(191) DEFAULT NULL,
  `timezone` varchar(191) DEFAULT NULL,
  `password` longtext DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `pre_schedule` int(11) DEFAULT 0,
  `schedule_for` longtext DEFAULT NULL,
  `template_id` longtext DEFAULT NULL,
  `start_url` longtext DEFAULT NULL,
  `join_url` longtext DEFAULT NULL,
  `event_id` longtext DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `video_consultations`
--
ALTER TABLE `video_consultations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_consultations_created_by_id_foreign` (`created_by_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `video_consultations`
--
ALTER TABLE `video_consultations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `video_consultations`
--
ALTER TABLE `video_consultations`
  ADD CONSTRAINT `video_consultations_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE custom_offers ADD COLUMN duration VARCHAR(255) NULL AFTER ended_at, ADD COLUMN duration_unit VARCHAR(255) NULL AFTER duration;