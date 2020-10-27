/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP FUNCTION IF EXISTS ds_check_column_type;
DROP PROCEDURE IF EXISTS ds_update_8;
DELIMITER $$

CREATE FUNCTION
    ds_check_column_type(`table` VARCHAR(255), `column` VARCHAR(255))
    RETURNS VARCHAR(255)
    RETURN (
        SELECT DATA_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_NAME = `table`
          AND table_schema = (SELECT DATABASE())
          AND COLUMN_NAME = `column`
    ) $$

CREATE PROCEDURE
    ds_update_8()
BEGIN

    /* Events */
    /* -------------------------------------------------------------------------------------------------------------- */
    IF ds_check_column_type('ds_events', 'created_on') = 'timestamp' THEN
        ALTER TABLE ds_events
            CHANGE COLUMN created_on created_on_old TIMESTAMP NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_events', 'created_on_old') = 'timestamp' THEN
        ALTER TABLE ds_events
            ADD COLUMN IF NOT EXISTS created_on INT(10) UNSIGNED NULL DEFAULT NULL AFTER created_on_old;
    END IF;
    IF
        ds_check_column_type('ds_events', 'created_on') = 'int' AND
        ds_check_column_type('ds_events', 'created_on_old') = 'timestamp'
    THEN
        UPDATE
            ds_events
        SET
            created_on = IF(created_on_old, UNIX_TIMESTAMP(created_on_old), NULL);
        ALTER TABLE ds_events
            DROP COLUMN IF EXISTS created_on_old;
    END IF;

    /* Groups */
    /* -------------------------------------------------------------------------------------------------------------- */
    IF ds_check_column_type('ds_groups', 'created_on') = 'datetime' THEN
        ALTER TABLE ds_groups
            CHANGE COLUMN created_on created_on_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_groups', 'created_on_old') = 'datetime' THEN
        ALTER TABLE ds_groups
            ADD COLUMN IF NOT EXISTS created_on INT(10) UNSIGNED NULL DEFAULT NULL AFTER created_on_old;
    END IF;
    IF
        ds_check_column_type('ds_groups', 'created_on') = 'int' AND
        ds_check_column_type('ds_groups', 'created_on_old') = 'datetime'
    THEN
        UPDATE
            ds_groups
        SET
            created_on = IF(created_on_old, UNIX_TIMESTAMP(created_on_old), NULL);
        ALTER TABLE ds_groups
            DROP COLUMN IF EXISTS created_on_old;
    END IF;

    /* Permissions */
    /* -------------------------------------------------------------------------------------------------------------- */
    IF ds_check_column_type('ds_permissions', 'created_on') = 'datetime' THEN
        ALTER TABLE ds_permissions
            CHANGE COLUMN created_on created_on_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_permissions', 'created_on_old') = 'datetime' THEN
        ALTER TABLE ds_permissions
            ADD COLUMN IF NOT EXISTS created_on INT(10) UNSIGNED NULL DEFAULT NULL AFTER created_on_old;
    END IF;
    IF
        ds_check_column_type('ds_permissions', 'created_on') = 'int' AND
        ds_check_column_type('ds_permissions', 'created_on_old') = 'datetime'
    THEN
        UPDATE
            ds_permissions
        SET
            created_on = IF(created_on_old, UNIX_TIMESTAMP(created_on_old), NULL);
        ALTER TABLE ds_permissions
            DROP COLUMN IF EXISTS created_on_old;
    END IF;

    /* Properties */
    /* -------------------------------------------------------------------------------------------------------------- */
    IF ds_check_column_type('ds_properties', 'created_on') = 'datetime' THEN
        ALTER TABLE ds_properties
            CHANGE COLUMN created_on created_on_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_properties', 'created_on_old') = 'datetime' THEN
        ALTER TABLE ds_properties
            ADD COLUMN IF NOT EXISTS created_on INT(10) UNSIGNED NULL DEFAULT NULL AFTER created_on_old;
    END IF;
    IF
        ds_check_column_type('ds_properties', 'created_on') = 'int' AND
        ds_check_column_type('ds_properties', 'created_on_old') = 'datetime'
    THEN
        UPDATE
            ds_properties
        SET
            created_on = IF(created_on_old, UNIX_TIMESTAMP(created_on_old), NULL);
        ALTER TABLE ds_properties
            DROP COLUMN IF EXISTS created_on_old;
    END IF;

    /* Users */
    /* -------------------------------------------------------------------------------------------------------------- */
    ALTER TABLE ds_users
        ADD COLUMN IF NOT EXISTS password_expired TINYINT(1) DEFAULT NULL AFTER password,
        ADD COLUMN IF NOT EXISTS root TINYINT(1) DEFAULT NULL AFTER password_expired,
        ADD COLUMN IF NOT EXISTS inactive_by VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER inactive;

    /* Inactive on */
    IF ds_check_column_type('ds_users', 'inactive_on') = 'datetime' THEN
        ALTER TABLE ds_users
            CHANGE COLUMN inactive_on inactive_on_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_users', 'inactive_on_old') = 'datetime' THEN
        ALTER TABLE ds_users
            ADD COLUMN IF NOT EXISTS inactive_on INT(10) UNSIGNED NULL DEFAULT NULL AFTER inactive_on_old;
    END IF;
    IF
        ds_check_column_type('ds_users', 'inactive_on') = 'int' AND
        ds_check_column_type('ds_users', 'inactive_on_old') = 'datetime'
    THEN
        UPDATE
            ds_users
        SET
            inactive_on = IF(inactive_on_old, UNIX_TIMESTAMP(inactive_on_old), NULL);
        ALTER TABLE ds_users
            DROP COLUMN IF EXISTS inactive_on_old;
    END IF;

    /* Login last attempt */
    IF ds_check_column_type('ds_users', 'login_last_attempt') = 'datetime' THEN
        ALTER TABLE ds_users
            CHANGE COLUMN login_last_attempt login_last_attempt_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_users', 'login_last_attempt_old') = 'datetime' THEN
        ALTER TABLE ds_users
            ADD COLUMN IF NOT EXISTS login_last_attempt INT(10) UNSIGNED NULL DEFAULT NULL AFTER login_last_attempt_old;
    END IF;
    IF
        ds_check_column_type('ds_users', 'login_last_attempt') = 'int' AND
        ds_check_column_type('ds_users', 'login_last_attempt_old') = 'datetime'
    THEN
        UPDATE
            ds_users
        SET
            login_last_attempt = IF(login_last_attempt_old, UNIX_TIMESTAMP(login_last_attempt_old), NULL);
        ALTER TABLE ds_users
            DROP COLUMN IF EXISTS login_last_attempt_old;
    END IF;

    /* Login last success */
    IF ds_check_column_type('ds_users', 'login_last_success') = 'datetime' THEN
        ALTER TABLE ds_users
            CHANGE COLUMN login_last_success login_last_success_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_users', 'login_last_success_old') = 'datetime' THEN
        ALTER TABLE ds_users
            ADD COLUMN IF NOT EXISTS login_last_success INT(10) UNSIGNED NULL DEFAULT NULL AFTER login_last_success_old;
    END IF;
    IF
        ds_check_column_type('ds_users', 'login_last_success') = 'int' AND
        ds_check_column_type('ds_users', 'login_last_success_old') = 'datetime'
    THEN
        UPDATE
            ds_users
        SET
            login_last_success = IF(login_last_success_old, UNIX_TIMESTAMP(login_last_success_old), NULL);
        ALTER TABLE ds_users
            DROP COLUMN IF EXISTS login_last_success_old;
    END IF;

    /* Created on */
    IF ds_check_column_type('ds_users', 'created_on') = 'datetime' THEN
        ALTER TABLE ds_users
            CHANGE COLUMN created_on created_on_old DATETIME NULL DEFAULT NULL;
    END IF;
    IF ds_check_column_type('ds_users', 'created_on_old') = 'datetime' THEN
        ALTER TABLE ds_users
            ADD COLUMN IF NOT EXISTS created_on INT(10) UNSIGNED NULL DEFAULT NULL AFTER created_on_old;
    END IF;
    IF
        ds_check_column_type('ds_users', 'created_on') = 'int' AND
        ds_check_column_type('ds_users', 'created_on_old') = 'datetime'
    THEN
        UPDATE
            ds_users
        SET
            created_on = IF(created_on_old, UNIX_TIMESTAMP(created_on_old), NULL);
        ALTER TABLE ds_users
            DROP COLUMN IF EXISTS created_on_old;
    END IF;

END $$

/* Update to DS 8 */
CALL ds_update_8() $$

DROP FUNCTION IF EXISTS ds_check_column_type $$
DROP PROCEDURE IF EXISTS ds_update_8 $$

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;