<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Services\ReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, ReportService $reportService): View
    {
        return view('admin.reports.index', $reportService->buildReportData($request->validated()));
    }

    public function export(ReportFilterRequest $request, ReportService $reportService): StreamedResponse
    {
        $projects = $reportService->exportProjects($request->validated());

        return response()->streamDownload(function () use ($projects): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Proyek', 'Pengguna Akhir', 'PIC', 'Status', 'Tenggat', 'Nilai Proyek', 'Biaya Internal', 'Margin', 'Margin (%)']);

            foreach ($projects as $project) {
                fputcsv($stream, [
                    $this->safeSpreadsheetCell($project->project_name),
                    $this->safeSpreadsheetCell($project->endUser?->nama),
                    $this->safeSpreadsheetCell($project->pic?->nama),
                    $this->statusLabel($project->status),
                    $project->deadline_date?->format('Y-m-d'),
                    $project->project_value,
                    $project->internal_cost,
                    $project->margin,
                    $project->percentage,
                ]);
            }

            fclose($stream);
        }, 'laporan-proyek-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeSpreadsheetCell(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+@\-\t\r]/', $value) === 1
            ? "'{$value}"
            : $value;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'planning' => 'Direncanakan',
            'progress' => 'Berjalan',
            'done' => 'Selesai',
            'cancel' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }
}
