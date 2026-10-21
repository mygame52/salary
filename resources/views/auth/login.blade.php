@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ - ระบบสลิปเงินเดือนออนไลน์')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-b from-purple-900 to-purple-800 px-6 py-8 text-center text-white">
            <img src="{{ asset('images/logo.png') }}" alt="กรมส่งเสริมการเรียนรู้" class="w-24 h-24 mx-auto bg-white rounded-full p-1 shadow-lg mb-4 object-contain">
            <h1 class="text-xl font-bold">ระบบสลิปเงินเดือนออนไลน์</h1>
            <p class="text-purple-200 text-sm mt-1">สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช</p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST" class="p-6 sm:p-8 space-y-5">
            @csrf

            <div>
                <label for="login" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    ชื่อผู้ใช้งาน / เลขบัตรประจำตัวประชาชน 13 หลัก
                </label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    placeholder="เช่น admin หรือ เลขบัตรประชาชน 13 หลัก"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    รหัสผ่าน (Password)
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="กรอกรหัสผ่าน"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition"
                >
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-purple-700 focus:ring-purple-600">
                    <span>จดจำการเข้าสู่ระบบ</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-purple-800 hover:bg-purple-900 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition"
            >
                เข้าสู่ระบบ
            </button>

            <div class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                สำหรับเจ้าหน้าที่การเงิน: ชื่อผู้ใช้ <strong>admin</strong> / รหัสผ่าน <strong>1234</strong><br>
                สำหรับบุคลากร: ใช้ <strong>เลขบัตรประชาชน 13 หลัก</strong> (รหัสเริ่มต้น <strong>1234</strong>)
            </div>
        </form>
    </div>
</div>
@endsection
