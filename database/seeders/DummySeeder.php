<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EndUser;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder
{
    public function run(): void
    {


        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */


        $admin = User::create([

            'nama' => 'Administrator',

            'email' => 'admin@test.com',

            'password' => Hash::make('password'),

            'role' => 'admin',

            'is_active' => true,

        ]);



        $pic1 = User::create([

            'nama' => 'Budi PIC',

            'email' => 'budi@test.com',

            'password' => Hash::make('password'),

            'role' => 'user',

            'is_active' => true,

        ]);



        $pic2 = User::create([

            'nama' => 'Andi PIC',

            'email' => 'andi@test.com',

            'password' => Hash::make('password'),

            'role' => 'user',

            'is_active' => true,

        ]);





        /*
        |--------------------------------------------------------------------------
        | END USERS
        |--------------------------------------------------------------------------
        */


        $customer1 = EndUser::create([

            'nama' => 'PT Teknologi Nusantara',

            'industri' => 'Technology',

            'contact' => 'Rudi',

            'telepon' => '081234567890',

            'email' => 'contact@teknologi.com',

            'kota' => 'Jakarta',

            'npwp' => '01.234.567.8-999.000',

        ]);




        $customer2 = EndUser::create([

            'nama' => 'PT Maju Bersama',

            'industri' => 'Manufacturing',

            'contact' => 'Dewi',

            'telepon' => '081298765432',

            'email' => 'info@majubersama.com',

            'kota' => 'Bekasi',

            'npwp' => '02.345.678.9-888.000',

        ]);




        $customer3 = EndUser::create([

            'nama' => 'CV Digital Solution',

            'industri' => 'IT Service',

            'contact' => 'Fajar',

            'telepon' => '081277788899',

            'email' => 'hello@digitalsolution.com',

            'kota' => 'Bandung',

            'npwp' => '03.456.789.1-777.000',

        ]);






        /*
        |--------------------------------------------------------------------------
        | PROJECTS
        |--------------------------------------------------------------------------
        */


        Project::create([


            'end_user_id' => $customer1->id,

            'pic_id' => $pic1->id,


            'project_name'
                => 'Implementasi ERP System',


            'account'
                => 150000000,


            'po_number'
                => 'PO-001-2026',


            'quotation_number'
                => 'QTN-001-2026',


            'quotation_distribusi'
                => 90000000,


            'margin'
                => 60000000,


            'percentage'
                => 40,


            'deadline_date'
                => now()->addMonths(2),


            'status'
                => 'progress',

        ]);






        Project::create([


            'end_user_id' => $customer2->id,

            'pic_id' => $pic2->id,


            'project_name'
                => 'Website Corporate',


            'account'
                => 75000000,


            'po_number'
                => 'PO-002-2026',


            'quotation_number'
                => 'QTN-002-2026',


            'quotation_distribusi'
                => 45000000,


            'margin'
                => 30000000,


            'percentage'
                => 40,


            'deadline_date'
                => now()->addMonths(1),


            'status'
                => 'planning',

        ]);







        Project::create([


            'end_user_id' => $customer3->id,

            'pic_id' => $pic1->id,


            'project_name'
                => 'Maintenance Application',


            'account'
                => 50000000,


            'po_number'
                => 'PO-003-2026',


            'quotation_number'
                => 'QTN-003-2026',


            'quotation_distribusi'
                => 35000000,


            'margin'
                => 15000000,


            'percentage'
                => 30,


            'deadline_date'
                => now()->subDays(5),


            'status'
                => 'done',

        ]);

    }
}
