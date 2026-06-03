<?php

final class AuthController extends Controller
{
    public function login(): void
    {
        Middleware::guest();
        $this->view('auth/login', ['title' => 'Iniciar sesión'], null);
    }

    public function authenticate(): void
    {
        Middleware::guest();
        $data = $this->input();
        $userModel = new User();
        $user = $userModel->findByEmail($data['email'] ?? '');
        if (!$user || $user['status'] !== 'active' || !password_verify($data['password'] ?? '', $user['password'])) {
            Session::flash('error', 'Credenciales inválidas o usuario inactivo.');
            Session::flash('old_email', $data['email'] ?? '');
            $this->redirect('/login');
        }
        Auth::login($user);
        $userModel->touchLogin((int) $user['id']);
        $userModel->log((int) $user['id'], 'login', 'Inicio de sesión en Sistema Academiapp.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        if (Auth::check()) { (new User())->log((int) Session::get('user_id'), 'logout', 'Cierre de sesión.'); }
        Auth::logout();
        header('Location: ' . App::url('/login'));
        exit;
    }
}
