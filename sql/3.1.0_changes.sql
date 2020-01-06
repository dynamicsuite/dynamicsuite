ALTER TABLE `ds_users` CHANGE COLUMN `username` `username` VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `user_id`;
ALTER TABLE `ds_users` CHANGE COLUMN `created_by` `created_by` VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `inactive_time`;
ALTER TABLE `ds_users` CHANGE COLUMN `password` `password` CHAR(96) NULL DEFAULT NULL AFTER `username`;
ALTER TABLE `ds_events` CHANGE COLUMN `created_by` `created_by` VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `type`;
ALTER TABLE `ds_groups` CHANGE COLUMN `created_by` `created_by` VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `description`;