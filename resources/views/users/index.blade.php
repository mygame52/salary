@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งาน สิทธิ์การเข้าถึง และรหัสผ่าน')

@section('content')
<div class="space-y-6">
    {{-- Page Header & Action Buttons --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-800 text-xs font-bold border border-purple-200/60">
                <span>🔐 ระบบจัดการบัญชีผู้ใช้งาน & สิทธิ์เจ้าหน้าที่การเงิน</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">
                จัดการผู้ใช้งาน ดูรหัสผ่าน รีเซ็ต และกำหนดสิทธิ์
            </h2>
            <p class="text-xs sm:text-sm text-slate-500">
                บุคลากรใช้ <strong>เลขที่บัญชีธนาคาร</strong> เป็น User และใช้ <strong>เลขบัตรประชาชน 13 หลัก</strong> เป็นรหัสผ่านเริ่มต้น พร้อมระบบรีเซ็ตรหัสและเพิ่มบัญชี <strong class="text-indigo-700">เจ้าหน้าที่การเงิน</strong> ที่ทำงานได้เหมือน Admin
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button
                type="button"
                onclick="openAddUserModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-800 hover:to-indigo-800 text-white text-xs sm:text-sm font-bold shadow-md shadow-purple-900/20 transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>+ เพิ่มเจ้าหน้าที่การเงิน / ผู้ใช้งาน</span>
            </button>

            <a
                href="{{ route('activity-logs.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold border border-slate-200 transition"
            >
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>ดูประวัติการใช้งาน (Logs)</span>
            </a>
        </div>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500">บัญชีผู้ใช้งานทั้งหมด</div>
                <div class="text-2xl font-extrabold text-slate-900 font-num mt-1">{{ number_format($totalUsers) }} <span class="text-xs font-normal text-slate-500">บัญชี</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">รวมผู้ดูแลและบุคลากรทุกประเภท</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-indigo-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-indigo-700">Admin & เจ้าหน้าที่การเงิน</div>
                <div class="text-2xl font-extrabold text-indigo-900 font-num mt-1">{{ number_format($staffCount) }} <span class="text-xs font-normal text-indigo-600">คน</span></div>
                <div class="text-[11px] text-indigo-500 mt-0.5">มีสิทธิ์จัดการระบบและเงินเดือน</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-amber-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-amber-800">สถานะรหัสผ่าน</div>
                <div class="text-lg font-extrabold text-slate-900 font-num mt-1">
                    รหัสเริ่มต้น: <span class="text-amber-700">{{ number_format($defaultPasswordCount) }}</span>
                </div>
                <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">
                    ตั้งรหัสเองแล้ว: {{ number_format($customPasswordCount) }} คน
                </div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-emerald-700">เข้าใช้งานระบบวันนี้</div>
                <div class="text-2xl font-extrabold text-emerald-800 font-num mt-1">{{ number_format($loggedInTodayCount) }} <span class="text-xs font-normal text-emerald-600">คน</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">บันทึกเวลาเข้าใช้งานอัตโนมัติ</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Filter Tabs & Search Form --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-4">
        {{-- Group Tabs --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <a
                    href="{{ route('users.index', ['pwd_status' => $pwdStatus, 'q' => $search]) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $group === '' ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    ทั้งหมด ({{ $totalUsers }})
                </a>
                <a
                    href="{{ route('users.index', ['group' => 'staff', 'pwd_status' => $pwdStatus, 'q' => $search]) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $group === 'staff' ? 'bg-indigo-700 text-white shadow-xs' : 'bg-indigo-50 text-indigo-800 hover:bg-indigo-100' }}"
                >
                    🛡️ Admin & จนท.การเงิน ({{ $staffCount }})
                </a>
                <a
                    href="{{ route('users.index', ['group' => 'ข้าราชการ', 'pwd_status' => $pwdStatus, 'q' => $search]) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $group === 'ข้าราชการ' ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    ข้าราชการ ({{ $civilCount }})
                </a>
                <a
                    href="{{ route('users.index', ['group' => 'พนักงานราชการ', 'pwd_status' => $pwdStatus, 'q' => $search]) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $group === 'พนักงานราชการ' ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    พนักงานราชการ ({{ $govCount }})
                </a>
                <a
                    href="{{ route('users.index', ['group' => 'ลูกจ้างประจำ', 'pwd_status' => $pwdStatus, 'q' => $search]) }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $group === 'ลูกจ้างประจำ' ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    ลูกจ้างประจำ ({{ $permCount }})
                </a>
            </div>

            <button
                type="button"
                id="toggleAllPasswordsBtn"
                onclick="toggleAllPasswords()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 transition cursor-pointer"
            >
                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span id="toggleAllPasswordsText">แสดงรหัสผ่านทั้งหมดในหน้านี้</span>
            </button>
        </div>

        {{-- Search & Password Filter --}}
        <form action="{{ route('users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <input type="hidden" name="group" value="{{ $group }}">

            <div class="md:col-span-6">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="ค้นหาชื่อ-สกุล, Username, เลขบัตรประชาชน 13 หลัก หรือ เลขบัญชี..."
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                >
            </div>

            <div class="md:col-span-3">
                <select
                    name="pwd_status"
                    onchange="this.form.submit()"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white"
                >
                    <option value="">-- สถานะรหัสผ่านทั้งหมด --</option>
                    <option value="default" {{ $pwdStatus === 'default' ? 'selected' : '' }}>ใช้รหัสเริ่มต้น (เลขบัตร ปชช. / 1234) ({{ $defaultPasswordCount }})</option>
                    <option value="custom" {{ $pwdStatus === 'custom' ? 'selected' : '' }}>เปลี่ยนรหัสใหม่แล้ว ({{ $customPasswordCount }})</option>
                </select>
            </div>

            <div class="md:col-span-3 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-purple-900 hover:bg-purple-950 text-white text-xs sm:text-sm font-bold transition"
                >
                    ค้นหา
                </button>
                @if($search !== '' || $pwdStatus !== '' || $group !== '')
                    <a
                        href="{{ route('users.index') }}"
                        class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-semibold transition"
                    >
                        ล้างค่า
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">ชื่อ-นามสกุล / สังกัด</th>
                        <th class="py-3.5 px-4">สิทธิ์การใช้งาน (Role)</th>
                        <th class="py-3.5 px-4">User เข้าสู่ระบบ (เลขบัญชี / Username)</th>
                        <th class="py-3.5 px-4">Password (เริ่มต้น: เลขบัตร 13 หลัก)</th>
                        <th class="py-3.5 px-4">เข้าใช้งานล่าสุด</th>
                        <th class="py-3.5 px-4 text-right">จัดการรหัส / แก้ไข</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($users as $idx => $u)
                        @php
                            $realPass = $u->visible_password ?: $u->getDefaultPasswordValue();
                            $isDefaultPass = $u->isDefaultPassword();
                            $defaultTargetPass = $u->getDefaultPasswordValue();
                            $editPayload = [
                                'id' => $u->id,
                                'name' => $u->name,
                                'username' => $u->username,
                                'role' => $u->role,
                                'citizen_id' => $u->citizen_id,
                                'bank_account' => $u->bank_account,
                                'position' => $u->position,
                                'level_or_district' => $u->level_or_district,
                                'visible_password' => $realPass,
                                'default_password' => $defaultTargetPass,
                                'update_url' => route('users.update', $u),
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition {{ in_array($u->role, ['admin', 'finance'], true) ? 'bg-indigo-50/25' : '' }}">
                            <td class="py-3.5 px-4 text-center font-num text-xs text-slate-400">
                                {{ $users->firstItem() + $idx }}
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>{{ $u->name }}</span>
                                    @if($u->id === auth()->id())
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">คุณ</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-1.5">
                                    @if($u->position)
                                        <span>{{ $u->position }}</span>
                                    @endif
                                    @if($u->level_or_district)
                                        <span class="text-slate-300">•</span>
                                        <span>{{ $u->level_or_district }}</span>
                                    @endif
                                    @if(!in_array($u->role, ['admin', 'finance'], true))
                                        <span class="text-slate-300">•</span>
                                        <span class="text-purple-700 font-medium">{{ $u->employee_type }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $u->role_badge_class }}">
                                    @if($u->role === 'admin')
                                        👑 ผู้ดูแลระบบ (Admin)
                                    @elseif($u->role === 'finance')
                                        🛡️ เจ้าหน้าที่การเงิน
                                    @else
                                        👤 บุคลากร
                                    @endif
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="space-y-1 font-num text-xs">
                                    @if($u->bank_account)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">User (เลขบัญชี)</span>
                                            <span class="font-bold text-slate-900 select-all">{{ $u->bank_account }}</span>
                                        </div>
                                    @endif
                                    @if($u->username)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 font-bold text-[10px]">Username</span>
                                            <span class="font-bold text-slate-900 select-all">{{ $u->username }}</span>
                                        </div>
                                    @endif
                                    @if($u->citizen_id)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold text-[10px]">บัตร ปชช.</span>
                                            <span class="text-slate-600 select-all">{{ $u->citizen_id }}</span>
                                        </div>
                                    @endif
                                    @if(!$u->username && !$u->citizen_id && !$u->bank_account)
                                        <span class="text-rose-500 text-xs font-semibold">⚠️ ยังไม่มีรหัสเข้าใช้</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 font-num text-xs font-bold text-slate-800">
                                        <span
                                            id="pwd-mask-{{ $u->id }}"
                                            class="pwd-mask tracking-widest text-slate-400"
                                        >••••••••</span>
                                        <span
                                            id="pwd-val-{{ $u->id }}"
                                            class="pwd-val hidden text-purple-900 select-all"
                                        >{{ $realPass }}</span>
                                    </div>

                                    <button
                                        type="button"
                                        onclick="toggleSinglePassword({{ $u->id }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-purple-800 hover:bg-purple-50 transition cursor-pointer"
                                        title="ดู/ซ่อนรหัสผ่าน"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-1">
                                    @if($isDefaultPass)
                                        @if(!empty($u->citizen_id) && $realPass === preg_replace('/\D/', '', (string) $u->citizen_id))
                                            <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200/70">
                                                เริ่มต้น (เลขบัตร 13 หลัก)
                                            </span>
                                        @else
                                            <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200/70">
                                                เริ่มต้น (1234 • รอเลขบัตร)
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/70">
                                            กำหนดรหัสใหม่แล้ว
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-xs text-slate-500">
                                @if($u->last_login_at)
                                    <div class="font-semibold text-slate-700 font-num">
                                        {{ $u->last_login_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-num">
                                        IP: {{ $u->last_login_ip ?: '-' }}
                                    </div>
                                @else
                                    <span class="text-slate-400">ยังไม่เคยเข้าใช้งาน</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    {{-- Quick Reset to Default (Citizen ID 13 digits or 1234) --}}
                                    <form
                                        action="{{ route('users.reset-password', $u) }}"
                                        method="POST"
                                        onsubmit="return confirm('ยืนยันการรีเซ็ตรหัสผ่านของ {{ $u->name }} กลับเป็นค่าเริ่มต้น ({{ $defaultTargetPass }}) ?')"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition cursor-pointer"
                                            title="รีเซ็ตรหัสเป็นค่าเริ่มต้น ({{ $defaultTargetPass }})"
                                        >
                                            🔄 Reset ค่าเริ่มต้น
                                        </button>
                                    </form>

                                    {{-- Edit User / Role / Password Modal Trigger --}}
                                    <button
                                        type="button"
                                        data-user="{{ json_encode($editPayload) }}"
                                        onclick="openEditUserModal(JSON.parse(this.dataset.user))"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 transition cursor-pointer"
                                    >
                                        ✏️ แก้ไข / ตั้งรหัส
                                    </button>

                                    {{-- Delete User (Only if no payslips and not self) --}}
                                    @if($u->id !== auth()->id() && $u->payslips_count == 0)
                                        <form
                                            action="{{ route('users.destroy', $u) }}"
                                            method="POST"
                                            onsubmit="return confirm('ยืนยันการลบบัญชีผู้ใช้งาน {{ $u->name }} ?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="px-2 py-1.5 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition cursor-pointer"
                                                title="ลบผู้ใช้งาน"
                                            >
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-sm">
                                ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไขที่ค้นหา
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Recent Activity Logs Preview --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">ประวัติการเข้าใช้งานและกิจกรรมล่าสุด (Recent Logs)</h3>
                <p class="text-xs text-slate-500">บันทึกการเข้าสู่ระบบ การเปลี่ยนรหัสผ่าน และการจัดการผู้ใช้งานล่าสุด</p>
            </div>
            <a
                href="{{ route('activity-logs.index') }}"
                class="text-xs font-bold text-purple-800 hover:text-purple-950 underline"
            >
                ดูประวัติทั้งหมด &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold">
                        <th class="py-2.5 px-3">วัน-เวลา</th>
                        <th class="py-2.5 px-3">ผู้ทำรายการ</th>
                        <th class="py-2.5 px-3">ประเภทกิจกรรม</th>
                        <th class="py-2.5 px-3">รายละเอียด</th>
                        <th class="py-2.5 px-3">IP / อุปกรณ์</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2.5 px-3 font-num text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">
                                {{ $log->user_name ?: 'ไม่ระบุตัวตน' }}
                                <span class="text-[10px] font-normal text-slate-400">({{ $log->role_label }})</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $log->action_badge_class }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ $log->description }}
                            </td>
                            <td class="py-2.5 px-3 font-num text-slate-400 whitespace-nowrap">
                                {{ $log->ip_address ?: '-' }} • {{ $log->device_summary }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">ยังไม่มีประวัติการใช้งาน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal 1: Add New User / Finance Officer --}}
<div
    id="addUserModal"
    class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs hidden flex items-center justify-center p-4"
>
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5 font-bold text-slate-900 text-base">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div>
                    <div>เพิ่มผู้ใช้งาน / เจ้าหน้าที่การเงิน</div>
                    <div class="text-xs font-normal text-slate-500">สร้างบัญชีเข้าสู่ระบบใหม่พร้อมกำหนดสิทธิ์</div>
                </div>
            </div>
            <button type="button" onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 leading-none">&times;</button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    สิทธิ์การใช้งานระบบ (Role) <span class="text-rose-500">*</span>
                </label>
                <select
                    name="role"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-purple-600 outline-none bg-white"
                >
                    <option value="finance">🛡️ เจ้าหน้าที่การเงิน (ทำงานจัดการระบบและเงินเดือนได้เหมือน Admin)</option>
                    <option value="admin">👑 ผู้ดูแลระบบ (Admin)</option>
                    <option value="employee">👤 บุคลากรทั่วไป (ดูเฉพาะสลิปของตนเอง)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        ชื่อผู้ใช้สำหรับล็อกอิน (Username) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="username"
                        required
                        placeholder="เช่น finance01, admin2"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        รหัสผ่านเริ่มต้น <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="password"
                        value="1234"
                        required
                        minlength="4"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                    >
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    ชื่อ-นามสกุล <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    required
                    placeholder="ระบุชื่อ-นามสกุลของเจ้าหน้าที่"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        ตำแหน่ง (ระบุหรือไม่ก็ได้)
                    </label>
                    <input
                        type="text"
                        name="position"
                        placeholder="เช่น นักวิชาการเงินและบัญชี"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        เลขบัตรประชาชน 13 หลัก (ถ้ามี)
                    </label>
                    <input
                        type="text"
                        name="citizen_id"
                        maxlength="20"
                        placeholder="ใช้ล็อกอินแทน Username ได้"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                    type="button"
                    onclick="closeAddUserModal()"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    ยกเลิก
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold bg-purple-900 hover:bg-purple-950 text-white shadow-xs transition cursor-pointer"
                >
                    บันทึกเพิ่มผู้ใช้งาน
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 2: Edit User / Role / Reset Custom Password --}}
<div
    id="editUserModal"
    class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs hidden flex items-center justify-center p-4"
>
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5 font-bold text-slate-900 text-base">
                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <div>แก้ไขผู้ใช้งาน / สิทธิ์ / รหัสผ่าน</div>
                    <div id="editModalSubtitle" class="text-xs font-normal text-slate-500"></div>
                </div>
            </div>
            <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 leading-none">&times;</button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    สิทธิ์การใช้งานระบบ (Role) <span class="text-rose-500">*</span>
                </label>
                <select
                    id="edit_role"
                    name="role"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-purple-600 outline-none bg-white"
                >
                    <option value="employee">👤 บุคลากรทั่วไป (ดูสลิปของตนเอง)</option>
                    <option value="finance">🛡️ เจ้าหน้าที่การเงิน (ทำงานได้เหมือน Admin)</option>
                    <option value="admin">👑 ผู้ดูแลระบบ (Admin)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    ชื่อ-นามสกุล <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="edit_name"
                    name="name"
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        ชื่อผู้ใช้ (Username)
                    </label>
                    <input
                        type="text"
                        id="edit_username"
                        name="username"
                        placeholder="เช่น admin, finance01"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        เลขบัตรประชาชน 13 หลัก
                    </label>
                    <input
                        type="text"
                        id="edit_citizen_id"
                        name="citizen_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        เลขที่บัญชีธนาคาร
                    </label>
                    <input
                        type="text"
                        id="edit_bank_account"
                        name="bank_account"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        ตำแหน่ง
                    </label>
                    <input
                        type="text"
                        id="edit_position"
                        name="position"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                    >
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-purple-50/70 border border-purple-200/80 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-purple-950">🔑 รหัสผ่านปัจจุบันของบัญชีนี้:</span>
                    <span id="edit_current_visible_password" class="font-num font-extrabold text-purple-900 bg-white px-2.5 py-0.5 rounded border border-purple-200"></span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        ตั้งรหัสผ่านใหม่ (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            id="edit_new_password"
                            name="new_password"
                            minlength="4"
                            placeholder="ระบุรหัสผ่านใหม่ที่ต้องการตั้ง (อย่างน้อย 4 ตัวอักษร)"
                            class="flex-1 px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                        >
                        <button
                            type="button"
                            id="edit_use_default_pwd_btn"
                            onclick="document.getElementById('edit_new_password').value = (document.getElementById('edit_citizen_id').value.replace(/\D/g, '') || '1234')"
                            class="px-3 py-2 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold transition shrink-0 cursor-pointer"
                        >
                            ใช้รหัสเริ่มต้น (เลขบัตร/1234)
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                    type="button"
                    onclick="closeEditUserModal()"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    ยกเลิก
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold bg-purple-900 hover:bg-purple-950 text-white shadow-xs transition cursor-pointer"
                >
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let allPasswordsVisible = false;

    function toggleSinglePassword(userId) {
        const mask = document.getElementById('pwd-mask-' + userId);
        const val = document.getElementById('pwd-val-' + userId);
        if (mask && val) {
            mask.classList.toggle('hidden');
            val.classList.toggle('hidden');
        }
    }

    function toggleAllPasswords() {
        allPasswordsVisible = !allPasswordsVisible;
        const masks = document.querySelectorAll('.pwd-mask');
        const vals = document.querySelectorAll('.pwd-val');
        const btnText = document.getElementById('toggleAllPasswordsText');

        masks.forEach(el => {
            if (allPasswordsVisible) el.classList.add('hidden');
            else el.classList.remove('hidden');
        });
        vals.forEach(el => {
            if (allPasswordsVisible) el.classList.remove('hidden');
            else el.classList.add('hidden');
        });

        if (btnText) {
            btnText.textContent = allPasswordsVisible
                ? 'ซ่อนรหัสผ่านทั้งหมด'
                : 'แสดงรหัสผ่านทั้งหมดในหน้านี้';
        }
    }

    function openAddUserModal() {
        const modal = document.getElementById('addUserModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeAddUserModal() {
        const modal = document.getElementById('addUserModal');
        if (modal) modal.classList.add('hidden');
    }

    function openEditUserModal(user) {
        const modal = document.getElementById('editUserModal');
        const form = document.getElementById('editUserForm');
        if (!modal || !form) return;

        form.action = user.update_url;
        document.getElementById('editModalSubtitle').textContent = user.name;
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_username').value = user.username || '';
        document.getElementById('edit_role').value = user.role || 'employee';
        document.getElementById('edit_citizen_id').value = user.citizen_id || '';
        document.getElementById('edit_bank_account').value = user.bank_account || '';
        document.getElementById('edit_position').value = user.position || '';
        document.getElementById('edit_current_visible_password').textContent = user.visible_password || '1234';
        document.getElementById('edit_new_password').value = '';

        modal.classList.remove('hidden');
    }

    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
