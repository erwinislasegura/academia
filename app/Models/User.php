<?php

final class User extends Model
{
    public function all(array $filters = []): array
    {
        $sql = 'SELECT u.*, r.name role_name, r.slug role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE 1=1';
        $params = [];
        if (!empty($filters['q'])) {
            $sql .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $params[] = '%' . $filters['q'] . '%'; $params[] = '%' . $filters['q'] . '%';
        }
        if (($filters['status'] ?? '') !== '') { $sql .= ' AND u.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['role_id'])) { $sql .= ' AND u.role_id = ?'; $params[] = (int) $filters['role_id']; }
        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findWithRole(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.name role_name, r.slug role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.name role_name, r.slug role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = ?'; $params = [$email];
        if ($exceptId) { $sql .= ' AND id != ?'; $params[] = $exceptId; }
        $stmt = $this->db->prepare($sql); $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password, role_id, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$data['name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT), (int) $data['role_id'], $data['status']]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = [$data['name'], $data['email'], (int) $data['role_id'], $data['status']];
        $passwordSql = '';
        if (!empty($data['password'])) { $passwordSql = ', password = ?'; $params[] = password_hash($data['password'], PASSWORD_DEFAULT); }
        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, role_id = ?, status = ?{$passwordSql}, updated_at = NOW() WHERE id = ?");
        $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $user = $this->findWithRole($id);
        if (!$user || (int) Session::get('user_id') === $id) { return false; }
        if ($user['role_slug'] === 'super-administrador' && $this->superAdminsCount() <= 1) { return false; }
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function toggleStatus(int $id): bool
    {
        $user = $this->findWithRole($id);
        if (!$user || (int) Session::get('user_id') === $id) { return false; }
        if ($user['role_slug'] === 'super-administrador' && $user['status'] === 'active' && $this->superAdminsCount() <= 1) { return false; }
        $new = $user['status'] === 'active' ? 'inactive' : 'active';
        $stmt = $this->db->prepare('UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$new, $id]);
    }

    public function touchLogin(int $id): void
    {
        $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$id]);
    }

    public function activeCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    }

    public function superAdminsCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'super-administrador' AND u.status = 'active'")->fetchColumn();
    }

    public function log(?int $userId, string $action, string $description): void
    {
        $stmt = $this->db->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $action, $description, $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
    }

    public function recentActivity(int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT a.*, u.name user_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
