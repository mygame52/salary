@extends('layouts.app')

@section('title', 'ประวัติการเข้าใช้งานและกิจกรรมในระบบ (Activity Logs)')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-800 text-xs font-bold border border-purple-200/60">
                <span>📋 ระบบบันทึกประวัติการเข้าใช้งาน (Audit & Access Logs)</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">
                ประวัติการเข้าใช้งานระบบและการทำรายการของผู้ใช้
            </h2>
            <p class="text-xs sm:text-sm text-slate-500">
                ติดตามการเข้าสู่ระบบ ออกจากระบบ การเปลี่ยน/รีเซ็ตรหัสผ่าน การจัดการผู้ใช้ และการนำเข้าข้อมูลเงินเดือน พร้อม IP Address และอุปกรณ์
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a
                href="{{ route('users.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-900 hover:bg-purple-950 text-white text-xs sm:text-sm font-bold shadow-xs transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>ไปหน้าจัดการผู้ใช้งาน & รหัสผ่าน</span>
            </a>
        </div>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500">รายการบันทึกทั้งหมด</div>
                <div class="text-2xl font-extrabold text-slate-900 font-num mt-1">{{ number_format($totalLogs) }} <span class="text-xs font-normal text-slate-500">รายการ</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">บันทึกทุกกิจกรรมสำคัญอัตโนมัติ</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-emerald-700">เข้าสู่ระบบสำเร็จ (วันนี้ / สะสม)</div>
                <div class="text-2xl font-extrabold text-emerald-800 font-num mt-1">
                    {{ number_format($loginTodayCount) }} <span class="text-sm font-normal text-slate-400">/ {{ number_format($loginSuccessCount) }}</span>
                </div>
                <div class="text-[11px] text-emerald-600 mt-0.5">ครั้งที่เข้าใช้งานสำเร็จ</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-amber-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-amber-800">เปลี่ยน / รีเซ็ตรหัสผ่าน</div>
                <div class="text-2xl font-extrabold text-amber-900 font-num mt-1">{{ number_format($passwordEventsCount) }} <span class="text-xs font-normal text-amber-700">ครั้ง</span></div>
                <div class="text-[11px] text-amber-600 mt-0.5">ทั้งเปลี่ยนเองและรีเซ็ตโดย Admin</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-rose-200/80 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-rose-700">ล็อกอินไม่สำเร็จ</div>
                <div class="text-2xl font-extrabold text-rose-800 font-num mt-1">{{ number_format($loginFailedCount) }} <span class="text-xs font-normal text-rose-600">ครั้ง</span></div>
                <div class="text-[11px] text-rose-500 mt-0.5">รหัสผ่านผิดหรือไม่พบบัญชี</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center gap-1.5 border-b border-slate-100 pb-3">
            <a
                href="{{ route('activity-logs.index', ['role' => $role, 'date' => $date, 'q' => $search]) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $action === '' ? 'bg-purple-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
            >
                ทั้งหมด
            </a>
            <a
                href="{{ route('activity-logs.index', ['action' => 'auth', 'role' => $role, 'date' => $date, 'q' => $search]) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $action === 'auth' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}"
            >
                🟢 เข้า - ออกจากระบบ
            </a>
            <a
                href="{{ route('activity-logs.index', ['action' => 'login_failed', 'role' => $role, 'date' => $date, 'q' => $search]) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $action === 'login_failed' ? 'bg-rose-700 text-white shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}"
            >
                🔴 ล็อกอินไม่สำเร็จ
            </a>
            <a
                href="{{ route('activity-logs.index', ['action' => 'user_mgmt', 'role' => $role, 'date' => $date, 'q' => $search]) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $action === 'user_mgmt' ? 'bg-indigo-700 text-white shadow-xs' : 'bg-indigo-50 text-indigo-800 hover:bg-indigo-100' }}"
            >
                🔑 จัดการผู้ใช้ & รหัสผ่าน
            </a>
            <a
                href="{{ route('activity-logs.index', ['action' => 'payroll', 'role' => $role, 'date' => $date, 'q' => $search]) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $action === 'payroll' ? 'bg-teal-700 text-white shadow-xs' : 'bg-teal-50 text-teal-800 hover:bg-teal-100' }}"
            >
                📊 นำเข้า / ลบงวดเงินเดือน
            </a>
        </div>

        <form action="{{ route('activity-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <input type="hidden" name="action" value="{{ $action }}">

            <div class="md:col-span-5">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="ค้นหาชื่อผู้ใช้งาน, รายละเอียด, หรือ IP Address..."
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                >
            </div>

            <div class="md:col-span-3">
                <select
                    name="role"
                    onchange="this.form.submit()"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white"
                >
                    <option value="">-- ทุกกลุ่มผู้ใช้งาน --</option>
                    <option value="staff" {{ $role === 'staff' ? 'selected' : '' }}>Admin & เจ้าหน้าที่การเงิน</option>
                    <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>บุคลากรทั่วไป</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <input
                    type="date"
                    name="date"
                    value="{{ $date }}"
                    onchange="this.form.submit()"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-purple-600 outline-none font-num"
                >
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-purple-900 hover:bg-purple-950 text-white text-xs sm:text-sm font-bold transition"
                >
                    ค้นหา
                </button>
                @if($search !== '' || $role !== '' || $date !== '' || $action !== '')
                    <a
                        href="{{ route('activity-logs.index') }}"
                        class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition"
                    >
                        ล้าง
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Activity Logs Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4">วัน - เวลา</th>
                        <th class="py-3.5 px-4">ผู้ทำรายการ (User)</th>
                        <th class="py-3.5 px-4">สิทธิ์</th>
                        <th class="py-3.5 px-4">กิจกรรม</th>
                        <th class="py-3.5 px-4">รายละเอียดการทำรายการ</th>
                        <th class="py-3.5 px-4">IP Address / อุปกรณ์</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-num text-xs text-slate-600 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-slate-400">{{ $log->created_at->format('H:i:s น.') }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">
                                    {{ $log->user_name ?: 'ไม่ระบุตัวตน' }}
                                </div>
                                @if($log->target_name && $log->target_name !== $log->user_name)
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        เป้าหมาย: <span class="font-semibold text-purple-800">{{ $log->target_name }}</span>
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($log->user_role === 'admin')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        ผู้ดูแลระบบ (Admin)
                                    </span>
                                @elseif($log->user_role === 'finance')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        เจ้าหน้าที่การเงิน
                                    </span>
                                @elseif($log->user_role === 'employee')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        บุคลากร
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $log->action_badge_class }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $log->description }}
                            </td>

                            <td class="py-3.5 px-4 text-xs text-slate-500 whitespace-nowrap font-num">
                                <div class="font-semibold text-slate-700">IP: {{ $log->ip_address ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->device_summary }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-sm">
                                ยังไม่มีบันทึกประวัติการใช้งานตามเงื่อนไขที่เลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
