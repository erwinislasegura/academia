<?php

final class AdmissionApplication extends Model
{
    private bool $historyTableReady = false;

    public function create(array $data): int
    {
        $this->ensureStatusHistoryTable();
        $statusId = $this->defaultStatusId();
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
            'status_id' => $statusId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);

        $applicationId = (int) $this->db->lastInsertId();
        if ($statusId !== null) {
            $this->insertInitialStatusHistory($applicationId, $statusId);
        }

        return $applicationId;
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
        $this->ensureStatusHistoryTable();
        $applications = $this->db->query(
            'SELECT a.id, a.guardian_first_names, a.guardian_last_names, a.guardian_email, a.guardian_phone,
                    a.student_name, a.student_gender, a.student_birthdate,
                    TIMESTAMPDIFF(YEAR, a.student_birthdate, CURDATE()) AS student_age,
                    a.course, a.message, a.status_id, a.created_at,
                    s.name AS status_name, s.color AS status_color
             FROM admission_applications a
             LEFT JOIN admission_statuses s ON s.id = a.status_id
             ORDER BY a.created_at DESC, a.id DESC'
        )->fetchAll();

        foreach ($applications as &$application) {
            $application = array_merge($application, $this->statusTiming((int) $application['id'], (string) $application['created_at']));
        }
        unset($application);

        return $applications;
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

