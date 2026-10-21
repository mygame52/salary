@extends('layouts.app')

@section('title', 'หน้าหลักเจ้าหน้าที่การเงิน - ระบบสลิปเงินเดือนออนไลน์')

@section('content')
<div class="space-y-6">
    {{-- Duplicate Detection Warning Card --}}
    @if(session('duplicate_warning'))
        @php
            $warning = session('duplicate_warning');
            $formData = session('temp_form_data', []);
            $tempPath = session('temp_file_path');
        @endphp
        <div class="bg-amber-50 border-2 border-amber-400 rounded-2xl p-5 shadow-md space-y-4">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-amber-500 text-white rounded-xl">
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

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase">งวดเดือนที่อัปโหลดแล้ว</div>
            <div class="text-2xl font-bold text-purple-900 mt-1">{{ number_format($stats['total_batches']) }} งวด</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase">จำนวนสลิปเงินเดือนทั้งหมด</div>
            <div class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($stats['total_payslips']) }} ใบ</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase">ข้าราชการ</div>
            <div class="text-2xl font-bold text-purple-800 mt-1">{{ number_format($stats['civil_servants']) }} คน</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase">งวดปี พ.ศ.</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $years->first() ?? '2569' }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left Column: Upload Excel Form --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-purple-900 text-white px-5 py-4">
                    <h2 class="font-bold text-base">อัปโหลดไฟล์เงินเดือนประจำเดือน (Excel / CSV)</h2>
                    <p class="text-xs text-purple-200 mt-0.5">ระบบจะตรวจสอบความซ้ำซ้อนของงวดเดือนและรายชื่อก่อนบันทึก</p>
                </div>

                <form action="{{ route('batches.upload') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">ประเภทบุคลากร</label>
                        <select name="employee_type" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm">
                            <option value="ข้าราชการ" selected>ข้าราชการ</option>
                            <option value="พนักงานราชการ">พนักงานราชการ</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">* ระบบสามารถตรวจจับประเภทและเดือนจากหัวตารางในไฟล์ให้อัตโนมัติด้วย</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">ประจำเดือน</label>
                            <select name="month" id="uploadMonth" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm">
                                @foreach($thaiMonths as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ $mNum == 12 ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">ปี พ.ศ.</label>
                            <input type="number" name="year_be" id="uploadYear" value="2569" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">ข้อความรอบเงินเดือน (เว้นว่างให้อัตโนมัติได้)</label>
                            <input type="text" name="period_text" placeholder="เช่น 01 - 31 ธันวาคม 2568" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">ข้อความวันที่จ่าย (เว้นว่างให้อัตโนมัติได้)</label>
                            <input type="text" name="pay_date_text" placeholder="เช่น 25 ธันวาคม 2568" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">เลือกไฟล์ Excel (.xlsx, .xls) หรือ .csv</label>
                        <input
                            type="file"
                            name="payroll_file"
                            accept=".xlsx,.xls,.csv"
                            required
                            class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-800 hover:file:bg-purple-100 border border-slate-300 rounded-xl p-1.5"
                        >
                    </div>

                    <button type="submit" class="w-full bg-purple-800 hover:bg-purple-900 text-white font-bold py-2.5 rounded-xl shadow transition text-sm">
                        อัปโหลดและสร้างสลิปเงินเดือน
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Column: Quick Search & Monthly Batches --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Quick Employee Payslip Search --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap gap-2">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="ค้นหาสลิปด่วน: พิมพ์ชื่อ-สกุล, เลขบัญชี, อำเภอ หรือเลขบัตร 13 หลัก..."
                        class="flex-1 min-w-[220px] px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-600 outline-none text-sm"
                    >
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                        ค้นหาสลิป
                    </button>
                    @if($search !== '')
                        <a href="{{ route('dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-xl text-sm font-medium">
                            ล้างค่า
                        </a>
                    @endif
                </form>

                @if($search !== '')
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <div class="text-xs font-bold text-slate-500 mb-2">ผลการค้นหา "{{ $search }}" ({{ $searchResults->count() }} รายการ)</div>
                        @if($searchResults->isEmpty())
                            <div class="text-sm text-slate-500 py-3 text-center">ไม่พบสลิปเงินเดือนที่ตรงกับคำค้นหา</div>
                        @else
                            <div class="overflow-x-auto max-h-72">
                                <table class="w-full text-sm text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-600 text-xs border-b">
                                            <th class="py-2 px-2.5">งวดเดือน</th>
                                            <th class="py-2 px-2.5">ชื่อ-นามสกุล</th>
                                            <th class="py-2 px-2.5">ประเภท / สังกัด</th>
                                            <th class="py-2 px-2.5 text-right">เงินได้สุทธิ</th>
                                            <th class="py-2 px-2.5 text-center">ดู/พิมพ์</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($searchResults as $slip)
                                            <tr class="hover:bg-slate-50">
                                                <td class="py-2 px-2.5 font-medium">{{ $slip->batch?->month_name }} {{ $slip->batch?->year_be }}</td>
                                                <td class="py-2 px-2.5">{{ $slip->full_name }}</td>
                                                <td class="py-2 px-2.5 text-xs text-slate-600">{{ $slip->employee_type }} ({{ $slip->level_or_district ?: '-' }})</td>
                                                <td class="py-2 px-2.5 text-right font-semibold text-emerald-700 tabular-nums">{{ number_format((float) $slip->net_pay, 2) }}</td>
                                                <td class="py-2 px-2.5 text-center">
                                                    <a href="{{ route('payslips.show', $slip) }}" class="inline-block bg-purple-100 hover:bg-purple-200 text-purple-800 text-xs font-bold px-2.5 py-1 rounded-lg">
                                                        เปิดสลิป
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

            {{-- Monthly Batches List --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-bold text-base text-slate-800">รายการงวดเงินเดือนในระบบ</h2>

                    <div class="flex items-center gap-2 text-xs">
                        <a href="{{ route('dashboard') }}" class="px-2.5 py-1 rounded-lg {{ !$filterType ? 'bg-purple-800 text-white font-semibold' : 'bg-slate-100 text-slate-700' }}">ทั้งหมด</a>
                        <a href="{{ route('dashboard', ['type' => 'พนักงานราชการ']) }}" class="px-2.5 py-1 rounded-lg {{ $filterType === 'พนักงานราชการ' ? 'bg-purple-800 text-white font-semibold' : 'bg-slate-100 text-slate-700' }}">พนักงานราชการ</a>
                        <a href="{{ route('dashboard', ['type' => 'ข้าราชการ']) }}" class="px-2.5 py-1 rounded-lg {{ $filterType === 'ข้าราชการ' ? 'bg-purple-800 text-white font-semibold' : 'bg-slate-100 text-slate-700' }}">ข้าราชการ</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase">
                                <th class="py-3 px-4">ประจำเดือน</th>
                                <th class="py-3 px-4">ประเภท</th>
                                <th class="py-3 px-4 text-center">จำนวนคน</th>
                                <th class="py-3 px-4 text-right">รวมรายรับ</th>
                                <th class="py-3 px-4 text-right">รวมรับสุทธิ</th>
                                <th class="py-3 px-4 text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($batches as $batch)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800">{{ $batch->month_name }} {{ $batch->year_be }}</div>
                                        <div class="text-xs text-slate-500">จ่าย: {{ $batch->pay_date_text }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($batch->employee_type === 'พนักงานราชการ')
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">พนักงานราชการ</span>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">ข้าราชการ</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center font-semibold">{{ number_format($batch->total_employees) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ number_format((float) $batch->total_income, 2) }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums font-bold text-emerald-700">{{ number_format((float) $batch->total_net_pay, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('batches.show', $batch) }}" class="inline-flex items-center gap-1 bg-purple-800 hover:bg-purple-900 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
                                            ดูรายชื่อ / พิมพ์สลิป
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">ยังไม่มีข้อมูลงวดเงินเดือน กรุณาอัปโหลดไฟล์ทางด้านซ้าย</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
