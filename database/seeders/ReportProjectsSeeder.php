<?php

namespace Database\Seeders;

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $endUsers = EndUser::query()->orderBy('id')->get();
        $pics = User::query()->where('role', 'user')->orderBy('id')->get();

        if ($endUsers->isEmpty() || $pics->isEmpty()) {
            throw new RuntimeException('Seed end user dan PIC terlebih dahulu sebelum membuat project demo laporan.');
        }

        $projectNames = [
            'Implementasi Dashboard Data',
            'Upgrade Infrastruktur Jaringan',
            'Pengembangan Portal Layanan',
            'Otomasi Workflow Operasional',
            'Modernisasi Sistem ERP',
            'Integrasi API dan Sistem',
        ];
        $firstMonth = now()->startOfMonth()->subMonths(11);

        DB::transaction(function () use ($endUsers, $pics, $projectNames, $firstMonth): void {
            foreach (range(0, 11) as $monthIndex) {
                $month = $firstMonth->copy()->addMonths($monthIndex);

                foreach (range(0, 2) as $projectIndex) {
                    $createdAt = $month->copy()->addDays([2, 7, 12][$projectIndex])->setTime(9 + $projectIndex, 0);
                    $projectValue = 50000000 + ($monthIndex * 7000000) + ($projectIndex * 18000000);
                    $costRatio = 0.58 + ((($monthIndex + $projectIndex) % 4) * 0.06);
                    $internalCost = (int) round($projectValue * $costRatio);
                    $margin = $projectValue - $internalCost;
                    $isCurrentMonth = $monthIndex === 11;
                    $isPreviousMonthOverdue = $monthIndex === 10 && $projectIndex === 2;

                    if ($isCurrentMonth) {
                        $status = ['planning', 'progress', 'planning'][$projectIndex];
                        $deadlineDate = now()->addDays(4 + ($projectIndex * 7))->toDateString();
                    } elseif ($isPreviousMonthOverdue) {
                        $status = 'progress';
                        $deadlineDate = today()->subDays(3)->toDateString();
                    } else {
                        $status = ($monthIndex % 5 === 0 && $projectIndex === 2) ? 'cancel' : 'done';
                        $deadlineDate = $month->copy()->addMonth()->addDays(10 + ($projectIndex * 5))->toDateString();
                    }

                    $monthCode = $month->format('Ym');
                    $sequence = str_pad((string) ($projectIndex + 1), 2, '0', STR_PAD_LEFT);
                    $poNumber = "DEMO-REPORT-{$monthCode}-{$sequence}";

                    Project::updateOrCreate(
                        ['po_number' => $poNumber],
                        [
                            'end_user_id' => $endUsers[($monthIndex + $projectIndex) % $endUsers->count()]->id,
                            'pic_id' => $pics[($monthIndex + $projectIndex) % $pics->count()]->id,
                            'project_name' => 'Demo - '.$projectNames[($monthIndex + $projectIndex) % count($projectNames)].' - '.$month->translatedFormat('M Y').' #'.$sequence,
                            'project_value' => $projectValue,
                            'internal_cost' => $internalCost,
                            'margin' => $margin,
                            'percentage' => round(($margin / $projectValue) * 100, 2),
                            'po_number' => $poNumber,
                            'quotation_number' => 'DEMO-QTN-'.$monthCode.'-'.$sequence,
                            'status' => $status,
                            'deadline_date' => $deadlineDate,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ],
                    );
                }
            }
        });
    }
}
