<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\Setting;
use App\Models\User;
use App\Services\PayrollImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'civil_servants' => User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'ข้าราชการ')->count(),
            'gov_employees' => User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'พนักงานราชการ')->count(),
            'total_net_payout' => (float) PayrollBatch::sum('total_net_pay'),
            'total_income_payout' => (float) PayrollBatch::sum('total_income'),
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

            ActivityLog::record(
                'payroll_upload',
                "อัปโหลดทับข้อมูลเงินเดือนสำเร็จ {$batchCount} งวดเดือน ({$totalSlips} สลิป)",
                Auth::user()
            );

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

        ActivityLog::record(
            'payroll_upload',
            "นำเข้าข้อมูลเงินเดือนสำเร็จ {$batchCount} งวดเดือน ({$totalSlips} สลิป) จากไฟล์ {$originalName}",
            Auth::user()
        );

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

        ActivityLog::record(
            'payroll_delete',
            "ลบงวดข้อมูลเงินเดือน {$title}",
            Auth::user()
        );

        return redirect()
            ->route('dashboard')
            ->with('success', "ลบงวดข้อมูล {$title} เรียบร้อยแล้ว");
    }

    public function exportBatchCsv(PayrollBatch $batch)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $filename = 'payroll_' . $batch->employee_type . '_' . $batch->month . '_' . $batch->year_be . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($batch) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel on Windows
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ลำดับ', 'ชื่อ-สกุล', 'ประเภทบุคลากร', 'ตำแหน่ง', 'อำเภอ/สังกัด', 'เลขประจำตัว 13 หลัก', 'เลขบัญชีธนาคาร', 'รวมรายรับ (บาท)', 'รวมรายหัก (บาท)', 'ยอดรับสุทธิ (บาท)', 'หมายเหตุ']);

            foreach ($batch->payslips()->orderBy('seq_no')->get() as $idx => $slip) {
                fputcsv($handle, [
                    $slip->seq_no ?: ($idx + 1),
                    $slip->full_name,
                    $slip->employee_type,
                    $slip->position,
                    $slip->level_or_district,
                    $slip->citizen_id,
                    $slip->bank_account,
                    number_format((float) $slip->total_income, 2, '.', ''),
                    number_format((float) $slip->total_deduction, 2, '.', ''),
                    number_format((float) $slip->net_pay, 2, '.', ''),
                    $slip->remark,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
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

    public function editPayslip(Payslip $payslip)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $payslip->load('batch', 'user');

        return view('payroll.payslip-edit', compact('payslip'));
    }

    public function updatePayslip(Request $request, Payslip $payslip)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'citizen_id' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:150'],
            'level_or_district' => ['nullable', 'string', 'max:150'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'remark' => ['nullable', 'string', 'max:255'],
            'income_items' => ['nullable', 'array'],
            'income_items.*.label' => ['nullable', 'string', 'max:150'],
            'income_items.*.amount' => ['nullable', 'numeric'],
            'deduction_items' => ['nullable', 'array'],
            'deduction_items.*.label' => ['nullable', 'string', 'max:150'],
            'deduction_items.*.amount' => ['nullable', 'numeric'],
        ]);

        // Process income items
        $cleanedIncome = [];
        $totalIncome = 0;
        foreach ($request->input('income_items', []) as $item) {
            $label = trim((string) ($item['label'] ?? ''));
            $amount = (float) ($item['amount'] ?? 0);
            if ($label !== '' && $amount > 0) {
                $cleanedIncome[] = [
                    'label' => $label,
                    'amount' => round($amount, 2),
                ];
                $totalIncome += $amount;
            }
        }

        // Process deduction items
        $cleanedDeductions = [];
        $totalDeduction = 0;
        foreach ($request->input('deduction_items', []) as $item) {
            $label = trim((string) ($item['label'] ?? ''));
            $amount = (float) ($item['amount'] ?? 0);
            if ($label !== '' && $amount > 0) {
                $cleanedDeductions[] = [
                    'label' => $label,
                    'amount' => round($amount, 2),
                ];
                $totalDeduction += $amount;
            }
        }

        $netPay = round($totalIncome - $totalDeduction, 2);

        $cleanCitizenId = $data['citizen_id'] ? preg_replace('/\D/', '', $data['citizen_id']) : null;
        $cleanBank = $data['bank_account'] ? preg_replace('/\D/', '', $data['bank_account']) : null;

        $payslip->update([
            'full_name' => trim($data['full_name']),
            'citizen_id' => $cleanCitizenId ?: null,
            'position' => $data['position'] ? trim($data['position']) : null,
            'level_or_district' => $data['level_or_district'] ? trim($data['level_or_district']) : null,
            'bank_account' => $cleanBank ?: null,
            'bank_name' => $data['bank_name'] ?: 'ธ.กรุงไทย',
            'remark' => $data['remark'] ? trim($data['remark']) : null,
            'income_items' => $cleanedIncome,
            'deduction_items' => $cleanedDeductions,
            'total_income' => round($totalIncome, 2),
            'total_deduction' => round($totalDeduction, 2),
            'net_pay' => $netPay,
        ]);

        // If employee user exists, sync citizen ID and bank account as well
        if ($payslip->user) {
            $userUpdates = [];
            if ($cleanCitizenId) {
                $userUpdates['citizen_id'] = $cleanCitizenId;
                if ($payslip->user->isDefaultPassword() && $payslip->user->visible_password !== $cleanCitizenId) {
                    $userUpdates['password'] = Hash::make($cleanCitizenId);
                    $userUpdates['visible_password'] = $cleanCitizenId;
                }
            }
            if ($cleanBank) {
                $userUpdates['bank_account'] = $cleanBank;
            }
            if (!empty($data['position'])) {
                $userUpdates['position'] = trim($data['position']);
            }
            if (!empty($data['bank_name'])) {
                $userUpdates['bank_name'] = trim($data['bank_name']);
            }
            if (!empty($userUpdates)) {
                $payslip->user->update($userUpdates);
            }
        }

        // Recalculate batch totals
        $batch = $payslip->batch;
        if ($batch) {
            $batch->update([
                'total_income' => round((float) $batch->payslips()->sum('total_income'), 2),
                'total_deduction' => round((float) $batch->payslips()->sum('total_deduction'), 2),
                'total_net_pay' => round((float) $batch->payslips()->sum('net_pay'), 2),
            ]);
        }

        return redirect()
            ->route('payslips.show', $payslip)
            ->with('success', "บันทึกการแก้ไขสลิปเงินเดือนของ {$payslip->full_name} เรียบร้อยแล้ว (ยอดรวมงวดได้รับการปรับปรุงอัตโนมัติ)");
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

        // Calculate YTD summaries for this employee
        $totalIncomeYtd = (float) $payslips->sum('total_income');
        $totalDeductionYtd = (float) $payslips->sum('total_deduction');
        $totalNetPayYtd = (float) $payslips->sum('net_pay');
        $latestPayslip = $payslips->first();

        // Available years
        $years = $payslips->pluck('batch.year_be')->filter()->unique()->values();

        return view('payroll.my-payslips', compact(
            'user', 'payslips', 'totalIncomeYtd', 'totalDeductionYtd', 'totalNetPayYtd', 'latestPayslip', 'years'
        ));
    }

    public function annualReport(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $availableYears = PayrollBatch::select('year_be')
            ->distinct()
            ->orderByDesc('year_be')
            ->pluck('year_be')
            ->toArray();

        $selectedYear = (int) $request->query('year', !empty($availableYears) ? $availableYears[0] : 2569);
        $selectedType = trim((string) $request->query('type', ''));
        $selectedMode = $request->query('mode', 'fiscal'); // 'fiscal' or 'calendar'

        // Determine month sequences
        $monthSequence = [];
        if ($selectedMode === 'fiscal') {
            $monthSequence = [
                ['month' => 10, 'year' => $selectedYear - 1, 'name' => 'ตุลาคม'],
                ['month' => 11, 'year' => $selectedYear - 1, 'name' => 'พฤศจิกายน'],
                ['month' => 12, 'year' => $selectedYear - 1, 'name' => 'ธันวาคม'],
                ['month' => 1, 'year' => $selectedYear, 'name' => 'มกราคม'],
                ['month' => 2, 'year' => $selectedYear, 'name' => 'กุมภาพันธ์'],
                ['month' => 3, 'year' => $selectedYear, 'name' => 'มีนาคม'],
                ['month' => 4, 'year' => $selectedYear, 'name' => 'เมษายน'],
                ['month' => 5, 'year' => $selectedYear, 'name' => 'พฤษภาคม'],
                ['month' => 6, 'year' => $selectedYear, 'name' => 'มิถุนายน'],
                ['month' => 7, 'year' => $selectedYear, 'name' => 'กรกฎาคม'],
                ['month' => 8, 'year' => $selectedYear, 'name' => 'สิงหาคม'],
                ['month' => 9, 'year' => $selectedYear, 'name' => 'กันยายน'],
            ];
        } else {
            $thaiMonths = PayrollBatch::thaiMonths();
            for ($m = 1; $m <= 12; $m++) {
                $monthSequence[] = [
                    'month' => $m,
                    'year' => $selectedYear,
                    'name' => $thaiMonths[$m] ?? "เดือน {$m}",
                ];
            }
        }

        $query = PayrollBatch::query()
            ->when($selectedType !== '', fn ($q) => $q->where('employee_type', $selectedType));

        $batchRecords = $query->get();

        $batchesByPeriod = [];
        $relevantBatchIds = [];
        foreach ($batchRecords as $b) {
            $k = "{$b->year_be}_{$b->month}";
            if (!isset($batchesByPeriod[$k])) {
                $batchesByPeriod[$k] = [];
            }
            $batchesByPeriod[$k][] = $b;
        }

        $monthlyData = [];
        $runningCumulativeIncome = 0;
        $runningCumulativeDeduction = 0;
        $runningCumulativeNet = 0;

        $grandTotalIncome = 0;
        $grandTotalDeduction = 0;
        $grandTotalNet = 0;
        $totalEmployeeMonthUnits = 0;
        $monthsWithDataCount = 0;

        foreach ($monthSequence as $mItem) {
            $k = "{$mItem['year']}_{$mItem['month']}";
            $batches = $batchesByPeriod[$k] ?? [];

            $mIncome = 0;
            $mDeduction = 0;
            $mNet = 0;
            $mEmployees = 0;
            $hasData = !empty($batches);

            if ($hasData) {
                $monthsWithDataCount++;
                foreach ($batches as $b) {
                    $mIncome += (float) $b->total_income;
                    $mDeduction += (float) $b->total_deduction;
                    $mNet += (float) $b->total_net_pay;
                    $mEmployees += (int) $b->total_employees;
                    $relevantBatchIds[] = $b->id;
                }
            }

            $runningCumulativeIncome += $mIncome;
            $runningCumulativeDeduction += $mDeduction;
            $runningCumulativeNet += $mNet;

            $grandTotalIncome += $mIncome;
            $grandTotalDeduction += $mDeduction;
            $grandTotalNet += $mNet;
            $totalEmployeeMonthUnits += $mEmployees;

            $monthlyData[] = [
                'month' => $mItem['month'],
                'year' => $mItem['year'],
                'name' => $mItem['name'],
                'label' => $mItem['name'] . ' ' . mb_substr((string) $mItem['year'], 2),
                'full_label' => $mItem['name'] . ' ' . $mItem['year'],
                'has_data' => $hasData,
                'batches_count' => count($batches),
                'employees_count' => $mEmployees,
                'income' => $mIncome,
                'deduction' => $mDeduction,
                'net_pay' => $mNet,
                'cumulative_income' => $runningCumulativeIncome,
                'cumulative_deduction' => $runningCumulativeDeduction,
                'cumulative_net_pay' => $runningCumulativeNet,
                'batch_ids' => array_map(fn ($b) => $b->id, $batches),
            ];
        }

        $avgEmployeesPerMonth = $monthsWithDataCount > 0 ? round($totalEmployeeMonthUnits / $monthsWithDataCount) : 0;
        $deductionPercentage = $grandTotalIncome > 0 ? round(($grandTotalDeduction / $grandTotalIncome) * 100, 1) : 0;
        $netPayoutPercentage = $grandTotalIncome > 0 ? round(($grandTotalNet / $grandTotalIncome) * 100, 1) : 0;

        // Deduction categories breakdown
        $deductionCategories = [];
        if (!empty($relevantBatchIds)) {
            $slips = Payslip::whereIn('payroll_batch_id', $relevantBatchIds)->select('deduction_items')->get();
            foreach ($slips as $slip) {
                foreach ($slip->deduction_items ?? [] as $dItem) {
                    $dLabel = trim((string) ($dItem['label'] ?? ''));
                    $dAmount = (float) ($dItem['amount'] ?? 0);
                    if ($dLabel !== '' && $dAmount > 0) {
                        $deductionCategories[$dLabel] = ($deductionCategories[$dLabel] ?? 0) + $dAmount;
                    }
                }
            }
            arsort($deductionCategories);
        }

        $topDeductions = array_slice($deductionCategories, 0, 6, true);
        $otherDeductionsSum = array_sum(array_slice($deductionCategories, 6, null, true));
        if ($otherDeductionsSum > 0) {
            $topDeductions['รายการหักอื่น ๆ'] = $otherDeductionsSum;
        }

        return view('payroll.annual-report', compact(
            'availableYears', 'selectedYear', 'selectedType', 'selectedMode',
            'monthlyData', 'grandTotalIncome', 'grandTotalDeduction', 'grandTotalNet',
            'avgEmployeesPerMonth', 'monthsWithDataCount', 'deductionPercentage',
            'netPayoutPercentage', 'topDeductions'
        ));
    }

    public function exportAnnualReportCsv(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $selectedYear = (int) $request->query('year', 2569);
        $selectedType = trim((string) $request->query('type', ''));
        $selectedMode = $request->query('mode', 'fiscal');

        $monthSequence = [];
        if ($selectedMode === 'fiscal') {
            $monthSequence = [
                ['month' => 10, 'year' => $selectedYear - 1, 'name' => 'ตุลาคม'],
                ['month' => 11, 'year' => $selectedYear - 1, 'name' => 'พฤศจิกายน'],
                ['month' => 12, 'year' => $selectedYear - 1, 'name' => 'ธันวาคม'],
                ['month' => 1, 'year' => $selectedYear, 'name' => 'มกราคม'],
                ['month' => 2, 'year' => $selectedYear, 'name' => 'กุมภาพันธ์'],
                ['month' => 3, 'year' => $selectedYear, 'name' => 'มีนาคม'],
                ['month' => 4, 'year' => $selectedYear, 'name' => 'เมษายน'],
                ['month' => 5, 'year' => $selectedYear, 'name' => 'พฤษภาคม'],
                ['month' => 6, 'year' => $selectedYear, 'name' => 'มิถุนายน'],
                ['month' => 7, 'year' => $selectedYear, 'name' => 'กรกฎาคม'],
                ['month' => 8, 'year' => $selectedYear, 'name' => 'สิงหาคม'],
                ['month' => 9, 'year' => $selectedYear, 'name' => 'กันยายน'],
            ];
        } else {
            $thaiMonths = PayrollBatch::thaiMonths();
            for ($m = 1; $m <= 12; $m++) {
                $monthSequence[] = [
                    'month' => $m,
                    'year' => $selectedYear,
                    'name' => $thaiMonths[$m] ?? "เดือน {$m}",
                ];
            }
        }

        $query = PayrollBatch::query()
            ->when($selectedType !== '', fn ($q) => $q->where('employee_type', $selectedType));

        $batchRecords = $query->get();

        $batchesByPeriod = [];
        foreach ($batchRecords as $b) {
            $k = "{$b->year_be}_{$b->month}";
            if (!isset($batchesByPeriod[$k])) {
                $batchesByPeriod[$k] = [];
            }
            $batchesByPeriod[$k][] = $b;
        }

        $typeLabel = $selectedType ?: 'ทุกประเภทบุคลากร';
        $modeLabel = $selectedMode === 'fiscal' ? "ปีงบประมาณ_{$selectedYear}" : "ปีปฏิทิน_{$selectedYear}";
        $filename = "annual_payroll_{$modeLabel}_{$typeLabel}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($monthSequence, $batchesByPeriod) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ลำดับ', 'งวดประจำเดือน', 'จำนวนบุคลากร (คน)', 'รวมรายรับ (บาท)', 'รวมรายหัก (บาท)', 'ยอดจ่ายสุทธิ (บาท)', 'เงินได้สะสมรวม (บาท)', 'เงินหักสะสมรวม (บาท)', 'ยอดจ่ายสุทธิสะสม (บาท)']);

            $cumIncome = 0;
            $cumDeduct = 0;
            $cumNet = 0;

            $totalIncome = 0;
            $totalDeduct = 0;
            $totalNet = 0;

            foreach ($monthSequence as $idx => $mItem) {
                $k = "{$mItem['year']}_{$mItem['month']}";
                $batches = $batchesByPeriod[$k] ?? [];

                $mIncome = 0;
                $mDeduct = 0;
                $mNet = 0;
                $mEmp = 0;

                foreach ($batches as $b) {
                    $mIncome += (float) $b->total_income;
                    $mDeduct += (float) $b->total_deduction;
                    $mNet += (float) $b->total_net_pay;
                    $mEmp += (int) $b->total_employees;
                }

                $cumIncome += $mIncome;
                $cumDeduct += $mDeduct;
                $cumNet += $mNet;

                $totalIncome += $mIncome;
                $totalDeduct += $mDeduct;
                $totalNet += $mNet;

                fputcsv($handle, [
                    $idx + 1,
                    $mItem['name'] . ' ' . $mItem['year'],
                    $mEmp,
                    number_format($mIncome, 2, '.', ''),
                    number_format($mDeduct, 2, '.', ''),
                    number_format($mNet, 2, '.', ''),
                    number_format($cumIncome, 2, '.', ''),
                    number_format($cumDeduct, 2, '.', ''),
                    number_format($cumNet, 2, '.', ''),
                ]);
            }

            fputcsv($handle, [
                'รวมทั้งสิ้น',
                '-',
                '-',
                number_format($totalIncome, 2, '.', ''),
                number_format($totalDeduct, 2, '.', ''),
                number_format($totalNet, 2, '.', ''),
                number_format($cumIncome, 2, '.', ''),
                number_format($cumDeduct, 2, '.', ''),
                number_format($cumNet, 2, '.', ''),
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
