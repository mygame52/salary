@extends('layouts.app')

@section('title', 'หน้าหลักเจ้าหน้าที่การเงิน - ระบบสลิปเงินเดือนออนไลน์')

@section('content')
<div class="space-y-6">
    {{-- 1. ส่วนสรุปยอดภาพรวม (Executive Summary Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">งวดเดือนในระบบ</div>
                <div class="text-2xl font-bold text-purple-950 mt-1">{{ number_format($stats['total_batches']) }} งวด</div>
                <div class="text-[12px] text-purple-700 font-medium mt-0.5">ปี พ.ศ. {{ $years->first() ?? '2569' }}</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-800 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">สลิปเงินเดือนทั้งหมด</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($stats['total_payslips']) }} ใบ</div>
                <div class="text-[12px] text-emerald-600 font-medium mt-0.5">พร้อมพิมพ์และตรวจสอบ</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">ข้าราชการในระบบ</div>
                <div class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($stats['civil_servants']) }} คน</div>
                <div class="text-[12px] text-blue-600 font-medium mt-0.5">สกร.ประจำจังหวัดนครศรีธรรมราช</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">ยอดเบิกจ่ายสุทธิสะสมรวม</div>
                <div class="text-2xl font-bold text-slate-900 mt-1 font-num">{{ number_format($stats['total_net_payout'] ?? 0, 2) }} <span class="text-xs font-normal text-slate-500">บาท</span></div>
                <div class="text-[12px] text-slate-500 font-medium mt-1 flex items-center justify-between gap-1">
                    <span>ยอดรวมทุกงวด</span>
                    <a href="{{ route('reports.annual') }}" class="text-xs text-purple-700 hover:text-purple-900 font-bold hover:underline inline-flex items-center gap-0.5" title="เปิดหน้ารายงานสรุปการเบิกจ่ายรายปี">
                        <span>ดูกราฟรายปี</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- System Alert Banner if missing citizen ID --}}
    @if(($systemNotifications['missing_citizen_id'] ?? 0) > 0)
        <div class="bg-gradient-to-r from-rose-500 via-rose-600 to-amber-600 rounded-2xl p-4 sm:p-5 text-white shadow-md flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center shrink-0 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-sm sm:text-base flex items-center gap-2">
                        <span>แจ้งเตือนข้อมูลบุคลากร: มีข้าราชการ {{ number_format($systemNotifications['missing_citizen_id']) }} คน ที่ยังไม่ได้ระบุเลขประจำตัว 13 หลัก</span>
                    </div>
                    <div class="text-xs text-rose-100 mt-0.5">
                        หากไม่ระบุเลข 13 หลัก จะไม่สามารถพิมพ์เลขประจำตัวผู้เสียภาษีบนสลิป และข้าราชการจะไม่สามารถใช้เลข 13 หลักเข้าสู่ระบบได้
                    </div>
                </div>
            </div>
            <a
                href="{{ route('employees.index', ['filter' => 'missing_citizen_id']) }}"
                class="px-4 py-2 rounded-xl bg-white text-rose-700 hover:bg-rose-50 font-bold text-xs shadow-sm transition whitespace-nowrap inline-flex items-center gap-1.5"
            >
                <span>กรอกเลข 13 หลักทันที</span>
                <span>&rarr;</span>
            </a>
        </div>
    @endif

    {{-- System Alert Banner if personnel missing payroll > 1 month (Needs Status Review) --}}
    @if(($systemNotifications['needs_status_review'] ?? 0) > 0)
        <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 rounded-2xl p-4 sm:p-5 text-white shadow-md flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center shrink-0 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-sm sm:text-base flex items-center gap-2">
                        <span>แจ้งเตือนสถานะบุคลากร: มี {{ number_format($systemNotifications['needs_status_review']) }} คน ขาดข้อมูลการเงินเกิน 1 เดือน (ยังไม่ได้ระบุสถานะ)</span>
                    </div>
                    <div class="text-xs text-amber-100 mt-0.5">
                        ไม่มีข้อมูลเงินเดือนในงวดปัจจุบันและงวดก่อนหน้า แต่สถานะยังเป็น "ปกติ" โปรดตรวจสอบและระบุสถานะ (เช่น ย้าย / เกษียณ / ลาออก / เสียชีวิต)
                    </div>
                </div>
            </div>
            <a
                href="{{ route('employees.index', ['filter' => 'needs_status_review']) }}"
                class="px-4 py-2 rounded-xl bg-white text-amber-800 hover:bg-amber-50 font-bold text-xs shadow-sm transition whitespace-nowrap inline-flex items-center gap-1.5"
            >
                <span>ตรวจสอบและระบุสถานะ ({{ number_format($systemNotifications['needs_status_review']) }} คน)</span>
                <span>&rarr;</span>
            </a>
        </div>
    @endif

    {{-- 2. พื้นที่แสดงผลส่วนการจัดการเงินเดือน (Dashboard Main Content) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- TAB 1: รายการงวดเงินเดือน (Batches List) --}}
        <div id="tab-content-batches" class="tab-content p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">ตารางรายการงวดเงินเดือนข้าราชการ</h2>
                    <p class="text-xs text-slate-500 mt-0.5">คลิก "ดูรายชื่อ / พิมพ์สลิป" เพื่อเข้าดูสลิปรายบุคคล หรือสั่งพิมพ์รวมทั้งงวดเดือน</p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="switchTab('upload')"
                        class="bg-purple-800 hover:bg-purple-900 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-sm transition inline-flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>อัปโหลดงวดใหม่ / อัปเดตข้อมูล</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-4">ประจำงวดเดือน</th>
                            <th class="py-3 px-4">รอบเงินเดือน / วันที่จ่าย</th>
                            <th class="py-3 px-4">ประเภทบุคลากร</th>
                            <th class="py-3 px-4 text-center">จำนวน (คน)</th>
                            <th class="py-3 px-4 text-right">รวมรายรับ (บาท)</th>
                            <th class="py-3 px-4 text-right">รวมรายจ่าย/หัก (บาท)</th>
                            <th class="py-3 px-4 text-right">ยอดรับสุทธิรวม (บาท)</th>
                            <th class="py-3 px-4 text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $sumEmployees = 0;
                            $sumGross = 0;
                            $sumDeduct = 0;
                            $sumNet = 0;
                        @endphp
                        @forelse($batches as $batch)
                            @php
                                $sumEmployees += $batch->total_employees;
                                $sumGross += (float) $batch->total_income;
                                $sumDeduct += (float) $batch->total_deduction;
                                $sumNet += (float) $batch->total_net_pay;
                            @endphp
                            <tr class="hover:bg-purple-50/30 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-base">{{ $batch->month_name }} {{ $batch->year_be }}</div>
                                    <div class="text-[11px] text-slate-400">อัปเดตเมื่อ {{ $batch->created_at?->format('d/m/Y H:i น.') ?: '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    <div>รอบ: <strong>{{ $batch->period_text }}</strong></div>
                                    <div>จ่าย: <strong>{{ $batch->pay_date_text }}</strong></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($batch->employee_type === 'พนักงานราชการ')
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">พนักงานราชการ</span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900">ข้าราชการ</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-slate-100 text-sm">
                                        {{ number_format($batch->total_employees) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums text-slate-700">
                                    {{ number_format((float) $batch->total_income, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums text-red-600">
                                    {{ number_format((float) $batch->total_deduction, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right tabular-nums font-bold text-emerald-700 text-base">
                                    {{ number_format((float) $batch->total_net_pay, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a
                                            href="{{ route('batches.show', $batch) }}"
                                            class="inline-flex items-center gap-1 bg-purple-800 hover:bg-purple-900 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm transition"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>ดูรายชื่อ / สลิป</span>
                                        </a>
                                        <a
                                            href="{{ route('batches.print', $batch) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-xl transition"
                                            title="พิมพ์สลิปทั้งงวดทุกคน"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>พิมพ์</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500">
                                    <div class="text-base font-semibold">ยังไม่มีข้อมูลงวดเงินเดือนในระบบ</div>
                                    <p class="text-xs text-slate-400 mt-1">สามารถเริ่มต้นด้วยการคลิกแท็บ "อัปโหลดข้อมูลเงินเดือน" ด้านบน</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($batches->isNotEmpty())
                        <tfoot>
                            <tr class="bg-slate-100/90 font-bold border-t-2 border-slate-300 text-slate-800">
                                <td colspan="3" class="py-3 px-4 text-right">ยอดรวมทั้งหมด ({{ $batches->count() }} งวด):</td>
                                <td class="py-3 px-4 text-center">{{ number_format($sumEmployees) }} คน-ครั้ง</td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ number_format($sumGross, 2) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums text-red-600">{{ number_format($sumDeduct, 2) }}</td>
                                <td class="py-3 px-4 text-right tabular-nums text-emerald-800 text-base">{{ number_format($sumNet, 2) }}</td>
                                <td class="py-3 px-4"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- TAB 2: อัปโหลดข้อมูลเงินเดือน (Upload & Import) --}}
        <div id="tab-content-upload" class="tab-content p-6 space-y-6 hidden">
            {{-- Duplicate Detection Warning Card (If Present) --}}
            @if(session('duplicate_warning'))
                @php
                    $warning = session('duplicate_warning');
                    $formData = session('temp_form_data', []);
                    $tempPath = session('temp_file_path');
                @endphp
                <div class="bg-amber-50 border-2 border-amber-400 rounded-2xl p-5 shadow-md space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-amber-500 text-white rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-amber-900">
                                แจ้งเตือน: ตรวจพบข้อมูลงวดเงินเดือนซ้ำซ้อนในระบบ
                            </h2>
                            <p class="text-sm text-amber-800 mt-0.5">
                                ไฟล์ที่คุณเลือก <strong>{{ $formData['original_filename'] ?? '' }}</strong> มีงวดเงินเดือนที่มีข้อมูลบันทึกอยู่ในระบบแล้ว กรุณาตรวจสอบรายละเอียดก่อนดำเนินการ
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto bg-white rounded-xl border border-amber-200">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="bg-amber-100/60 text-amber-900 text-xs uppercase font-bold border-b border-amber-200">
                                    <th class="py-2.5 px-4">งวดเดือนในไฟล์</th>
                                    <th class="py-2.5 px-4">สถานะความซ้ำซ้อน</th>
                                    <th class="py-2.5 px-4">ข้อมูลเดิมในระบบ</th>
                                    <th class="py-2.5 px-4">ข้อมูลใหม่ในไฟล์</th>
                                    <th class="py-2.5 px-4">ตรวจพบชื่อซ้ำในไฟล์</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-100">
                                @foreach($warning['sections'] as $sec)
                                    <tr class="{{ $sec['is_duplicate'] ? 'bg-amber-50/50' : 'bg-white' }}">
                                        <td class="py-2.5 px-4 font-bold text-slate-800">
                                            {{ $sec['employee_type'] }} {{ $sec['month_name'] }} {{ $sec['year_be'] }}
                                        </td>
                                        <td class="py-2.5 px-4">
                                            @if($sec['is_duplicate'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                    มีข้อมูลเดิมแล้ว (ซ้ำ)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                    เป็นงวดใหม่
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-4 text-xs text-slate-600">
                                            @if($sec['existing_batch'])
                                                <div>จำนวน: <strong>{{ $sec['existing_batch']['total_employees'] }} คน</strong></div>
                                                <div>รับสุทธิ: <strong>{{ number_format($sec['existing_batch']['total_net_pay'], 2) }} บ.</strong></div>
                                                <div class="text-[11px] text-slate-400">เมื่อ {{ $sec['existing_batch']['uploaded_at'] }}</div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-4 text-xs text-slate-800">
                                            <div>
                                                จำนวน: <strong>{{ $sec['employee_count'] }} คน</strong>
                                                @if($sec['existing_batch'])
                                                    @php
                                                        $diff = $sec['employee_count'] - $sec['existing_batch']['total_employees'];
                                                    @endphp
                                                    @if($diff > 0)
                                                        <span class="inline-block ml-1 px-1.5 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-700">เพิ่มขึ้น +{{ $diff }} คน</span>
                                                    @elseif($diff < 0)
                                                        <span class="inline-block ml-1 px-1.5 py-0.5 rounded text-[11px] font-bold bg-orange-100 text-orange-700">ลดลง {{ $diff }} คน</span>
                                                    @else
                                                        <span class="inline-block ml-1 px-1.5 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600">เท่าเดิม</span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div>รับสุทธิ: <strong>{{ number_format($sec['total_net_pay'], 2) }} บ.</strong></div>
                                        </td>
                                        <td class="py-2.5 px-4 text-xs">
                                            @if(!empty($sec['duplicate_names']))
                                                <span class="text-red-700 font-medium">พบชื่อซ้ำ {{ count($sec['duplicate_names']) }} คน: {{ implode(', ', $sec['duplicate_names']) }}</span>
                                            @else
                                                <span class="text-emerald-700">ไม่มี</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <div class="text-xs text-amber-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>คำแนะนำ:</strong> หากเป็นการนำไฟล์เดิมมาพิมพ์เพิ่มข้อมูลข้าราชการ ให้กดยืนยันเพื่ออัปเดตสลิปเป็นยอดล่าสุด ระบบจะปรับปรุงข้อมูลให้ครบถ้วน</span>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <form action="{{ route('batches.cancel-upload') }}" method="POST">
                                @csrf
                                <input type="hidden" name="temp_file_path" value="{{ $tempPath }}">
                                <button type="submit" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold px-4 py-2 rounded-xl transition">
                                    ยกเลิกการอัปโหลด
                                </button>
                            </form>

                            <form action="{{ route('batches.upload') }}" method="POST">
                                @csrf
                                <input type="hidden" name="confirm_overwrite" value="1">
                                <input type="hidden" name="temp_file_path" value="{{ $tempPath }}">
                                <input type="hidden" name="employee_type" value="{{ $formData['employee_type'] ?? 'ข้าราชการ' }}">
                                <input type="hidden" name="month" value="{{ $formData['month'] ?? 1 }}">
                                <input type="hidden" name="year_be" value="{{ $formData['year_be'] ?? 2569 }}">
                                <input type="hidden" name="period_text" value="{{ $formData['period_text'] ?? '' }}">
                                <input type="hidden" name="pay_date_text" value="{{ $formData['pay_date_text'] ?? '' }}">
                                <input type="hidden" name="original_filename" value="{{ $formData['original_filename'] ?? '' }}">

                                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-5 py-2 rounded-xl shadow transition inline-flex items-center gap-1.5" onclick="return confirm('ยืนยันอัปโหลดทับข้อมูลเดิม? ข้อมูลสลิปในงวดนี้จะถูกปรับปรุงเป็นยอดล่าสุดตามไฟล์ใหม่')">
                                    ยืนยันอัปโหลดทับ / อัปเดตข้อมูลล่าสุด (Overwrite)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2-Column Systematic Layout for Uploading --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Form Section (7 cols) --}}
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800">ฟอร์มอัปโหลดไฟล์เงินเดือนประจำงวด</h3>
                        <p class="text-xs text-slate-500 mt-0.5">เลือกไฟล์และระบุข้อมูลงวด ระบบจะสแกนและตรวจสอบความซ้ำซ้อนให้อัตโนมัติ</p>
                    </div>

                    <form action="{{ route('batches.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">เลือกไฟล์ Excel (.xlsx, .xls) หรือ .csv</label>
                            <input
                                type="file"
                                name="payroll_file"
                                accept=".xlsx,.xls,.csv"
                                required
                                class="w-full text-sm text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-purple-900 hover:file:bg-purple-200 border border-slate-300 rounded-xl p-2 bg-slate-50/50"
                            >
                            <p class="text-xs text-slate-500 mt-1">* รองรับไฟล์รวมหลาย Sheet (ระบบจะนำเข้าแยกทีละงวดเดือนให้อัตโนมัติ)</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">ประเภทบุคลากร</label>
                                <select name="employee_type" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm bg-white">
                                    <option value="ข้าราชการ" selected>ข้าราชการ</option>
                                    <option value="พนักงานราชการ">พนักงานราชการ</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">ประจำเดือน</label>
                                    <select name="month" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm bg-white">
                                        @foreach($thaiMonths as $mNum => $mName)
                                            <option value="{{ $mNum }}" {{ $mNum == 1 ? 'selected' : '' }}>{{ $mName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">ปี พ.ศ.</label>
                                    <input type="number" name="year_be" value="2569" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm bg-white">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">ข้อความรอบเงินเดือน (เว้นว่างให้อัตโนมัติได้)</label>
                                <input type="text" name="period_text" placeholder="เช่น 01 - 31 มกราคม 2569" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">ข้อความวันที่จ่าย (เว้นว่างให้อัตโนมัติได้)</label>
                                <input type="text" name="pay_date_text" placeholder="เช่น 25 มกราคม 2569" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-purple-900 hover:bg-purple-950 text-white font-bold py-3 rounded-xl shadow transition text-sm flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span>อัปโหลดและตรวจสอบข้อมูล</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Instructions & Guidelines Panel (5 cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-purple-50/70 border border-purple-200/80 rounded-2xl p-5">
                        <div class="flex items-center gap-2.5 text-purple-950 font-bold text-sm">
                            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>ระบบป้องกันความซ้ำซ้อน 100%</span>
                        </div>
                        <p class="text-xs text-purple-900/80 mt-1.5 leading-relaxed">
                            ระบบจะตรวจสอบชื่อไฟล์ งวดเดือน และรายชื่อบุคลากรทุกครั้ง หากพบว่างวดนั้นมีอยู่แล้ว ระบบจะไม่สร้างงวดซ้ำ แต่จะแสดงหน้าต่างเปรียบเทียบเพื่อให้คุณเลือกยืนยันหรือยกเลิก
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
                        <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 inline-flex items-center justify-center text-xs font-bold">1</span>
                            <span>การทยอยพิมพ์ข้อมูลเพิ่ม (กรณีที่ 1)</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            หากเปิดไฟล์เดิมมาพิมพ์เพิ่มข้อมูล (เช่น ครั้งแรกอัปโหลด 40 คน ต่อมาพิมพ์เพิ่มเป็น 60 หรือ 80 คนในไฟล์เดิม) สามารถอัปโหลดไฟล์เข้ามาได้ทันที:
                        </p>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <li>แถวที่ยังว่างหรือยอดเงิน 0 ระบบจะข้ามให้อัตโนมัติ</li>
                            <li>ระบบจะแสดงป้ายเตือน <strong>"เพิ่มขึ้น +X คน"</strong> ให้เห็นยอดเปรียบเทียบ</li>
                            <li>กดยืนยัน <strong>"อัปโหลดทับ"</strong> สลิปจะได้รับการอัปเดตเป็นยอดล่าสุดทันที</li>
                            <li>รหัสผ่านและข้อมูลบัญชีของข้าราชการจะไม่สูญหาย</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: ค้นหาสลิปเงินเดือนด่วน (Quick Search & Lookup) --}}
        <div id="tab-content-search" class="tab-content p-6 space-y-5 hidden">
            <div class="max-w-3xl mx-auto space-y-4">
                <div class="text-center space-y-1">
                    <h2 class="text-lg font-bold text-slate-800">ค้นหาสลิปเงินเดือนข้าราชการ</h2>
                    <p class="text-xs text-slate-500">พิมพ์คำค้นหาเพื่อค้นหาสลิปของข้าราชการทุกคนในระบบข้ามทุกงวดเดือน</p>
                </div>

                <form action="{{ route('dashboard') }}" method="GET" class="flex gap-2">
                    <input type="hidden" name="tab" value="search">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="พิมพ์ชื่อ-นามสกุล, เลขที่บัญชี, สังกัด/อำเภอ หรือเลขประจำตัวประชาชน 13 หลัก..."
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm shadow-sm"
                        >
                    </div>

                    <button type="submit" class="bg-purple-900 hover:bg-purple-950 text-white px-6 py-3 rounded-xl text-sm font-bold transition shadow-sm">
                        ค้นหา
                    </button>

                    @if($search !== '')
                        <a href="{{ route('dashboard', ['tab' => 'search']) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center">
                            ล้างค่า
                        </a>
                    @endif
                </form>
            </div>

            {{-- Search Results Section --}}
            @if($search !== '')
                <div class="border-t border-slate-200 pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-bold text-slate-700">
                            ผลการค้นหาสำหรับ "{{ $search }}" 
                            <span class="text-purple-800 font-semibold">(พบ {{ number_format($searchResults->count()) }} รายการ)</span>
                        </div>
                    </div>

                    @if($searchResults->isEmpty())
                        <div class="bg-slate-50 rounded-xl p-8 text-center text-slate-500 border border-slate-200">
                            <svg class="w-10 h-10 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="font-semibold text-slate-700">ไม่พบสลิปเงินเดือนที่ตรงกับคำค้นหา</div>
                            <p class="text-xs text-slate-400 mt-1">กรุณาตรวจสอบการสะกดชื่อ หรือค้นหาด้วยเลขที่บัญชีธนาคาร</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 text-xs border-b font-bold">
                                        <th class="py-2.5 px-3">งวดประจำเดือน</th>
                                        <th class="py-2.5 px-3">ชื่อ-นามสกุล</th>
                                        <th class="py-2.5 px-3">ตำแหน่ง / สังกัด</th>
                                        <th class="py-2.5 px-3">เลขที่บัญชี</th>
                                        <th class="py-2.5 px-3 text-right">เงินได้สุทธิ</th>
                                        <th class="py-2.5 px-3 text-center">ดูสลิป / พิมพ์</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($searchResults as $slip)
                                        <tr class="hover:bg-purple-50/40 transition">
                                            <td class="py-2.5 px-3 font-semibold text-purple-950">
                                                {{ $slip->batch?->month_name }} {{ $slip->batch?->year_be }}
                                            </td>
                                            <td class="py-2.5 px-3 font-medium text-slate-900">
                                                {{ $slip->full_name }}
                                            </td>
                                            <td class="py-2.5 px-3 text-xs text-slate-600">
                                                {{ $slip->display_position }}
                                                @if($slip->level_or_district)
                                                    <span class="text-slate-400">({{ $slip->level_or_district }})</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-xs text-slate-600 font-mono">
                                                {{ $slip->formatted_bank_account }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-bold text-emerald-700 tabular-nums text-sm">
                                                {{ number_format((float) $slip->net_pay, 2) }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                <a
                                                    href="{{ route('payslips.show', $slip) }}"
                                                    class="inline-block bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs font-bold px-3 py-1 rounded-lg transition"
                                                >
                                                    เปิดสลิป &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

        // Show active content
        const target = document.getElementById('tab-content-' + tabName);
        if (target) {
            target.classList.remove('hidden');
        }

        // Update Left Sidebar navigation active styles
        if (typeof updateSidebarActiveTab === 'function') {
            updateSidebarActiveTab(tabName);
        }

        // Update URL hash without page jump
        if (history.replaceState) {
            history.replaceState(null, null, '#' + tabName);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let initialTab = 'batches';
        const hash = window.location.hash.replace('#', '');
        const hasWarning = {{ session('duplicate_warning') ? 'true' : 'false' }};
        const hasSearch = {{ $search !== '' ? 'true' : 'false' }};
        const queryTab = '{{ request()->query("tab") }}';

        if (hasWarning || queryTab === 'upload') {
            initialTab = 'upload';
        } else if (hasSearch || queryTab === 'search') {
            initialTab = 'search';
        } else if (['batches', 'upload', 'search'].includes(hash)) {
            initialTab = hash;
        }

        switchTab(initialTab);
    });
</script>
@endsection
