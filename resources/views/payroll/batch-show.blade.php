@extends('layouts.app')

@section('title', 'งวดเงินเดือน ' . $batch->employee_type . ' ' . $batch->month_name . ' ' . $batch->year_be)

@section('content')
<div class="space-y-6">
    {{-- Header Banner Card --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 flex flex-wrap items-center justify-between gap-5">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2.5 text-xs">
                <a href="{{ route('dashboard') }}" class="text-purple-800 hover:text-purple-950 font-bold inline-flex items-center gap-1 transition">
                    &larr; กลับหน้าหลัก
                </a>
                <span class="text-slate-300">/</span>
                <span class="px-2.5 py-0.5 rounded-full font-bold {{ $batch->employee_type === 'พนักงานราชการ' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-900 border border-amber-300/50' }}">
                    {{ $batch->employee_type }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                ประจำเดือน {{ $batch->month_name }} {{ $batch->year_be }}
            </h1>
            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 font-medium">
                <span>รอบเงินเดือน: <strong class="text-slate-800">{{ $batch->period_text }}</strong></span>
                <span>•</span>
                <span>วันที่จ่าย: <strong class="text-slate-800">{{ $batch->pay_date_text }}</strong></span>
                <span>•</span>
                <span>จำนวน: <strong class="text-purple-900 font-bold">{{ number_format($batch->total_employees) }} คน</strong></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                onclick="document.getElementById('editBatchHeaderBox').classList.toggle('hidden')"
                class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl transition inline-flex items-center gap-1.5"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span>แก้ไขรอบ / วันที่จ่าย</span>
            </button>

            <a
                href="{{ route('batches.export-csv', $batch) }}"
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition inline-flex items-center gap-1.5"
                title="ดาวน์โหลดตารางข้อมูลประจำงวดเป็นไฟล์ Excel/CSV สำหรับกระทบยอดหรือตรวจสอบ"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>ส่งออก Excel (CSV)</span>
            </a>

            <a
                href="{{ route('batches.print', $batch) }}"
                target="_blank"
                class="bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition inline-flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>พิมพ์สลิปทั้งงวดทุกคน ({{ $batch->total_employees }} ใบ)</span>
            </a>

            <form action="{{ route('batches.destroy', $batch) }}" method="POST" onsubmit="return confirm('ยืนยันการลบข้อมูลเงินเดือนงวด {{ $batch->month_name }} {{ $batch->year_be }} นี้?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold px-3 py-2.5 rounded-xl transition border border-rose-200">
                    ลบงวดนี้
                </button>
            </form>
        </div>
    </div>

    {{-- Edit Period & Pay Date Box --}}
    <div id="editBatchHeaderBox" class="hidden bg-amber-50/80 border border-amber-300 rounded-2xl p-5 shadow-xs">
        <div class="text-xs font-bold text-amber-900 mb-3">แก้ไขข้อความรอบเงินเดือนและวันที่จ่าย (ที่จะพิมพ์ลงบนใบสลิป):</div>
        <form action="{{ route('batches.update', $batch) }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            @method('PUT')
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-bold text-slate-700 mb-1">ข้อความรอบเงินเดือนบนสลิป</label>
                <input type="text" name="period_text" value="{{ $batch->period_text }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white">
            </div>
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-bold text-slate-700 mb-1">ข้อความวันที่จ่ายบนสลิป</label>
                <input type="text" name="pay_date_text" value="{{ $batch->pay_date_text }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white">
            </div>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-xl shadow-xs">
                บันทึกการแก้ไข
            </button>
        </form>
    </div>

    {{-- Batch KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase">จำนวนข้าราชการในงวด</div>
            <div class="text-2xl font-bold text-purple-950 mt-1 font-num">{{ number_format($batch->total_employees) }} <span class="text-xs font-normal text-slate-500">คน</span></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase">ยอดรวมรายรับทั้งหมด</div>
            <div class="text-2xl font-bold text-slate-800 mt-1 font-num">{{ number_format((float) $batch->total_income, 2) }} <span class="text-xs font-normal text-slate-500">บ.</span></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-semibold text-slate-500 uppercase">ยอดรวมรายจ่าย/หักทั้งหมด</div>
            <div class="text-2xl font-bold text-rose-600 mt-1 font-num">{{ number_format((float) $batch->total_deduction, 2) }} <span class="text-xs font-normal text-slate-500">บ.</span></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs bg-emerald-50/20">
            <div class="text-xs font-semibold text-emerald-800 uppercase">ยอดจ่ายสุทธิรวมทั้งสิ้น</div>
            <div class="text-2xl font-bold text-emerald-700 mt-1 font-num">{{ number_format((float) $batch->total_net_pay, 2) }} <span class="text-xs font-normal text-emerald-600">บ.</span></div>
        </div>
    </div>

    {{-- Search & District Filter Card --}}
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/80">
        <form action="{{ route('batches.show', $batch) }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="ค้นหาชื่อ-นามสกุล, เลขที่บัญชี, เลขบัตร 13 หลัก..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                >
            </div>

            <select name="district" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium">
                <option value="">-- ทุกสังกัด / ทุกระดับ ({{ $districts->count() }}) --</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}" {{ $district === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-slate-900 hover:bg-black text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-xs">
                ค้นหา
            </button>

            @if($search !== '' || $district !== '')
                <a href="{{ route('batches.show', $batch) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                    ล้างตัวกรอง
                </a>
            @endif
        </form>
    </div>

    {{-- Payslips Table Card --}}
    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 text-xs uppercase font-bold tracking-wider">
                        <th class="py-3 px-3 text-center w-14">ลำดับ</th>
                        <th class="py-3 px-4">ชื่อ - นามสกุล</th>
                        <th class="py-3 px-3">{{ $batch->employee_type === 'พนักงานราชการ' ? 'อำเภอ/สังกัด' : 'ระดับ / ตำแหน่ง' }}</th>
                        <th class="py-3 px-3">เลขประจำตัว 13 หลัก</th>
                        <th class="py-3 px-3">เลขที่บัญชี</th>
                        <th class="py-3 px-3 text-right">รวมรายได้ (บ.)</th>
                        <th class="py-3 px-3 text-right">รวมรายจ่าย (บ.)</th>
                        <th class="py-3 px-3 text-right">เงินได้สุทธิ (บ.)</th>
                        <th class="py-3 px-4 text-center">สลิปเงินเดือน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                        <tr class="hover:bg-purple-50/30 transition">
                            <td class="py-3 px-3 text-center text-slate-400 font-num text-xs">{{ $slip->seq_no ?: $loop->iteration }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 text-xs font-bold flex items-center justify-center shrink-0">
                                        {{ mb_substr($slip->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div>{{ $slip->full_name }}</div>
                                        @if($slip->remark)
                                            <div class="text-[11px] text-slate-400 font-normal">{{ $slip->remark }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-600 text-xs">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 font-medium">
                                    {{ $slip->level_or_district ?: '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-xs">
                                @if($slip->display_citizen_id !== '-')
                                    <span class="font-mono text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                        {{ $slip->display_citizen_id }}
                                    </span>
                                @else
                                    <span class="text-amber-600 font-medium text-[11px] bg-amber-50 px-2 py-0.5 rounded">ยังไม่ระบุ</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-mono text-xs text-slate-600">
                                {{ $slip->formatted_bank_account }}
                            </td>
                            <td class="py-3 px-3 text-right tabular-nums text-slate-700 font-num">
                                {{ number_format((float) $slip->total_income, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right tabular-nums text-rose-600 font-num">
                                {{ number_format((float) $slip->total_deduction, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right tabular-nums font-bold text-emerald-700 text-base font-num">
                                {{ number_format((float) $slip->net_pay, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a
                                        href="{{ route('payslips.show', $slip) }}"
                                        class="inline-flex items-center gap-1 bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-xs transition"
                                    >
                                        <span>เปิดสลิป</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                    <a
                                        href="{{ route('payslips.edit', $slip) }}"
                                        class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold px-2.5 py-1.5 rounded-xl border border-amber-200 transition"
                                        title="แก้ไขสลิปเงินเดือนรายบุคคล"
                                    >
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>แก้ไข</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-500">
                                <div class="text-base font-semibold">ไม่พบข้อมูลสลิปที่ตรงกับเงื่อนไข</div>
                                <p class="text-xs text-slate-400 mt-1">ลองเปลี่ยนคำค้นหาหรือตัวกรองสังกัด</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($payslips->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-300">
                        <td colspan="5" class="py-3.5 px-4 text-right">ยอดรวมทั้งหมด ({{ $payslips->count() }} คน):</td>
                        <td class="py-3.5 px-3 text-right tabular-nums font-num">{{ number_format((float) $payslips->sum('total_income'), 2) }}</td>
                        <td class="py-3.5 px-3 text-right tabular-nums text-rose-600 font-num">{{ number_format((float) $payslips->sum('total_deduction'), 2) }}</td>
                        <td class="py-3.5 px-3 text-right tabular-nums text-emerald-800 text-base font-num">{{ number_format((float) $payslips->sum('net_pay'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
