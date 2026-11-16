<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ระบบสลิปเงินเดือนออนไลน์ - สกร.ประจำจังหวัดนครศรีธรรมราช')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Sarabun', 'Inter', sans-serif;
        }
        .font-num {
            font-family: 'Inter', 'Sarabun', sans-serif;
            font-feature-settings: 'tnum' on, 'lnum' on;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body, html, .h-screen, .h-full, .overflow-hidden, .overflow-y-auto {
                height: auto !important;
                overflow: visible !important;
            }
            body {
                background: #fff !important;
                color: #000 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.05);
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased h-full overflow-hidden">
    @auth
    <div class="h-screen w-full flex overflow-hidden bg-slate-50">
        {{-- Mobile Backdrop --}}
        <div
            id="mobileSidebarBackdrop"
            onclick="toggleSidebar()"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 hidden md:hidden transition-all duration-300"
        ></div>

        {{-- LEFT SIDEBAR: Completely stationary & locked in place (Never moves when right content scrolls) --}}
        <aside
            id="appSidebar"
            class="no-print w-72 h-screen flex-shrink-0 flex flex-col z-40 fixed inset-y-0 left-0 md:static transition-transform duration-300 transform -translate-x-full md:translate-x-0 bg-[#0B1120] border-r border-slate-800/80 shadow-2xl md:shadow-none select-none"
        >
            <div class="flex flex-col h-full overflow-hidden">
                {{-- Agency Brand Header --}}
                <div class="p-4 border-b border-slate-800/80 bg-slate-900/60 flex items-center gap-3 shrink-0">
                    <div class="h-11 w-11 rounded-xl bg-white p-1 flex items-center justify-center shrink-0 shadow-md ring-1 ring-white/10">
                        <img src="{{ asset('images/logo.png') }}" alt="ตรากรมส่งเสริมการเรียนรู้" class="h-full w-full object-contain">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-extrabold text-white text-[14px] leading-tight tracking-tight">
                            สกร. นครศรีธรรมราช
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate font-normal">
                            ระบบสลิปเงินเดือนออนไลน์
                        </div>
                        <div class="inline-flex items-center gap-1.5 mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>e-PaySlip (ข้าราชการ)</span>
                        </div>
                    </div>

                    {{-- Close button for mobile --}}
                    <button type="button" onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Sidebar Navigation Menu (Modern, Clean, with all 3 circled items) --}}
                <nav class="p-3.5 flex-1 space-y-6 overflow-y-auto">
                    @if(auth()->user()->isAdmin())
                        {{-- Group 1: การจัดการเงินเดือน (All 3 items that were on the right side) --}}
                        <div class="space-y-1.5">
                            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400/80 flex items-center justify-between">
                                <span>การจัดการเงินเดือน</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800/80 text-purple-300">พ.ศ. 2569</span>
                            </div>

                            @php
                                $isOnDashboard = request()->routeIs('dashboard');
                                $curTab = request()->query('tab', 'batches');
                                if (request()->filled('q')) $curTab = 'search';
                                $totalBatches = $systemNotifications['total_batches'] ?? 9;
                            @endphp

                            {{-- 1. รายการงวดเงินเดือน --}}
                            <a
                                href="{{ route('dashboard', ['tab' => 'batches']) }}#batches"
                                onclick="handleSidebarTab(event, 'batches')"
                                id="nav-item-batches"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ ($isOnDashboard && $curTab === 'batches') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ ($isOnDashboard && $curTab === 'batches') ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="flex-1">รายการงวดเงินเดือน</span>
                                <span class="nav-badge px-2 py-0.5 rounded-full text-[11px] font-bold border transition-colors {{ ($isOnDashboard && $curTab === 'batches') ? 'bg-white/20 text-white border-white/30' : 'bg-slate-800 text-slate-300 border-slate-700/60' }}">
                                    {{ $totalBatches }}
                                </span>
                            </a>

                            {{-- 2. อัปโหลดข้อมูลเงินเดือน (Excel) --}}
                            <a
                                href="{{ route('dashboard', ['tab' => 'upload']) }}#upload"
                                onclick="handleSidebarTab(event, 'upload')"
                                id="nav-item-upload"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ ($isOnDashboard && $curTab === 'upload') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ ($isOnDashboard && $curTab === 'upload') ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span class="flex-1">อัปโหลดข้อมูลเงินเดือน (Excel)</span>
                                @if(session('duplicate_warning'))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900 animate-pulse">
                                        เตือนงวดซ้ำ
                                    </span>
                                @endif
                            </a>

                            {{-- 3. ค้นหาสลิปเงินเดือนด่วน --}}
                            <a
                                href="{{ route('dashboard', ['tab' => 'search']) }}#search"
                                onclick="handleSidebarTab(event, 'search')"
                                id="nav-item-search"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ ($isOnDashboard && $curTab === 'search') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ ($isOnDashboard && $curTab === 'search') ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span class="flex-1">ค้นหาสลิปเงินเดือนด่วน</span>
                            </a>

                            {{-- 4. สรุปการเบิกจ่ายรายปี (Annual Dashboard) --}}
                            @php
                                $isOnAnnualReport = request()->routeIs('reports.annual*');
                            @endphp
                            <a
                                href="{{ route('reports.annual') }}"
                                id="nav-item-annual"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ $isOnAnnualReport ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ $isOnAnnualReport ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="flex-1">สรุปการเบิกจ่ายรายปี</span>
                                <span class="nav-badge px-1.5 py-0.5 rounded text-[10px] font-bold border transition-colors {{ $isOnAnnualReport ? 'bg-white/20 text-white border-white/30' : 'bg-purple-950/60 text-purple-300 border-purple-800/60' }}">
                                    กราฟ
                                </span>
                            </a>
                        </div>

                        {{-- Group 2: ฐานข้อมูลบุคลากร (แยก ข้าราชการ / ลูกจ้างประจำ / พนักงานราชการ) --}}
                        <div class="space-y-1.5">
                            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400/80 flex items-center justify-between">
                                <span>ฐานข้อมูลบุคลากร</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800/80 text-purple-300 font-num">{{ $systemNotifications['count_total_employees'] ?? 0 }} คน</span>
                            </div>

                            @php
                                $isOnEmployees = request()->routeIs('employees.*');
                                $curEmpType = request()->query('type', '');
                                $isAllEmployees = $isOnEmployees && empty($curEmpType);
                                $isCivil = $isOnEmployees && $curEmpType === 'ข้าราชการ';
                                $isPermanent = $isOnEmployees && $curEmpType === 'ลูกจ้างประจำ';
                                $isGov = $isOnEmployees && $curEmpType === 'พนักงานราชการ';
                                $missingCId = $systemNotifications['missing_citizen_id'] ?? 0;
                            @endphp

                            {{-- ทะเบียนบุคลากร (ทั้งหมด) --}}
                            <a
                                href="{{ route('employees.index') }}"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ $isAllEmployees ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ $isAllEmployees ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="flex-1">ทะเบียนบุคลากร</span>
                                @if($missingCId > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/25 text-rose-300 border border-rose-500/40" title="ยังไม่ระบุเลข 13 หลัก {{ $missingCId }} คน">
                                        ขาด {{ $missingCId }}
                                    </span>
                                @endif
                            </a>

                            {{-- Sub-items แยก 3 ประเภทบุคลากร --}}
                            <div class="pl-3 space-y-1 border-l-2 border-slate-800/80 ml-5 my-1">
                                {{-- 1. ข้าราชการ --}}
                                <a
                                    href="{{ route('employees.index', ['type' => 'ข้าราชการ']) }}"
                                    class="group flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-all {{ $isCivil ? 'bg-purple-600/30 text-purple-200 font-bold border border-purple-500/40' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isCivil ? 'bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></span>
                                        <span>ข้าราชการ</span>
                                    </div>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold font-num {{ $isCivil ? 'bg-purple-500/30 text-purple-200' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $systemNotifications['count_civil'] ?? 0 }}
                                    </span>
                                </a>

                                {{-- 2. ลูกจ้างประจำ --}}
                                <a
                                    href="{{ route('employees.index', ['type' => 'ลูกจ้างประจำ']) }}"
                                    class="group flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-all {{ $isPermanent ? 'bg-emerald-600/30 text-emerald-200 font-bold border border-emerald-500/40' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isPermanent ? 'bg-emerald-400' : 'bg-slate-600 group-hover:bg-emerald-400' }}"></span>
                                        <span>ลูกจ้างประจำ</span>
                                    </div>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold font-num {{ $isPermanent ? 'bg-emerald-500/30 text-emerald-200' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $systemNotifications['count_permanent'] ?? 0 }}
                                    </span>
                                </a>

                                {{-- 3. พนักงานราชการ --}}
                                <a
                                    href="{{ route('employees.index', ['type' => 'พนักงานราชการ']) }}"
                                    class="group flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-all {{ $isGov ? 'bg-blue-600/30 text-blue-200 font-bold border border-blue-500/40' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isGov ? 'bg-blue-400' : 'bg-slate-600 group-hover:bg-blue-400' }}"></span>
                                        <span>พนักงานราชการ</span>
                                    </div>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold font-num {{ $isGov ? 'bg-blue-500/30 text-blue-200' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $systemNotifications['count_gov'] ?? 0 }}
                                    </span>
                                </a>

                                {{-- Alert: ขาดข้อมูลเงินเดือน > 1 เดือน --}}
                                @if(($systemNotifications['needs_status_review'] ?? 0) > 0)
                                    <a
                                        href="{{ route('employees.index', ['filter' => 'needs_status_review']) }}"
                                        class="group flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-all bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30 hover:bg-amber-500/30"
                                        title="ขาดข้อมูลเงินเดือนเกิน 1 เดือน รอระบุสถานะ"
                                    >
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-ping"></span>
                                            <span class="truncate">รอระบุสถานะ (>1 ด.)</span>
                                        </div>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold font-num bg-amber-500 text-white shrink-0">
                                            {{ $systemNotifications['needs_status_review'] }}
                                        </span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Group 3: การตั้งค่าระบบ & ผู้ใช้งาน --}}
                        <div class="space-y-1.5">
                            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">
                                การตั้งค่าระบบ & ผู้ใช้งาน
                            </div>

                            <a
                                href="{{ route('users.index') }}"
                                class="group flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span class="truncate">จัดการผู้ใช้งาน & รหัสผ่าน</span>
                                </div>
                                @if(isset($systemNotifications['count_users_all']))
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold font-num shrink-0 {{ request()->routeIs('users.*') ? 'bg-white/20 text-white border border-white/30' : 'bg-slate-800 text-slate-300 border border-slate-700/60' }}">
                                        {{ $systemNotifications['count_users_all'] }}
                                    </span>
                                @endif
                            </a>

                            <a
                                href="{{ route('activity-logs.index') }}"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('activity-logs.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ request()->routeIs('activity-logs.*') ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span>ประวัติการใช้งาน (Logs)</span>
                            </a>

                            <a
                                href="{{ route('settings.edit') }}"
                                class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] font-medium' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 transition-colors nav-icon {{ request()->routeIs('settings.*') ? 'text-white' : 'text-slate-400 group-hover:text-purple-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>ตั้งค่าหัวสลิป & ลายเซ็น</span>
                            </a>

                            <button
                                type="button"
                                onclick="openPasswordModal()"
                                class="w-full group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium text-slate-400 hover:text-white hover:bg-white/[0.06] transition text-left"
                            >
                                <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>เปลี่ยนรหัสผ่านของฉัน</span>
                            </button>
                        </div>
                    @else
                        {{-- Regular Employee Menu --}}
                        <div class="space-y-1.5">
                            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">
                                เมนูบุคลากร
                            </div>

                            <a
                                href="{{ route('my-payslips') }}"
                                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-950/40"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>สลิปเงินเดือนของฉัน</span>
                            </a>

                            <button
                                type="button"
                                onclick="openPasswordModal()"
                                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-medium text-slate-400 hover:text-white hover:bg-white/[0.06] transition text-left"
                            >
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>เปลี่ยนรหัสผ่าน</span>
                            </button>
                        </div>
                    @endif
                </nav>

                {{-- User Profile Card & Logout Footer --}}
                <div class="p-3 border-t border-slate-800/80 bg-slate-900/50 shrink-0">
                    <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-2.5 flex items-center justify-between gap-2.5 shadow-sm">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-white truncate leading-tight">
                                    {{ auth()->user()->name }}
                                </div>
                                <div class="text-[10px] text-slate-400 truncate flex items-center gap-1 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>{{ auth()->user()->isAdmin() ? auth()->user()->role_label : (auth()->user()->employee_type ?: 'บุคลากร') }}</span>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                            @csrf
                            <button
                                type="submit"
                                title="ออกจากระบบ"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT (RIGHT-HAND SIDE: Independent scrolling, Left Sidebar never moves) --}}
        <div class="flex-1 flex flex-col h-screen min-w-0 overflow-y-auto bg-slate-50">
            {{-- Top Navbar Bar --}}
            <header class="no-print bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
                <div class="px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-4">
                    {{-- Mobile menu button & breadcrumbs --}}
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            onclick="toggleSidebar()"
                            class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                            aria-label="เปิดเมนู"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <div>
                            <div class="flex items-center gap-2 text-[11px] font-semibold text-purple-900/70">
                                <span>สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-500">ระบบสลิปเงินเดือน</span>
                            </div>
                            <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                                @yield('title', 'ระบบสลิปเงินเดือนออนไลน์')
                            </h1>
                        </div>
                    </div>

                    {{-- Top Header Action & Notification Center --}}
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->isAdmin())
                            {{-- Notification Center Dropdown --}}
                            <div class="relative" id="notificationDropdownContainer">
                                <button
                                    type="button"
                                    id="notificationBellBtn"
                                    onclick="toggleNotifications()"
                                    class="relative p-2 rounded-xl text-slate-600 hover:text-purple-900 hover:bg-purple-50 transition border border-slate-200/80 bg-white shadow-2xs"
                                    aria-label="การแจ้งเตือนระบบ"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    @php
                                        $alertsCount = $systemNotifications['total_alerts'] ?? 0;
                                    @endphp
                                    @if($alertsCount > 0)
                                        <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-white text-[9px] font-black items-center justify-center">{{ $alertsCount }}</span>
                                        </span>
                                    @endif
                                </button>

                                {{-- Notification Panel Dropdown --}}
                                <div
                                    id="notificationMenu"
                                    class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 py-3 z-50 text-xs transition-all"
                                >
                                    <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                        <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                            <span>ศูนย์แจ้งเตือนระบบ</span>
                                        </div>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $alertsCount > 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $alertsCount > 0 ? $alertsCount . ' รายการเตือน' : 'ระบบปกติ' }}
                                        </span>
                                    </div>

                                    <div class="p-3 space-y-2.5 max-h-80 overflow-y-auto">
                                        {{-- Alert 1: Missing Citizen ID --}}
                                        @if(($systemNotifications['missing_citizen_id'] ?? 0) > 0)
                                            <div class="p-3 bg-rose-50/80 border border-rose-200 rounded-xl space-y-1.5">
                                                <div class="flex items-center gap-2 font-bold text-rose-900">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                                    <span>ยังไม่ระบุเลขบัตรประชาชน 13 หลัก</span>
                                                </div>
                                                <p class="text-rose-700 text-[11px] leading-relaxed">
                                                    มีข้าราชการ {{ $systemNotifications['missing_citizen_id'] }} คน ที่ยังไม่มีเลข 13 หลัก ซึ่งจำเป็นสำหรับการออกสลิปและการล็อกอิน
                                                </p>
                                                <a href="{{ route('employees.index', ['filter' => 'missing_citizen_id']) }}" class="inline-flex items-center gap-1 mt-1 text-[11px] font-bold text-rose-800 hover:text-rose-950 underline">
                                                    <span>ตรวจสอบและระบุเลข 13 หลัก</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            </div>
                                        @else
                                            <div class="p-2.5 bg-emerald-50/60 border border-emerald-200 rounded-xl flex items-center gap-2 text-emerald-800">
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>เลขบัตรประชาชน 13 หลัก ครบถ้วนทุกคน</span>
                                            </div>
                                        @endif

                                        {{-- Alert 2: Missing Bank --}}
                                        @if(($systemNotifications['missing_bank'] ?? 0) > 0)
                                            <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl space-y-1">
                                                <div class="flex items-center gap-2 font-bold text-amber-900">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                    <span>ยังไม่ระบุเลขที่บัญชีธนาคาร ({{ $systemNotifications['missing_bank'] }} คน)</span>
                                                </div>
                                                <a href="{{ route('employees.index', ['filter' => 'missing_bank']) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 hover:text-amber-950 underline">
                                                    <span>ตรวจสอบรายชื่อ</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            </div>
                                        @endif

                                        {{-- Alert 3: Missing Payroll > 1 Month (Needs Status Review) --}}
                                        @if(($systemNotifications['needs_status_review'] ?? 0) > 0)
                                            <div class="p-3 bg-rose-50/80 border border-rose-200 rounded-xl space-y-1.5">
                                                <div class="flex items-center gap-2 font-bold text-rose-900">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                                    <span>ขาดข้อมูลเงินเดือนเกิน 1 เดือน ({{ $systemNotifications['needs_status_review'] }} คน)</span>
                                                </div>
                                                <p class="text-rose-700 text-[11px] leading-relaxed">
                                                    มีบุคลากรที่ไม่มีข้อมูลเงินเดือนในงวดปัจจุบันและงวดก่อนหน้าเกิน 1 เดือน แต่สถานะยังเป็น "ปกติ" โปรดตรวจสอบและระบุสถานะ (ย้าย/เกษียณ/ลาออก/เสียชีวิต)
                                                </p>
                                                <a href="{{ route('employees.index', ['filter' => 'needs_status_review']) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-900 hover:text-rose-950 underline">
                                                    <span>ตรวจสอบและเลือกสถานะ ({{ $systemNotifications['needs_status_review'] }} คน)</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            </div>
                                        @endif

                                        {{-- Status 3: Batch import status --}}
                                        @if($latest = ($systemNotifications['latest_batch'] ?? null))
                                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-2.5 text-slate-700">
                                                <div class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                                <div class="text-[11px]">
                                                    <div class="font-bold text-slate-800">งวดล่าสุด: {{ $latest->month_name }} {{ $latest->year_be }}</div>
                                                    <div class="text-slate-500">รวม {{ number_format($latest->payslips_count) }} สลิป พร้อมพิมพ์</div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Protection 4: Duplicate Check Active --}}
                                        <div class="p-2.5 bg-purple-50/60 border border-purple-200/60 rounded-xl flex items-center gap-2 text-[11px] text-purple-900">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span>ระบบตรวจจับและป้องกันข้อมูลงวดซ้ำ: พร้อมทำงาน</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-50 text-purple-900 border border-purple-200/70 font-semibold shadow-2xs text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>งวดเงินเดือนปี พ.ศ. 2569 (ข้าราชการ)</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Page Body --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto space-y-6">
                    {{-- Alert: Success --}}
                    @if(session('success'))
                        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-4 py-3.5 rounded-2xl flex items-center justify-between shadow-xs no-print">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm">{{ session('success') }}</span>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold p-1 text-xl leading-none">&times;</button>
                        </div>
                    @endif

                    {{-- Alert: Info --}}
                    @if(session('info'))
                        <div class="bg-blue-50 border border-blue-300 text-blue-900 px-4 py-3.5 rounded-2xl flex items-center justify-between shadow-xs no-print">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm">{{ session('info') }}</span>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-blue-700 hover:text-blue-950 font-bold p-1 text-xl leading-none">&times;</button>
                        </div>
                    @endif

                    {{-- Alert: Warning --}}
                    @if(session('warning'))
                        <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-3.5 rounded-2xl flex items-center justify-between shadow-xs no-print">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm">{{ session('warning') }}</span>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-amber-700 hover:text-amber-950 font-bold p-1 text-xl leading-none">&times;</button>
                        </div>
                    @endif

                    {{-- Alert: Duplicate Warning --}}
                    @if(session('duplicate_warning'))
                        <div class="bg-amber-50 border-2 border-amber-400 text-amber-950 px-5 py-4 rounded-2xl flex items-center justify-between shadow-xs no-print">
                            <div class="flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-amber-900">แจ้งเตือนการอัปโหลดไฟล์ซ้ำ</div>
                                    <div class="text-xs text-amber-800 mt-0.5">{{ session('duplicate_warning') }}</div>
                                </div>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-amber-800 hover:text-amber-950 font-bold p-1 text-xl leading-none">&times;</button>
                        </div>
                    @endif

                    {{-- Alert: Error --}}
                    @if(session('error'))
                        <div class="bg-rose-50 border border-rose-300 text-rose-900 px-4 py-3.5 rounded-2xl flex items-center justify-between shadow-xs no-print">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm">{{ session('error') }}</span>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-950 font-bold p-1 text-xl leading-none">&times;</button>
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div class="bg-rose-50 border border-rose-300 text-rose-900 p-4 rounded-2xl shadow-xs no-print">
                            <div class="font-bold text-sm mb-1.5 flex items-center gap-2 text-rose-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>โปรดตรวจสอบข้อผิดพลาด:</span>
                            </div>
                            <ul class="list-disc list-inside text-xs space-y-1 pl-7">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

            {{-- Footer --}}
            <footer class="no-print bg-white border-t border-slate-200/80 py-4 px-6 text-center text-xs text-slate-500">
                สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช | ระบบสลิปเงินเดือนออนไลน์ (e-PaySlip)
            </footer>
        </div>
    </div>

    {{-- Global Change Password Modal (For Admin, Finance Officer, and Employee) --}}
    <div
        id="passwordModal"
        class="no-print fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs hidden flex items-center justify-center p-4 transition-all duration-200"
    >
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 space-y-5 border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5 font-bold text-slate-800 text-base">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <span>เปลี่ยนรหัสผ่านเข้าสู่ระบบ ({{ auth()->user()->name }})</span>
                </div>
                <button type="button" onclick="closePasswordModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 leading-none">&times;</button>
            </div>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        รหัสผ่านปัจจุบัน <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="current_password"
                        required
                        placeholder="กรอกรหัสผ่านปัจจุบัน"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        รหัสผ่านใหม่ <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="new_password"
                        required
                        minlength="4"
                        placeholder="อย่างน้อย 4 ตัวอักษร"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        ยืนยันรหัสผ่านใหม่อีกครั้ง <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="new_password_confirmation"
                        required
                        minlength="4"
                        placeholder="กรอกรหัสผ่านใหม่อีกครั้งให้ตรงกัน"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        onclick="closePasswordModal()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                    >
                        ยกเลิก
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl text-xs font-bold bg-purple-900 hover:bg-purple-950 text-white shadow-xs transition"
                    >
                        บันทึกรหัสผ่านใหม่
                    </button>
                </div>
            </form>
        </div>
    </div>
    @else
        {{-- For unauthenticated users (Login Page) --}}
        <main class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-slate-900 via-purple-950 to-slate-900 p-4">
            @yield('content')
        </main>
    @endauth

    <script>
        function openPasswordModal() {
            const modal = document.getElementById('passwordModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closePasswordModal() {
            const modal = document.getElementById('passwordModal');
            if (modal) modal.classList.add('hidden');
        }
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }
        }

        function toggleNotifications() {
            const menu = document.getElementById('notificationMenu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('notificationDropdownContainer');
            const menu = document.getElementById('notificationMenu');
            if (container && menu && !container.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Sidebar Tab Handler
        function handleSidebarTab(event, tabName) {
            if (typeof switchTab === 'function') {
                event.preventDefault();
                switchTab(tabName);
                // On mobile, close sidebar after clicking
                const sidebar = document.getElementById('appSidebar');
                const backdrop = document.getElementById('mobileSidebarBackdrop');
                if (window.innerWidth < 768 && sidebar && !sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            } else {
                // Not on dashboard page: default link takes user to /dashboard?tab=...#...
            }
        }

        // Update active highlight on left sidebar items
        function updateSidebarActiveTab(tabName) {
            const tabBatches = document.getElementById('nav-item-batches');
            const tabUpload = document.getElementById('nav-item-upload');
            const tabSearch = document.getElementById('nav-item-search');

            const activeClasses = ['bg-gradient-to-r', 'from-purple-600', 'to-indigo-600', 'text-white', 'shadow-md', 'shadow-purple-950/40', 'font-semibold'];
            const inactiveClasses = ['text-slate-400', 'hover:text-white', 'hover:bg-white/[0.06]', 'font-medium'];

            const items = [
                { name: 'batches', el: tabBatches },
                { name: 'upload', el: tabUpload },
                { name: 'search', el: tabSearch }
            ];

            items.forEach(item => {
                if (!item.el) return;
                const icon = item.el.querySelector('.nav-icon');
                const badge = item.el.querySelector('.nav-badge');

                if (item.name === tabName) {
                    item.el.classList.remove(...inactiveClasses);
                    item.el.classList.add(...activeClasses);
                    if (icon) {
                        icon.classList.remove('text-slate-400');
                        icon.classList.add('text-white');
                    }
                    if (badge) {
                        badge.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700/60');
                        badge.classList.add('bg-white/20', 'text-white', 'border-white/30');
                    }
                } else {
                    item.el.classList.remove(...activeClasses);
                    item.el.classList.add(...inactiveClasses);
                    if (icon) {
                        icon.classList.remove('text-white');
                        icon.classList.add('text-slate-400');
                    }
                    if (badge) {
                        badge.classList.remove('bg-white/20', 'text-white', 'border-white/30');
                        badge.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700/60');
                    }
                }
            });
        }
    </script>
</body>
</html>
