@extends('layouts.app')

@section('title', 'สลิปเงินเดือน - ' . $payslip->full_name . ' (' . $payslip->batch->month_name . ' ' . $payslip->batch->year_be . ')')

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        .payslip-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Action Toolbar (Hidden when printing) --}}
    <div class="no-print mb-5 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('batches.show', $payslip->batch) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition">
                    &larr; กลับหน้ารายชื่อประจำเดือน
                </a>
            @else
                <a href="{{ route('my-payslips') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition">
                    &larr; กลับหน้ารายการสลิปของฉัน
                </a>
            @endif
        </div>

        <div class="flex items-center gap-2.5">
            @if(auth()->user()->isAdmin() && $payslip->user)
                <button
                    type="button"
                    onclick="document.getElementById('quickEditBox').classList.toggle('hidden')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold shadow-sm transition"
                >
                    แก้ไขเลขบัตร 13 หลัก / ตำแหน่ง
                </button>
            @endif

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-purple-800 hover:bg-purple-900 text-white text-sm font-bold shadow transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                พิมพ์สลิปเงินเดือน / บันทึก PDF
            </button>
        </div>
    </div>

    {{-- Quick Edit Citizen ID / Position for Admin --}}
    @if(auth()->user()->isAdmin() && $payslip->user)
        <div id="quickEditBox" class="no-print hidden mb-5 bg-amber-50 border border-amber-300 rounded-xl p-4">
            <form action="{{ route('employees.update', $payslip->user) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $payslip->user->name }}">
                <input type="hidden" name="employee_type" value="{{ $payslip->user->employee_type }}">
                <input type="hidden" name="level_or_district" value="{{ $payslip->user->level_or_district }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">เลขประจำตัวผู้เสียภาษี (13 หลัก)</label>
                    <input type="text" name="citizen_id" value="{{ $payslip->user->citizen_id }}" maxlength="13" placeholder="กรอกเลข 13 หลัก" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ตำแหน่งที่แสดงบนสลิป</label>
                    <input type="text" name="position" value="{{ $payslip->user->position ?: $payslip->display_position }}" placeholder="เช่น นักวิชาการศึกษา" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">เลขที่บัญชีธนาคาร</label>
                    <input type="text" name="bank_account" value="{{ $payslip->user->bank_account }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-4 rounded-lg text-sm transition">
                        บันทึกและอัปเดตสลิป
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Official Payslip Sheet --}}
    @include('payroll.payslip-card', ['payslip' => $payslip, 'settings' => $settings])
</div>
@endsection
