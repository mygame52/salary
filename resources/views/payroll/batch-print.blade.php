@extends('layouts.app')

@section('title', 'พิมพ์สลิปเงินเดือนทั้งหมด - ' . $batch->employee_type . ' ' . $batch->month_name . ' ' . $batch->year_be)

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
        .payslip-page {
            page-break-after: always;
            break-after: page;
        }
        .payslip-page:last-child {
            page-break-after: auto;
            break-after: auto;
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
<div class="max-w-4xl mx-auto space-y-6">
    <div class="no-print flex flex-wrap items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-3xl shadow-xs border border-slate-200/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('batches.show', $batch) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                &larr; กลับหน้างวดเดือน ({{ $batch->month_name }} {{ $batch->year_be }})
            </a>
            <span class="text-xs text-slate-500 font-medium">รวมทั้งหมด <strong class="text-purple-900 font-bold">{{ $payslips->count() }} คน</strong></span>
        </div>

        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-900 hover:bg-purple-950 text-white text-xs font-bold shadow-sm transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>พิมพ์สลิปทั้งงวด ({{ $payslips->count() }} ใบ)</span>
        </button>
    </div>

    <div class="space-y-8 print:space-y-0">
        @foreach($payslips as $payslip)
            <div class="payslip-page">
                @include('payroll.payslip-card', ['payslip' => $payslip, 'settings' => $settings])
            </div>
        @endforeach
    </div>
</div>
@endsection
