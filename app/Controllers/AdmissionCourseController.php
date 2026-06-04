<?php

final class AdmissionCourseController extends Controller
{
    public function index(): void
    {
        Middleware::permission('configurar_postulaciones');
        $this->view('admission_courses/index', [
            'title' => 'Cursos de admisión',
            'courses' => (new AdmissionCourse())->all(),
        ]);
    }

    public function create(): void
    {
        Middleware::permission('configurar_postulaciones');
        $this->view('admission_courses/create', [
            'title' => 'Crear curso',
            'course' => ['name' => '', 'slug' => '', 'sort_order' => 0, 'is_active' => 1, 'is_new_slots' => 0],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        Middleware::permission('configurar_postulaciones');
        $data = $this->input();
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $data['is_new_slots'] = isset($_POST['is_new_slots']) ? 1 : 0;
        $errors = $this->validate($data);

        if ($errors) {
            $this->view('admission_courses/create', [
                'title' => 'Crear curso',
                'course' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $id = (new AdmissionCourse())->create($data);
        } catch (PDOException $exception) {
            $this->view('admission_courses/create', [
                'title' => 'Crear curso',
                'course' => $data,
                'errors' => ['slug' => 'Ya existe un curso con ese slug.'],
            ]);
            return;
        }

        (new User())->log((int) Session::get('user_id'), 'admission_course_created', 'Creó el curso de admisión #' . $id . '.');
        Session::flash('success', 'Curso creado correctamente. Si está activo, aparecerá de inmediato en el formulario público.');
        $this->redirect('/admission-courses');
    }

    public function edit(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $course = (new AdmissionCourse())->find($id);
        if (!$course) {
            http_response_code(404);
            exit('Curso no encontrado');
        }

        $this->view('admission_courses/edit', [
            'title' => 'Editar curso',
            'course' => $course,
            'errors' => [],
        ]);
    }

    public function update(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $model = new AdmissionCourse();
        $course = $model->find($id);
        if (!$course) {
            http_response_code(404);
            exit('Curso no encontrado');
        }

        $data = $this->input();
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $data['is_new_slots'] = isset($_POST['is_new_slots']) ? 1 : 0;
        $errors = $this->validate($data);

        if ($errors) {
            $this->view('admission_courses/edit', [
                'title' => 'Editar curso',
                'course' => array_merge($course, $data),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $model->update($id, $data);
        } catch (PDOException $exception) {
            $this->view('admission_courses/edit', [
                'title' => 'Editar curso',
                'course' => array_merge($course, $data),
                'errors' => ['slug' => 'Ya existe un curso con ese slug.'],
            ]);
            return;
        }

        (new User())->log((int) Session::get('user_id'), 'admission_course_updated', 'Actualizó el curso de admisión #' . $id . '.');
        Session::flash('success', 'Curso actualizado correctamente.');
        $this->redirect('/admission-courses');
    }

    public function delete(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $ok = (new AdmissionCourse())->delete($id);
        (new User())->log((int) Session::get('user_id'), 'admission_course_deleted', 'Intentó eliminar el curso de admisión #' . $id . '.');
        Session::flash($ok ? 'success' : 'error', $ok ? 'Curso eliminado.' : 'No se puede eliminar un curso con postulaciones asociadas. Puedes deshabilitarlo para ocultarlo del formulario.');
        $this->redirect('/admission-courses');
    }

    private function validate(array $data): array
    {
        $errors = Validator::required($data, ['name' => 'Nombre', 'slug' => 'Slug']);

        if (!empty($data['slug']) && !preg_match('/^[a-z0-9-]+$/', (string) $data['slug'])) {
            $errors['slug'] = 'Usa solo minúsculas, números y guiones.';
        }

        return $errors;
    }
}
