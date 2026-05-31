<?php

final class Auth
{
    public static function check(): bool { return (bool) Session::get('user_id'); }

    public static function user(): ?array
    {
        if (!self::check()) { return null; }
        static $user = null;
        if ($user === null || ($user['id'] ?? null) !== Session::get('user_id')) {
            $user = (new User())->findWithRole((int) Session::get('user_id'));
        }
        return $user ?: null;
    }

    public static function role(): ?string { return self::user()['role_name'] ?? null; }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) { return false; }
        if (($user['role_slug'] ?? '') === 'super-administrador') { return true; }
        return (new Permission())->roleHasPermission((int) $user['role_id'], $permission);
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
    }

    public static function logout(): void { Session::destroy(); }
}
