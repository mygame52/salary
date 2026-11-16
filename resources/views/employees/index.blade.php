@extends('layouts.app')

@section('title', 'ทะเบียนบุคลากร - สกร.ประจำจังหวัดนครศรีธรรมราช')

@section('content')
<div class="space-y-6">
    {{-- Header Banner Card --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 flex flex-wrap items-center justify-between gap-5">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-900 border border-purple-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>ฐานข้อมูลบุคลากร สกร.ประจำจังหวัดนครศรีธรรมราช</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                ทะเบียนบุคลากร
            </h1>
            <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                ระบบจัดการข้อมูลบุคลากรแยกตามประเภท (ข้าราชการ, ลูกจ้างประจำ, พนักงานราชการ) พร้อมเลขประจำตัว 13 หลักและเลขที่บัญชีธนาคารเพื่อออกสลิปเงินเดือน
            </p>
        </div>

        <div class="text-right">
            <div class="text-xs font-semibold text-slate-500">บุคลากรทั้งหมดในระบบ</div>
            <div class="text-3xl font-extrabold text-purple-950 font-num">{{ number_format($totalEmployeesCount ?? $employees->total()) }} <span class="text-sm font-normal text-slate-500">คน</span></div>
        </div>
    </div>

    {{-- Category Tabs: ทั้งหมด / ข้าราชการ / ลูกจ้างประจำ / พนักงานราชการ --}}
    <div class="bg-white rounded-2xl p-2 shadow-xs border border-slate-200/80">
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('employees.index', array_merge(request()->except(['type', 'page']))) }}"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition {{ empty($type) ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
            >
                <span>👥 ทั้งหมด</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold font-num {{ empty($type) ? 'bg-purple-700 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ number_format($totalEmployeesCount ?? 0) }}
                </span>
            </a>

            <a
                href="{{ route('employees.index', array_merge(request()->except(['type', 'page']), ['type' => 'ข้าราชการ'])) }}"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition {{ $type === 'ข้าราชการ' ? 'bg-amber-500 text-white shadow-xs ring-2 ring-amber-300' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
            >
                <span>🏛️ ข้าราชการ</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold font-num {{ $type === 'ข้าราชการ' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-900' }}">
                    {{ number_format($civilServantCount ?? 0) }}
                </span>
            </a>

            <a
                href="{{ route('employees.index', array_merge(request()->except(['type', 'page']), ['type' => 'ลูกจ้างประจำ'])) }}"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition {{ $type === 'ลูกจ้างประจำ' ? 'bg-emerald-600 text-white shadow-xs ring-2 ring-emerald-300' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
            >
                <span>👷 ลูกจ้างประจำ</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold font-num {{ $type === 'ลูกจ้างประจำ' ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-900' }}">
                    {{ number_format($permanentCount ?? 0) }}
                </span>
            </a>

            <a
                href="{{ route('employees.index', array_merge(request()->except(['type', 'page']), ['type' => 'พนักงานราชการ'])) }}"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition {{ $type === 'พนักงานราชการ' ? 'bg-blue-600 text-white shadow-xs ring-2 ring-blue-300' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
            >
                <span>📋 พนักงานราชการ</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold font-num {{ $type === 'พนักงานราชการ' ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-900' }}">
                    {{ number_format($govEmployeeCount ?? 0) }}
                </span>
            </a>
        </div>
    </div>

    {{-- Search & Filter Card --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80 space-y-3">
        {{-- Quick Filter Badges --}}
        <div class="flex flex-wrap items-center gap-2 pb-2 border-b border-slate-100 text-xs">
            <span class="font-bold text-slate-500 mr-1">สถานะข้อมูล:</span>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['filter', 'page']))) }}"
                class="px-3 py-1.5 rounded-xl font-bold transition {{ empty($filter) ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
            >
                ทั้งหมด ({{ number_format($totalEmployeesCount ?? $employees->total()) }})
            </a>

            @if(($needsStatusReviewCount ?? 0) > 0)
                <a
                    href="{{ route('employees.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'needs_status_review'])) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition {{ $filter === 'needs_status_review' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-300' }}"
                >
                    <span class="w-2 h-2 rounded-full bg-amber-500 {{ $filter === 'needs_status_review' ? 'bg-white' : 'animate-ping' }}"></span>
                    <span>⚠️ ขาดข้อมูลเงินเดือนเกิน 1 เดือน ({{ number_format($needsStatusReviewCount) }} คน) รอระบุสถานะ</span>
                </a>
            @endif

            <a
                href="{{ route('employees.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'missing_citizen_id'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition {{ $filter === 'missing_citizen_id' ? 'bg-rose-600 text-white shadow-xs' : ($missingCitizenCount > 0 ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') }}"
            >
                @if($missingCitizenCount > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500 {{ $filter === 'missing_citizen_id' ? 'bg-white' : 'animate-ping' }}"></span>
                @endif
                <span>ยังไม่ระบุเลข 13 หลัก ({{ number_format($missingCitizenCount) }} คน)</span>
            </a>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['filter', 'page']), ['filter' => 'missing_bank'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition {{ $filter === 'missing_bank' ? 'bg-amber-600 text-white shadow-xs' : ($missingBankCount > 0 ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200') }}"
            >
                <span>ยังไม่ระบุเลขบัญชีธนาคาร ({{ number_format($missingBankCount) }} คน)</span>
            </a>
        </div>

        {{-- Status Filter Pills --}}
        <div class="flex flex-wrap items-center gap-2 pb-2 border-b border-slate-100 text-xs">
            <span class="font-bold text-slate-500 mr-1">สถานะบุคลากร:</span>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['status', 'page']))) }}"
                class="px-2.5 py-1 rounded-lg font-bold transition {{ empty($status) ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
            >
                ทุกสถานะ
            </a>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['status', 'page']), ['status' => 'ปกติ'])) }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold transition {{ $status === 'ปกติ' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }}"
            >
                <span>🟢 ปกติ</span>
                <span class="font-num font-bold">({{ number_format($statusNormalCount ?? 0) }})</span>
            </a>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['status', 'page']), ['status' => 'ย้าย'])) }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold transition {{ $status === 'ย้าย' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-800 hover:bg-blue-100 border border-blue-200' }}"
            >
                <span>🔄 ย้าย</span>
                <span class="font-num font-bold">({{ number_format($statusTransferredCount ?? 0) }})</span>
            </a>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['status', 'page']), ['status' => 'เกษียณ'])) }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold transition {{ $status === 'เกษียณ' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200' }}"
            >
                <span>🎖️ เกษียณ</span>
                <span class="font-num font-bold">({{ number_format($statusRetiredCount ?? 0) }})</span>
            </a>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['status', 'page']), ['status' => 'ลาออก'])) }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold transition {{ $status === 'ลาออก' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}"
            >
                <span>📤 ลาออก</span>
                <span class="font-num font-bold">({{ number_format($statusResignedCount ?? 0) }})</span>
            </a>
            <a
                href="{{ route('employees.index', array_merge(request()->except(['status', 'page']), ['status' => 'เสียชีวิต'])) }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold transition {{ $status === 'เสียชีวิต' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300' }}"
            >
                <span>⚫ เสียชีวิต</span>
                <span class="font-num font-bold">({{ number_format($statusDeceasedCount ?? 0) }})</span>
            </a>
        </div>

        <form action="{{ route('employees.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            @if(!empty($filter))
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            @if(!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative flex-1 min-w-[240px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="ค้นหาชื่อ-สกุล, เลขบัตร 13 หลัก, เลขที่บัญชี, หมายเหตุ..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                >
            </div>

            <select name="type" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium">
                <option value="">-- ทุกประเภทบุคลากร --</option>
                <option value="ข้าราชการ" {{ $type === 'ข้าราชการ' ? 'selected' : '' }}>ข้าราชการ</option>
                <option value="ลูกจ้างประจำ" {{ $type === 'ลูกจ้างประจำ' ? 'selected' : '' }}>ลูกจ้างประจำ</option>
                <option value="พนักงานราชการ" {{ $type === 'พนักงานราชการ' ? 'selected' : '' }}>พนักงานราชการ</option>
            </select>

            <select name="status" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium">
                <option value="">-- ทุกสถานะ --</option>
                <option value="ปกติ" {{ $status === 'ปกติ' ? 'selected' : '' }}>🟢 ปกติ</option>
                <option value="ย้าย" {{ $status === 'ย้าย' ? 'selected' : '' }}>🔄 ย้าย</option>
                <option value="เกษียณ" {{ $status === 'เกษียณ' ? 'selected' : '' }}>🎖️ เกษียณ</option>
                <option value="ลาออก" {{ $status === 'ลาออก' ? 'selected' : '' }}>📤 ลาออก</option>
                <option value="เสียชีวิต" {{ $status === 'เสียชีวิต' ? 'selected' : '' }}>⚫ เสียชีวิต</option>
            </select>

            <select name="district" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none bg-white font-medium">
                <option value="">-- ทุกอำเภอ / สังกัด / ระดับ --</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}" {{ $district === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-slate-900 hover:bg-black text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-xs transition">
                ค้นหา
            </button>

            @if($search !== '' || $type !== '' || $status !== '' || $district !== '' || $filter !== '')
                <a href="{{ route('employees.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                    ล้างค่า
                </a>
            @endif
        </form>
    </div>

    {{-- Bulk Edit Form --}}
    <form action="{{ route('employees.bulk-update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-bold text-slate-600">
                    แสดงรายชื่อ {{ $employees->firstItem() ?? 0 }} - {{ $employees->lastItem() ?? 0 }} จากทั้งหมด {{ number_format($employees->total()) }} คน
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 text-xs uppercase font-bold tracking-wider">
                            <th class="py-3 px-3 text-center w-12">#</th>
                            <th class="py-3 px-4">ชื่อ - นามสกุล</th>
                            <th class="py-3 px-3">ประเภท / สังกัด</th>
                            <th class="py-3 px-3 w-48">สถานะบุคลากร</th>
                            <th class="py-3 px-3 w-48">เลขประจำตัว 13 หลัก</th>
                            <th class="py-3 px-3 w-52">ตำแหน่งที่แสดงบนสลิป</th>
                            <th class="py-3 px-3 w-44">เลขที่บัญชี 10 หลัก</th>
                            <th class="py-3 px-3 text-center w-28">สลิปในระบบ</th>
                            <th class="py-3 px-3 text-center w-24">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-purple-50/30 transition">
                                <td class="py-3 px-3 text-center text-slate-400 font-num text-xs">
                                    {{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-800 text-xs font-bold flex items-center justify-center shrink-0">
                                            {{ mb_substr($emp->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span>{{ $emp->name }}</span>
                                            @if($emp->status_note)
                                                <div class="text-[10px] text-slate-500 font-normal mt-0.5">
                                                    {{ $emp->status_note }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-xs">
                                    <select
                                        name="employees[{{ $emp->id }}][employee_type]"
                                        class="w-full px-2 py-1 rounded-lg border text-xs font-semibold focus:ring-1 focus:ring-purple-600 outline-none transition {{ $emp->employee_type === 'ข้าราชการ' ? 'bg-amber-50 text-amber-900 border-amber-300' : ($emp->employee_type === 'ลูกจ้างประจำ' ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : 'bg-blue-50 text-blue-900 border-blue-300') }}"
                                    >
                                        <option value="ข้าราชการ" {{ $emp->employee_type === 'ข้าราชการ' ? 'selected' : '' }}>ข้าราชการ</option>
                                        <option value="ลูกจ้างประจำ" {{ $emp->employee_type === 'ลูกจ้างประจำ' ? 'selected' : '' }}>ลูกจ้างประจำ</option>
                                        <option value="พนักงานราชการ" {{ $emp->employee_type === 'พนักงานราชการ' ? 'selected' : '' }}>พนักงานราชการ</option>
                                    </select>
                                    @if($emp->level_or_district)
                                        <div class="text-[11px] text-slate-500 mt-1 truncate max-w-[130px]" title="{{ $emp->level_or_district }}">
                                            ({{ $emp->level_or_district }})
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-xs">
                                    <select
                                        name="employees[{{ $emp->id }}][status]"
                                        class="w-full px-2 py-1 rounded-lg border text-xs font-bold focus:ring-1 focus:ring-purple-600 outline-none transition {{ $emp->status === 'ย้าย' ? 'bg-blue-50 text-blue-900 border-blue-300' : ($emp->status === 'เกษียณ' ? 'bg-purple-50 text-purple-900 border-purple-300' : ($emp->status === 'ลาออก' ? 'bg-amber-50 text-amber-900 border-amber-300' : ($emp->status === 'เสียชีวิต' ? 'bg-slate-100 text-slate-800 border-slate-300' : 'bg-emerald-50 text-emerald-900 border-emerald-300'))) }}"
                                    >
                                        <option value="ปกติ" {{ $emp->status === 'ปกติ' ? 'selected' : '' }}>🟢 ปกติ</option>
                                        <option value="ย้าย" {{ $emp->status === 'ย้าย' ? 'selected' : '' }}>🔄 ย้าย</option>
                                        <option value="เกษียณ" {{ $emp->status === 'เกษียณ' ? 'selected' : '' }}>🎖️ เกษียณ</option>
                                        <option value="ลาออก" {{ $emp->status === 'ลาออก' ? 'selected' : '' }}>📤 ลาออก</option>
                                        <option value="เสียชีวิต" {{ $emp->status === 'เสียชีวิต' ? 'selected' : '' }}>⚫ เสียชีวิต</option>
                                    </select>

                                    @if(in_array($emp->id, $needsStatusReviewUserIds))
                                        <div class="mt-1">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-900 bg-amber-100 px-1.5 py-0.5 rounded border border-amber-300 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>ขาดเงินเดือน >1 ด.</span>
                                            </span>
                                        </div>
                                    @endif

                                    @if(isset($latestPayslipsByUserId[$emp->id]))
                                        <div class="text-[10px] text-slate-500 mt-1 truncate" title="รับเงินเดือนงวดล่าสุด: {{ $latestPayslipsByUserId[$emp->id]->last_month_name }} {{ $latestPayslipsByUserId[$emp->id]->last_year_be }}">
                                            งวดล่าสุด: {{ $latestPayslipsByUserId[$emp->id]->last_month_name }} {{ $latestPayslipsByUserId[$emp->id]->last_year_be }}
                                        </div>
                                    @else
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            ยังไม่มีข้อมูลเงินเดือน
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <input
                                        type="text"
                                        name="employees[{{ $emp->id }}][citizen_id]"
                                        value="{{ $emp->citizen_id }}"
                                        maxlength="13"
                                        placeholder="เลข 13 หลัก"
                                        class="w-full px-3 py-1.5 rounded-xl border {{ $emp->citizen_id ? 'border-emerald-300 bg-emerald-50/20 text-emerald-950 font-bold' : 'border-slate-300 bg-white' }} font-mono text-xs focus:ring-2 focus:ring-purple-600 outline-none transition"
                                    >
                                </td>
                                <td class="py-3 px-3">
                                    <input
                                        type="text"
                                        name="employees[{{ $emp->id }}][position]"
                                        value="{{ $emp->position }}"
                                        placeholder="เช่น นักวิชาการศึกษา"
                                        class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-purple-600 outline-none bg-white transition"
                                    >
                                </td>
                                <td class="py-3 px-3">
                                    <input
                                        type="text"
                                        name="employees[{{ $emp->id }}][bank_account]"
                                        value="{{ $emp->bank_account }}"
                                        placeholder="เลขบัญชี 10 หลัก"
                                        class="w-full px-3 py-1.5 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-purple-600 outline-none bg-white transition"
                                    >
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('dashboard', ['q' => $emp->name]) }}" class="inline-flex items-center gap-1 text-purple-700 hover:text-purple-950 hover:underline font-bold text-xs bg-purple-50 px-2.5 py-1 rounded-lg">
                                        <span>{{ $emp->payslips_count }} งวด</span>
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a
                                        href="{{ route('employees.edit', $emp) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>แก้ไข</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-500">
                                    <div class="text-base font-semibold">ไม่พบรายชื่อบุคลากร</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->count() > 0)
                <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        {{ $employees->links() }}
                    </div>
                    <button
                        type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-6 py-2.5 rounded-xl shadow-xs transition inline-flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>บันทึกข้อมูลที่แก้ไขในหน้านี้ทั้งหมด</span>
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection
