@extends('layouts.app')

@section('title', 'สลิปเงินเดือน - ' . $payslip->full_name . ' (' . $payslip->batch->month_name . ' ' . $payslip->batch->year_be . ')')

@push('styles')
<style>
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        .payslip-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        .payslip-watermark {
            display: flex !important;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    {{-- Action Toolbar (Hidden when printing) --}}
    <div class="no-print flex flex-wrap items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-3xl shadow-xs border border-slate-200/80">
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('batches.show', $payslip->batch) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    &larr; กลับหน้ารายชื่อประจำเดือน
                </a>
            @else
                <a href="{{ route('my-payslips') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    &larr; กลับหน้ารายการสลิปของฉัน
                </a>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if(auth()->user()->isAdmin())
                <a
                    href="{{ route('payslips.edit', $payslip) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>แก้ไขสลิปเงินเดือน (รายรับ/รายหัก)</span>
                </a>

                @if($payslip->user)
                    <a
                        href="{{ route('employees.edit', $payslip->user) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-300"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>แก้ไขข้อมูลประวัติบุคคล</span>
                    </a>
                @endif
            @endif

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>พิมพ์สลิปเงินเดือน / บันทึก PDF</span>
            </button>
        </div>
    </div>

    {{-- Official Payslip Sheet --}}
    @include('payroll.payslip-card', ['payslip' => $payslip, 'settings' => $settings])
</div>
@endsection
