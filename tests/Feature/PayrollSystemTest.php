<?php

namespace Tests\Feature;

use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_view_dashboard_and_payslip(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => '1234',
        ]);

        $response->assertRedirect('/dashboard');

        $dash = $this->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช');

        $batch = PayrollBatch::first();
        $this->assertNotNull($batch);

        $batchResp = $this->get(route('batches.show', $batch));
        $batchResp->assertStatus(200);

        $payslip = Payslip::first();
        $this->assertNotNull($payslip);

        $slipResp = $this->get(route('payslips.show', $payslip));
        $slipResp->assertStatus(200);
        $slipResp->assertSee('สลิปเงินเดือน/PaySlip');
        $slipResp->assertSee('นางดารารัตน์ เพ็ชรทวี');
    }

    public function test_upload_detects_duplicate_batch_and_handles_overwrite(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $this->assertEquals(9, PayrollBatch::count());

        $seedFilePath = storage_path('app/seed_data/civil_servants_2569_multisheet.xls');
        $this->assertFileExists($seedFilePath);

        // Copy to temp file so UploadedFile::move() does not delete the original seed file
        $copyPath = tempnam(sys_get_temp_dir(), 'test_pay_') . '.xls';
        copy($seedFilePath, $copyPath);

        // Upload the same file again
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $copyPath,
            'civil_servants_reupload.xls',
            'application/vnd.ms-excel',
            null,
            true
        );

        $response = $this->post(route('batches.upload'), [
            'payroll_file' => $uploadedFile,
            'employee_type' => 'ข้าราชการ',
            'month' => 1,
            'year_be' => 2569,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('duplicate_warning');
        $response->assertSessionHas('temp_file_path');

        $warning = session('duplicate_warning');
        $this->assertTrue($warning['has_duplicate_batches']);
        $this->assertNotEmpty($warning['sections']);

        $tempPath = session('temp_file_path');
        $tempFullPath = storage_path('app/' . $tempPath);
        $this->assertFileExists($tempFullPath);

        // Verify batch count is STILL 9 (nothing written to DB yet)
        $this->assertEquals(9, PayrollBatch::count());

        // Now test overwrite confirmation
        $overwriteResp = $this->post(route('batches.upload'), [
            'confirm_overwrite' => '1',
            'temp_file_path' => $tempPath,
            'employee_type' => 'ข้าราชการ',
            'month' => 1,
            'year_be' => 2569,
            'original_filename' => 'civil_servants_reupload.xls',
        ]);

        $overwriteResp->assertRedirect(route('dashboard'));
        $overwriteResp->assertSessionHas('success');

        // Batches count is 10 (9 overwritten + 1 new month 10 from the 10-sheet file)
        $this->assertEquals(10, PayrollBatch::count());
        // Verify exactly 1 batch per month exists (no duplicates created)
        for ($m = 1; $m <= 10; $m++) {
            $this->assertEquals(1, PayrollBatch::where('month', $m)->count());
        }

        // Temp file should be removed
        $this->assertFileDoesNotExist($tempFullPath);
    }
}
