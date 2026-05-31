<?php

$router->get('/', function () {
    header('Location: ' . (Auth::check() ? '/dashboard' : '/login'));
    exit;
});
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users/store', [UserController::class, 'store']);
$router->get('/users/show/{id}', [UserController::class, 'show']);
$router->get('/users/edit/{id}', [UserController::class, 'edit']);
$router->post('/users/update/{id}', [UserController::class, 'update']);
$router->post('/users/delete/{id}', [UserController::class, 'delete']);
$router->post('/users/status/{id}', [UserController::class, 'status']);
$router->get('/roles', [RoleController::class, 'index']);
$router->get('/roles/create', [RoleController::class, 'create']);
$router->post('/roles/store', [RoleController::class, 'store']);
$router->get('/roles/edit/{id}', [RoleController::class, 'edit']);
$router->post('/roles/update/{id}', [RoleController::class, 'update']);
$router->post('/roles/delete/{id}', [RoleController::class, 'delete']);
