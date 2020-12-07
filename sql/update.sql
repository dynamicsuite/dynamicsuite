/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/* Setup */
DROP FUNCTION IF EXISTS ds_check_column_type;
DROP PROCEDURE IF EXISTS ds_convert_time_column_to_int;
DROP PROCEDURE IF EXISTS ds_update_8;
DELIMITER $$

/* Get the column type */
CREATE FUNCTION ds_check_column_type(
    `table` VARCHAR(255),
    `column` VARCHAR(255)
)
    RETURNS VARCHAR(255)
    RETURN (
        SELECT DATA_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_NAME = `table`
            AND table_schema = (SELECT DATABASE())
            AND COLUMN_NAME = `column`
    )
$$

/* Convert a time like column to INT */
CREATE PROCEDURE ds_convert_time_column_to_int(
    IN table_name_in VARCHAR(255),
    IN column_name_in VARCHAR(255),
    IN old_type VARCHAR(255)
)
BEGIN

    /* Get the name to use for the old column */
    SET @column_name_old = CONCAT(old_type, '_old');

    /* Move the old type column to a new temporary one */
    SET @copy_query = CONCAT(
        'ALTER TABLE ', table_name_in, ' CHANGE COLUMN ',
        column_name_in, ' ', @column_name_old, ' ', old_type, ' NULL DEFAULT NULL;'
    );

    /* Create the new INT column */
    SET @create_query = CONCAT(
        'ALTER TABLE ', table_name_in, ' ADD COLUMN IF NOT EXISTS ',
        column_name_in, ' INT(10) UNSIGNED NULL DEFAULT NULL AFTER ', @column_name_old, ';'
    );

    /* Update the new column with the old value as UNIX_TIMESTAMP */
    SET @update_query = CONCAT(
        'UPDATE ', table_name_in, ' SET ',
        column_name_in, '=IF(', @column_name_old, ',UNIX_TIMESTAMP(', @column_name_old, '), NULL);'
    );

    /* Delete the temporary column */
    SET @drop_query = CONCAT(
        'ALTER TABLE ', table_name_in, ' DROP COLUMN IF EXISTS ', @column_name_old, ';'
    );

    /* Execute temporary copy */
    IF ds_check_column_type(table_name_in, column_name_in) = old_type THEN
        PREPARE copy_stmt FROM @copy_query;
        EXECUTE copy_stmt;
        DEALLOCATE PREPARE copy_stmt;
    END IF;

    /* Setup new column */
    IF ds_check_column_type(table_name_in, @column_name_old) = old_type THEN
        PREPARE create_stmt FROM @create_query;
        EXECUTE create_stmt;
        DEALLOCATE PREPARE create_stmt;
    END IF;

    /* Copy and cleanup */
    IF
        ds_check_column_type(table_name_in, column_name_in) = 'int' AND
        ds_check_column_type(table_name_in, @column_name_old) = old_type
    THEN
        PREPARE update_stmt FROM @update_query;
        EXECUTE update_stmt;
        DEALLOCATE PREPARE update_stmt;
        PREPARE drop_stmt FROM @drop_query;
        EXECUTE drop_stmt;
        DEALLOCATE PREPARE drop_stmt;
    END IF;

END $$

/* Updates for DS 8 */
CREATE PROCEDURE ds_update_8()
BEGIN

    CALL ds_convert_time_column_to_int('ds_events', 'created_on', 'timestamp');
    CALL ds_convert_time_column_to_int('ds_groups', 'created_on', 'datetime');
    CALL ds_convert_time_column_to_int('ds_permissions', 'created_on', 'datetime');
    CALL ds_convert_time_column_to_int('ds_properties', 'created_on', 'datetime');
    CALL ds_convert_time_column_to_int('ds_users', 'inactive_on', 'datetime');
    CALL ds_convert_time_column_to_int('ds_users', 'login_last_attempt', 'datetime');
    CALL ds_convert_time_column_to_int('ds_users', 'login_last_success', 'datetime');
    CALL ds_convert_time_column_to_int('ds_users', 'created_on', 'datetime');

    ALTER TABLE ds_users
        ADD COLUMN IF NOT EXISTS password_expired TINYINT(1) unsigned DEFAULT NULL AFTER password;

END $$

/* Update to DS 8 */
CALL ds_update_8() $$

/* Cleanup */
DROP FUNCTION IF EXISTS ds_check_column_type $$
DROP PROCEDURE IF EXISTS ds_convert_time_column_to_int $$
DROP PROCEDURE IF EXISTS ds_update_8 $$

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;