    public function updateStatus(int $id, ?int $statusId, ?int $changedBy = null): bool
    {
        if ($statusId !== null && !$this->statusExists($statusId)) {
            return false;
        }

        $application = $this->find($id);
        if (!$application) {
            return false;
        }

        $currentStatusId = $application['status_id'] !== null ? (int) $application['status_id'] : null;
        if ($currentStatusId === $statusId) {
            return true;
        }

        $this->ensureStatusHistoryTable();
        $this->db->beginTransaction();
        try {
            $this->backfillInitialStatusHistory($application);
            $lastChangedAt = $this->lastStatusChangeAt($id) ?? (string) $application['created_at'];
            $changedAt = date('Y-m-d H:i:s');
            $stageSeconds = max(0, strtotime($changedAt) - strtotime($lastChangedAt));
            $totalSeconds = max(0, strtotime($changedAt) - strtotime((string) $application['created_at']));

            $stmt = $this->db->prepare('UPDATE admission_applications SET status_id = ? WHERE id = ?');
            $stmt->execute([$statusId, $id]);

            $history = $this->db->prepare(
                'INSERT INTO admission_status_history
                 (application_id, from_status_id, to_status_id, changed_by, changed_at, duration_seconds, total_elapsed_seconds)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $history->execute([$id, $currentStatusId, $statusId, $changedBy, $changedAt, $stageSeconds, $totalSeconds]);
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[AdmissionApplication] No fue posible registrar el cambio de estado: ' . $exception->getMessage());
            return false;
        }
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

    public function averageTimeByStatus(): array
    {
        $this->ensureStatusHistoryTable();
        return $this->db->query(
            "SELECT s.id, s.name AS label, s.color,
                    COUNT(h.id) AS transitions,
                    ROUND(AVG(h.duration_seconds)) AS average_stage_seconds,
                    ROUND(AVG(h.total_elapsed_seconds)) AS average_total_seconds
             FROM admission_statuses s
             LEFT JOIN admission_status_history h
                    ON h.to_status_id = s.id
                   AND h.from_status_id IS NOT NULL
             WHERE s.is_active = 1
             GROUP BY s.id, s.name, s.color, s.sort_order
             ORDER BY s.sort_order ASC, s.id ASC"
        )->fetchAll();
    }

    public function acceptedApplicationTimelines(int $limit = 12): array
    {
        $this->ensureStatusHistoryTable();
        $limit = max(1, min($limit, 50));
        $stmt = $this->db->prepare(
            "SELECT a.id, a.student_name, a.course, a.created_at AS received_at,
                    accepted.accepted_at,
                    CASE
                        WHEN accepted.accepted_at IS NULL THEN NULL
                        ELSE TIMESTAMPDIFF(SECOND, a.created_at, accepted.accepted_at)
                    END AS elapsed_seconds
             FROM admission_applications a
             INNER JOIN admission_statuses current_status
                     ON current_status.id = a.status_id
                    AND current_status.slug = 'aceptada'
             LEFT JOIN (
                 SELECT h.application_id, MIN(h.changed_at) AS accepted_at
                 FROM admission_status_history h
                 INNER JOIN admission_statuses accepted_status
                         ON accepted_status.id = h.to_status_id
                        AND accepted_status.slug = 'aceptada'
                 WHERE h.from_status_id IS NOT NULL
                 GROUP BY h.application_id
             ) accepted ON accepted.application_id = a.id
             ORDER BY COALESCE(accepted.accepted_at, a.created_at) DESC, a.id DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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

    private function ensureStatusHistoryTable(): void
    {
        if ($this->historyTableReady) {
            return;
        }

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS admission_status_history (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                application_id BIGINT UNSIGNED NOT NULL,
                from_status_id BIGINT UNSIGNED NULL,
                to_status_id BIGINT UNSIGNED NULL,
                changed_by BIGINT UNSIGNED NULL,
                changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
                total_elapsed_seconds INT UNSIGNED NOT NULL DEFAULT 0,
                INDEX idx_admission_history_application (application_id, changed_at),
                INDEX idx_admission_history_transition (from_status_id, to_status_id),
                CONSTRAINT fk_admission_history_application FOREIGN KEY (application_id) REFERENCES admission_applications(id) ON DELETE CASCADE,
                CONSTRAINT fk_admission_history_from_status FOREIGN KEY (from_status_id) REFERENCES admission_statuses(id) ON DELETE SET NULL,
                CONSTRAINT fk_admission_history_to_status FOREIGN KEY (to_status_id) REFERENCES admission_statuses(id) ON DELETE SET NULL,
                CONSTRAINT fk_admission_history_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->historyTableReady = true;
    }

    private function insertInitialStatusHistory(int $applicationId, int $statusId, ?string $createdAt = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admission_status_history
             (application_id, from_status_id, to_status_id, changed_at, duration_seconds, total_elapsed_seconds)
             VALUES (?, NULL, ?, ?, 0, 0)'
        );
        $stmt->execute([$applicationId, $statusId, $createdAt ?? date('Y-m-d H:i:s')]);
    }

    private function backfillInitialStatusHistory(array $application): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admission_status_history WHERE application_id = ?');
        $stmt->execute([(int) $application['id']]);
        if ((int) $stmt->fetchColumn() === 0 && $application['status_id'] !== null) {
            $this->insertInitialStatusHistory(
                (int) $application['id'],
                (int) $application['status_id'],
                (string) $application['created_at']
            );
        }
    }

    private function lastStatusChangeAt(int $applicationId): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT changed_at FROM admission_status_history WHERE application_id = ? ORDER BY changed_at DESC, id DESC LIMIT 1'
        );
        $stmt->execute([$applicationId]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (string) $value;
    }

    private function statusTiming(int $applicationId, string $createdAt): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.changed_at, h.duration_seconds, h.total_elapsed_seconds,
                    previous_status.name AS previous_status_name
             FROM admission_status_history h
             LEFT JOIN admission_statuses previous_status ON previous_status.id = h.from_status_id
             WHERE h.application_id = ?
             ORDER BY h.changed_at DESC, h.id DESC
             LIMIT 1'
        );
        $stmt->execute([$applicationId]);
        $history = $stmt->fetch();

        $now = time();
        $createdTimestamp = strtotime($createdAt) ?: $now;
        $lastChangedTimestamp = $history && !empty($history['changed_at']) ? (strtotime((string) $history['changed_at']) ?: $createdTimestamp) : $createdTimestamp;

        return [
            'previous_status_name' => $history['previous_status_name'] ?? null,
            'last_transition_seconds' => $history && $history['previous_status_name'] !== null ? (int) $history['duration_seconds'] : null,
            'total_to_current_seconds' => $history && $history['previous_status_name'] !== null ? (int) $history['total_elapsed_seconds'] : null,
            'current_status_seconds' => max(0, $now - $lastChangedTimestamp),
            'total_elapsed_seconds' => max(0, $now - $createdTimestamp),
        ];
    }
}
