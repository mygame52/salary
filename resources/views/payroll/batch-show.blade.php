@extends('layouts.app')

@section('title', 'งวดเงินเดือน ' . $batch->employee_type . ' ' . $batch->month_name . ' ' . $batch->year_be)

@section('content')
<div class="space-y-5">
    {{-- Top Bar --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('dashboard') }}" class="text-sm text-purple-800 hover:underline font-medium">&larr; หน้าหลัก</a>
                <span class="text-slate-300">|</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $batch->employee_type === 'พนักงานราชการ' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $batch->employee_type }}
                </span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mt-1">
                รายการสลิปเงินเดือน ประจำเดือน {{ $batch->month_name }} {{ $batch->year_be }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                รอบเงินเดือน: <strong>{{ $batch->period_text }}</strong> | วันที่จ่าย: <strong>{{ $batch->pay_date_text }}</strong> | จำนวน {{ number_format($batch->total_employees) }} คน
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                onclick="document.getElementById('editBatchHeaderBox').classList.toggle('hidden')"
                class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-3.5 py-2 rounded-xl transition"
            >
                แก้ไขรอบเงินเดือน / วันที่จ่าย
            </button>

            <a
                href="{{ route('batches.print', $batch) }}"
                class="bg-purple-800 hover:bg-purple-900 text-white text-xs font-bold px-4 py-2 rounded-xl shadow transition inline-flex items-center gap-1.5"
            >
                พิมพ์สลิปทั้งเดือนทุกคน ({{ $batch->total_employees }} ใบ)
            </a>

            <form action="{{ route('batches.destroy', $batch) }}" method="POST" onsubmit="return confirm('ยืนยันการลบข้อมูลเงินเดือนงวดนี้?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold px-3 py-2 rounded-xl transition">
                    ลบงวดนี้
                </button>
            </form>
        </div>
    </div>

    {{-- Edit Period & Pay Date Form --}}
    <div id="editBatchHeaderBox" class="hidden bg-amber-50 border border-amber-300 rounded-xl p-4">
        <form action="{{ route('batches.update', $batch) }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            @method('PUT')
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-bold text-slate-700 mb-1">ข้อความรอบเงินเดือนที่แสดงบนสลิป</label>
                <input type="text" name="period_text" value="{{ $batch->period_text }}" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-bold text-slate-700 mb-1">ข้อความวันที่จ่ายที่แสดงบนสลิป</label>
                <input type="text" name="pay_date_text" value="{{ $batch->pay_date_text }}" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-1.5 rounded-lg">
                บันทึก
            </button>
        </form>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
        <form action="{{ route('batches.show', $batch) }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="ค้นหาชื่อ-นามสกุล, เลขที่บัญชี, เลขบัตร 13 หลัก..."
                class="flex-1 min-w-[220px] px-4 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-purple-600"
            >

            <select name="district" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-purple-600">
                <option value="">-- ทุกสังกัด / ทุกระดับ ({{ $districts->count() }}) --</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}" {{ $district === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                ค้นหา
            </button>

            @if($search !== '' || $district !== '')
                <a href="{{ route('batches.show', $batch) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm px-3 py-2 rounded-xl">
                    ล้างตัวกรอง
                </a>
            @endif
        </form>
    </div>

    {{-- Payslips Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase">
                        <th class="py-3 px-3 text-center w-14">ลำดับ</th>
                        <th class="py-3 px-4">ชื่อ - นามสกุล</th>
                        <th class="py-3 px-3">{{ $batch->employee_type === 'พนักงานราชการ' ? 'อำเภอ/สังกัด' : 'ระดับ/ตำแหน่ง' }}</th>
                        <th class="py-3 px-3">เลขบัตร 13 หลัก</th>
                        <th class="py-3 px-3">เลขที่บัญชี</th>
                        <th class="py-3 px-3 text-right">รวมรายได้</th>
                        <th class="py-3 px-3 text-right">รวมรายจ่าย</th>
                        <th class="py-3 px-3 text-right">เงินได้สุทธิ</th>
                        <th class="py-3 px-4 text-center">สลิปเงินเดือน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3 text-center text-slate-500">{{ $slip->seq_no ?: $loop->iteration }}</td>
                            <td class="py-2.5 px-4 font-semibold text-slate-800">
                                {{ $slip->full_name }}
                                @if($slip->remark)
                                    <div class="text-xs text-slate-400 font-normal">{{ $slip->remark }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $slip->level_or_district ?: '-' }}</td>
                            <td class="py-2.5 px-3">
                                @if($slip->display_citizen_id !== '-')
                                    <span class="font-mono text-xs text-slate-700">{{ $slip->display_citizen_id }}</span>
                                @else
                                    <span class="text-xs text-amber-600">ยังไม่กรอก</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono text-xs text-slate-600">{{ $slip->formatted_bank_account }}</td>
                            <td class="py-2.5 px-3 text-right tabular-nums">{{ number_format((float) $slip->total_income, 2) }}</td>
                            <td class="py-2.5 px-3 text-right tabular-nums text-red-600">{{ number_format((float) $slip->total_deduction, 2) }}</td>
                            <td class="py-2.5 px-3 text-right tabular-nums font-bold text-emerald-700">{{ number_format((float) $slip->net_pay, 2) }}</td>
                            <td class="py-2.5 px-4 text-center">
                                <a
                                    href="{{ route('payslips.show', $slip) }}"
                                    class="inline-flex items-center gap-1 bg-purple-800 hover:bg-purple-900 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition"
                                >
                                    ดู / พิมพ์สลิป
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-500">ไม่พบข้อมูลรายการที่ค้นหา</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($payslips->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-100 font-bold text-slate-800 border-t-2 border-slate-300">
                        <td colspan="5" class="py-3 px-4 text-right">รวม ({{ $payslips->count() }} คน)</td>
                        <td class="py-3 px-3 text-right tabular-nums">{{ number_format((float) $payslips->sum('total_income'), 2) }}</td>
                        <td class="py-3 px-3 text-right tabular-nums text-red-700">{{ number_format((float) $payslips->sum('total_deduction'), 2) }}</td>
                        <td class="py-3 px-3 text-right tabular-nums text-emerald-800">{{ number_format((float) $payslips->sum('net_pay'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
