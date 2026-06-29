<?php

final class Middleware
{
    public static function auth(): void
    {
        if (!Auth::user()) {
            Session::forget('user_id');
            Session::flash('error', 'Debes iniciar sesión para continuar.');
            header('Location: ' . App::url('/login'));
            exit;
        }
    }

    public static function guest(): void
    {
        if (Auth::user()) { header('Location: ' . App::url('/dashboard')); exit; }
    }

    public static function permission(string $permission): void
    {
        self::auth();
        if (!Auth::can($permission)) {
            http_response_code(403);
            exit('<h1>403</h1><p>No tienes permisos para acceder a esta sección.</p>');
        }
    }
}
