/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `ds_events` (
  `event_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `package_id` VARCHAR(64) DEFAULT NULL,
  `type` INT(10) DEFAULT NULL,
  `created_by` VARCHAR(254) CHARACTER SET utf8 DEFAULT NULL,
  `ip` VARCHAR(39) DEFAULT NULL,
  `session` CHAR(64) DEFAULT NULL,
  `affected` VARCHAR(254) CHARACTER SET utf8 DEFAULT NULL,
  `message` VARCHAR(2048) DEFAULT NULL,
  `filter_1` INT(10) UNSIGNED DEFAULT NULL,
  `filter_2` INT(10) UNSIGNED DEFAULT NULL,
  `filter_3` INT(10) UNSIGNED DEFAULT NULL,
  `filter_4` INT(10) UNSIGNED DEFAULT NULL,
  `filter_5` INT(10) UNSIGNED DEFAULT NULL,
  `filter_6` CHAR(1) DEFAULT NULL,
  `filter_7` CHAR(1) DEFAULT NULL,
  `filter_8` CHAR(1) DEFAULT NULL,
  `filter_9` CHAR(1) DEFAULT NULL,
  `filter_10` CHAR(1) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `timestamp` (`timestamp`),
  KEY `package_id` (`package_id`),
  KEY `type` (`type`),
  KEY `filter_1` (`filter_1`),
  KEY `filter_2` (`filter_2`),
  KEY `filter_3` (`filter_3`),
  KEY `filter_4` (`filter_4`),
  KEY `filter_5` (`filter_5`),
  KEY `filter_6` (`filter_6`),
  KEY `filter_7` (`filter_7`),
  KEY `filter_8` (`filter_8`),
  KEY `filter_9` (`filter_9`),
  KEY `filter_10` (`filter_10`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_permissions` (
  `permission_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` VARCHAR(64) DEFAULT NULL,
  `name` VARCHAR(64) DEFAULT NULL,
  `domain` VARCHAR(64) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_on` DATETIME DEFAULT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `package_id_name` (`package_id`,`name`),
  INDEX `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_groups` (
  `group_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) DEFAULT NULL,
  `description` VARCHAR(64) DEFAULT NULL,
  `domain` VARCHAR(64) DEFAULT NULL,
  `created_by` VARCHAR(254) DEFAULT NULL COLLATE 'utf8_general_ci',
  `created_on` DATETIME DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `name` (`name`),
  INDEX `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_users` (
  `user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(254) DEFAULT NULL COLLATE 'utf8_general_ci',
  `password` CHAR(96) NOT NULL DEFAULT '',
  `inactive` tinyINT(1) UNSIGNED DEFAULT NULL,
  `inactive_time` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(254) DEFAULT NULL COLLATE 'utf8_general_ci',
  `created_on` DATETIME DEFAULT NULL,
  `login_attempts` tinyINT(1) UNSIGNED DEFAULT 0,
  `login_last_attempt` DATETIME DEFAULT NULL,
  `login_last_success` DATETIME DEFAULT NULL,
  `login_last_ip` VARCHAR(39) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_group_permissions` (
  `group_id` INT(10) UNSIGNED NOT NULL,
  `permission_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`group_id`,`permission_id`),
  KEY `ds_group_permissions-permission_id` (`permission_id`),
  CONSTRAINT `ds_group_permissions-group_id` FOREIGN KEY (`group_id`) REFERENCES `ds_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ds_group_permissions-permission_id` FOREIGN KEY (`permission_id`) REFERENCES `ds_permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_user_groups` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `group_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `ds_user_groups-group_id` (`group_id`),
  CONSTRAINT `ds_user_groups-group_id` FOREIGN KEY (`group_id`) REFERENCES `ds_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ds_user_groups-user_id` FOREIGN KEY (`user_id`) REFERENCES `ds_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_properties` (
  `property_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NULL DEFAULT NULL,
  `domain` VARCHAR(64) NULL DEFAULT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `type` ENUM('int','float','bool','string') NOT NULL,
  `default` VARCHAR(2048) NULL DEFAULT NULL,
  `created_by` VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
  `created_on` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`property_id`),
  UNIQUE INDEX `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_property_data` (
  `property_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `domain` VARCHAR(64) NULL DEFAULT NULL,
  `value` VARCHAR(2048) NULL DEFAULT NULL,
  UNIQUE INDEX `domain_property_id` (`domain`, `property_id`),
  INDEX `fk_property_data_property_id` (`property_id`),
  CONSTRAINT `fk_property_data_property_id` FOREIGN KEY (`property_id`) REFERENCES `ds_properties` (`property_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_view_group_permissions` (
   `group_id` INT(10) UNSIGNED NOT NULL,
   `permission_id` INT(10) UNSIGNED NULL DEFAULT '0',
   `package_id` VARCHAR(64) NULL DEFAULT NULL,
   `name` VARCHAR(64) NULL DEFAULT NULL,
   `domain` VARCHAR(64) NULL DEFAULT NULL,
   `description` VARCHAR(255) NULL DEFAULT NULL
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `ds_view_user_groups` (
   `user_id` INT(10) UNSIGNED NOT NULL,
   `group_id` INT(10) UNSIGNED NULL DEFAULT '0',
   `name` VARCHAR(64) NULL DEFAULT NULL,
   `domain` VARCHAR(64) NULL DEFAULT NULL,
   `description` VARCHAR(64) NULL DEFAULT NULL
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `ds_view_user_permissions` (
   `user_id` INT(10) UNSIGNED NOT NULL,
   `group_id` INT(10) UNSIGNED NOT NULL,
   `permission_id` INT(10) UNSIGNED NULL DEFAULT '0',
   `package_id` VARCHAR(64) NULL DEFAULT NULL,
   `name` VARCHAR(64) NULL DEFAULT NULL,
   `domain` VARCHAR(64) NULL DEFAULT NULL,
   `description` VARCHAR(255) NULL DEFAULT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `ds_view_user_permissions`;
CREATE VIEW IF NOT EXISTS `ds_view_user_permissions`
AS SELECT
   `meta`.`user_id` AS `user_id`,
   `meta`.`group_id` AS `group_id`,
   `data`.`permission_id` AS `permission_id`,
   `data`.`package_id` AS `package_id`,
   `data`.`name` AS `name`,
   `data`.`domain` AS `domain`,
   `data`.`description` AS `description`
FROM (`ds_user_groups` `meta` left join `ds_view_group_permissions` `data` on(`data`.`group_id` = `meta`.`group_id`)) group by `meta`.`user_id`,`data`.`permission_id`;

DROP TABLE IF EXISTS `ds_view_user_groups`;
CREATE VIEW IF NOT EXISTS `ds_view_user_groups`
AS SELECT
   `data`.`user_id` AS `user_id`,
   `meta`.`group_id` AS `group_id`,
   `meta`.`name` AS `name`,
   `meta`.`domain` AS `domain`,
   `meta`.`description` AS `description`
FROM (`ds_user_groups` `data` left join `ds_groups` `meta` on(`data`.`group_id` = `meta`.`group_id`));

DROP TABLE IF EXISTS `ds_view_group_permissions`;
CREATE VIEW IF NOT EXISTS `ds_view_group_permissions`
AS SELECT
   `data`.`group_id` AS `group_id`,
   `meta`.`permission_id` AS `permission_id`,
   `meta`.`package_id` AS `package_id`,
   `meta`.`name` AS `name`,
   `meta`.`domain` AS `domain`,
   `meta`.`description` AS `description`
FROM (`ds_group_permissions` `data` left join `ds_permissions` `meta` on(`data`.`permission_id` = `meta`.`permission_id`));

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;