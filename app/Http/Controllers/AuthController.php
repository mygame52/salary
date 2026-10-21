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
                $q->orWhere('citizen_id', $cleanDigits);
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $user->isAdmin()
            ? redirect()->intended(route('dashboard'))
            : redirect()->intended(route('my-payslips'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
