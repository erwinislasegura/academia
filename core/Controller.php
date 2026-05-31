<?php

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = App::root('app/Views/' . $view . '.php');
        if (!is_file($viewFile)) { http_response_code(500); exit('Vista no encontrada'); }
        if ($layout === null) { require $viewFile; return; }
        $layoutFile = App::root('app/Views/' . $layout . '.php');
        require $layoutFile;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function input(): array
    {
        return array_map(static fn($v) => is_string($v) ? trim($v) : $v, $_POST);
    }
}
