<?php

final class Permission extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM permissions ORDER BY module, name')->fetchAll();
    }

    public function grouped(): array
    {
        $grouped = [];
        foreach ($this->all() as $permission) {
            $grouped[$permission['module']][] = $permission;
        }
        return $grouped;
    }

    public function roleHasPermission(int $roleId, string $slug): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.slug = ?');
        $stmt->execute([$roleId, $slug]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM permissions')->fetchColumn();
    }
}
