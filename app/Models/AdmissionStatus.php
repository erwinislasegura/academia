<?php

final class AdmissionStatus extends Model
{
    public function all(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';
        return $this->db->query(
            "SELECT s.*, COUNT(a.id) applications_count
             FROM admission_statuses s
             LEFT JOIN admission_applications a ON a.status_id = s.id
             {$where}
             GROUP BY s.id
             ORDER BY s.sort_order ASC, s.id ASC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admission_statuses WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admission_statuses (name, slug, color, description, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['color'] ?? '#071D7A',
            $data['description'] ?? null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE admission_statuses
             SET name = ?, slug = ?, color = ?, description = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['color'] ?? '#071D7A',
            $data['description'] ?? null,
            (int) ($data['sort_order'] ?? 0),
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        if ($this->applicationsCount($id) > 0) {
            return false;
        }

        $this->db->prepare('DELETE FROM admission_statuses WHERE id = ?')->execute([$id]);
        return true;
    }

    public function applicationsCount(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM admission_applications WHERE status_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
