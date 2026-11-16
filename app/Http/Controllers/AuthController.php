<?php

namespace App\Http\Controllers;

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
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'กรุณากรอกชื่อผู้ใช้ หรือ เลขบัตรประชาชน 13 หลัก',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $loginInput = trim($data['login']);
        $cleanDigits = preg_replace('/\D/', '', $loginInput);

        $user = User::where('username', $loginInput)
            ->when($cleanDigits !== '', function ($q) use ($cleanDigits) {
                $q->orWhere('citizen_id', $cleanDigits)
                  ->orWhere('bank_account', $cleanDigits);
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'ชื่อผู้ใช้ เลขบัตรประชาชน หรือรหัสผ่านไม่ถูกต้อง']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

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

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', 'เปลี่ยนรหัสผ่านเข้าสู่ระบบเรียบร้อยแล้ว');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
