<?php

declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = $requestPath;
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base = rtrim(str_replace('\\', '/', dirname($script)), '/');
if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base)) ?: '/';
}

$imagePath = str_starts_with($path, '/images/') ? $path : $requestPath;
if (str_starts_with($imagePath, '/images/')) {
    $imageRoot = realpath(__DIR__ . '/images');
    $image = realpath(__DIR__ . $imagePath);
    if ($imageRoot !== false && $image !== false && str_starts_with($image, $imageRoot . DIRECTORY_SEPARATOR) && is_file($image)) {
        $types = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        readfile($image);
        exit;
    }
}

if (str_starts_with($path, '/assets/')) {
    $assetRoot = realpath(__DIR__ . '/public/assets');
    $asset = realpath(__DIR__ . '/public' . $path);
    if ($assetRoot !== false && $asset !== false && str_starts_with($asset, $assetRoot . DIRECTORY_SEPARATOR) && is_file($asset)) {
        $types = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        readfile($asset);
        exit;
    }
}

require_once __DIR__ . '/core/App.php';

App::boot(__DIR__);
