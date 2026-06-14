-- Production-safe migration for the admissions management update.
-- Adds the student demographic fields required by the admissions list/export.
-- This script is idempotent: it can be executed more than once without failing
-- when the columns or indexes already exist.

DROP PROCEDURE IF EXISTS ensure_admission_application_demographics;

DELIMITER //
CREATE PROCEDURE ensure_admission_application_demographics()
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'admission_applications'
      AND COLUMN_NAME = 'student_gender'
  ) THEN
    ALTER TABLE admission_applications
      ADD COLUMN student_gender ENUM('nino','nina') NULL AFTER student_name;
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'admission_applications'
      AND COLUMN_NAME = 'student_birthdate'
  ) THEN
    ALTER TABLE admission_applications
      ADD COLUMN student_birthdate DATE NULL AFTER student_gender;
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'admission_applications'
      AND INDEX_NAME = 'idx_admission_applications_gender'
  ) THEN
    ALTER TABLE admission_applications
      ADD INDEX idx_admission_applications_gender (student_gender);
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'admission_applications'
      AND INDEX_NAME = 'idx_admission_applications_birthdate'
  ) THEN
    ALTER TABLE admission_applications
      ADD INDEX idx_admission_applications_birthdate (student_birthdate);
  END IF;
END//
DELIMITER ;

CALL ensure_admission_application_demographics();

DROP PROCEDURE IF EXISTS ensure_admission_application_demographics;
