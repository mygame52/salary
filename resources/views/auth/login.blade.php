@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ - ระบบสลิปเงินเดือนออนไลน์ สกร.ประจำจังหวัดนครศรีธรรมราช')

@section('content')
<div class="w-full max-w-md mx-auto my-8">
    {{-- Card Container --}}
    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl border border-white/20 overflow-hidden ring-1 ring-black/5">
        {{-- Card Header --}}
        <div class="bg-gradient-to-br from-[#1a0e36] via-[#24134b] to-[#160c2d] px-8 pt-9 pb-8 text-center text-white relative overflow-hidden">
            {{-- Background decorative circles --}}
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-purple-500/10 blur-xl"></div>
            <div class="absolute -left-8 -bottom-8 w-32 h-32 rounded-full bg-amber-400/10 blur-xl"></div>

            {{-- Circular Logo with Ring --}}
            <div class="relative inline-block mb-4">
                <div class="w-24 h-24 rounded-full bg-white p-1.5 shadow-xl ring-4 ring-purple-400/20 mx-auto flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="ตรากรมส่งเสริมการเรียนรู้" class="w-full h-full object-contain">
                </div>
            </div>

            <h1 class="text-xl font-bold tracking-tight text-white">
                ระบบสลิปเงินเดือนออนไลน์
            </h1>
            <p class="text-xs text-purple-200/90 mt-1 font-medium leading-relaxed max-w-xs mx-auto">
                สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช
            </p>

            <div class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-amber-300 border border-white/10">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>ข้าราชการ & เจ้าหน้าที่การเงิน</span>
            </div>
        </div>

        {{-- Login Form --}}
        <form action="{{ route('login.submit') }}" method="POST" class="p-7 sm:p-8 space-y-5">
            @csrf

            <div>
                <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    เลขที่บัญชีธนาคาร / ชื่อผู้ใช้งาน (Username)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        placeholder="กรอกเลขที่บัญชีธนาคาร 10 หลัก (หรือ Username)"
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition shadow-2xs bg-slate-50/50"
                    >
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    รหัสผ่าน (Password)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="เลขบัตรประชาชน 13 หลัก หรือรหัสผ่านที่ตั้งไว้"
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition shadow-2xs bg-slate-50/50"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="inline-flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-purple-700 focus:ring-purple-600">
                    <span>จดจำการเข้าสู่ระบบ</span>
                </label>
                <span class="text-slate-500">รหัสเริ่มต้น: <strong class="text-purple-900 font-semibold">เลขบัตร ปชช. 13 หลัก</strong></span>
            </div>

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-purple-900 to-indigo-900 hover:from-purple-950 hover:to-indigo-950 text-white font-bold py-3.5 px-4 rounded-2xl shadow-md hover:shadow-lg transition-all duration-150 flex items-center justify-center gap-2 text-sm"
            >
                <span>เข้าสู่ระบบ</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        {{-- Helper Information Footer --}}
        <div class="px-7 py-4 bg-slate-50/90 border-t border-slate-100 text-center space-y-1">
            <p class="text-xs text-slate-600 font-medium">
                💡 บุคลากรใช้ <strong>เลขที่บัญชีธนาคาร</strong> เป็น User และใช้ <strong>เลขบัตรประชาชน 13 หลัก</strong> เป็น Password เริ่มต้น
            </p>
            <p class="text-[11px] text-slate-400">
                หากพบปัญหาการเข้าสู่ระบบ ติดต่อเจ้าหน้าที่การเงิน สกร.ประจำจังหวัดนครศรีธรรมราช
            </p>
        </div>
    </div>
</div>
@endsection
