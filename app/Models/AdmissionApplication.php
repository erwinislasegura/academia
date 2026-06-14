<?php

final class AdmissionApplication extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admission_applications
            (guardian_first_names, guardian_last_names, guardian_email, guardian_phone, student_name, student_gender, student_birthdate, course, message, status_id, ip_address, user_agent)
            VALUES (:guardian_first_names, :guardian_last_names, :guardian_email, :guardian_phone, :student_name, :student_gender, :student_birthdate, :course, :message, :status_id, :ip_address, :user_agent)'
        );
        $stmt->execute([
            'guardian_first_names' => $data['nombres_apoderado'],
            'guardian_last_names' => $data['apellidos_apoderado'],
            'guardian_email' => $data['email'],
            'guardian_phone' => $data['telefono'],
            'student_name' => $data['estudiante'],
            'student_gender' => $data['sexo_estudiante'],
            'student_birthdate' => $data['fecha_nacimiento'],
            'course' => $data['curso'],
            'message' => $data['mensaje'] ?: null,
            'status_id' => $this->defaultStatusId(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }


    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, guardian_first_names, guardian_last_names, guardian_email, guardian_phone,
                    student_name, student_gender, student_birthdate, course, message, status_id, created_at
             FROM admission_applications
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT a.id, a.guardian_first_names, a.guardian_last_names, a.guardian_email, a.guardian_phone,
                    a.student_name, a.student_gender, a.student_birthdate,
                    TIMESTAMPDIFF(YEAR, a.student_birthdate, CURDATE()) AS student_age,
                    a.course, a.message, a.status_id, a.created_at,
                    s.name AS status_name, s.color AS status_color
             FROM admission_applications a
             LEFT JOIN admission_statuses s ON s.id = a.status_id
             ORDER BY a.created_at DESC, a.id DESC'
        )->fetchAll();
    }


    public function update(int $id, array $data): bool
    {
        if (!$this->exists($id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE admission_applications
             SET guardian_first_names = :guardian_first_names,
                 guardian_last_names = :guardian_last_names,
                 guardian_email = :guardian_email,
                 guardian_phone = :guardian_phone,
                 student_name = :student_name,
                 student_gender = :student_gender,
                 student_birthdate = :student_birthdate,
                 course = :course,
                 message = :message
             WHERE id = :id'
        );

        return $stmt->execute([
            'guardian_first_names' => $data['nombres_apoderado'],
            'guardian_last_names' => $data['apellidos_apoderado'],
            'guardian_email' => $data['email'],
            'guardian_phone' => $data['telefono'],
            'student_name' => $data['estudiante'],
            'student_gender' => $data['sexo_estudiante'],
            'student_birthdate' => $data['fecha_nacimiento'],
            'course' => $data['curso'],
            'message' => $data['mensaje'] ?: null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        if (!$this->exists($id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM admission_applications WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
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

    public function dashboardMetrics(): array
    {
        $total = $this->count();
        $newThisWeek = (int) $this->db->query(
            'SELECT COUNT(*) FROM admission_applications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();
        $contacted = (int) $this->db->query(
            "SELECT COUNT(*)
             FROM admission_applications a
             INNER JOIN admission_statuses s ON s.id = a.status_id
             WHERE s.slug IN ('contactada', 'aceptada')"
        )->fetchColumn();
        $accepted = (int) $this->db->query(
            "SELECT COUNT(*)
             FROM admission_applications a
             INNER JOIN admission_statuses s ON s.id = a.status_id
             WHERE s.slug = 'aceptada'"
        )->fetchColumn();

        $girls = (int) $this->db->query("SELECT COUNT(*) FROM admission_applications WHERE student_gender = 'nina'")->fetchColumn();
        $boys = (int) $this->db->query("SELECT COUNT(*) FROM admission_applications WHERE student_gender = 'nino'")->fetchColumn();
        $withoutGender = max(0, $total - $girls - $boys);
        $withoutBirthdate = (int) $this->db->query('SELECT COUNT(*) FROM admission_applications WHERE student_birthdate IS NULL')->fetchColumn();

        return [
            'total' => $total,
            'new_this_week' => $newThisWeek,
            'contact_rate' => $total > 0 ? round(($contacted / $total) * 100, 1) : 0,
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
            'girls' => $girls,
            'boys' => $boys,
            'without_gender' => $withoutGender,
            'with_gender' => $girls + $boys,
            'without_birthdate' => $withoutBirthdate,
        ];
    }

    public function countByCourse(): array
    {
        return $this->db->query(
            'SELECT course AS label, COUNT(*) AS total
             FROM admission_applications
             GROUP BY course
             ORDER BY total DESC, course ASC'
        )->fetchAll();
    }

    public function countByCourseAndGender(): array
    {
        return $this->db->query(
            "SELECT course AS label,
                    COUNT(*) AS total,
                    SUM(CASE WHEN student_gender = 'nina' THEN 1 ELSE 0 END) AS girls,
                    SUM(CASE WHEN student_gender = 'nino' THEN 1 ELSE 0 END) AS boys,
                    SUM(CASE WHEN student_gender IS NULL THEN 1 ELSE 0 END) AS without_gender
             FROM admission_applications
             GROUP BY course
             ORDER BY total DESC, course ASC"
        )->fetchAll();
    }

    public function countByGender(): array
    {
        return $this->db->query(
            "SELECT CASE
                        WHEN student_gender = 'nina' THEN 'Niñas'
                        WHEN student_gender = 'nino' THEN 'Niños'
                        ELSE 'Sin dato'
                    END AS label,
                    CASE
                        WHEN student_gender = 'nina' THEN '#E51B2B'
                        WHEN student_gender = 'nino' THEN '#071D7A'
                        ELSE '#F2B632'
                    END AS color,
                    COUNT(*) AS total
             FROM admission_applications
             GROUP BY label, color
             ORDER BY FIELD(label, 'Niñas', 'Niños', 'Sin dato')"
        )->fetchAll();
    }

    public function countByAgeRange(): array
    {
        return $this->db->query(
            "SELECT CASE
                        WHEN student_birthdate IS NULL THEN 'Sin fecha'
                        WHEN TIMESTAMPDIFF(YEAR, student_birthdate, CURDATE()) <= 5 THEN '≤ 5 años'
                        WHEN TIMESTAMPDIFF(YEAR, student_birthdate, CURDATE()) BETWEEN 6 AND 8 THEN '6-8 años'
                        WHEN TIMESTAMPDIFF(YEAR, student_birthdate, CURDATE()) BETWEEN 9 AND 11 THEN '9-11 años'
                        WHEN TIMESTAMPDIFF(YEAR, student_birthdate, CURDATE()) BETWEEN 12 AND 14 THEN '12-14 años'
                        ELSE '15+ años'
                    END AS label,
                    COUNT(*) AS total
             FROM admission_applications
             GROUP BY label
             ORDER BY FIELD(label, '≤ 5 años', '6-8 años', '9-11 años', '12-14 años', '15+ años', 'Sin fecha')"
        )->fetchAll();
    }

    public function countByStatusAndGender(): array
    {
        return $this->db->query(
            "SELECT COALESCE(s.name, 'Sin estado') AS label,
                    COALESCE(s.color, '#94A3B8') AS color,
                    COUNT(a.id) AS total,
                    SUM(CASE WHEN a.student_gender = 'nina' THEN 1 ELSE 0 END) AS girls,
                    SUM(CASE WHEN a.student_gender = 'nino' THEN 1 ELSE 0 END) AS boys,
                    SUM(CASE WHEN a.student_gender IS NULL THEN 1 ELSE 0 END) AS without_gender
             FROM admission_applications a
             LEFT JOIN admission_statuses s ON s.id = a.status_id
             GROUP BY label, color
             ORDER BY total DESC, label ASC"
        )->fetchAll();
    }

    public function countByStatus(): array
    {
        return $this->db->query(
            "SELECT COALESCE(s.name, 'Sin estado') AS label, COALESCE(s.color, '#94A3B8') AS color, COUNT(a.id) AS total
             FROM admission_applications a
             LEFT JOIN admission_statuses s ON s.id = a.status_id
             GROUP BY label, color
             ORDER BY total DESC, label ASC"
        )->fetchAll();
    }



    public function trendLastDays(int $days = 14): array
    {
        $days = max(7, min($days, 30));
        $intervalDays = $days - 1;

        return $this->db->query(
            'SELECT DATE(created_at) AS label, COUNT(*) AS total
             FROM admission_applications
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ' . $intervalDays . ' DAY)
             GROUP BY DATE(created_at)
             ORDER BY label ASC'
        )->fetchAll();
    }

    public function latest(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.student_name, a.student_gender, a.student_birthdate, TIMESTAMPDIFF(YEAR, a.student_birthdate, CURDATE()) AS student_age, a.course, a.created_at, s.name AS status_name, s.color AS status_color
             FROM admission_applications a
             LEFT JOIN admission_statuses s ON s.id = a.status_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function defaultStatusId(): ?int
    {
        $statusId = $this->db->query(
            "SELECT id FROM admission_statuses WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 1"
        )->fetchColumn();

        return $statusId ? (int) $statusId : null;
    }
}
