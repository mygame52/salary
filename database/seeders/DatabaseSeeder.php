<?php

namespace Database\Seeders;

use App\Models\PayrollBatch;
use App\Models\Setting;
use App\Models\User;
use App\Services\PayrollImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin user (username: admin, password: 1234)
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'เจ้าหน้าที่การเงิน (Admin)',
                'email' => 'admin@nfe-nakhon.go.th',
                'password' => Hash::make('1234'),
                'role' => 'admin',
                'employee_type' => 'ข้าราชการ',
                'position' => 'นักวิชาการเงินและบัญชี',
            ]
        );

        // 2. Default Settings matching the sample payslip & new logo
        Setting::setValue('agency_name', 'สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช');
        Setting::setValue('agency_tax_id', '0994000550537');
        Setting::setValue('logo_path', '/images/logo.png');
        Setting::setValue('signer_name', 'นางดารารัตน์ เพ็ชรทวี');
        Setting::setValue('signer_position', 'ตำแหน่ง นักวิชาการเงินและบัญชี');
        Setting::setValue('signer_title', 'เจ้าหน้าที่การเงิน');

        // 3. Import only Civil Servants (ข้าราชการ) 2569 (ม.ค. - ก.ย. 69) as requested
        $importer = app(PayrollImportService::class);
        $civilXls = storage_path('app/seed_data/civil_servants_2569_multisheet.xls');

        if (file_exists($civilXls)) {
            $importer->importFile(
                $civilXls,
                'ข้าราชการ',
                1,
                2569,
                null,
                null,
                'เงินเดือนข้าราชการ_2569_หลายSheet.xls',
                $admin->id
            );

            // Exclude October 2569 as requested by user ("ตุลาคม 69 น่าจะยังไม่สมบูรณ์")
            PayrollBatch::where('employee_type', 'ข้าราชการ')
                ->where('year_be', 2569)
                ->where('month', 10)
                ->delete();
        } elseif (file_exists(storage_path('app/seed_data/civil_servants_2569.csv'))) {
            $importer->importFile(
                storage_path('app/seed_data/civil_servants_2569.csv'),
                'ข้าราชการ',
                1,
                2569,
                null,
                null,
                'เงินเดือนข้าราชการ_2569.csv',
                $admin->id
            );

            PayrollBatch::where('employee_type', 'ข้าราชการ')
                ->where('year_be', 2569)
                ->where('month', 10)
                ->delete();
        }
    }
}
