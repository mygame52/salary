<?php

namespace App\Http\Controllers;

use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\Setting;
use App\Models\User;
use App\Services\PayrollImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function dashboard(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('my-payslips');
        }

        $filterType = $request->query('type');
        $filterYear = $request->query('year');
        $search = trim((string) $request->query('q', ''));

        $batchesQuery = PayrollBatch::query()
            ->when($filterType, fn ($q) => $q->where('employee_type', $filterType))
            ->when($filterYear, fn ($q) => $q->where('year_be', $filterYear))
            ->orderByDesc('year_be')
            ->orderByDesc('month')
            ->orderBy('employee_type');

        $batches = $batchesQuery->get();

        // Quick payslip search across all batches if search term entered
        $searchResults = collect();
        if ($search !== '') {
            $searchResults = Payslip::with('batch', 'user')
                ->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('citizen_id', 'like', "%{$search}%")
                        ->orWhere('bank_account', 'like', "%{$search}%")
                        ->orWhere('level_or_district', 'like', "%{$search}%");
                })
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        }

        $stats = [
            'total_batches' => PayrollBatch::count(),
            'total_payslips' => Payslip::count(),
            'civil_servants' => User::where('role', '!=', 'admin')->where('employee_type', 'ข้าราชการ')->count(),
            'gov_employees' => User::where('role', '!=', 'admin')->where('employee_type', 'พนักงานราชการ')->count(),
        ];

        $thaiMonths = PayrollBatch::thaiMonths();
        $years = PayrollBatch::select('year_be')->distinct()->orderByDesc('year_be')->pluck('year_be');

        return view('payroll.dashboard', compact(
            'batches',
            'stats',
            'thaiMonths',
            'years',
            'filterType',
            'filterYear',
            'search',
            'searchResults'
        ));
    }

    public function upload(Request $request, PayrollImportService $importer)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $isConfirmOverwrite = $request->boolean('confirm_overwrite');

        if ($isConfirmOverwrite) {
            $data = $request->validate([
                'temp_file_path' => ['required', 'string'],
                'employee_type' => ['required', 'in:พนักงานราชการ,ข้าราชการ'],
                'month' => ['required', 'integer', 'between:1,12'],
                'year_be' => ['required', 'integer', 'between:2550,2600'],
                'period_text' => ['nullable', 'string', 'max:150'],
                'pay_date_text' => ['nullable', 'string', 'max:150'],
                'original_filename' => ['nullable', 'string', 'max:255'],
            ]);

            $tempFullPath = storage_path('app/' . $data['temp_file_path']);
            if (!file_exists($tempFullPath)) {
                return redirect()->route('dashboard')->withErrors([
                    'payroll_file' => 'ไฟล์ชั่วคราวหมดอายุ กรุณาเลือกไฟล์และอัปโหลดใหม่อีกครั้ง',
                ]);
            }

            $batches = $importer->importFile(
                $tempFullPath,
                $data['employee_type'],
                (int) $data['month'],
                (int) $data['year_be'],
                ($data['period_text'] ?? null) ?: null,
                ($data['pay_date_text'] ?? null) ?: null,
                ($data['original_filename'] ?? null) ?: 'payroll_upload.xlsx',
                Auth::id()
            );

            // Clean up temp file
            @unlink($tempFullPath);

            $totalSlips = collect($batches)->sum('total_employees');
            $batchCount = count($batches);

            return redirect()
                ->route('dashboard')
                ->with('success', "อัปโหลดทับข้อมูลเดิมเรียบร้อยแล้ว จำนวน {$batchCount} งวดเดือน (รวม {$totalSlips} สลิป)");
        }

        $data = $request->validate([
            'payroll_file' => ['required', 'file', 'max:20480'],
            'employee_type' => ['required', 'in:พนักงานราชการ,ข้าราชการ'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year_be' => ['required', 'integer', 'between:2550,2600'],
            'period_text' => ['nullable', 'string', 'max:150'],
            'pay_date_text' => ['nullable', 'string', 'max:150'],
        ], [
            'payroll_file.required' => 'กรุณาเลือกไฟล์ Excel หรือ CSV ที่ต้องการอัปโหลด',
        ]);

        $file = $request->file('payroll_file');
        $originalName = $file->getClientOriginalName();
        $realPath = $file->getRealPath();

        // 1. Check for duplicates in database and inside the file
        $preview = $importer->previewFile(
            $realPath,
            $data['employee_type'],
            (int) $data['month'],
            (int) $data['year_be'],
            $originalName
        );

        if ($preview['has_duplicate_batches'] || $preview['has_duplicate_persons']) {
            // Save temp file for confirmation
            $tempDir = storage_path('app/temp_payroll');
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            $tempExt = $file->getClientOriginalExtension() ?: 'xlsx';
            $tempRelativePath = 'temp_payroll/temp_' . uniqid() . '.' . $tempExt;
            $file->move($tempDir, basename($tempRelativePath));

            return redirect()->route('dashboard')
                ->with('duplicate_warning', $preview)
                ->with('temp_file_path', $tempRelativePath)
                ->with('temp_form_data', [
                    'employee_type' => $data['employee_type'],
                    'month' => $data['month'],
                    'year_be' => $data['year_be'],
                    'period_text' => $data['period_text'] ?? '',
                    'pay_date_text' => $data['pay_date_text'] ?? '',
                    'original_filename' => $originalName,
                ]);
        }

        // 2. No duplicate found, import immediately
        $batches = $importer->importFile(
            $realPath,
            $data['employee_type'],
            (int) $data['month'],
            (int) $data['year_be'],
            ($data['period_text'] ?? null) ?: null,
            ($data['pay_date_text'] ?? null) ?: null,
            $originalName,
            Auth::id()
        );

        if (empty($batches)) {
            return back()->withErrors([
                'payroll_file' => 'ไม่พบข้อมูลเงินเดือนในไฟล์ที่อัปโหลด กรุณาตรวจสอบหัวตารางของไฟล์ Excel/CSV',
            ]);
        }

        $totalSlips = collect($batches)->sum('total_employees');
        $batchCount = count($batches);

        if ($batchCount === 1) {
            return redirect()
                ->route('batches.show', $batches[0])
                ->with('success', "นำเข้าข้อมูลสำเร็จ จำนวน {$totalSlips} รายการ");
        }

        return redirect()
            ->route('dashboard')
            ->with('success', "นำเข้าข้อมูลสำเร็จ {$batchCount} งวดเดือน รวมทั้งหมด {$totalSlips} สลิป");
    }

    public function cancelUpload(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $tempPath = $request->input('temp_file_path');
        if ($tempPath) {
            $fullPath = storage_path('app/' . $tempPath);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        return redirect()->route('dashboard')->with('info', 'ยกเลิกการอัปโหลดเรียบร้อยแล้ว');
    }

    public function showBatch(Request $request, PayrollBatch $batch)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $search = trim((string) $request->query('q', ''));
        $district = trim((string) $request->query('district', ''));

        $payslips = $batch->payslips()
            ->with('user')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('bank_account', 'like', "%{$search}%")
                        ->orWhere('citizen_id', 'like', "%{$search}%")
                        ->orWhere('position_number', 'like', "%{$search}%");
                });
            })
            ->when($district !== '', fn ($q) => $q->where('level_or_district', $district))
            ->orderBy('seq_no')
            ->orderBy('id')
            ->get();

        $districts = $batch->payslips()
            ->whereNotNull('level_or_district')
            ->where('level_or_district', '!=', '')
            ->select('level_or_district')
            ->distinct()
            ->orderBy('level_or_district')
            ->pluck('level_or_district');

        return view('payroll.batch-show', compact('batch', 'payslips', 'search', 'district', 'districts'));
    }

    public function updateBatch(Request $request, PayrollBatch $batch)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'period_text' => ['required', 'string', 'max:150'],
            'pay_date_text' => ['required', 'string', 'max:150'],
        ]);

        $batch->update($data);

        return back()->with('success', 'บันทึกข้อมูลรอบเงินเดือนและวันที่จ่ายเรียบร้อยแล้ว');
    }

    public function destroyBatch(PayrollBatch $batch)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $title = "{$batch->employee_type} {$batch->month_name} {$batch->year_be}";
        $batch->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', "ลบงวดข้อมูล {$title} เรียบร้อยแล้ว");
    }

    public function showPayslip(Payslip $payslip)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $payslip->user_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ดูสลิปเงินเดือนของผู้อื่น');
        }

        $payslip->load('batch', 'user');
        $settings = Setting::allAsMap();

        return view('payroll.payslip-print', compact('payslip', 'settings'));
    }

    public function printBatch(PayrollBatch $batch)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $payslips = $batch->payslips()->with('user')->orderBy('seq_no')->orderBy('id')->get();
        $settings = Setting::allAsMap();

        return view('payroll.batch-print', compact('batch', 'payslips', 'settings'));
    }

    public function myPayslips(Request $request)
    {
        $user = Auth::user();
        $payslips = Payslip::with('batch')
            ->where('user_id', $user->id)
            ->get()
            ->sortByDesc(fn ($p) => ($p->batch?->year_be ?? 0) * 100 + ($p->batch?->month ?? 0))
            ->values();

        return view('payroll.my-payslips', compact('user', 'payslips'));
    }
}
