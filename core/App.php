<?php

final class App
{
    private static string $root;
    private static array $config = [];
    private static string $basePath = '';

    public static function boot(string $root): void
    {
        self::$root = rtrim($root, '/');
        spl_autoload_register([self::class, 'autoload']);
        self::$config['app'] = require self::$root . '/config/app.php';
        date_default_timezone_set(self::$config['app']['timezone']);
        self::$basePath = self::detectBasePath();
        Session::start();

        $router = new Router();
        require self::$root . '/routes/web.php';
        $router->dispatch($_SERVER['REQUEST_METHOD'], self::requestPath());
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function url(string $path = ''): string
    {
        if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $path)) {
            return $path;
        }

        $path = '/' . ltrim($path, '/');
        return (self::$basePath ?: '') . ($path === '/' ? '/' : $path);
    }

    public static function asset(string $path): string
    {
        return self::url($path);
    }

    private static function requestPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (self::$basePath !== '' && str_starts_with($path, self::$basePath)) {
            $path = substr($path, strlen(self::$basePath)) ?: '/';
        }
        return '/' . ltrim($path, '/');
    }

    private static function detectBasePath(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');

        return $base === '/' || $base === '.' ? '' : $base;
    }

    public static function autoload(string $class): void
    {
        foreach (['/core/', '/app/Controllers/', '/app/Models/', '/app/Services/'] as $path) {
            $file = self::$root . $path . $class . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }

    public static function root(string $path = ''): string
    {
        return self::$root . ($path ? '/' . ltrim($path, '/') : '');
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
        if (!isset(self::$config[$file])) {
            $path = self::root("config/{$file}.php");
            self::$config[$file] = is_file($path) ? require $path : [];
        }
        return $item ? (self::$config[$file][$item] ?? $default) : self::$config[$file];
    }
}
