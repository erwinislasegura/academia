<?php

final class Role extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT r.*, COUNT(u.id) users_count FROM roles r LEFT JOIN users u ON u.role_id = r.id GROUP BY r.id ORDER BY r.id')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data, array $permissions): int
    {
        $stmt = $this->db->prepare('INSERT INTO roles (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? null]);
        $id = (int) $this->db->lastInsertId();
        $this->syncPermissions($id, $permissions);
        return $id;
    }

    public function update(int $id, array $data, array $permissions): void
    {
        $stmt = $this->db->prepare('UPDATE roles SET name = ?, slug = ?, description = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? null, $id]);
        $this->syncPermissions($id, $permissions);
    }

    public function delete(int $id): bool
    {
        if ($this->usersCount($id) > 0) { return false; }
        $this->db->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$id]);
        $this->db->prepare('DELETE FROM roles WHERE id = ? AND slug != ?')->execute([$id, 'super-administrador']);
        return true;
    }

    public function permissions(int $roleId): array
    {
        $stmt = $this->db->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
        $stmt->execute([$roleId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'permission_id'));
    }

    public function usersCount(int $roleId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role_id = ?');
        $stmt->execute([$roleId]);
        return (int) $stmt->fetchColumn();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM roles')->fetchColumn();
    }

    private function syncPermissions(int $roleId, array $permissions): void
    {
        $role = $this->find($roleId);
        if (($role['slug'] ?? '') === 'super-administrador' && empty($permissions)) { return; }
        $this->db->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$roleId]);
        $stmt = $this->db->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
        foreach (array_unique(array_map('intval', $permissions)) as $permissionId) {
            $stmt->execute([$roleId, $permissionId]);
        }
    }
}
