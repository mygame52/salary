@extends('layouts.app')

@section('title', 'รายงานสรุปการเบิกจ่ายรายปี (Annual Financial Dashboard) - สกร.ประจำจังหวัดนครศรีธรรมราช')

@section('content')
<div class="space-y-6">

    {{-- 1. Header Banner & Quick Controls --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/40 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-40 -bottom-16 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>รายงานการเงินประจำปี &bull; Annual Financial Dashboard</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    สรุปการเบิกจ่ายเงินเดือนและเงินหักสะสม
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    {{ $selectedMode === 'fiscal' ? 'รอบปีงบประมาณ พ.ศ. ' . $selectedYear . ' (ตุลาคม ' . ($selectedYear - 1) . ' – กันยายน ' . $selectedYear . ')' : 'รอบปีปฏิทิน พ.ศ. ' . $selectedYear . ' (มกราคม – ธันวาคม ' . $selectedYear . ')' }}
                    @if($selectedType)
                        &bull; กลุ่ม: <span class="text-purple-300 font-semibold">{{ $selectedType }}</span>
                    @else
                        &bull; กลุ่ม: <span class="text-purple-300 font-semibold">ทุกประเภทบุคลากร</span>
                    @endif
                </p>
            </div>

            {{-- Action buttons (CSV Export & Print) --}}
            <div class="flex flex-wrap items-center gap-3 no-print">
                <a
                    href="{{ route('reports.annual.export-csv', request()->all()) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-emerald-950/30 transition hover:-translate-y-0.5"
                    title="ดาวน์โหลดข้อมูลเป็นไฟล์ Excel / CSV พร้อมเปิดใช้งานได้ทันที"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>ส่งออก Excel (CSV)</span>
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold border border-white/20 backdrop-blur-xs transition hover:-translate-y-0.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>พิมพ์รายงาน</span>
                </button>
            </div>
        </div>

        {{-- Interactive Filter Toolbar --}}
        <div class="mt-6 pt-6 border-t border-white/10 no-print">
            <form action="{{ route('reports.annual') }}" method="GET" class="flex flex-wrap items-center gap-3">
                {{-- Mode Switcher (Fiscal vs Calendar) --}}
                <div class="inline-flex bg-slate-900/80 p-1 rounded-xl border border-slate-700/80 text-xs">
                    <button
                        type="submit"
                        name="mode"
                        value="fiscal"
                        class="px-3 py-1.5 rounded-lg font-medium transition {{ $selectedMode === 'fiscal' ? 'bg-purple-600 text-white shadow font-bold' : 'text-slate-400 hover:text-white' }}"
                    >
                        ปีงบประมาณ (ต.ค. - ก.ย.)
                    </button>
                    <button
                        type="submit"
                        name="mode"
                        value="calendar"
                        class="px-3 py-1.5 rounded-lg font-medium transition {{ $selectedMode === 'calendar' ? 'bg-purple-600 text-white shadow font-bold' : 'text-slate-400 hover:text-white' }}"
                    >
                        ปีปฏิทิน (ม.ค. - ธ.ค.)
                    </button>
                </div>

                {{-- Year Selector --}}
                <div class="flex items-center gap-2 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-700/80 text-xs">
                    <span class="text-slate-400 font-medium">ปี พ.ศ.:</span>
                    <select
                        name="year"
                        onchange="this.form.submit()"
                        class="bg-transparent text-white font-semibold focus:outline-hidden cursor-pointer"
                    >
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" class="bg-slate-900 text-white" {{ $selectedYear == $yr ? 'selected' : '' }}>
                                {{ $yr }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Employee Type Filter --}}
                <div class="flex items-center gap-2 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-700/80 text-xs">
                    <span class="text-slate-400 font-medium">ประเภทบุคลากร:</span>
                    <select
                        name="type"
                        onchange="this.form.submit()"
                        class="bg-transparent text-white font-semibold focus:outline-hidden cursor-pointer"
                    >
                        <option value="" class="bg-slate-900 text-white" {{ $selectedType === '' ? 'selected' : '' }}>ทั้งหมด (รวมทุกประเภท)</option>
                        <option value="ข้าราชการ" class="bg-slate-900 text-white" {{ $selectedType === 'ข้าราชการ' ? 'selected' : '' }}>ข้าราชการ</option>
                        <option value="ลูกจ้างประจำ" class="bg-slate-900 text-white" {{ $selectedType === 'ลูกจ้างประจำ' ? 'selected' : '' }}>ลูกจ้างประจำ</option>
                        <option value="พนักงานราชการ" class="bg-slate-900 text-white" {{ $selectedType === 'พนักงานราชการ' ? 'selected' : '' }}>พนักงานราชการ</option>
                    </select>
                </div>

                @if($selectedType || $selectedMode !== 'fiscal' || $selectedYear != ($availableYears[0] ?? 2569))
                    <a
                        href="{{ route('reports.annual') }}"
                        class="text-xs text-purple-300 hover:text-white underline ml-auto py-1"
                    >
                        ล้างตัวกรอง
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- 2. KPI Summary Cards (5 Metrics) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Card 1: Total Net Pay --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">ยอดจ่ายสุทธิรวมทั้งปี</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-num tracking-tight">
                ฿{{ number_format($grandTotalNet, 2) }}
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-purple-700 font-semibold">
                <span>คิดเป็น {{ $netPayoutPercentage }}% ของรายรับรวม</span>
            </div>
        </div>

        {{-- Card 2: Total Gross Income --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">รายรับรวมทั้งปี (Gross)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-700 font-num tracking-tight">
                ฿{{ number_format($grandTotalIncome, 2) }}
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                <span>เงินเดือน + เงินประจำตำแหน่ง + ตกเบิก</span>
            </div>
        </div>

        {{-- Card 3: Total Deductions --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">เงินหักสะสมรวมทั้งปี</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-600 font-num tracking-tight">
                ฿{{ number_format($grandTotalDeduction, 2) }}
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-600 font-semibold">
                <span>สัดส่วนหัก {{ $deductionPercentage }}% ของรายรับ</span>
            </div>
        </div>

        {{-- Card 4: Months with Data --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">งวดที่มีข้อมูล</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-indigo-700 font-num tracking-tight">
                {{ $monthsWithDataCount }} <span class="text-sm font-semibold text-slate-500">/ 12 เดือน</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                <span>ความครอบคลุม {{ round(($monthsWithDataCount / 12) * 100) }}% ของรอบปี</span>
            </div>
        </div>

        {{-- Card 5: Average Personnel --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">บุคลากรเฉลี่ยต่องวด</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-600 font-num tracking-tight">
                {{ number_format($avgEmployeesPerMonth) }} <span class="text-sm font-semibold text-slate-500">คน</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                <span>ยอดเฉลี่ยผู้ได้รับเงินเดือน</span>
            </div>
        </div>
    </div>

    {{-- 3. Visual Charts Section (Chart.js) --}}
    <div class="space-y-6">
        {{-- Chart 1: Main Monthly Comparison Bar & Line Chart --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                        กราฟเปรียบเทียบยอดเบิกจ่ายเงินเดือนและเงินหักสะสมในแต่ละเดือน
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        แสดงข้อมูลแท่งรายรับ-รายหักรายเดือน พร้อมเส้นแสดงยอดจ่ายสุทธิสะสมตลอดทั้งปี
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <span class="inline-flex items-center gap-1.5 text-emerald-700">
                        <span class="w-3 h-3 rounded-xs bg-emerald-500"></span> รายรับรวม
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-rose-600">
                        <span class="w-3 h-3 rounded-xs bg-rose-500"></span> เงินหักรวม
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-indigo-700">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span> ยอดจ่ายสุทธิประจำเดือน
                    </span>
                </div>
            </div>

            <div class="h-80 sm:h-96 w-full">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
        </div>

        {{-- Row of 2 Secondary Charts: Cumulative Net Pay Curve & Top Deductions Doughnut --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Chart 2: Cumulative Net Pay Line Chart (7 cols) --}}
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                เส้นโค้งยอดเงินจ่ายสุทธิสะสม (Cumulative Payout Growth)
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                อัตราการสะสมของงบประมาณจ่ายจริงเดือนต่อเดือน
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 font-num">
                            ฿{{ number_format($grandTotalNet, 2) }}
                        </span>
                    </div>
                    <div class="h-64 sm:h-72 w-full">
                        <canvas id="cumulativeNetChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Chart 3: Top Deduction Categories Breakdown (5 cols) --}}
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                สัดส่วนรายการหักเงินสะสม
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                รายการหักยอดนิยม (กบข. / สหกรณ์ / ภาษี ฯลฯ)
                            </p>
                        </div>
                    </div>

                    @if(!empty($topDeductions))
                        <div class="h-64 w-full flex items-center justify-center">
                            <canvas id="deductionBreakdownChart"></canvas>
                        </div>
                    @else
                        <div class="h-64 flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-12 h-12 mb-2 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            <span class="text-xs">ไม่พบข้อมูลรายการหักสำหรับเงื่อนไขนี้</span>
                        </div>
                    @endif
                </div>

                {{-- Deduction Legend List --}}
                @if(!empty($topDeductions))
                    <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                        @foreach(array_slice($topDeductions, 0, 4, true) as $dLabel => $dVal)
                            <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50">
                                <span class="truncate text-slate-600" title="{{ $dLabel }}">{{ $dLabel }}</span>
                                <span class="font-bold font-num text-slate-800 ml-1">฿{{ number_format($dVal) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. Detailed Month-by-Month Financial Breakdown Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/60">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    ตารางรายละเอียดการเบิกจ่ายและยอดสะสมรายเดือน (12 เดือน)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    แสดงยอดประจำเดือนและยอดเงินสะสมทบไปตามลำดับงวดบัญชี
                </p>
            </div>
            <div class="text-xs text-slate-500 font-num">
                รอบปี: <strong class="text-slate-800">{{ $selectedMode === 'fiscal' ? "ปีงบประมาณ $selectedYear" : "ปีปฏิทิน $selectedYear" }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-600 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">ลำดับ / งวดเดือน</th>
                        <th class="py-3 px-3 text-center">บุคลากร</th>
                        <th class="py-3 px-4 text-right text-emerald-700">รายรับประจำเดือน</th>
                        <th class="py-3 px-4 text-right text-rose-600">รายหักประจำเดือน</th>
                        <th class="py-3 px-4 text-right text-indigo-700">จ่ายสุทธิประจำเดือน</th>
                        <th class="py-3 px-4 text-right bg-slate-50/80 text-slate-700">รายรับสะสม</th>
                        <th class="py-3 px-4 text-right bg-slate-50/80 text-slate-700">เงินหักสะสม</th>
                        <th class="py-3 px-4 text-right bg-purple-50/50 text-purple-900 font-extrabold">จ่ายสุทธิสะสม</th>
                        <th class="py-3 px-3 text-center no-print">งวดเงินเดือน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($monthlyData as $idx => $m)
                        <tr class="hover:bg-slate-50/80 transition {{ $m['has_data'] ? '' : 'text-slate-400 bg-slate-50/30' }}">
                            {{-- Month label --}}
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold {{ $m['has_data'] ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-500' }}">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-bold {{ $m['has_data'] ? 'text-slate-800' : 'text-slate-400' }}">
                                            {{ $m['name'] }} {{ $m['year'] }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            งวดที่ {{ $idx + 1 }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Employee count --}}
                            <td class="py-3 px-3 text-center font-num">
                                @if($m['has_data'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ number_format($m['employees_count']) }} คน
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            {{-- Monthly Income --}}
                            <td class="py-3 px-4 text-right font-num font-semibold {{ $m['has_data'] ? 'text-emerald-700' : 'text-slate-300' }}">
                                {{ $m['has_data'] ? '฿' . number_format($m['income'], 2) : '-' }}
                            </td>

                            {{-- Monthly Deduction --}}
                            <td class="py-3 px-4 text-right font-num font-semibold {{ $m['has_data'] ? 'text-rose-600' : 'text-slate-300' }}">
                                {{ $m['has_data'] ? '฿' . number_format($m['deduction'], 2) : '-' }}
                            </td>

                            {{-- Monthly Net Pay --}}
                            <td class="py-3 px-4 text-right font-num font-bold {{ $m['has_data'] ? 'text-indigo-700' : 'text-slate-300' }}">
                                {{ $m['has_data'] ? '฿' . number_format($m['net_pay'], 2) : '-' }}
                            </td>

                            {{-- Cumulative Income --}}
                            <td class="py-3 px-4 text-right font-num bg-slate-50/80 text-slate-700">
                                ฿{{ number_format($m['cumulative_income'], 2) }}
                            </td>

                            {{-- Cumulative Deduction --}}
                            <td class="py-3 px-4 text-right font-num bg-slate-50/80 text-slate-700">
                                ฿{{ number_format($m['cumulative_deduction'], 2) }}
                            </td>

                            {{-- Cumulative Net Pay --}}
                            <td class="py-3 px-4 text-right font-num font-bold bg-purple-50/50 text-purple-900">
                                ฿{{ number_format($m['cumulative_net_pay'], 2) }}
                            </td>

                            {{-- Action Link --}}
                            <td class="py-3 px-3 text-center no-print">
                                @if(!empty($m['batch_ids']))
                                    @if(count($m['batch_ids']) === 1)
                                        <a
                                            href="{{ route('batches.show', $m['batch_ids'][0]) }}"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 hover:text-purple-800 hover:underline"
                                            title="ดูรายละเอียดงวดเงินเดือนนี้"
                                        >
                                            <span>เปิดดู</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-500 font-semibold">
                                            {{ count($m['batch_ids']) }} ชุด
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-slate-400">ยังไม่มีงวด</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- Table Grand Summary Footer --}}
                <tfoot class="bg-slate-900 text-white font-bold">
                    <tr>
                        <td class="py-3.5 px-4 text-xs tracking-wider uppercase">
                            รวมทั้งสิ้น (Grand Total)
                        </td>
                        <td class="py-3.5 px-3 text-center font-num text-xs text-slate-300">
                            เฉลี่ย {{ number_format($avgEmployeesPerMonth) }} คน
                        </td>
                        <td class="py-3.5 px-4 text-right font-num text-emerald-300">
                            ฿{{ number_format($grandTotalIncome, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-num text-rose-300">
                            ฿{{ number_format($grandTotalDeduction, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-num text-purple-200 text-base">
                            ฿{{ number_format($grandTotalNet, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-num text-slate-300 bg-slate-950/40">
                            ฿{{ number_format($grandTotalIncome, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-num text-slate-300 bg-slate-950/40">
                            ฿{{ number_format($grandTotalDeduction, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-num text-amber-300 bg-slate-950/60 text-base">
                            ฿{{ number_format($grandTotalNet, 2) }}
                        </td>
                        <td class="py-3.5 px-3 text-center text-xs text-slate-400 no-print">
                            {{ $monthsWithDataCount }} / 12 งวด
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

{{-- 5. Chart.js CDN & Interactive Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthlyData = @json($monthlyData);
        const topDeductions = @json($topDeductions);

        const labels = monthlyData.map(item => item.label);
        const incomeData = monthlyData.map(item => item.income);
        const deductionData = monthlyData.map(item => item.deduction);
        const netPayData = monthlyData.map(item => item.net_pay);
        const cumulativeNetData = monthlyData.map(item => item.cumulative_net_pay);

        const formatCurrency = (val) => {
            return '฿' + Number(val).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        };

        // --- 1. Main Monthly Comparison Chart (Grouped Bar + Line) ---
        const ctx1 = document.getElementById('monthlyComparisonChart');
        if (ctx1) {
            new Chart(ctx1, {
                data: {
                    labels: labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'รายรับรวม (บาท)',
                            data: incomeData,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1,
                            borderRadius: 6,
                            order: 2,
                            barPercentage: 0.7,
                            categoryPercentage: 0.7,
                        },
                        {
                            type: 'bar',
                            label: 'เงินหักรวม (บาท)',
                            data: deductionData,
                            backgroundColor: 'rgba(244, 63, 94, 0.85)',
                            borderColor: 'rgb(244, 63, 94)',
                            borderWidth: 1,
                            borderRadius: 6,
                            order: 3,
                            barPercentage: 0.7,
                            categoryPercentage: 0.7,
                        },
                        {
                            type: 'line',
                            label: 'ยอดจ่ายสุทธิประจำเดือน (บาท)',
                            data: netPayData,
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.15)',
                            borderWidth: 3,
                            pointBackgroundColor: 'rgb(79, 70, 229)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            tension: 0.3,
                            fill: false,
                            order: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleColor: '#F8FAFC',
                            bodyColor: '#F8FAFC',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Sarabun', size: 12, weight: '500' },
                                color: '#64748B'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(226, 232, 240, 0.6)' },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#64748B',
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- 2. Cumulative Net Pay Growth Curve (Line with Gradient Area) ---
        const ctx2 = document.getElementById('cumulativeNetChart');
        if (ctx2) {
            const chart2Context = ctx2.getContext('2d');
            const gradientBg = chart2Context.createLinearGradient(0, 0, 0, 300);
            gradientBg.addColorStop(0, 'rgba(124, 58, 237, 0.35)');
            gradientBg.addColorStop(1, 'rgba(124, 58, 237, 0.01)');

            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'ยอดจ่ายสุทธิสะสม (บาท)',
                            data: cumulativeNetData,
                            borderColor: 'rgb(124, 58, 237)',
                            backgroundColor: gradientBg,
                            borderWidth: 3,
                            fill: true,
                            pointBackgroundColor: 'rgb(124, 58, 237)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            tension: 0.35,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleColor: '#F8FAFC',
                            bodyColor: '#F8FAFC',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return 'สะสมถึงงวดนี้: ' + formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Sarabun', size: 11 },
                                color: '#64748B'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(226, 232, 240, 0.6)' },
                            ticks: {
                                font: { family: 'Inter', size: 11 },
                                color: '#64748B',
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- 3. Top Deductions Doughnut Chart ---
        const ctx3 = document.getElementById('deductionBreakdownChart');
        if (ctx3 && topDeductions && Object.keys(topDeductions).length > 0) {
            const deductionLabels = Object.keys(topDeductions);
            const deductionValues = Object.values(topDeductions);

            const palette = [
                '#6366F1', // Indigo
                '#EC4899', // Pink
                '#F59E0B', // Amber
                '#10B981', // Emerald
                '#06B6D4', // Cyan
                '#8B5CF6', // Purple
                '#94A3B8', // Slate (Others)
            ];

            new Chart(ctx3, {
                type: 'doughnut',
                data: {
                    labels: deductionLabels,
                    datasets: [
                        {
                            data: deductionValues,
                            backgroundColor: palette.slice(0, deductionLabels.length),
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: 'Sarabun', size: 11 },
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleColor: '#F8FAFC',
                            bodyColor: '#F8FAFC',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.raw || 0;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${formatCurrency(val)} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
