<?php

final class AdmissionApplication extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admission_applications
            (guardian_first_names, guardian_last_names, guardian_email, guardian_phone, student_name, course, message, status_id, ip_address, user_agent)
            VALUES (:guardian_first_names, :guardian_last_names, :guardian_email, :guardian_phone, :student_name, :course, :message, :status_id, :ip_address, :user_agent)'
        );
        $stmt->execute([
            'guardian_first_names' => $data['nombres_apoderado'],
            'guardian_last_names' => $data['apellidos_apoderado'],
            'guardian_email' => $data['email'],
            'guardian_phone' => $data['telefono'],
            'student_name' => $data['estudiante'],
            'course' => $data['curso'],
            'message' => $data['mensaje'] ?: null,
            'status_id' => $this->defaultStatusId(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT a.id, a.guardian_first_names, a.guardian_last_names, a.guardian_email, a.guardian_phone,
                    a.student_name, a.course, a.message, a.status_id, a.created_at,
                    s.name AS status_name, s.color AS status_color
             FROM admission_applications a
             LEFT JOIN admission_statuses s ON s.id = a.status_id
             ORDER BY a.created_at DESC, a.id DESC'
        )->fetchAll();
    }

    public function updateStatus(int $id, ?int $statusId): bool
    {
        if ($statusId !== null && !$this->statusExists($statusId)) {
            return false;
        }

        if (!$this->exists($id)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE admission_applications SET status_id = ? WHERE id = ?');
        $stmt->execute([$statusId, $id]);
        return true;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admission_applications WHERE id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function statusExists(int $statusId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admission_statuses WHERE id = ? AND is_active = 1');
        $stmt->execute([$statusId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM admission_applications')->fetchColumn();
    }

    private function defaultStatusId(): ?int
    {
        $statusId = $this->db->query(
            "SELECT id FROM admission_statuses WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 1"
        )->fetchColumn();

        return $statusId ? (int) $statusId : null;
    }
}
