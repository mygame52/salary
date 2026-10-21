<?php

namespace App\Http\Controllers;

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
        $district = $request->query('district', '');
        $search = trim((string) $request->query('q', ''));

        $employees = User::where('role', '!=', 'admin')
            ->withCount('payslips')
            ->when($type !== '', fn ($q) => $q->where('employee_type', $type))
            ->when($district !== '', fn ($q) => $q->where('level_or_district', $district))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('citizen_id', 'like', "%{$search}%")
                        ->orWhere('bank_account', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('level_or_district', 'like', "%{$search}%");
                });
            })
            ->orderBy('employee_type')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $districts = User::where('role', '!=', 'admin')
            ->whereNotNull('level_or_district')
            ->where('level_or_district', '!=', '')
            ->select('level_or_district')
            ->distinct()
            ->orderBy('level_or_district')
            ->pluck('level_or_district');

        return view('employees.index', compact('employees', 'type', 'district', 'search', 'districts'));
    }

    public function update(Request $request, User $employee)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'citizen_id' => ['nullable', 'string', 'max:20'],
            'employee_type' => ['required', 'in:พนักงานราชการ,ข้าราชการ'],
            'position' => ['nullable', 'string', 'max:150'],
            'level_or_district' => ['nullable', 'string', 'max:150'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'new_password' => ['nullable', 'string', 'min:4', 'max:50'],
        ]);

        $cleanCitizenId = $data['citizen_id'] ? preg_replace('/\D/', '', $data['citizen_id']) : null;
        $cleanBank = $data['bank_account'] ? preg_replace('/\D/', '', $data['bank_account']) : null;

        $updates = [
            'name' => trim($data['name']),
            'citizen_id' => $cleanCitizenId ?: null,
            'employee_type' => $data['employee_type'],
            'position' => $data['position'] ? trim($data['position']) : null,
            'level_or_district' => $data['level_or_district'] ? trim($data['level_or_district']) : null,
            'bank_account' => $cleanBank ?: null,
            'bank_name' => $data['bank_name'] ?: 'ธ.กรุงไทย',
        ];

        if (!empty($data['new_password'])) {
            $updates['password'] = Hash::make($data['new_password']);
        }

        $employee->update($updates);

        // Sync updated citizen_id, position, bank_account to all payslips of this employee
        $employee->payslips()->update([
            'citizen_id' => $employee->citizen_id,
            'position' => $employee->position,
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
