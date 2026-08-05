ALTER TABLE `members`
  ADD COLUMN `line_user_id` varchar(64) DEFAULT NULL AFTER `pdpa_consent_version`,
  ADD COLUMN `line_display_name` varchar(255) DEFAULT NULL AFTER `line_user_id`,
  ADD COLUMN `line_linked_at` datetime DEFAULT NULL AFTER `line_display_name`;

CREATE UNIQUE INDEX `uniq_members_line_user_id` ON `members` (`line_user_id`);
