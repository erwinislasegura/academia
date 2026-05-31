<?php

final class Router
{
    private array $routes = [];

    public function get(string $uri, array|callable $action): void { $this->add('GET', $uri, $action); }
    public function post(string $uri, array|callable $action): void { $this->add('POST', $uri, $action); }
    private function add(string $method, string $uri, array|callable $action): void { $this->routes[$method][] = [$uri, $action]; }

    public function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/' && str_ends_with($uri, '/')) { $uri = rtrim($uri, '/'); }
        foreach ($this->routes[$method] ?? [] as [$route, $action]) {
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([0-9]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                array_shift($matches);
                if (is_callable($action)) { $action(...$matches); return; }
                [$controller, $methodName] = $action;
                (new $controller())->{$methodName}(...$matches);
                return;
            }
        }
        http_response_code(404);
        echo '<h1>404</h1><p>Ruta no encontrada.</p>';
    }
}
