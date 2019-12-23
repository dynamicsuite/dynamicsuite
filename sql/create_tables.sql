/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE `ds_events` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `package_id` varchar(64) DEFAULT NULL,
  `type` int(10) DEFAULT NULL,
  `created_by` varchar(64) DEFAULT NULL,
  `ip` varchar(39) DEFAULT NULL,
  `session` char(64) DEFAULT NULL,
  `affected` varchar(64) DEFAULT NULL,
  `message` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `timestamp` (`timestamp`),
  KEY `package_id` (`package_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ds_group_permissions` (
  `group_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`permission_id`),
  KEY `ds_group_permissions-permission_id` (`permission_id`),
  CONSTRAINT `ds_group_permissions-group_id` FOREIGN KEY (`group_id`) REFERENCES `ds_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ds_group_permissions-permission_id` FOREIGN KEY (`permission_id`) REFERENCES `ds_permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ds_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `description` varchar(64) DEFAULT NULL,
  `created_by` varchar(64) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ds_permissions` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` varchar(64) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `package_id_name` (`package_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ds_user_groups` (
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `ds_user_groups-group_id` (`group_id`),
  CONSTRAINT `ds_user_groups-group_id` FOREIGN KEY (`group_id`) REFERENCES `ds_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ds_user_groups-user_id` FOREIGN KEY (`user_id`) REFERENCES `ds_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ds_users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) DEFAULT NULL,
  `password` char(96) NOT NULL DEFAULT '',
  `inactive` tinyint(1) unsigned DEFAULT NULL,
  `inactive_time` datetime DEFAULT NULL,
  `created_by` varchar(64) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `login_attempts` tinyint(1) unsigned DEFAULT 0,
  `login_last_attempt` datetime DEFAULT NULL,
  `login_last_success` datetime DEFAULT NULL,
  `login_last_ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ds_view_group_permissions` (
   `group_id` INT(10) UNSIGNED NOT NULL,
   `permission_id` INT(10) UNSIGNED NULL DEFAULT '0',
   `package_id` VARCHAR(64) NULL DEFAULT NULL,
   `name` VARCHAR(64) NULL DEFAULT NULL,
   `description` VARCHAR(255) NULL DEFAULT NULL
) ENGINE=MyISAM;

CREATE TABLE `ds_view_user_groups` (
   `user_id` INT(10) UNSIGNED NOT NULL,
   `group_id` INT(10) UNSIGNED NULL DEFAULT '0',
   `name` VARCHAR(64) NULL DEFAULT NULL,
   `description` VARCHAR(64) NULL DEFAULT NULL
) ENGINE=MyISAM;

CREATE TABLE `ds_view_user_permissions` (
   `user_id` INT(10) UNSIGNED NOT NULL,
   `group_id` INT(10) UNSIGNED NOT NULL,
   `permission_id` INT(10) UNSIGNED NULL DEFAULT '0',
   `package_id` VARCHAR(64) NULL DEFAULT NULL,
   `name` VARCHAR(64) NULL DEFAULT NULL,
   `description` VARCHAR(255) NULL DEFAULT NULL
) ENGINE=MyISAM;


DROP TABLE `ds_view_user_permissions`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `ds_view_user_permissions`
AS SELECT
   `meta`.`user_id` AS `user_id`,
   `meta`.`group_id` AS `group_id`,
   `data`.`permission_id` AS `permission_id`,
   `data`.`package_id` AS `package_id`,
   `data`.`name` AS `name`,
   `data`.`description` AS `description`
FROM (`ds_user_groups` `meta` left join `ds_view_group_permissions` `data` on(`data`.`group_id` = `meta`.`group_id`)) group by `meta`.`user_id`,`data`.`permission_id`;

DROP TABLE `ds_view_user_groups`;
CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `ds_view_user_groups`
AS SELECT
   `data`.`user_id` AS `user_id`,
   `meta`.`group_id` AS `group_id`,
   `meta`.`name` AS `name`,
   `meta`.`description` AS `description`
FROM (`ds_user_groups` `data` left join `ds_groups` `meta` on(`data`.`group_id` = `meta`.`group_id`));

DROP TABLE `ds_view_group_permissions`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `ds_view_group_permissions`
AS SELECT
   `data`.`group_id` AS `group_id`,
   `meta`.`permission_id` AS `permission_id`,
   `meta`.`package_id` AS `package_id`,
   `meta`.`name` AS `name`,
   `meta`.`description` AS `description`
FROM (`ds_group_permissions` `data` left join `ds_permissions` `meta` on(`data`.`permission_id` = `meta`.`permission_id`));

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;