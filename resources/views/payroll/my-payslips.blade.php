@extends('layouts.app')

@section('title', 'สลิปเงินเดือนของฉัน - ' . $user->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Personnel Hero Profile Card --}}
    <div class="bg-gradient-to-br from-[#0c1322] via-[#161f38] to-[#1e1338] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        {{-- Background ambient glow --}}
        <div class="absolute -right-12 -top-12 w-80 h-80 rounded-full bg-purple-600/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-80 h-80 rounded-full bg-indigo-600/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            {{-- Top Row: Identity & Badges --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-purple-200/80">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช</span>
                    <span class="text-slate-500">•</span>
                    <span>ระบบ e-PaySlip บุคลากร</span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="openPasswordModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition border border-white/10 shadow-xs"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>เปลี่ยนรหัสผ่าน</span>
                    </button>
                </div>
            </div>

            {{-- Middle Row: Avatar + Name + Category + Position --}}
            <div class="flex flex-wrap items-center justify-between gap-6 pt-1">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-500 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center shrink-0 shadow-lg ring-4 ring-white/10">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                                {{ $user->name }}
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->employee_type === 'ข้าราชการ' ? 'bg-amber-400/20 text-amber-300 border border-amber-400/30' : ($user->employee_type === 'ลูกจ้างประจำ' ? 'bg-emerald-400/20 text-emerald-300 border border-emerald-400/30' : 'bg-blue-400/20 text-blue-300 border border-blue-400/30') }}">
                                {{ $user->employee_type }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-300">
                            ตำแหน่ง: <strong class="text-white">{{ $user->position ?: 'นักวิชาการศึกษา' }}</strong>
                            @if($user->level_or_district)
                                <span class="text-purple-200">({{ $user->level_or_district }})</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Official Identification Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs w-full lg:w-auto">
                    <div class="bg-white/5 backdrop-blur-md rounded-2xl p-3 border border-white/10 space-y-0.5 min-w-[200px]">
                        <div class="text-[11px] text-slate-400 font-medium">เลขประจำตัว 13 หลัก</div>
                        <div class="font-mono font-bold text-white text-sm">
                            {{ $user->citizen_id ?: 'ยังไม่ระบุ' }}
                        </div>
                    </div>

                    <div class="bg-white/5 backdrop-blur-md rounded-2xl p-3 border border-white/10 space-y-0.5 min-w-[200px]">
                        <div class="text-[11px] text-slate-400 font-medium">เลขที่บัญชีรับเงินโอน</div>
                        <div class="font-mono font-bold text-emerald-400 text-sm">
                            {{ $user->formatted_bank_account }}
                        </div>
                        <div class="text-[10px] text-slate-400">{{ $user->bank_name ?: 'ธ.กรุงไทย' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Overview Summary Cards (YTD) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Net Pay YTD --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">เงินได้สุทธิสะสมรวม</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1 font-num">
                    {{ number_format($totalNetPayYtd, 2) }} <span class="text-xs font-normal text-slate-500">บ.</span>
                </div>
                <div class="text-[11px] text-emerald-600 font-medium mt-0.5">ยอดรับสุทธิจริงทุกงวด</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        {{-- Card 2: Total Income YTD --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">รายรับรวมสะสม</div>
                <div class="text-2xl font-bold text-slate-800 mt-1 font-num">
                    {{ number_format($totalIncomeYtd, 2) }} <span class="text-xs font-normal text-slate-500">บ.</span>
                </div>
                <div class="text-[11px] text-purple-700 font-medium mt-0.5">เงินเดือนและเงินประจำตำแหน่ง</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-800 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        {{-- Card 3: Total Deductions YTD --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">เงินหักสะสมรวม</div>
                <div class="text-2xl font-bold text-rose-600 mt-1 font-num">
                    {{ number_format($totalDeductionYtd, 2) }} <span class="text-xs font-normal text-slate-500">บ.</span>
                </div>
                <div class="text-[11px] text-rose-500 font-medium mt-0.5">ภาษี กบข. สหกรณ์ ฯลฯ</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
        </div>

        {{-- Card 4: Total Payslips Count --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">สลิปเงินเดือนในระบบ</div>
                <div class="text-2xl font-bold text-indigo-950 mt-1 font-num">
                    {{ $payslips->count() }} <span class="text-xs font-normal text-slate-500">งวด</span>
                </div>
                <div class="text-[11px] text-indigo-600 font-medium mt-0.5">พร้อมเปิดดูและพิมพ์ได้ตลอดเวลา</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    {{-- Highlight: Latest Monthly Payslip Feature Card --}}
    @if($latestPayslip)
        <div class="bg-gradient-to-r from-purple-900 to-indigo-900 rounded-3xl p-6 sm:p-7 text-white shadow-md flex flex-wrap items-center justify-between gap-5">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-slate-900">
                    <span>งวดเงินเดือนล่าสุด</span>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold text-white">
                    ประจำเดือน {{ $latestPayslip->batch?->month_name }} พ.ศ. {{ $latestPayslip->batch?->year_be }}
                </h3>
                <div class="flex flex-wrap items-center gap-4 text-xs text-purple-200 pt-1">
                    <span>วันที่จ่าย: <strong class="text-white">{{ $latestPayslip->batch?->pay_date_text }}</strong></span>
                    <span>•</span>
                    <span>รายได้: <strong class="text-white font-num">{{ number_format($latestPayslip->total_income, 2) }}</strong> บ.</span>
                    <span>•</span>
                    <span>รายการหัก: <strong class="text-rose-300 font-num">{{ number_format($latestPayslip->total_deduction, 2) }}</strong> บ.</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-[11px] text-purple-200">เงินได้สุทธิเข้าบัญชี</div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-amber-300 font-num">
                        {{ number_format($latestPayslip->net_pay, 2) }} <span class="text-sm font-normal text-white">บาท</span>
                    </div>
                </div>

                <a
                    href="{{ route('payslips.show', $latestPayslip) }}"
                    class="bg-white hover:bg-slate-100 text-purple-950 font-bold px-5 py-3 rounded-2xl shadow-md transition flex items-center gap-2 text-xs sm:text-sm whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>เปิดดู / พิมพ์สลิปนี้</span>
                </a>
            </div>
        </div>
    @endif

    {{-- Monthly Payslip History Table Card --}}
    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h2 class="font-bold text-base text-slate-800">ประวัติสลิปเงินเดือนรายงวดทั้งหมด</h2>
                <p class="text-xs text-slate-500 mt-0.5">เลือกงวดที่ต้องการเพื่อเปิดดูรายละเอียดฉบับเต็ม สั่งพิมพ์ หรือบันทึกเป็นไฟล์ PDF ทางการ</p>
            </div>
            <div class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-purple-50 text-purple-900 border border-purple-200">
                รวมทั้งหมด {{ $payslips->count() }} งวดเดือน
            </div>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 text-xs uppercase font-bold tracking-wider">
                        <th class="py-3.5 px-5">งวดประจำเดือน</th>
                        <th class="py-3.5 px-4">วันที่จ่าย</th>
                        <th class="py-3.5 px-4 text-right">รวมรายได้ (บาท)</th>
                        <th class="py-3.5 px-4 text-right">รวมเงินหัก (บาท)</th>
                        <th class="py-3.5 px-4 text-right">เงินได้สุทธิ (บาท)</th>
                        <th class="py-3.5 px-5 text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                        <tr class="hover:bg-purple-50/30 transition">
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900 text-base">
                                    {{ $slip->batch?->month_name }} {{ $slip->batch?->year_be }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    รอบ: {{ $slip->batch?->period_text }}
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600 font-medium">
                                {{ $slip->batch?->pay_date_text }}
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums text-slate-700 font-num font-medium">
                                {{ number_format((float) $slip->total_income, 2) }}
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums text-rose-600 font-num font-medium">
                                {{ number_format((float) $slip->total_deduction, 2) }}
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums font-bold text-emerald-700 text-base font-num">
                                {{ number_format((float) $slip->net_pay, 2) }}
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a
                                    href="{{ route('payslips.show', $slip) }}"
                                    class="inline-flex items-center gap-1.5 bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>เปิดดู / พิมพ์</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="text-base font-semibold">ยังไม่มีข้อมูลสลิปเงินเดือนในระบบ</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="sm:hidden divide-y divide-slate-100">
            @forelse($payslips as $slip)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-900 text-base">
                                {{ $slip->batch?->month_name }} {{ $slip->batch?->year_be }}
                            </div>
                            <div class="text-[11px] text-slate-400">จ่ายเมื่อ {{ $slip->batch?->pay_date_text }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500">สุทธิ</div>
                            <div class="font-bold text-emerald-700 text-lg font-num">
                                {{ number_format((float) $slip->net_pay, 2) }} <span class="text-xs">บ.</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-2.5 rounded-xl">
                        <div>
                            <span class="text-slate-500">รวมรายรับ:</span>
                            <span class="font-bold text-slate-800 ml-1 font-num">{{ number_format((float) $slip->total_income, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">รวมเงินหัก:</span>
                            <span class="font-bold text-rose-600 ml-1 font-num">{{ number_format((float) $slip->total_deduction, 2) }}</span>
                        </div>
                    </div>

                    <a
                        href="{{ route('payslips.show', $slip) }}"
                        class="w-full inline-flex items-center justify-center gap-1.5 bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-xs transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>เปิดดูสลิปเงินเดือน / สั่งพิมพ์</span>
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    ยังไม่มีข้อมูลสลิปเงินเดือนในระบบ
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
