<?php

final class RoleController extends Controller
{
    public function index(): void
    {
        Middleware::permission('gestionar_roles');
        $this->view('roles/index', ['title' => 'Roles y privilegios', 'roles' => (new Role())->all()]);
    }

    public function create(): void
    {
        Middleware::permission('gestionar_permisos');
        $this->view('roles/create', ['title' => 'Crear rol', 'permissions' => (new Permission())->grouped(), 'selected' => [], 'errors' => []]);
    }

    public function store(): void
    {
        Middleware::permission('gestionar_permisos');
        $data = $this->input(); $errors = $this->validate($data);
        if ($errors) { $this->view('roles/create', ['title' => 'Crear rol', 'permissions' => (new Permission())->grouped(), 'selected' => $_POST['permissions'] ?? [], 'errors' => $errors, 'old' => $data]); return; }
        $id = (new Role())->create($data, $_POST['permissions'] ?? []);
        (new User())->log((int) Session::get('user_id'), 'role_created', 'Creó el rol #' . $id . '.');
        Session::flash('success', 'Rol creado correctamente.');
        $this->redirect('/roles');
    }

    public function edit(int $id): void
    {
        Middleware::permission('gestionar_permisos');
        $role = (new Role())->find($id);
        if (!$role) { http_response_code(404); exit('Rol no encontrado'); }
        $this->view('roles/edit', ['title' => 'Editar rol', 'role' => $role, 'permissions' => (new Permission())->grouped(), 'selected' => (new Role())->permissions($id), 'errors' => []]);
    }

    public function update(int $id): void
    {
        Middleware::permission('gestionar_permisos');
        $role = (new Role())->find($id);
        if (!$role) { http_response_code(404); exit('Rol no encontrado'); }
        $data = $this->input(); $errors = $this->validate($data);
        if ($role['slug'] === 'super-administrador' && empty($_POST['permissions'])) { $errors['permissions'] = 'El Super Administrador debe conservar permisos de control.'; }
        if ($errors) { $this->view('roles/edit', ['title' => 'Editar rol', 'role' => array_merge($role, $data), 'permissions' => (new Permission())->grouped(), 'selected' => $_POST['permissions'] ?? [], 'errors' => $errors]); return; }
        (new Role())->update($id, $data, $_POST['permissions'] ?? []);
        (new User())->log((int) Session::get('user_id'), 'role_updated', 'Actualizó el rol #' . $id . '.');
        Session::flash('success', 'Rol actualizado correctamente.');
        $this->redirect('/roles');
    }

    public function delete(int $id): void
    {
        Middleware::permission('gestionar_permisos');
        $ok = (new Role())->delete($id);
        (new User())->log((int) Session::get('user_id'), 'role_deleted', 'Intentó eliminar el rol #' . $id . '.');
        Session::flash($ok ? 'success' : 'error', $ok ? 'Rol eliminado.' : 'No se puede eliminar un rol protegido o con usuarios asociados.');
        $this->redirect('/roles');
    }

    private function validate(array $data): array
    {
        $errors = Validator::required($data, ['name' => 'Nombre', 'slug' => 'Slug']);
        if (!empty($data['slug']) && !preg_match('/^[a-z0-9-]+$/', $data['slug'])) { $errors['slug'] = 'Usa solo minúsculas, números y guiones.'; }
        return $errors;
    }
}
