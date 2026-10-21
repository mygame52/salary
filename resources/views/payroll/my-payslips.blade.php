@extends('layouts.app')

@section('title', 'สลิปเงินเดือนของฉัน - ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $user->name }}</h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ $user->employee_type }} | ตำแหน่ง: {{ $user->position ?: 'นักวิชาการศึกษา' }}
                @if($user->level_or_district)
                    ({{ $user->level_or_district }})
                @endif
            </p>
            <p class="text-xs text-slate-500 mt-0.5">
                เลขประจำตัวผู้เสียภาษี: <span class="font-mono">{{ $user->citizen_id ?: '-' }}</span> | เลขที่บัญชี: <span class="font-mono">{{ $user->formatted_bank_account }} {{ $user->bank_name }}</span>
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="font-bold text-base text-slate-800">รายการสลิปเงินเดือนย้อนหลัง</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase">
                        <th class="py-3 px-5">งวดประจำเดือน</th>
                        <th class="py-3 px-4">วันที่จ่าย</th>
                        <th class="py-3 px-4 text-right">รวมรายได้</th>
                        <th class="py-3 px-4 text-right">รวมรายจ่าย</th>
                        <th class="py-3 px-4 text-right">เงินได้สุทธิ</th>
                        <th class="py-3 px-5 text-center">พิมพ์สลิป</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-5 font-bold text-slate-800">
                                {{ $slip->batch?->month_name }} {{ $slip->batch?->year_be }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $slip->batch?->pay_date_text }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums">{{ number_format((float) $slip->total_income, 2) }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-red-600">{{ number_format((float) $slip->total_deduction, 2) }}</td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-bold text-emerald-700">{{ number_format((float) $slip->net_pay, 2) }}</td>
                            <td class="py-3.5 px-5 text-center">
                                <a href="{{ route('payslips.show', $slip) }}" class="inline-flex items-center gap-1.5 bg-purple-800 hover:bg-purple-900 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition">
                                    เปิดดู / พิมพ์สลิป
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">ยังไม่มีข้อมูลสลิปเงินเดือน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
