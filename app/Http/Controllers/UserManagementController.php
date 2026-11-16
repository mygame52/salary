<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $group = trim((string) $request->query('group', ''));
        $pwdStatus = trim((string) $request->query('pwd_status', ''));
        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->withCount('payslips')
            ->when($group === 'staff', fn ($q) => $q->whereIn('role', ['admin', 'finance']))
            ->when(in_array($group, ['ข้าราชการ', 'พนักงานราชการ', 'ลูกจ้างประจำ'], true), function ($q) use ($group) {
                $q->whereNotIn('role', ['admin', 'finance'])->where('employee_type', $group);
            })
            ->when($pwdStatus === 'default', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('visible_password', '1234')
                        ->orWhereNull('visible_password')
                        ->orWhereColumn('visible_password', 'citizen_id');
                });
            })
            ->when($pwdStatus === 'custom', function ($q) {
                $q->whereNotNull('visible_password')
                    ->where('visible_password', '!=', '1234')
                    ->where(function ($sub) {
                        $sub->whereNull('citizen_id')
                            ->orWhere('citizen_id', '')
                            ->orWhereColumn('visible_password', '!=', 'citizen_id');
                    });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('citizen_id', 'like', "%{$search}%")
                        ->orWhere('bank_account', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('level_or_district', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN role = 'admin' THEN 1 WHEN role = 'finance' THEN 2 ELSE 3 END")
            ->orderBy('employee_type')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $totalUsers = User::count();
        $staffCount = User::whereIn('role', ['admin', 'finance'])->count();
        $civilCount = User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'ข้าราชการ')->count();
        $govCount = User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'พนักงานราชการ')->count();
        $permCount = User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'ลูกจ้างประจำ')->count();

        $defaultPasswordCount = User::where(function ($q) {
            $q->where('visible_password', '1234')
                ->orWhereNull('visible_password')
                ->orWhereColumn('visible_password', 'citizen_id');
        })->count();
        $customPasswordCount = max(0, $totalUsers - $defaultPasswordCount);
        $loggedInTodayCount = User::whereDate('last_login_at', today())->count();

        $recentLogs = ActivityLog::orderByDesc('created_at')->limit(8)->get();

        return view('users.index', compact(
            'users',
            'group',
            'pwdStatus',
            'search',
            'totalUsers',
            'staffCount',
            'civilCount',
            'govCount',
            'permCount',
            'defaultPasswordCount',
            'customPasswordCount',
            'loggedInTodayCount',
            'recentLogs'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:finance,admin,employee'],
            'password' => ['required', 'string', 'min:4', 'max:50'],
            'citizen_id' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:150'],
            'employee_type' => ['nullable', 'in:ข้าราชการ,พนักงานราชการ,ลูกจ้างประจำ'],
        ], [
            'username.required' => 'กรุณาระบุชื่อผู้ใช้ (Username) สำหรับเข้าสู่ระบบ',
            'username.unique' => 'ชื่อผู้ใช้ (Username) นี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น',
            'name.required' => 'กรุณาระบุชื่อ-นามสกุล',
            'password.required' => 'กรุณากำหนดรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร',
        ]);

        $cleanCitizenId = !empty($data['citizen_id']) ? preg_replace('/\D/', '', $data['citizen_id']) : null;
        $cleanBank = !empty($data['bank_account']) ? preg_replace('/\D/', '', $data['bank_account']) : null;

        $defaultPosition = match ($data['role']) {
            'admin' => 'ผู้ดูแลระบบ',
            'finance' => 'เจ้าหน้าที่การเงิน',
            default => null,
        };

        $newUser = User::create([
            'username' => trim($data['username']),
            'name' => trim($data['name']),
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'visible_password' => $data['password'],
            'citizen_id' => $cleanCitizenId ?: null,
            'bank_account' => $cleanBank ?: null,
            'position' => !empty($data['position']) ? trim($data['position']) : $defaultPosition,
            'employee_type' => $data['employee_type'] ?? 'ข้าราชการ',
            'status' => 'ปกติ',
        ]);

        ActivityLog::record(
            'user_create',
            "เพิ่มผู้ใช้งานใหม่ '{$newUser->name}' (Username: {$newUser->username}, สิทธิ์: {$newUser->role_label})",
            Auth::user(),
            $newUser
        );

        return back()->with('success', "เพิ่มผู้ใช้งาน '{$newUser->name}' ({$newUser->role_label}) เรียบร้อยแล้ว");
    }

    public function update(Request $request, User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'role' => ['required', 'in:admin,finance,employee'],
            'citizen_id' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:150'],
            'level_or_district' => ['nullable', 'string', 'max:150'],
            'new_password' => ['nullable', 'string', 'min:4', 'max:50'],
        ], [
            'username.unique' => 'ชื่อผู้ใช้ (Username) นี้ซ้ำกับผู้อื่นในระบบ',
            'new_password.min' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 4 ตัวอักษร',
        ]);

        // Prevent removing admin/finance privilege from oneself if they are the currently logged-in user
        if ($user->id === Auth::id() && !in_array($data['role'], ['admin', 'finance'], true)) {
            return back()->withErrors(['role' => 'ไม่สามารถลดสิทธิ์บัญชีที่กำลังใช้งานอยู่เป็นบุคลากรทั่วไปได้']);
        }

        $cleanCitizenId = !empty($data['citizen_id']) ? preg_replace('/\D/', '', $data['citizen_id']) : null;
        $cleanBank = !empty($data['bank_account']) ? preg_replace('/\D/', '', $data['bank_account']) : null;

        $oldRole = $user->role;

        $updates = [
            'name' => trim($data['name']),
            'username' => !empty($data['username']) ? trim($data['username']) : null,
            'role' => $data['role'],
            'citizen_id' => $cleanCitizenId ?: null,
            'bank_account' => $cleanBank ?: null,
            'position' => !empty($data['position']) ? trim($data['position']) : null,
            'level_or_district' => !empty($data['level_or_district']) ? trim($data['level_or_district']) : null,
        ];

        $passwordChanged = false;
        if (!empty($data['new_password'])) {
            $updates['password'] = Hash::make($data['new_password']);
            $updates['visible_password'] = $data['new_password'];
            $passwordChanged = true;
        } elseif ($cleanCitizenId && !in_array($data['role'], ['admin', 'finance'], true) && $user->isDefaultPassword() && $user->visible_password !== $cleanCitizenId) {
            $updates['password'] = Hash::make($cleanCitizenId);
            $updates['visible_password'] = $cleanCitizenId;
        }

        $user->update($updates);

        if ($user->payslips()->exists()) {
            $user->payslips()->update([
                'full_name' => $user->name,
                'citizen_id' => $user->citizen_id,
                'position' => $user->position,
                'level_or_district' => $user->level_or_district,
                'bank_account' => $user->bank_account,
            ]);
        }

        $logNotes = [];
        if ($oldRole !== $user->role) {
            $logNotes[] = "เปลี่ยนสิทธิ์เป็น {$user->role_label}";
        }
        if ($passwordChanged) {
            $logNotes[] = "ตั้งรหัสผ่านใหม่";
        }
        $suffix = !empty($logNotes) ? ' (' . implode(', ', $logNotes) . ')' : '';

        ActivityLog::record(
            $passwordChanged ? 'password_reset' : 'user_update',
            "แก้ไขข้อมูลผู้ใช้งาน '{$user->name}'{$suffix}",
            Auth::user(),
            $user
        );

        return back()->with('success', "บันทึกการแก้ไขข้อมูลผู้ใช้งาน '{$user->name}' เรียบร้อยแล้ว");
    }

    public function resetPassword(Request $request, User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $defaultPass = $user->getDefaultPasswordValue();
        $user->update([
            'password' => Hash::make($defaultPass),
            'visible_password' => $defaultPass,
        ]);

        $label = $defaultPass === '1234' ? '1234' : "เลขบัตรประชาชน 13 หลัก ({$defaultPass})";

        ActivityLog::record(
            'password_reset',
            "รีเซ็ตรหัสผ่านของ '{$user->name}' กลับเป็นค่าเริ่มต้น ({$label})",
            Auth::user(),
            $user
        );

        return back()->with('success', "รีเซ็ตรหัสผ่านของ '{$user->name}' เป็นค่าเริ่มต้น ({$defaultPass}) เรียบร้อยแล้ว");
    }

    public function destroy(User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['delete' => 'ไม่สามารถลบบัญชีที่กำลังเข้าสู่ระบบอยู่ได้']);
        }

        if ($user->payslips()->exists()) {
            return back()->withErrors(['delete' => "ไม่สามารถลบ '{$user->name}' ได้เนื่องจากมีประวัติสลิปเงินเดือนในระบบ กรุณาปรับสถานะเป็น ย้าย/เกษียณ/ลาออก แทน"]);
        }

        $deletedName = $user->name;
        $deletedUsername = $user->username ?: '-';
        $user->delete();

        ActivityLog::record(
            'user_delete',
            "ลบบัญชีผู้ใช้งาน '{$deletedName}' (Username: {$deletedUsername})",
            Auth::user(),
            null,
            $deletedName
        );

        return back()->with('success', "ลบบัญชีผู้ใช้งาน '{$deletedName}' เรียบร้อยแล้ว");
    }

    public function activityLogs(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $action = trim((string) $request->query('action', ''));
        $role = trim((string) $request->query('role', ''));
        $date = trim((string) $request->query('date', ''));
        $search = trim((string) $request->query('q', ''));

        $logs = ActivityLog::query()
            ->when($action !== '', function ($q) use ($action) {
                if ($action === 'auth') {
                    $q->whereIn('action', ['login', 'logout']);
                } elseif ($action === 'user_mgmt') {
                    $q->whereIn('action', ['password_change', 'password_reset', 'user_create', 'user_update', 'user_delete']);
                } elseif ($action === 'payroll') {
                    $q->whereIn('action', ['payroll_upload', 'payroll_delete']);
                } else {
                    $q->where('action', $action);
                }
            })
            ->when($role !== '', function ($q) use ($role) {
                if ($role === 'staff') {
                    $q->whereIn('user_role', ['admin', 'finance']);
                } else {
                    $q->where('user_role', $role);
                }
            })
            ->when($date !== '', fn ($q) => $q->whereDate('created_at', $date))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('user_name', 'like', "%{$search}%")
                        ->orWhere('target_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $totalLogs = ActivityLog::count();
        $loginSuccessCount = ActivityLog::where('action', 'login')->count();
        $loginTodayCount = ActivityLog::where('action', 'login')->whereDate('created_at', today())->count();
        $loginFailedCount = ActivityLog::where('action', 'login_failed')->count();
        $passwordEventsCount = ActivityLog::whereIn('action', ['password_change', 'password_reset'])->count();

        return view('users.activity-logs', compact(
            'logs',
            'action',
            'role',
            'date',
            'search',
            'totalLogs',
            'loginSuccessCount',
            'loginTodayCount',
            'loginFailedCount',
            'passwordEventsCount'
        ));
    }
}
