<?php

final class DashboardController extends Controller
{
    public function index(): void
    {
        Middleware::permission('ver_dashboard');
        $userModel = new User();
        $admissionModel = new AdmissionApplication();
        $this->view('dashboard/index', [
            'title' => 'Inicio Academiapp',
            'activeUsers' => $userModel->activeCount(),
            'rolesCount' => (new Role())->count(),
            'permissionsCount' => (new Permission())->count(),
            'admissionsCount' => $admissionModel->count(),
            'admissionMetrics' => $admissionModel->dashboardMetrics(),
            'applicationsByCourse' => $admissionModel->countByCourse(),
            'applicationsByCourseAndGender' => $admissionModel->countByCourseAndGender(),
            'applicationsByGender' => $admissionModel->countByGender(),
            'applicationsByAgeRange' => $admissionModel->countByAgeRange(),
            'applicationsByStatusAndGender' => $admissionModel->countByStatusAndGender(),
            'applicationsByStatus' => $admissionModel->countByStatus(),
            'applicationsTrend' => $admissionModel->trendLastDays(),
            'latestApplications' => $admissionModel->latest(),
            'activity' => $userModel->recentActivity(),
        ]);
    }
}
