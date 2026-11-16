@extends('layouts.app')

@section('title', 'แก้ไขข้อมูลรายบุคคล - ' . $employee->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('employees.index') }}" class="text-purple-800 hover:text-purple-950 font-bold inline-flex items-center gap-1 transition">
                    &larr; กลับหน้ารายชื่อบุคลากร
                </a>
                <span class="text-slate-300">/</span>
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-900 border border-amber-300/50">
                    {{ $employee->employee_type }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                แก้ไขข้อมูลรายบุคคล: {{ $employee->name }}
            </h1>
            <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
                การแก้ไขข้อมูลที่นี่จะซิงค์อัปเดตลงในใบสลิปเงินเดือนทุกงวดของบุคคลนี้ให้อัตโนมัติ และใช้เป็นข้อมูลประจำตัวในการเข้าสู่ระบบ
            </p>
        </div>

        <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-900 font-extrabold text-xl flex items-center justify-center shrink-0 shadow-xs">
            {{ mb_substr($employee->name, 0, 1) }}
        </div>
    </div>

    {{-- Edit Form --}}
    <form action="{{ route('employees.update', $employee) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: ข้อมูลทั่วไปและตำแหน่ง --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800">1. ข้อมูลประจำตัวและตำแหน่งราชการ</h2>
                <p class="text-xs text-slate-500 mt-0.5">ระบุชื่อ-สกุล เลข 13 หลัก และสังกัดที่ถูกต้อง</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ชื่อ - นามสกุล <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $employee->name) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        เลขประจำตัวประชาชน / เลขผู้เสียภาษี (13 หลัก)
                    </label>
                    <input
                        type="text"
                        name="citizen_id"
                        value="{{ old('citizen_id', $employee->citizen_id) }}"
                        maxlength="13"
                        placeholder="เช่น 1809900123456"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ประเภทบุคลากร <span class="text-rose-500">*</span>
                    </label>
                    <select name="employee_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium">
                        <option value="ข้าราชการ" {{ old('employee_type', $employee->employee_type) === 'ข้าราชการ' ? 'selected' : '' }}>ข้าราชการ</option>
                        <option value="ลูกจ้างประจำ" {{ old('employee_type', $employee->employee_type) === 'ลูกจ้างประจำ' ? 'selected' : '' }}>ลูกจ้างประจำ</option>
                        <option value="พนักงานราชการ" {{ old('employee_type', $employee->employee_type) === 'พนักงานราชการ' ? 'selected' : '' }}>พนักงานราชการ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ตำแหน่งที่แสดงบนสลิป
                    </label>
                    <input
                        type="text"
                        name="position"
                        value="{{ old('position', $employee->position) }}"
                        placeholder="เช่น ครู, นักวิชาการศึกษา ชก."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ระดับ / อำเภอ / สังกัด
                    </label>
                    <input
                        type="text"
                        name="level_or_district"
                        list="districtList"
                        value="{{ old('level_or_district', $employee->level_or_district) }}"
                        placeholder="เช่น เมือง, ท่าศาลา, ชก."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                    <datalist id="districtList">
                        @foreach($districts as $d)
                            <option value="{{ $d }}"></option>
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        เลขที่ตำแหน่ง (Position Number)
                    </label>
                    <input
                        type="text"
                        name="position_number"
                        value="{{ old('position_number', $employee->position_number) }}"
                        placeholder="เช่น 1234 หรือเว้นว่างได้"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white font-mono"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        หมายเหตุประจำตัว (Remark)
                    </label>
                    <input
                        type="text"
                        name="remark"
                        value="{{ old('remark', $employee->remark) }}"
                        placeholder="หมายเหตุเพิ่มเติม"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>
            </div>
        </div>

        {{-- Section 2: สถานะการทำงาน --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-800">2. สถานะการทำงาน (Employment Status)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">กำหนดสถานะการทำงาน เช่น ปกติ, ย้าย, เกษียณ, ลาออก หรือเสียชีวิต</p>
                </div>

                @php
                    $lastPayslip = $employee->latestPayslipWithBatch();
                @endphp
                @if($lastPayslip)
                    <div class="text-xs px-3 py-1 rounded-xl bg-purple-50 text-purple-900 border border-purple-200 font-semibold">
                        งวดเงินเดือนล่าสุด: <strong>{{ $lastPayslip->batch_month_name }} {{ $lastPayslip->batch_year }}</strong>
                    </div>
                @else
                    <div class="text-xs px-3 py-1 rounded-xl bg-slate-100 text-slate-600 font-semibold">
                        ยังไม่มีข้อมูลสลิปเงินเดือน
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        สถานะการทำงาน <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="status"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-purple-600 outline-none bg-white transition"
                    >
                        <option value="ปกติ" {{ old('status', $employee->status ?? 'ปกติ') === 'ปกติ' ? 'selected' : '' }}>🟢 ปกติ</option>
                        <option value="ย้าย" {{ old('status', $employee->status) === 'ย้าย' ? 'selected' : '' }}>🔄 ย้าย</option>
                        <option value="เกษียณ" {{ old('status', $employee->status) === 'เกษียณ' ? 'selected' : '' }}>🎖️ เกษียณ</option>
                        <option value="ลาออก" {{ old('status', $employee->status) === 'ลาออก' ? 'selected' : '' }}>📤 ลาออก</option>
                        <option value="เสียชีวิต" {{ old('status', $employee->status) === 'เสียชีวิต' ? 'selected' : '' }}>⚫ เสียชีวิต</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        หมายเหตุสถานะ (เช่น สังกัดใหม่ / สาเหตุ)
                    </label>
                    <input
                        type="text"
                        name="status_note"
                        value="{{ old('status_note', $employee->status_note) }}"
                        placeholder="เช่น ย้ายไป สกร.อำเภอ... หรือ เกษียณอายุ"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        วันที่มีผล (Effective Date)
                    </label>
                    <input
                        type="text"
                        name="status_date"
                        value="{{ old('status_date', $employee->status_date) }}"
                        placeholder="เช่น 1 ต.ค. 2569"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>
            </div>
        </div>

        {{-- Section 3: ข้อมูลการเงินและธนาคาร --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800">3. ข้อมูลบัญชีธนาคารรับเงินเดือน</h2>
                <p class="text-xs text-slate-500 mt-0.5">ระบุเลขบัญชีธนาคารสำหรับจ่ายเงินเดือนเข้าบัญชี</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        เลขที่บัญชีธนาคาร (10 หลัก)
                    </label>
                    <input
                        type="text"
                        name="bank_account"
                        value="{{ old('bank_account', $employee->bank_account) }}"
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
                        value="{{ old('bank_name', $employee->bank_name ?: 'ธ.กรุงไทย') }}"
                        placeholder="เช่น ธ.กรุงไทย"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ชื่อบัญชี (Account Name)
                    </label>
                    <input
                        type="text"
                        name="account_name"
                        value="{{ old('account_name', $employee->account_name ?: $employee->name) }}"
                        placeholder="ชื่อบัญชีธนาคาร"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                    >
                </div>
            </div>
        </div>

        {{-- Section 4: ความปลอดภัยและรหัสผ่าน --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800">4. กำหนดรหัสผ่านเข้าสู่ระบบใหม่ (Password Reset)</h2>
                <p class="text-xs text-slate-500 mt-0.5">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน (รหัสผ่านเริ่มต้นของระบบคือ 1234)</p>
            </div>

            <div class="max-w-md">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    รหัสผ่านใหม่
                </label>
                <input
                    type="password"
                    name="new_password"
                    placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 4 ตัวอักษร)"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition bg-white"
                >
            </div>
        </div>

        {{-- Save Button Bar --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('employees.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 transition">
                &larr; ยกเลิกและกลับ
            </a>

            <button
                type="submit"
                class="bg-purple-900 hover:bg-purple-950 text-white font-bold py-3.5 px-8 rounded-2xl shadow-md hover:shadow-lg transition flex items-center gap-2 text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>บันทึกการแก้ไขข้อมูลบุคคล</span>
            </button>
        </div>
    </form>

    {{-- Payslips History for this Employee --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-800">ประวัติสลิปเงินเดือนของ {{ $employee->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">สามารถคลิกดูหรือแก้ไขรายละเอียดสลิปในแต่ละงวดได้</p>
            </div>
            <div class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-900 border border-purple-200">
                มีสลิปทั้งหมด {{ $employee->payslips->count() }} งวด
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 text-xs uppercase font-bold tracking-wider">
                        <th class="py-3 px-4">งวดประจำเดือน</th>
                        <th class="py-3 px-3">วันที่จ่าย</th>
                        <th class="py-3 px-3 text-right">รวมรายได้</th>
                        <th class="py-3 px-3 text-right">รวมรายจ่าย</th>
                        <th class="py-3 px-3 text-right">รับสุทธิ</th>
                        <th class="py-3 px-4 text-center">จัดการสลิป</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->payslips as $slip)
                        <tr class="hover:bg-purple-50/30 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $slip->batch?->month_name }} {{ $slip->batch?->year_be }}
                            </td>
                            <td class="py-3 px-3 text-xs text-slate-600">
                                {{ $slip->batch?->pay_date_text }}
                            </td>
                            <td class="py-3 px-3 text-right tabular-nums text-slate-700 font-num">
                                {{ number_format((float) $slip->total_income, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right tabular-nums text-rose-600 font-num">
                                {{ number_format((float) $slip->total_deduction, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right tabular-nums font-bold text-emerald-700 font-num">
                                {{ number_format((float) $slip->net_pay, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a
                                        href="{{ route('payslips.show', $slip) }}"
                                        class="inline-block bg-purple-100 hover:bg-purple-200 text-purple-900 text-xs font-bold px-2.5 py-1 rounded-lg transition"
                                    >
                                        ดูสลิป
                                    </a>
                                    <a
                                        href="{{ route('payslips.edit', $slip) }}"
                                        class="inline-block bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold px-2.5 py-1 rounded-lg transition"
                                    >
                                        แก้ไขสลิป
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">ยังไม่มีสลิปเงินเดือน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
