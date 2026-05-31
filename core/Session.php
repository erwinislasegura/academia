<?php

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed { return $_SESSION[$key] ?? $default; }
    public static function put(string $key, mixed $value): void { $_SESSION[$key] = $value; }
    public static function forget(string $key): void { unset($_SESSION[$key]); }
    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) { $_SESSION['_flash'][$key] = $value; return null; }
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
    public static function regenerate(): void { session_regenerate_id(true); }
    public static function destroy(): void { $_SESSION = []; session_destroy(); }
}
