/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `ds_events` (
    `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `package_id` varchar(64) DEFAULT NULL,
    `type` int(10) DEFAULT NULL,
    `domain` varchar(255) DEFAULT NULL,
    `ip` varchar(39) DEFAULT NULL,
    `session` char(64) DEFAULT NULL,
    `affected` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `message` varchar(2048) DEFAULT NULL,
    `created_by` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `created_on` int(10) NULL DEFAULT NULL,
    PRIMARY KEY (`event_id`),
    KEY `package_id` (`package_id`),
    KEY `type` (`type`),
    KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_groups` (
    `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(64) DEFAULT NULL,
    `description` varchar(64) DEFAULT NULL,
    `domain` varchar(64) DEFAULT NULL,
    `created_by` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `created_on` int(10) NULL DEFAULT NULL,
    PRIMARY KEY (`group_id`),
    UNIQUE KEY `name_domain` (`name`,`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_groups_permissions` (
    `group_id` int(10) unsigned NOT NULL,
    `permission_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`group_id`,`permission_id`),
    KEY `ds_group_permissions-permission_id` (`permission_id`),
    CONSTRAINT `ds_group_permissions-group_id` FOREIGN KEY (`group_id`) REFERENCES `ds_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ds_group_permissions-permission_id` FOREIGN KEY (`permission_id`) REFERENCES `ds_permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_permissions` (
    `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `package_id` varchar(64) DEFAULT NULL,
    `name` varchar(64) DEFAULT NULL,
    `domain` varchar(64) DEFAULT NULL,
    `description` varchar(255) DEFAULT NULL,
    `created_by` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `created_on` int(10) NULL DEFAULT NULL,
    PRIMARY KEY (`permission_id`),
    UNIQUE KEY `package_id_name` (`package_id`,`name`),
    KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_properties` (
    `property_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(64) DEFAULT NULL,
    `domain` varchar(64) DEFAULT NULL,
    `description` varchar(255) DEFAULT NULL,
    `type` enum('int','float','bool','string') NOT NULL,
    `default` varchar(2048) DEFAULT NULL,
    `created_by` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `created_on` int(10) NULL DEFAULT NULL,
    PRIMARY KEY (`property_id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_properties_data` (
    `property_id` int(10) unsigned DEFAULT NULL,
    `domain` varchar(64) DEFAULT NULL,
    `value` varchar(2048) DEFAULT NULL,
    UNIQUE KEY `domain_property_id` (`domain`,`property_id`),
    KEY `fk_property_data_property_id` (`property_id`),
    CONSTRAINT `fk_property_data_property_id` FOREIGN KEY (`property_id`) REFERENCES `ds_properties` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_users` (
    `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `password` char(96) NOT NULL DEFAULT '',
    `password_expired` tinyint(1) unsigned DEFAULT NULL,
    `root` tinyint(1) unsigned DEFAULT NULL,
    `inactive` tinyint(1) unsigned DEFAULT NULL,
    `inactive_by` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `inactive_on` int(10) NULL DEFAULT NULL,
    `login_attempts` tinyint(1) unsigned DEFAULT 0,
    `login_last_attempt` int(10) NULL DEFAULT NULL,
    `login_last_success` int(10) NULL DEFAULT NULL,
    `login_last_ip` varchar(39) DEFAULT NULL,
    `created_by` varchar(254) CHARACTER SET utf8 DEFAULT NULL,
    `created_on` int(10) NULL DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ds_users_groups` (
    `user_id` int(10) unsigned NOT NULL,
    `group_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`user_id`,`group_id`),
    KEY `ds_user_groups-group_id` (`group_id`),
    CONSTRAINT `ds_user_groups-group_id` FOREIGN KEY (`group_id`) REFERENCES `ds_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ds_user_groups-user_id` FOREIGN KEY (`user_id`) REFERENCES `ds_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
