<?php

final class AdmissionCourse extends Model
{
    private const DEFAULT_COURSES = [
        ['name' => 'Kínder', 'slug' => 'kinder', 'sort_order' => 10],
        ['name' => '1º Básico', 'slug' => '1-basico', 'sort_order' => 20],
        ['name' => '2º Básico', 'slug' => '2-basico', 'sort_order' => 30],
        ['name' => '3º Básico', 'slug' => '3-basico', 'sort_order' => 40],
        ['name' => '4º Básico', 'slug' => '4-basico', 'sort_order' => 50],
        ['name' => '5º Básico', 'slug' => '5-basico', 'sort_order' => 60],
        ['name' => '6º Básico', 'slug' => '6-basico', 'sort_order' => 70],
        ['name' => '7º Básico', 'slug' => '7-basico', 'sort_order' => 80],
        ['name' => '8º Básico', 'slug' => '8-basico', 'sort_order' => 90],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function all(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE c.is_active = 1' : '';
        return $this->db->query(
            "SELECT c.*, COUNT(a.id) applications_count
             FROM admission_courses c
             LEFT JOIN admission_applications a ON a.course = c.name
             {$where}
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.id ASC"
        )->fetchAll();
    }

    public function activeOptions(): array
    {
        return $this->all(true);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admission_courses WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admission_courses WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admission_courses (name, slug, sort_order, is_active, is_new_slots, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['name'],
            $data['slug'],
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            !empty($data['is_new_slots']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $course = $this->find($id);
        if (!$course) {
            return;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE admission_courses
                 SET name = ?, slug = ?, sort_order = ?, is_active = ?, is_new_slots = ?, updated_at = NOW()
                 WHERE id = ?'
            );
            $stmt->execute([
                $data['name'],
                $data['slug'],
                (int) ($data['sort_order'] ?? 0),
                !empty($data['is_active']) ? 1 : 0,
                !empty($data['is_new_slots']) ? 1 : 0,
                $id,
            ]);

            if ((string) $course['name'] !== (string) $data['name']) {
                $stmt = $this->db->prepare('UPDATE admission_applications SET course = ? WHERE course = ?');
                $stmt->execute([$data['name'], $course['name']]);
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id): bool
    {
        if ($this->applicationsCount($id) > 0) {
            return false;
        }

        $this->db->prepare('DELETE FROM admission_courses WHERE id = ?')->execute([$id]);
        return true;
    }

    public function applicationsCount(int $id): int
    {
        $course = $this->find($id);
        if (!$course) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admission_applications WHERE course = ?');
        $stmt->execute([$course['name']]);
        return (int) $stmt->fetchColumn();
    }

    private function ensureSchema(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'admission_courses'");
        $tableExists = (bool) $stmt->fetchColumn();

        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS admission_courses (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                slug VARCHAR(120) NOT NULL UNIQUE,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                is_new_slots TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_admission_courses_active_order (is_active, sort_order),
                INDEX idx_admission_courses_new_slots (is_new_slots)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        if ($tableExists) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO admission_courses (name, slug, sort_order, is_active, is_new_slots, created_at, updated_at)
             VALUES (?, ?, ?, 1, 0, NOW(), NOW())'
        );
        foreach (self::DEFAULT_COURSES as $course) {
            $stmt->execute([$course['name'], $course['slug'], $course['sort_order']]);
        }
    }
}
