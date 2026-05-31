<?php

final class App
{
    private static string $root;
    private static array $config = [];

    public static function boot(string $root): void
    {
        self::$root = rtrim($root, '/');
        spl_autoload_register([self::class, 'autoload']);
        self::$config['app'] = require self::$root . '/config/app.php';
        date_default_timezone_set(self::$config['app']['timezone']);
        Session::start();

        $router = new Router();
        require self::$root . '/routes/web.php';
        $router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
    }

    public static function autoload(string $class): void
    {
        foreach (['/core/', '/app/Controllers/', '/app/Models/'] as $path) {
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
