<?php

final class UserController extends Controller
{
    public function index(): void
    {
        Middleware::permission('gestionar_usuarios');
        $filters = ['q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? '', 'role_id' => $_GET['role_id'] ?? ''];
        $this->view('users/index', ['title' => 'Gestión de usuarios', 'users' => (new User())->all($filters), 'roles' => (new Role())->all(), 'filters' => $filters]);
    }

    public function create(): void
    {
        Middleware::permission('crear_usuarios');
        $this->view('users/create', ['title' => 'Crear usuario', 'roles' => (new Role())->all(), 'errors' => []]);
    }

    public function store(): void
    {
        Middleware::permission('crear_usuarios');
        $data = $this->input();
        $errors = $this->validate($data, true);
        if ($errors) { $this->view('users/create', ['title' => 'Crear usuario', 'roles' => (new Role())->all(), 'errors' => $errors, 'old' => $data]); return; }
        $id = (new User())->create($data);
        (new User())->log((int) Session::get('user_id'), 'user_created', 'Creó el usuario #' . $id . ' (' . $data['email'] . ').');
        Session::flash('success', 'Usuario creado correctamente.');
        $this->redirect('/users');
    }

    public function show(int $id): void
    {
        Middleware::permission('gestionar_usuarios');
        $user = (new User())->findWithRole($id);
        if (!$user) { http_response_code(404); exit('Usuario no encontrado'); }
        $this->view('users/show', ['title' => 'Detalle de usuario', 'user' => $user]);
    }

    public function edit(int $id): void
    {
        Middleware::permission('editar_usuarios');
        $user = (new User())->find($id);
        if (!$user) { http_response_code(404); exit('Usuario no encontrado'); }
        $this->view('users/edit', ['title' => 'Editar usuario', 'user' => $user, 'roles' => (new Role())->all(), 'errors' => []]);
    }

    public function update(int $id): void
    {
        Middleware::permission('editar_usuarios');
        $data = $this->input();
        $errors = $this->validate($data, false, $id);
        if ($errors) { $this->view('users/edit', ['title' => 'Editar usuario', 'user' => array_merge((new User())->find($id) ?: [], $data), 'roles' => (new Role())->all(), 'errors' => $errors]); return; }
        (new User())->update($id, $data);
        (new User())->log((int) Session::get('user_id'), 'user_updated', 'Actualizó el usuario #' . $id . '.');
        Session::flash('success', 'Usuario actualizado correctamente.');
        $this->redirect('/users');
    }

    public function delete(int $id): void
    {
        Middleware::permission('eliminar_usuarios');
        $ok = (new User())->delete($id);
        (new User())->log((int) Session::get('user_id'), 'user_deleted', 'Intentó eliminar el usuario #' . $id . '.');
        Session::flash($ok ? 'success' : 'error', $ok ? 'Usuario eliminado.' : 'No se pudo eliminar el usuario por reglas de seguridad.');
        $this->redirect('/users');
    }

    public function status(int $id): void
    {
        Middleware::permission('editar_usuarios');
        $ok = (new User())->toggleStatus($id);
        (new User())->log((int) Session::get('user_id'), 'user_status', 'Cambió estado del usuario #' . $id . '.');
        Session::flash($ok ? 'success' : 'error', $ok ? 'Estado actualizado.' : 'No se pudo cambiar el estado.');
        $this->redirect('/users');
    }

    private function validate(array $data, bool $passwordRequired, ?int $id = null): array
    {
        $errors = Validator::required($data, ['name' => 'Nombre', 'email' => 'Email', 'role_id' => 'Rol', 'status' => 'Estado']);
        if (!empty($data['email']) && !Validator::email($data['email'])) { $errors['email'] = 'Email no válido.'; }
        if (!empty($data['email']) && (new User())->emailExists($data['email'], $id)) { $errors['email'] = 'El email ya se encuentra registrado.'; }
        if ($passwordRequired || !empty($data['password'])) {
            if (strlen($data['password'] ?? '') < 8) { $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.'; }
        }
        if (!in_array($data['status'] ?? '', ['active', 'inactive'], true)) { $errors['status'] = 'Estado inválido.'; }
        return $errors;
    }
}
