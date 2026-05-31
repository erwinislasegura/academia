<?php

final class AdmissionApplication extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admission_applications
            (guardian_first_names, guardian_last_names, guardian_email, guardian_phone, student_name, course, message, ip_address, user_agent)
            VALUES (:guardian_first_names, :guardian_last_names, :guardian_email, :guardian_phone, :student_name, :course, :message, :ip_address, :user_agent)'
        );
        $stmt->execute([
            'guardian_first_names' => $data['nombres_apoderado'],
            'guardian_last_names' => $data['apellidos_apoderado'],
            'guardian_email' => $data['email'],
            'guardian_phone' => $data['telefono'],
            'student_name' => $data['estudiante'],
            'course' => $data['curso'],
            'message' => $data['mensaje'] ?: null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id, guardian_first_names, guardian_last_names, guardian_email, guardian_phone, student_name, course, message, created_at
             FROM admission_applications
             ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM admission_applications')->fetchColumn();
    }
}
