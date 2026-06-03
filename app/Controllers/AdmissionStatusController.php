<?php

final class AdmissionStatusController extends Controller
{
    public function index(): void
    {
        Middleware::permission('configurar_postulaciones');
        $this->view('admission_statuses/index', [
            'title' => 'Estados de postulación',
            'statuses' => (new AdmissionStatus())->all(),
        ]);
    }

    public function create(): void
    {
        Middleware::permission('configurar_postulaciones');
        $this->view('admission_statuses/create', [
            'title' => 'Crear estado',
            'status' => ['name' => '', 'slug' => '', 'color' => '#071D7A', 'description' => '', 'sort_order' => 0, 'is_active' => 1],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        Middleware::permission('configurar_postulaciones');
        $data = $this->input();
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $errors = $this->validate($data);

        if ($errors) {
            $this->view('admission_statuses/create', [
                'title' => 'Crear estado',
                'status' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $id = (new AdmissionStatus())->create($data);
        (new User())->log((int) Session::get('user_id'), 'admission_status_created', 'Creó el estado de postulación #' . $id . '.');
        Session::flash('success', 'Estado creado correctamente.');
        $this->redirect('/admission-statuses');
    }

    public function edit(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $status = (new AdmissionStatus())->find($id);
        if (!$status) {
            http_response_code(404);
            exit('Estado no encontrado');
        }

        $this->view('admission_statuses/edit', [
            'title' => 'Editar estado',
            'status' => $status,
            'errors' => [],
        ]);
    }

    public function update(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $model = new AdmissionStatus();
        $status = $model->find($id);
        if (!$status) {
            http_response_code(404);
            exit('Estado no encontrado');
        }

        $data = $this->input();
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $errors = $this->validate($data);

        if ($errors) {
            $this->view('admission_statuses/edit', [
                'title' => 'Editar estado',
                'status' => array_merge($status, $data),
                'errors' => $errors,
            ]);
            return;
        }

        $model->update($id, $data);
        (new User())->log((int) Session::get('user_id'), 'admission_status_updated', 'Actualizó el estado de postulación #' . $id . '.');
        Session::flash('success', 'Estado actualizado correctamente.');
        $this->redirect('/admission-statuses');
    }

    public function delete(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $ok = (new AdmissionStatus())->delete($id);
        (new User())->log((int) Session::get('user_id'), 'admission_status_deleted', 'Intentó eliminar el estado de postulación #' . $id . '.');
        Session::flash($ok ? 'success' : 'error', $ok ? 'Estado eliminado.' : 'No se puede eliminar un estado con postulaciones asociadas.');
        $this->redirect('/admission-statuses');
    }

    private function validate(array $data): array
    {
        $errors = Validator::required($data, ['name' => 'Nombre', 'slug' => 'Slug']);

        if (!empty($data['slug']) && !preg_match('/^[a-z0-9-]+$/', (string) $data['slug'])) {
            $errors['slug'] = 'Usa solo minúsculas, números y guiones.';
        }
        if (!empty($data['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $data['color'])) {
            $errors['color'] = 'Usa un color hexadecimal válido.';
        }

        return $errors;
    }
}
