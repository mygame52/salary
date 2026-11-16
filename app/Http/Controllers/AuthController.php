<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('dashboard')
                : redirect()->route('my-payslips');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        ActivityLog::ensureSchemaExists();

        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'กรุณากรอกเลขที่บัญชีธนาคาร หรือ ชื่อผู้ใช้',
            'password.required' => 'กรุณากรอกรหัสผ่าน (เลขบัตรประชาชน 13 หลัก หรือรหัสผ่านที่ตั้งไว้)',
        ]);

        $loginInput = trim($data['login']);
        $cleanDigits = preg_replace('/\D/', '', $loginInput);
        $passwordInput = trim($data['password']);
        $cleanPassDigits = preg_replace('/\D/', '', $passwordInput);

        $user = User::where('username', $loginInput)
            ->when($cleanDigits !== '', function ($q) use ($cleanDigits) {
                $q->orWhere('bank_account', $cleanDigits)
                  ->orWhere('citizen_id', $cleanDigits);
            })
            ->first();

        $passwordValid = false;
        if ($user) {
            if (Hash::check($passwordInput, $user->password)) {
                $passwordValid = true;
            } elseif ($user->isDefaultPassword() && !empty($user->citizen_id)) {
                $cleanCitizen = preg_replace('/\D/', '', (string) $user->citizen_id);
                if ($cleanCitizen !== '' && $cleanPassDigits === $cleanCitizen) {
                    $passwordValid = true;
                    // Sync hash and visible_password to the 13-digit citizen_id
                    if ($user->visible_password !== $cleanCitizen) {
                        $user->update([
                            'password' => Hash::make($cleanCitizen),
                            'visible_password' => $cleanCitizen,
                        ]);
                    }
                }
            }
        }

        if (!$user || !$passwordValid) {
            ActivityLog::record(
                'login_failed',
                "พยายามเข้าสู่ระบบด้วยรหัสผู้ใช้ '{$loginInput}' ไม่สำเร็จ",
                $user,
                $user,
                $user ? $user->name : $loginInput
            );

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'เลขที่บัญชีธนาคาร ชื่อผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Ignore if columns are being migrated
        }

        ActivityLog::record(
            'login',
            "เข้าสู่ระบบสำเร็จ (ใช้รหัสเข้าใช้: {$loginInput})",
            $user,
            $user
        );

        return $user->isAdmin()
            ? redirect()->intended(route('dashboard'))
            : redirect()->intended(route('my-payslips'));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'current_password.required' => 'กรุณากรอกรหัสผ่านปัจจุบัน',
            'new_password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'new_password.min' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 4 ตัวอักษร',
            'new_password.confirmed' => 'การยืนยันรหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        $currentInput = trim((string) $request->input('current_password'));
        $currentMatches = Hash::check($currentInput, $user->password);
        if (!$currentMatches && $user->isDefaultPassword() && !empty($user->citizen_id)) {
            $cleanInputDigits = preg_replace('/\D/', '', $currentInput);
            $cleanCitizen = preg_replace('/\D/', '', (string) $user->citizen_id);
            if ($cleanCitizen !== '' && $cleanInputDigits === $cleanCitizen) {
                $currentMatches = true;
            }
        }

        if (!$currentMatches) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }

        $newPass = $request->input('new_password');
        $user->update([
            'password' => Hash::make($newPass),
            'visible_password' => $newPass,
        ]);

        ActivityLog::record(
            'password_change',
            'เปลี่ยนรหัสผ่านเข้าสู่ระบบด้วยตนเอง',
            $user,
            $user
        );

        return back()->with('success', 'เปลี่ยนรหัสผ่านเข้าสู่ระบบเรียบร้อยแล้ว');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record('logout', 'ออกจากระบบ', $user, $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
