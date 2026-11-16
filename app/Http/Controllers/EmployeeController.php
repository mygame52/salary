<?php

namespace App\Http\Controllers;

use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $type = $request->query('type', '');
        $status = $request->query('status', '');
        $district = $request->query('district', '');
        $filter = $request->query('filter', '');
        $search = trim((string) $request->query('q', ''));

        // Identify latest batch & recent payroll activity (> 1 month missing check)
        $latestBatch = PayrollBatch::orderByDesc('year_be')->orderByDesc('month')->first();
        $currentMonthUserIds = [];
        $prevMonthUserIds = [];

        if ($latestBatch) {
            $currentMonthUserIds = Payslip::where('payroll_batch_id', $latestBatch->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $targetMonth = $latestBatch->month == 1 ? 12 : $latestBatch->month - 1;
            $targetYear = $latestBatch->month == 1 ? $latestBatch->year_be - 1 : $latestBatch->year_be;
            $prevBatch = PayrollBatch::where('year_be', $targetYear)->where('month', $targetMonth)->first();
            if ($prevBatch) {
                $prevMonthUserIds = Payslip::where('payroll_batch_id', $prevBatch->id)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->unique()
                    ->toArray();
            }
        }

        $recentUserIds = array_unique(array_merge($currentMonthUserIds, $prevMonthUserIds));

        // Missing over 1 month from current batch
        $missingOver1MonthUserIds = User::where('role', '!=', 'admin')
            ->whereNotIn('id', $recentUserIds)
            ->pluck('id')
            ->toArray();

        // Alert group: missing over 1 month AND status is still 'ปกติ'
        $needsStatusReviewUserIds = User::where('role', '!=', 'admin')
            ->whereIn('id', $missingOver1MonthUserIds)
            ->where('status', 'ปกติ')
            ->pluck('id')
            ->toArray();

        $employees = User::where('role', '!=', 'admin')
            ->withCount('payslips')
            ->when($type !== '', fn ($q) => $q->where('employee_type', $type))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($district !== '', fn ($q) => $q->where('level_or_district', $district))
            ->when($filter === 'missing_citizen_id', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('citizen_id')->orWhere('citizen_id', '');
                });
            })
            ->when($filter === 'missing_bank', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('bank_account')->orWhere('bank_account', '');
                });
            })
            ->when($filter === 'needs_status_review', function ($q) use ($needsStatusReviewUserIds) {
                $q->whereIn('id', $needsStatusReviewUserIds);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('citizen_id', 'like', "%{$search}%")
                        ->orWhere('bank_account', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('level_or_district', 'like', "%{$search}%")
                        ->orWhere('status_note', 'like', "%{$search}%");
                });
            })
            ->orderBy('employee_type')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        // Fetch each employee's latest payslip month & year for quick table display
        $employeeIds = $employees->pluck('id')->toArray();
        $latestPayslipsByUserId = [];
        if (!empty($employeeIds)) {
            $slips = Payslip::join('payroll_batches', 'payslips.payroll_batch_id', '=', 'payroll_batches.id')
                ->whereIn('payslips.user_id', $employeeIds)
                ->select(
                    'payslips.user_id',
                    'payroll_batches.month_name as last_month_name',
                    'payroll_batches.year_be as last_year_be',
                    'payroll_batches.month as last_month'
                )
                ->orderByDesc('payroll_batches.year_be')
                ->orderByDesc('payroll_batches.month')
                ->get();

            foreach ($slips as $slip) {
                if (!isset($latestPayslipsByUserId[$slip->user_id])) {
                    $latestPayslipsByUserId[$slip->user_id] = $slip;
                }
            }
        }

        $districts = User::where('role', '!=', 'admin')
            ->whereNotNull('level_or_district')
            ->where('level_or_district', '!=', '')
            ->select('level_or_district')
            ->distinct()
            ->orderBy('level_or_district')
            ->pluck('level_or_district');

        $missingCitizenCount = User::where('role', '!=', 'admin')
            ->where(function ($q) {
                $q->whereNull('citizen_id')->orWhere('citizen_id', '');
            })->count();

        $missingBankCount = User::where('role', '!=', 'admin')
            ->where(function ($q) {
                $q->whereNull('bank_account')->orWhere('bank_account', '');
            })->count();

        $civilServantCount = User::where('role', '!=', 'admin')->where('employee_type', 'ข้าราชการ')->count();
        $permanentCount = User::where('role', '!=', 'admin')->where('employee_type', 'ลูกจ้างประจำ')->count();
        $govEmployeeCount = User::where('role', '!=', 'admin')->where('employee_type', 'พนักงานราชการ')->count();
        $totalEmployeesCount = User::where('role', '!=', 'admin')->count();

        // Status counts
        $statusNormalCount = User::where('role', '!=', 'admin')->where('status', 'ปกติ')->count();
        $statusTransferredCount = User::where('role', '!=', 'admin')->where('status', 'ย้าย')->count();
        $statusRetiredCount = User::where('role', '!=', 'admin')->where('status', 'เกษียณ')->count();
        $statusResignedCount = User::where('role', '!=', 'admin')->where('status', 'ลาออก')->count();
        $statusDeceasedCount = User::where('role', '!=', 'admin')->where('status', 'เสียชีวิต')->count();
        $needsStatusReviewCount = count($needsStatusReviewUserIds);

        return view('employees.index', compact(
            'employees', 'type', 'status', 'district', 'filter', 'search', 'districts',
            'latestBatch', 'needsStatusReviewUserIds', 'latestPayslipsByUserId',
            'missingCitizenCount', 'missingBankCount',
            'civilServantCount', 'permanentCount', 'govEmployeeCount', 'totalEmployeesCount',
            'statusNormalCount', 'statusTransferredCount', 'statusRetiredCount', 'statusResignedCount', 'statusDeceasedCount',
            'needsStatusReviewCount'
        ));
    }

    public function edit(User $employee)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $employee->load(['payslips.batch' => function ($q) {
            $q->orderByDesc('year_be')->orderByDesc('month');
        }]);

        $districts = User::where('role', '!=', 'admin')
            ->whereNotNull('level_or_district')
            ->where('level_or_district', '!=', '')
            ->select('level_or_district')
            ->distinct()
            ->orderBy('level_or_district')
            ->pluck('level_or_district');

        return view('employees.edit', compact('employee', 'districts'));
    }

    public function update(Request $request, User $employee)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'citizen_id' => ['nullable', 'string', 'max:20'],
            'employee_type' => ['required', 'in:พนักงานราชการ,ข้าราชการ,ลูกจ้างประจำ'],
            'status' => ['nullable', 'in:ปกติ,ย้าย,เกษียณ,ลาออก,เสียชีวิต'],
            'status_note' => ['nullable', 'string', 'max:255'],
            'status_date' => ['nullable', 'string', 'max:50'],
            'position_number' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:150'],
            'level_or_district' => ['nullable', 'string', 'max:150'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:255'],
            'new_password' => ['nullable', 'string', 'min:4', 'max:50'],
        ]);

        $cleanCitizenId = !empty($data['citizen_id']) ? preg_replace('/\D/', '', $data['citizen_id']) : null;
        $cleanBank = !empty($data['bank_account']) ? preg_replace('/\D/', '', $data['bank_account']) : null;

        $updates = [
            'name' => trim($data['name']),
            'citizen_id' => $cleanCitizenId ?: null,
            'employee_type' => $data['employee_type'],
            'status' => $data['status'] ?? $employee->status ?? 'ปกติ',
            'status_note' => !empty($data['status_note']) ? trim($data['status_note']) : null,
            'status_date' => !empty($data['status_date']) ? trim($data['status_date']) : null,
            'position_number' => !empty($data['position_number']) ? trim($data['position_number']) : null,
            'position' => !empty($data['position']) ? trim($data['position']) : null,
            'level_or_district' => !empty($data['level_or_district']) ? trim($data['level_or_district']) : null,
            'bank_account' => $cleanBank ?: null,
            'bank_name' => !empty($data['bank_name']) ? $data['bank_name'] : 'ธ.กรุงไทย',
            'account_name' => !empty($data['account_name']) ? trim($data['account_name']) : null,
            'remark' => !empty($data['remark']) ? trim($data['remark']) : null,
        ];

        if (!empty($data['new_password'])) {
            $updates['password'] = Hash::make($data['new_password']);
        }

        $employee->update($updates);

        // Sync updated citizen_id, position, bank_account, position_number to all payslips of this employee
        $employee->payslips()->update([
            'full_name' => $employee->name,
            'citizen_id' => $employee->citizen_id,
            'position_number' => $employee->position_number,
            'position' => $employee->position,
            'level_or_district' => $employee->level_or_district,
            'bank_account' => $employee->bank_account,
            'bank_name' => $employee->bank_name,
        ]);

        return back()->with('success', "บันทึกข้อมูลของ {$employee->name} เรียบร้อยแล้ว");
    }

    public function bulkUpdateCitizenIds(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $items = $request->input('employees', []);
        $updatedCount = 0;

        foreach ($items as $userId => $fields) {
            $user = User::where('role', '!=', 'admin')->find($userId);
            if (!$user) {
                continue;
            }

            $citizenId = isset($fields['citizen_id']) ? preg_replace('/\D/', '', (string) $fields['citizen_id']) : null;
            $position = isset($fields['position']) ? trim((string) $fields['position']) : null;
            $bankAccount = isset($fields['bank_account']) ? preg_replace('/\D/', '', (string) $fields['bank_account']) : null;
            $employeeType = isset($fields['employee_type']) ? trim((string) $fields['employee_type']) : null;
            $status = isset($fields['status']) ? trim((string) $fields['status']) : null;

            $dirty = false;
            if ($citizenId !== ($user->citizen_id ?? '')) {
                $user->citizen_id = $citizenId ?: null;
                $dirty = true;
            }
            if ($position !== null && $position !== ($user->position ?? '')) {
                $user->position = $position ?: null;
                $dirty = true;
            }
            if ($bankAccount !== null && $bankAccount !== ($user->bank_account ?? '')) {
                $user->bank_account = $bankAccount ?: null;
                $dirty = true;
            }
            if ($employeeType !== null && in_array($employeeType, ['ข้าราชการ', 'ลูกจ้างประจำ', 'พนักงานราชการ']) && $employeeType !== $user->employee_type) {
                $user->employee_type = $employeeType;
                $dirty = true;
            }
            if ($status !== null && in_array($status, ['ปกติ', 'ย้าย', 'เกษียณ', 'ลาออก', 'เสียชีวิต']) && $status !== $user->status) {
                $user->status = $status;
                $dirty = true;
            }

            if ($dirty) {
                $user->save();
                $user->payslips()->update([
                    'citizen_id' => $user->citizen_id,
                    'position' => $user->position,
                    'bank_account' => $user->bank_account,
                ]);
                $updatedCount++;
            }
        }

        return back()->with('success', "บันทึกข้อมูลบุคลากรเรียบร้อยแล้ว ({$updatedCount} รายการ)");
    }
}
