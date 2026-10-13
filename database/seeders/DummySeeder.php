<?php

namespace Database\Seeders;

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'nama' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        $pic1 = User::updateOrCreate(
            ['email' => 'budi@test.com'],
            [
                'nama' => 'Budi PIC',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ],
        );

        $pic2 = User::updateOrCreate(
            ['email' => 'andi@test.com'],
            [
                'nama' => 'Andi PIC',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ],
        );

        $pic3 = User::updateOrCreate(
            ['email' => 'fayyiz.khairy@ntu.co.id'],
            [
                'nama' => 'Fayyiz Khairy',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ],
        );

        $customers = collect([
            [
                'email' => 'contact@teknologi.com',
                'nama' => 'PT Teknologi Nusantara',
                'industri' => 'Technology',
                'contact' => 'Rudi',
                'telepon' => '081234567890',
                'kota' => 'Jakarta',
                'npwp' => '01.234.567.8-999.000',
            ],
            [
                'email' => 'info@majubersama.com',
                'nama' => 'PT Maju Bersama',
                'industri' => 'Manufacturing',
                'contact' => 'Dewi',
                'telepon' => '081298765432',
                'kota' => 'Bekasi',
                'npwp' => '02.345.678.9-888.000',
            ],
            [
                'email' => 'hello@digitalsolution.com',
                'nama' => 'CV Digital Solution',
                'industri' => 'IT Service',
                'contact' => 'Fajar',
                'telepon' => '081277788899',
                'kota' => 'Bandung',
                'npwp' => '03.456.789.1-777.000',
            ],
        ])->mapWithKeys(function (array $customer): array {
            $email = $customer['email'];
            unset($customer['email']);

            return [$email => EndUser::updateOrCreate(['email' => $email], $customer)];
        });

        $projects = [
            [
                'po_number' => 'PO-001-2026',
                'end_user_id' => $customers['contact@teknologi.com']->id,
                'pic_id' => $pic1->id,
                'project_name' => 'Implementasi ERP System',
                'project_value' => 150000000,
                'quotation_number' => 'QTN-001-2026',
                'internal_cost' => 90000000,
                'margin' => 60000000,
                'percentage' => 40,
                'deadline_date' => now()->addMonths(2)->toDateString(),
                'status' => 'progress',
            ],
            [
                'po_number' => 'PO-002-2026',
                'end_user_id' => $customers['info@majubersama.com']->id,
                'pic_id' => $pic2->id,
                'project_name' => 'Website Corporate',
                'project_value' => 75000000,
                'quotation_number' => 'QTN-002-2026',
                'internal_cost' => 45000000,
                'margin' => 30000000,
                'percentage' => 40,
                'deadline_date' => now()->addMonth()->toDateString(),
                'status' => 'planning',
            ],
            [
                'po_number' => 'PO-003-2026',
                'end_user_id' => $customers['hello@digitalsolution.com']->id,
                'pic_id' => $pic1->id,
                'project_name' => 'Maintenance Application',
                'project_value' => 50000000,
                'quotation_number' => 'QTN-003-2026',
                'internal_cost' => 35000000,
                'margin' => 15000000,
                'percentage' => 30,
                'deadline_date' => now()->subDays(5)->toDateString(),
                'status' => 'done',
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::updateOrCreate(
                ['po_number' => $projectData['po_number']],
                $projectData,
            );

        }
    }
}
