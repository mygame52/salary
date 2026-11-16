<?php

namespace Tests\Feature;

use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_admin_can_edit_individual_employee_and_payslip(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $employee = User::where('role', '!=', 'admin')->first();
        $this->assertNotNull($employee);

        // 1. Edit Employee Profile
        $editEmpResp = $this->get(route('employees.edit', $employee));
        $editEmpResp->assertStatus(200);
        $editEmpResp->assertSee('แก้ไขข้อมูลรายบุคคล');
        $editEmpResp->assertSee($employee->name);

        $updateEmpResp = $this->put(route('employees.update', $employee), [
            'name' => $employee->name . ' (ทดสอบแก้ไข)',
            'citizen_id' => '1809900998877',
            'employee_type' => 'ข้าราชการ',
            'position' => 'ผู้อำนวยการเชี่ยวชาญ',
            'level_or_district' => 'สกร.ประจำจังหวัด',
            'bank_account' => '8001122334',
            'bank_name' => 'ธ.กรุงไทย',
        ]);

        $updateEmpResp->assertSessionHas('success');
        $employee->refresh();
        $this->assertEquals('1809900998877', $employee->citizen_id);
        $this->assertEquals('ผู้อำนวยการเชี่ยวชาญ', $employee->position);

        // 2. Edit Individual Payslip
        $payslip = $employee->payslips()->first();
        $this->assertNotNull($payslip);

        $editSlipResp = $this->get(route('payslips.edit', $payslip));
        $editSlipResp->assertStatus(200);
        $editSlipResp->assertSee('แก้ไขสลิปเงินเดือน');

        $oldBatchNet = (float) $payslip->batch->total_net_pay;
        $oldSlipNet = (float) $payslip->net_pay;

        $updateSlipResp = $this->put(route('payslips.update', $payslip), [
            'full_name' => $employee->name,
            'citizen_id' => '1809900998877',
            'position' => 'ผู้อำนวยการเชี่ยวชาญ',
            'level_or_district' => 'สกร.ประจำจังหวัด',
            'bank_account' => '8001122334',
            'bank_name' => 'ธ.กรุงไทย',
            'remark' => 'แก้ไขยอดเงินพิเศษ',
            'income_items' => [
                ['label' => 'เงินเดือน', 'amount' => '50000.00'],
                ['label' => 'เงินประจำตำแหน่ง', 'amount' => '10000.00'],
            ],
            'deduction_items' => [
                ['label' => 'ภาษีหัก ณ ที่จ่าย', 'amount' => '2000.00'],
                ['label' => 'กบข.', 'amount' => '1500.00'],
            ],
        ]);

        $updateSlipResp->assertRedirect(route('payslips.show', $payslip));
        $updateSlipResp->assertSessionHas('success');

        $payslip->refresh();
        $this->assertEquals(60000.00, (float) $payslip->total_income);
        $this->assertEquals(3500.00, (float) $payslip->total_deduction);
        $this->assertEquals(56500.00, (float) $payslip->net_pay);
        $this->assertEquals('แก้ไขยอดเงินพิเศษ', $payslip->remark);

        // Batch total net pay should be updated by the diff
        $payslip->batch->refresh();
        $expectedBatchNet = round($oldBatchNet - $oldSlipNet + 56500.00, 2);
        $this->assertEquals($expectedBatchNet, (float) $payslip->batch->total_net_pay);
    }

    public function test_notification_center_and_clean_menus(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_test',
            'role' => 'admin',
        ]);

        $empWithoutCid = User::factory()->create([
            'name' => 'นายสมชาย ไร้บัตร',
            'username' => 'somchai',
            'role' => 'employee',
            'citizen_id' => null,
            'bank_account' => null,
        ]);

        $empWithCid = User::factory()->create([
            'name' => 'นางสมศรี มีบัตร',
            'username' => 'somsri',
            'role' => 'employee',
            'citizen_id' => '1809900112233',
            'bank_account' => '1234567890',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('ศูนย์แจ้งเตือนระบบ');
        $response->assertSee('ยังไม่ระบุเลขบัตรประชาชน 13 หลัก');

        // Test filter missing citizen id
        $empFilterResp = $this->actingAs($admin)->get(route('employees.index', ['filter' => 'missing_citizen_id']));
        $empFilterResp->assertStatus(200);
        $empFilterResp->assertSee('นายสมชาย ไร้บัตร');
        $empFilterResp->assertDontSee('นางสมศรี มีบัตร');
    }

    public function test_personnel_categories_and_types(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_personnel',
            'role' => 'admin',
        ]);

        $civil = User::factory()->create([
            'name' => 'นายข้าราชการ ใจดี',
            'username' => 'civil_man',
            'role' => 'employee',
            'employee_type' => 'ข้าราชการ',
        ]);

        $perm = User::factory()->create([
            'name' => 'นายลูกจ้าง มั่นคง',
            'username' => 'perm_man',
            'role' => 'employee',
            'employee_type' => 'ลูกจ้างประจำ',
        ]);

        $gov = User::factory()->create([
            'name' => 'นางสาวพนักงาน ขยัน',
            'username' => 'gov_woman',
            'role' => 'employee',
            'employee_type' => 'พนักงานราชการ',
        ]);

        // 1. Visit index - sees ทะเบียนบุคลากร and all 3 categories
        $resp = $this->actingAs($admin)->get(route('employees.index'));
        $resp->assertStatus(200);
        $resp->assertSee('ทะเบียนบุคลากร');
        $resp->assertSee('ข้าราชการ');
        $resp->assertSee('ลูกจ้างประจำ');
        $resp->assertSee('พนักงานราชการ');
        $resp->assertSee('นายข้าราชการ ใจดี');
        $resp->assertSee('นายลูกจ้าง มั่นคง');
        $resp->assertSee('นางสาวพนักงาน ขยัน');

        // 2. Filter by ลูกจ้างประจำ
        $permFilterResp = $this->actingAs($admin)->get(route('employees.index', ['type' => 'ลูกจ้างประจำ']));
        $permFilterResp->assertStatus(200);
        $permFilterResp->assertSee('นายลูกจ้าง มั่นคง');
        $permFilterResp->assertDontSee('นายข้าราชการ ใจดี');
        $permFilterResp->assertDontSee('นางสาวพนักงาน ขยัน');

        // 3. Update employee type to ลูกจ้างประจำ
        $updateResp = $this->actingAs($admin)->put(route('employees.update', $civil), [
            'name' => $civil->name,
            'citizen_id' => '1809900223344',
            'employee_type' => 'ลูกจ้างประจำ',
            'position' => 'ช่างฝีมือ',
        ]);
        $updateResp->assertSessionHas('success');
        $civil->refresh();
        $this->assertEquals('ลูกจ้างประจำ', $civil->employee_type);
    }

    public function test_personnel_login_view_payslips_and_update_password(): void
    {
        $employee = User::factory()->create([
            'name' => 'นางสาวสมใจ นครศรี',
            'username' => 'somjai123',
            'citizen_id' => '1809900998877',
            'bank_account' => '9031234567',
            'role' => 'employee',
            'employee_type' => 'พนักงานราชการ',
            'position' => 'นักวิชาการศึกษา',
            'level_or_district' => 'สกร.อำเภอเมือง',
            'password' => bcrypt('1234'),
        ]);

        $batch = PayrollBatch::create([
            'employee_type' => 'พนักงานราชการ',
            'month' => 9,
            'year_be' => 2569,
            'month_name' => 'กันยายน',
            'period_text' => 'ประจำเดือน กันยายน 2569',
            'pay_date_text' => '25 ก.ย. 2569',
            'source_filename' => 'test.xls',
            'total_employees' => 1,
            'total_income' => 25000,
            'total_deduction' => 1500,
            'total_net_pay' => 23500,
            'is_published' => true,
        ]);

        $payslip = Payslip::create([
            'payroll_batch_id' => $batch->id,
            'user_id' => $employee->id,
            'seq_no' => 1,
            'full_name' => $employee->name,
            'employee_type' => 'พนักงานราชการ',
            'position' => 'นักวิชาการศึกษา',
            'level_or_district' => 'สกร.อำเภอเมือง',
            'citizen_id' => $employee->citizen_id,
            'bank_account' => $employee->bank_account,
            'total_income' => 25000,
            'total_deduction' => 1500,
            'net_pay' => 23500,
            'income_items' => [['name' => 'เงินเดือน', 'amount' => 25000]],
            'deduction_items' => [['name' => 'กบข./ประกันสังคม', 'amount' => 1500]],
        ]);

        // 1. Login using Bank Account
        $loginWithBank = $this->post(route('login.submit'), [
            'login' => '9031234567',
            'password' => '1234',
        ]);
        $loginWithBank->assertRedirect(route('my-payslips'));
        $this->assertAuthenticatedAs($employee);

        // 2. View my-payslips page
        $myPayslipsResp = $this->actingAs($employee)->get(route('my-payslips'));
        $myPayslipsResp->assertStatus(200);
        $myPayslipsResp->assertSee('นางสาวสมใจ นครศรี');
        $myPayslipsResp->assertSee('พนักงานราชการ');
        $myPayslipsResp->assertSee('กันยายน 2569');
        $myPayslipsResp->assertSee('23,500.00');

        // 3. Change password
        $pwResp = $this->actingAs($employee)->put(route('profile.password'), [
            'current_password' => '1234',
            'new_password' => 'newsecret5678',
            'new_password_confirmation' => 'newsecret5678',
        ]);
        $pwResp->assertSessionHas('success');

        // 4. Verify new password can be used to log in
        auth()->logout();
        $newLogin = $this->post(route('login.submit'), [
            'login' => '1809900998877', // login with citizen ID
            'password' => 'newsecret5678',
        ]);
        $newLogin->assertRedirect(route('my-payslips'));
        $this->assertAuthenticatedAs($employee);
    }

    public function test_personnel_status_and_missing_payroll_alerts(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_status',
            'role' => 'admin',
        ]);

        // Employee 1: Active in current month (Month 9)
        $activeEmp = User::factory()->create([
            'name' => 'นายสมเกียรติ ทำงานปกติ',
            'role' => 'employee',
            'employee_type' => 'ข้าราชการ',
            'status' => 'ปกติ',
        ]);

        // Employee 2: Left 5 months ago (Last paid in Month 4, status still 'ปกติ' -> triggers alert)
        $missingEmp = User::factory()->create([
            'name' => 'นายบุญส่ง ขาดเงินเดือน',
            'role' => 'employee',
            'employee_type' => 'ข้าราชการ',
            'status' => 'ปกติ',
        ]);

        // Employee 3: Retired already (status 'เกษียณ')
        $retiredEmp = User::factory()->create([
            'name' => 'นางเกษียณ สบายดี',
            'role' => 'employee',
            'employee_type' => 'ข้าราชการ',
            'status' => 'เกษียณ',
            'status_note' => 'เกษียณ 30 ก.ย. 68',
        ]);

        $batchSep = PayrollBatch::create([
            'employee_type' => 'ข้าราชการ',
            'month' => 9,
            'year_be' => 2569,
            'month_name' => 'กันยายน',
            'period_text' => 'ประจำเดือน กันยายน 2569',
            'pay_date_text' => '25 ก.ย. 2569',
            'source_filename' => 'test_sep.xls',
            'total_employees' => 1,
            'total_income' => 30000,
            'total_deduction' => 2000,
            'total_net_pay' => 28000,
            'is_published' => true,
        ]);

        $batchApr = PayrollBatch::create([
            'employee_type' => 'ข้าราชการ',
            'month' => 4,
            'year_be' => 2569,
            'month_name' => 'เมษายน',
            'period_text' => 'ประจำเดือน เมษายน 2569',
            'pay_date_text' => '25 เม.ย. 2569',
            'source_filename' => 'test_apr.xls',
            'total_employees' => 1,
            'total_income' => 25000,
            'total_deduction' => 1500,
            'total_net_pay' => 23500,
            'is_published' => true,
        ]);

        // Active employee gets payslip in Sep
        Payslip::create([
            'payroll_batch_id' => $batchSep->id,
            'user_id' => $activeEmp->id,
            'seq_no' => 1,
            'full_name' => $activeEmp->name,
            'employee_type' => 'ข้าราชการ',
            'total_income' => 30000,
            'total_deduction' => 2000,
            'net_pay' => 28000,
        ]);

        // Missing employee last received payslip in April
        Payslip::create([
            'payroll_batch_id' => $batchApr->id,
            'user_id' => $missingEmp->id,
            'seq_no' => 1,
            'full_name' => $missingEmp->name,
            'employee_type' => 'ข้าราชการ',
            'total_income' => 25000,
            'total_deduction' => 1500,
            'net_pay' => 23500,
        ]);

        // 1. Dashboard displays alert for missing employee
        $dash = $this->actingAs($admin)->get(route('dashboard'));
        $dash->assertStatus(200);
        $dash->assertSee('ขาดข้อมูลการเงินเกิน 1 เดือน');

        // 2. Personnel index shows filter for needs_status_review
        $indexResp = $this->actingAs($admin)->get(route('employees.index', ['filter' => 'needs_status_review']));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('นายบุญส่ง ขาดเงินเดือน');
        $indexResp->assertDontSee('นายสมเกียรติ ทำงานปกติ');

        // 3. Admin updates status of missing employee to 'ย้าย'
        $updateResp = $this->actingAs($admin)->put(route('employees.update', $missingEmp), [
            'name' => $missingEmp->name,
            'employee_type' => 'ข้าราชการ',
            'status' => 'ย้าย',
            'status_note' => 'ย้ายไป สกร.จังหวัดพัทลุง',
            'status_date' => '1 พ.ค. 2569',
        ]);
        $updateResp->assertSessionHas('success');
        $missingEmp->refresh();
        $this->assertEquals('ย้าย', $missingEmp->status);
        $this->assertEquals('ย้ายไป สกร.จังหวัดพัทลุง', $missingEmp->status_note);

        // 4. Now that missing employee is marked as 'ย้าย', they no longer trigger the review alert
        $indexRespAfter = $this->actingAs($admin)->get(route('employees.index', ['filter' => 'needs_status_review']));
        $indexRespAfter->assertStatus(200);
        $indexRespAfter->assertSee('ไม่พบรายชื่อบุคลากร');

        // 5. Test status filter 'เกษียณ'
        $retiredResp = $this->actingAs($admin)->get(route('employees.index', ['status' => 'เกษียณ']));
        $retiredResp->assertStatus(200);
        $retiredResp->assertSee('นางเกษียณ สบายดี');
        $retiredResp->assertDontSee('นายสมเกียรติ ทำงานปกติ');
    }

    public function test_backup_database_and_export_batch_csv(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_backup',
            'role' => 'admin',
        ]);

        $batch = PayrollBatch::create([
            'employee_type' => 'ข้าราชการ',
            'month' => 9,
            'year_be' => 2569,
            'month_name' => 'กันยายน',
            'period_text' => 'ประจำเดือน กันยายน 2569',
            'pay_date_text' => '25 ก.ย. 2569',
            'source_filename' => 'test.xls',
            'total_employees' => 1,
            'total_income' => 30000,
            'total_deduction' => 2000,
            'total_net_pay' => 28000,
            'is_published' => true,
        ]);

        $employee = User::factory()->create([
            'name' => 'นายสมควร ส่งออก',
            'role' => 'employee',
            'employee_type' => 'ข้าราชการ',
        ]);

        Payslip::create([
            'payroll_batch_id' => $batch->id,
            'user_id' => $employee->id,
            'seq_no' => 1,
            'full_name' => $employee->name,
            'employee_type' => 'ข้าราชการ',
            'position' => 'นักวิชาการเงิน',
            'level_or_district' => 'เมือง',
            'citizen_id' => '1809900112233',
            'bank_account' => '9031122334',
            'total_income' => 30000,
            'total_deduction' => 2000,
            'net_pay' => 28000,
        ]);

        // 1. Test CSV export of batch
        $csvResp = $this->actingAs($admin)->get(route('batches.export-csv', $batch));
        $csvResp->assertStatus(200);
        $csvResp->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('นายสมควร ส่งออก', $csvResp->streamedContent());
        $this->assertStringContainsString('28000.00', $csvResp->streamedContent());

        // 2. Test database backup download
        $backupResp = $this->actingAs($admin)->get(route('settings.backup'));
        $backupResp->assertStatus(200);
        $this->assertStringContainsString('salary_db_backup_', $backupResp->headers->get('content-disposition'));
    }

    public function test_annual_financial_report_and_export_csv(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_annual',
            'role' => 'admin',
        ]);

        $employee = User::factory()->create([
            'username' => 'emp_annual',
            'role' => 'employee',
        ]);

        // Regular employee cannot access annual report (403)
        $empResp = $this->actingAs($employee)->get(route('reports.annual'));
        $empResp->assertStatus(403);

        // Create batches for fiscal year 2569: Oct 2568 and Jan 2569
        $batchOct = PayrollBatch::create([
            'employee_type' => 'ข้าราชการ',
            'month' => 10,
            'year_be' => 2568,
            'month_name' => 'ตุลาคม',
            'period_text' => 'ประจำเดือน ตุลาคม 2568',
            'pay_date_text' => '25 ต.ค. 2568',
            'source_filename' => 'oct2568.xls',
            'total_employees' => 1,
            'total_income' => 50000,
            'total_deduction' => 5000,
            'total_net_pay' => 45000,
            'is_published' => true,
        ]);

        Payslip::create([
            'payroll_batch_id' => $batchOct->id,
            'user_id' => $admin->id,
            'seq_no' => 1,
            'full_name' => 'นายทดสอบ รายปี',
            'employee_type' => 'ข้าราชการ',
            'position' => 'ผู้อำนวยการ',
            'citizen_id' => '1809900998877',
            'bank_account' => '9031122339',
            'income_items' => [['label' => 'เงินเดือน', 'amount' => 50000]],
            'deduction_items' => [
                ['label' => 'กบข.', 'amount' => 3000],
                ['label' => 'สหกรณ์ออมทรัพย์ครู', 'amount' => 2000],
            ],
            'total_income' => 50000,
            'total_deduction' => 5000,
            'net_pay' => 45000,
        ]);

        $batchJan = PayrollBatch::create([
            'employee_type' => 'ข้าราชการ',
            'month' => 1,
            'year_be' => 2569,
            'month_name' => 'มกราคม',
            'period_text' => 'ประจำเดือน มกราคม 2569',
            'pay_date_text' => '25 ม.ค. 2569',
            'source_filename' => 'jan2569.xls',
            'total_employees' => 1,
            'total_income' => 50000,
            'total_deduction' => 5000,
            'total_net_pay' => 45000,
            'is_published' => true,
        ]);

        Payslip::create([
            'payroll_batch_id' => $batchJan->id,
            'user_id' => $admin->id,
            'seq_no' => 1,
            'full_name' => 'นายทดสอบ รายปี',
            'employee_type' => 'ข้าราชการ',
            'position' => 'ผู้อำนวยการ',
            'citizen_id' => '1809900998877',
            'bank_account' => '9031122339',
            'income_items' => [['label' => 'เงินเดือน', 'amount' => 50000]],
            'deduction_items' => [
                ['label' => 'กบข.', 'amount' => 3000],
                ['label' => 'สหกรณ์ออมทรัพย์ครู', 'amount' => 2000],
            ],
            'total_income' => 50000,
            'total_deduction' => 5000,
            'net_pay' => 45000,
        ]);

        // Admin fiscal view
        $fiscalResp = $this->actingAs($admin)->get(route('reports.annual', ['mode' => 'fiscal', 'year' => 2569]));
        $fiscalResp->assertStatus(200);
        $fiscalResp->assertSee('สรุปการเบิกจ่ายเงินเดือนและเงินหักสะสม');
        $fiscalResp->assertSee('ปีงบประมาณ พ.ศ. 2569');
        $fiscalResp->assertSee('monthlyComparisonChart');
        $fiscalResp->assertSee('cumulativeNetChart');
        $fiscalResp->assertSee('deductionBreakdownChart');
        $fiscalResp->assertSee('กบข.');

        // Admin calendar view
        $calendarResp = $this->actingAs($admin)->get(route('reports.annual', ['mode' => 'calendar', 'year' => 2569]));
        $calendarResp->assertStatus(200);
        $calendarResp->assertSee('รอบปีปฏิทิน พ.ศ. 2569');

        // CSV export
        $csvResp = $this->actingAs($admin)->get(route('reports.annual.export-csv', ['mode' => 'fiscal', 'year' => 2569]));
        $csvResp->assertStatus(200);
        $csvResp->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ยอดจ่ายสุทธิสะสม (บาท)', $csvResp->streamedContent());
        $this->assertStringContainsString('ตุลาคม 2568', $csvResp->streamedContent());
        $this->assertStringContainsString('มกราคม 2569', $csvResp->streamedContent());
    }

    public function test_user_management_finance_officer_role_and_activity_logs(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin_master',
            'name' => 'แอดมินหลัก',
            'role' => 'admin',
            'password' => Hash::make('1234'),
            'visible_password' => '1234',
        ]);

        $employee = User::factory()->create([
            'name' => 'นางสาวสมหญิง ใจดี',
            'role' => 'employee',
            'employee_type' => 'พนักงานราชการ',
            'bank_account' => '8019998887',
            'password' => Hash::make('1234'),
            'visible_password' => '1234',
        ]);

        // 1. Admin views User Management page
        $indexResp = $this->actingAs($admin)->get(route('users.index'));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('จัดการผู้ใช้งาน ดูรหัสผ่าน รีเซ็ต และกำหนดสิทธิ์');
        $indexResp->assertSee('นางสาวสมหญิง ใจดี');

        // 2. Admin creates a new Finance Officer (เจ้าหน้าที่การเงิน)
        $storeResp = $this->actingAs($admin)->post(route('users.store'), [
            'username' => 'finance01',
            'name' => 'นางการเงิน ชำนาญงาน',
            'role' => 'finance',
            'password' => 'fin5678',
            'position' => 'นักวิชาการเงินและบัญชี',
        ]);
        $storeResp->assertSessionHas('success');

        $financeUser = User::where('username', 'finance01')->first();
        $this->assertNotNull($financeUser);
        $this->assertEquals('finance', $financeUser->role);
        $this->assertEquals('fin5678', $financeUser->visible_password);
        $this->assertTrue($financeUser->isAdmin());

        // 3. Finance Officer can log in and access Admin features (dashboard, employees, users, reports, logs)
        auth()->logout();
        $loginResp = $this->post(route('login.submit'), [
            'login' => 'finance01',
            'password' => 'fin5678',
        ]);
        $loginResp->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($financeUser);
        $this->assertNotNull($financeUser->fresh()->last_login_at);

        $this->actingAs($financeUser)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($financeUser)->get(route('employees.index'))->assertStatus(200);
        $this->actingAs($financeUser)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($financeUser)->get(route('activity-logs.index'))->assertStatus(200);

        // 4. Finance Officer edits employee password and then resets it to 1234
        $updateResp = $this->actingAs($financeUser)->put(route('users.update', $employee), [
            'name' => 'นางสาวสมหญิง ใจดี',
            'role' => 'employee',
            'bank_account' => '8019998887',
            'new_password' => 'custom9999',
        ]);
        $updateResp->assertSessionHas('success');
        $this->assertEquals('custom9999', $employee->fresh()->visible_password);
        $this->assertTrue(Hash::check('custom9999', $employee->fresh()->password));

        $resetResp = $this->actingAs($financeUser)->post(route('users.reset-password', $employee));
        $resetResp->assertSessionHas('success');
        $this->assertEquals('1234', $employee->fresh()->visible_password);
        $this->assertTrue(Hash::check('1234', $employee->fresh()->password));

        // 5. Activity Logs page shows recorded actions
        $logsResp = $this->actingAs($financeUser)->get(route('activity-logs.index'));
        $logsResp->assertStatus(200);
        $logsResp->assertSee('นางการเงิน ชำนาญงาน');
        $logsResp->assertSee('เพิ่มผู้ใช้งาน');
        $logsResp->assertSee('รีเซ็ตรหัสผ่าน');
        $logsResp->assertSee('เข้าสู่ระบบ');
    }
}
