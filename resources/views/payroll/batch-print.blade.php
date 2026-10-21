@extends('layouts.app')

@section('title', 'พิมพ์สลิปเงินเดือนทั้งหมด - ' . $batch->employee_type . ' ' . $batch->month_name . ' ' . $batch->year_be)

@push('styles')
<style>
    @media print {
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
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="no-print mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <div>
            <a href="{{ route('batches.show', $batch) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium">
                &larr; กลับหน้างวดเดือน ({{ $batch->month_name }} {{ $batch->year_be }})
            </a>
            <span class="ml-3 text-sm text-slate-600">ทั้งหมด {{ $payslips->count() }} คน</span>
        </div>

        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-purple-800 hover:bg-purple-900 text-white text-sm font-bold shadow"
        >
            พิมพ์ทั้งหมด ({{ $payslips->count() }} ใบ)
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
