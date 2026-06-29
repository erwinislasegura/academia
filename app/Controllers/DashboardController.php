<?php

final class DashboardController extends Controller
{
    public function index(): void
    {
        Middleware::permission('ver_dashboard');
        $model = new DashboardModel();
        $this->view('dashboard/index', array_merge(['title' => 'Dashboard operativo'], $model->getDashboardData($_GET)));
    }

    public function filtrar(): void
    {
        $this->index();
    }

    public function obtenerDatosGraficosAjax(): void
    {
        Middleware::permission('ver_dashboard');
        $model = new DashboardModel();
        $data = $model->getDashboardData($_GET);
        $this->json([
            'kpis' => $data['kpis'],
            'cablesPorEstado' => $data['cablesPorEstado'],
            'informesPorEstado' => $data['informesPorEstado'],
            'fallasMasFrecuentes' => $data['fallasMasFrecuentes'],
            'causasMasFrecuentes' => $data['causasMasFrecuentes'],
            'materialesMasUsados' => $data['materialesMasUsados'],
        ]);
    }

    public function alertasAjax(): void
    {
        Middleware::permission('ver_dashboard');
        $this->json((new DashboardModel())->getAlertasOperativas($_GET));
    }

    public function kpisAjax(): void
    {
        Middleware::permission('ver_dashboard');
        $this->json((new DashboardModel())->getDashboardData($_GET)['kpis']);
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
