@extends('layouts.app')

@section('title', 'แก้ไขสลิปเงินเดือน - ' . $payslip->full_name . ' (' . $payslip->batch->month_name . ' ' . $payslip->batch->year_be . ')')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('payslips.show', $payslip) }}" class="text-purple-800 hover:text-purple-950 font-bold inline-flex items-center gap-1 transition">
                    &larr; กลับหน้าใบสลิป
                </a>
                <span class="text-slate-300">/</span>
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-900 border border-amber-300/50">
                    {{ $payslip->batch->month_name }} {{ $payslip->batch->year_be }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                แก้ไขสลิปเงินเดือน: {{ $payslip->full_name }}
            </h1>
            <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
                เจ้าหน้าที่การเงินสามารถปรับปรุงรายการรายรับ-รายจ่ายของบุคคลนี้ได้โดยตรง ระบบจะคำนวณยอดสุทธิและปรับปรุงยอดรวมของงวดให้อัตโนมัติ
            </p>
        </div>

        <div class="text-right">
            <div class="text-xs font-semibold text-slate-500">งวดประจำเดือน</div>
            <div class="text-xl font-bold text-purple-950">{{ $payslip->batch->month_name }} {{ $payslip->batch->year_be }}</div>
            <div class="text-[11px] text-slate-400">รอบ: {{ $payslip->batch->period_text }}</div>
        </div>
    </div>

    {{-- Edit Payslip Form --}}
    <form action="{{ route('payslips.update', $payslip) }}" method="POST" id="payslipForm" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: ข้อมูลหัวสลิปของบุคคลนี้ --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">1. ข้อมูลบุคลากรบนสลิปใบนี้</h2>
                    <p class="text-xs text-slate-500 mt-0.5">ข้อมูลนี้จะปรากฏที่ส่วนหัวของใบสลิปเงินเดือน</p>
                </div>
                @if($payslip->user)
                    <a href="{{ route('employees.edit', $payslip->user) }}" target="_blank" class="text-xs font-bold text-purple-800 hover:underline inline-flex items-center gap-1">
                        <span>แก้ไขประวัติถาวร</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ชื่อ - นามสกุล <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="full_name"
                        value="{{ old('full_name', $payslip->full_name) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        เลขประจำตัวผู้เสียภาษี (13 หลัก)
                    </label>
                    <input
                        type="text"
                        name="citizen_id"
                        value="{{ old('citizen_id', $payslip->citizen_id ?: $payslip->user?->citizen_id) }}"
                        maxlength="13"
                        placeholder="เลข 13 หลัก"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ตำแหน่งที่แสดงบนสลิป
                    </label>
                    <input
                        type="text"
                        name="position"
                        value="{{ old('position', $payslip->position) }}"
                        placeholder="เช่น ครู, นักวิชาการศึกษา"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ระดับ / อำเภอ / สังกัด
                    </label>
                    <input
                        type="text"
                        name="level_or_district"
                        value="{{ old('level_or_district', $payslip->level_or_district) }}"
                        placeholder="เช่น ชก., เมือง, ท่าศาลา"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        เลขที่บัญชีธนาคาร
                    </label>
                    <input
                        type="text"
                        name="bank_account"
                        value="{{ old('bank_account', $payslip->bank_account) }}"
                        placeholder="เช่น 8000123456"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ธนาคาร
                    </label>
                    <input
                        type="text"
                        name="bank_name"
                        value="{{ old('bank_name', $payslip->bank_name ?: 'ธ.กรุงไทย') }}"
                        placeholder="เช่น ธ.กรุงไทย"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>
            </div>
        </div>

        {{-- Section 2: รายการรายได้ และ รายการรายหัก --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Income Items Column --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <h2 class="text-base font-bold text-slate-800">รายการรายได้ (บาท)</h2>
                    </div>
                    <button
                        type="button"
                        onclick="addIncomeRow()"
                        class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-xl transition inline-flex items-center gap-1 border border-emerald-200"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>เพิ่มรายได้</span>
                    </button>
                </div>

                <div id="incomeItemsContainer" class="space-y-2.5">
                    @php
                        $incomes = old('income_items', $payslip->income_items ?? []);
                    @endphp
                    @foreach($incomes as $idx => $item)
                        <div class="income-row flex items-center gap-2">
                            <input
                                type="text"
                                name="income_items[{{ $idx }}][label]"
                                value="{{ $item['label'] ?? '' }}"
                                placeholder="ชื่อรายการ (เช่น เงินเดือน)"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium"
                            >
                            <input
                                type="number"
                                step="0.01"
                                name="income_items[{{ $idx }}][amount]"
                                value="{{ $item['amount'] ?? 0 }}"
                                placeholder="0.00"
                                oninput="recalcTotals()"
                                class="income-amount w-32 px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-right focus:ring-2 focus:ring-purple-600 outline-none bg-white"
                            >
                            <button
                                type="button"
                                onclick="removeRow(this)"
                                class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition leading-none text-base"
                                title="ลบรายการนี้"
                            >
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between font-bold text-sm bg-slate-50/50 p-3 rounded-xl">
                    <span class="text-slate-700">รวมรายการได้ทั้งหมด:</span>
                    <span id="displayTotalIncome" class="font-mono text-slate-900 text-base">
                        {{ number_format((float) $payslip->total_income, 2) }} บ.
                    </span>
                </div>
            </div>

            {{-- Deduction Items Column --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <h2 class="text-base font-bold text-slate-800">รายการรายหัก (บาท)</h2>
                    </div>
                    <button
                        type="button"
                        onclick="addDeductRow()"
                        class="bg-rose-50 hover:bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1.5 rounded-xl transition inline-flex items-center gap-1 border border-rose-200"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>เพิ่มรายหัก</span>
                    </button>
                </div>

                <div id="deductItemsContainer" class="space-y-2.5">
                    @php
                        $deducts = old('deduction_items', $payslip->deduction_items ?? []);
                    @endphp
                    @foreach($deducts as $idx => $item)
                        <div class="deduct-row flex items-center gap-2">
                            <input
                                type="text"
                                name="deduction_items[{{ $idx }}][label]"
                                value="{{ $item['label'] ?? '' }}"
                                placeholder="ชื่อรายการ (เช่น ภาษี, กบข.)"
                                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium"
                            >
                            <input
                                type="number"
                                step="0.01"
                                name="deduction_items[{{ $idx }}][amount]"
                                value="{{ $item['amount'] ?? 0 }}"
                                placeholder="0.00"
                                oninput="recalcTotals()"
                                class="deduct-amount w-32 px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-right focus:ring-2 focus:ring-purple-600 outline-none bg-white text-rose-600"
                            >
                            <button
                                type="button"
                                onclick="removeRow(this)"
                                class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition leading-none text-base"
                                title="ลบรายการนี้"
                            >
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between font-bold text-sm bg-slate-50/50 p-3 rounded-xl">
                    <span class="text-slate-700">รวมรายการหักทั้งหมด:</span>
                    <span id="displayTotalDeduct" class="font-mono text-rose-600 text-base">
                        {{ number_format((float) $payslip->total_deduction, 2) }} บ.
                    </span>
                </div>
            </div>
        </div>

        {{-- Section 3: ยอดรับสุทธิและหมายเหตุ --}}
        <div class="bg-gradient-to-r from-purple-900 to-indigo-950 rounded-3xl p-6 sm:p-7 text-white shadow-md space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-bold text-purple-200 uppercase tracking-wide">ยอดคำนวณเงินได้สุทธิใหม่ (Net Pay)</div>
                    <div class="text-3xl font-extrabold text-amber-300 font-mono mt-1" id="displayNetPay">
                        {{ number_format((float) $payslip->net_pay, 2) }} บาท
                    </div>
                </div>
                <div class="text-xs text-purple-200/80 max-w-sm text-right sm:text-left">
                    * เมื่อบันทึก ยอดรวมของงวดเดือน ({{ $payslip->batch->month_name }} {{ $payslip->batch->year_be }}) จะได้รับการปรับปรุงตามยอดนี้ให้อัตโนมัติ
                </div>
            </div>

            <div class="border-t border-purple-800/60 pt-4">
                <label class="block text-xs font-bold text-purple-200 uppercase mb-1.5">
                    หมายเหตุประจำสลิป (Remark)
                </label>
                <input
                    type="text"
                    name="remark"
                    value="{{ old('remark', $payslip->remark) }}"
                    placeholder="เช่น ปรับปรุงยอดเงินเดือนตกเบิกตามคำสั่ง"
                    class="w-full px-4 py-2.5 rounded-xl border border-purple-700/60 text-sm text-white placeholder-purple-300/50 focus:ring-2 focus:ring-amber-400 outline-none bg-purple-950/60"
                >
            </div>
        </div>

        {{-- Save Button Bar --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('payslips.show', $payslip) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 transition">
                &larr; ยกเลิกและกลับ
            </a>

            <button
                type="submit"
                class="bg-purple-900 hover:bg-purple-950 text-white font-bold py-3.5 px-8 rounded-2xl shadow-md hover:shadow-lg transition flex items-center gap-2 text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>บันทึกการแก้ไขสลิปเงินเดือน</span>
            </button>
        </div>
    </form>
</div>

<script>
    let incomeCount = {{ count($incomes) }};
    let deductCount = {{ count($deducts) }};

    function addIncomeRow() {
        const container = document.getElementById('incomeItemsContainer');
        const row = document.createElement('div');
        row.className = 'income-row flex items-center gap-2';
        row.innerHTML = `
            <input
                type="text"
                name="income_items[${incomeCount}][label]"
                placeholder="ชื่อรายการ (เช่น ตกเบิก)"
                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium"
            >
            <input
                type="number"
                step="0.01"
                name="income_items[${incomeCount}][amount]"
                value="0.00"
                placeholder="0.00"
                oninput="recalcTotals()"
                class="income-amount w-32 px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-right focus:ring-2 focus:ring-purple-600 outline-none bg-white"
            >
            <button
                type="button"
                onclick="removeRow(this)"
                class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition leading-none text-base"
                title="ลบรายการนี้"
            >
                &times;
            </button>
        `;
        container.appendChild(row);
        incomeCount++;
        recalcTotals();
    }

    function addDeductRow() {
        const container = document.getElementById('deductItemsContainer');
        const row = document.createElement('div');
        row.className = 'deduct-row flex items-center gap-2';
        row.innerHTML = `
            <input
                type="text"
                name="deduction_items[${deductCount}][label]"
                placeholder="ชื่อรายการ (เช่น สหกรณ์)"
                class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium"
            >
            <input
                type="number"
                step="0.01"
                name="deduction_items[${deductCount}][amount]"
                value="0.00"
                placeholder="0.00"
                oninput="recalcTotals()"
                class="deduct-amount w-32 px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono text-right focus:ring-2 focus:ring-purple-600 outline-none bg-white text-rose-600"
            >
            <button
                type="button"
                onclick="removeRow(this)"
                class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-2 rounded-lg transition leading-none text-base"
                title="ลบรายการนี้"
            >
                &times;
            </button>
        `;
        container.appendChild(row);
        deductCount++;
        recalcTotals();
    }

    function removeRow(btn) {
        btn.parentElement.remove();
        recalcTotals();
    }

    function recalcTotals() {
        let totalIncome = 0;
        document.querySelectorAll('.income-amount').forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalIncome += val;
        });

        let totalDeduct = 0;
        document.querySelectorAll('.deduct-amount').forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalDeduct += val;
        });

        const netPay = totalIncome - totalDeduct;

        const fmt = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('displayTotalIncome').innerText = fmt.format(totalIncome) + ' บ.';
        document.getElementById('displayTotalDeduct').innerText = fmt.format(totalDeduct) + ' บ.';
        document.getElementById('displayNetPay').innerText = fmt.format(netPay) + ' บาท';
    }
</script>
@endsection
