ALTER TABLE admission_applications
  ADD COLUMN student_gender ENUM('nino','nina') NULL AFTER student_name,
  ADD COLUMN student_birthdate DATE NULL AFTER student_gender,
  ADD INDEX idx_admission_applications_gender (student_gender),
  ADD INDEX idx_admission_applications_birthdate (student_birthdate);
