<?php

final class DashboardController extends Controller
{
    public function index(): void
    {
        Middleware::permission('ver_dashboard');
        $userModel = new User();
        $this->view('dashboard/index', [
            'title' => 'Panel principal',
            'activeUsers' => $userModel->activeCount(),
            'rolesCount' => (new Role())->count(),
            'permissionsCount' => (new Permission())->count(),
            'admissionsCount' => (new AdmissionApplication())->count(),
            'activity' => $userModel->recentActivity(),
        ]);
    }
}
