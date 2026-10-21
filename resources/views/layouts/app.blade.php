<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ระบบสลิปเงินเดือนออนไลน์ - สกร.ประจำจังหวัดนครศรีธรรมราช')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    @auth
    <nav class="bg-purple-900 text-white shadow-md no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('my-payslips') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-11 w-11 rounded-full bg-white p-0.5 object-contain shadow">
                        <div>
                            <div class="font-bold text-base leading-tight">ระบบสลิปเงินเดือนออนไลน์ (e-PaySlip)</div>
                            <div class="text-xs text-purple-200">สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช</div>
                        </div>
                    </a>
                </div>

                <div class="flex items-center gap-2 sm:gap-4 text-sm">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('dashboard') || request()->routeIs('batches.*') ? 'bg-purple-800 text-amber-300' : 'hover:bg-purple-800' }}">
                            งวดเงินเดือน / อัปโหลดไฟล์
                        </a>
                        <a href="{{ route('employees.index') }}" class="px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('employees.*') ? 'bg-purple-800 text-amber-300' : 'hover:bg-purple-800' }}">
                            ทะเบียนบุคลากร (กรอกเลข 13 หลัก)
                        </a>
                        <a href="{{ route('settings.edit') }}" class="px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('settings.*') ? 'bg-purple-800 text-amber-300' : 'hover:bg-purple-800' }}">
                            ตั้งค่าหัวสลิป / ลายเซ็น
                        </a>
                    @else
                        <a href="{{ route('my-payslips') }}" class="px-3 py-2 rounded-lg font-medium bg-purple-800 text-amber-300">
                            สลิปเงินเดือนของฉัน
                        </a>
                    @endif

                    <div class="border-l border-purple-700 pl-3 flex items-center gap-3">
                        <span class="hidden md:inline text-purple-100">
                            {{ auth()->user()->name }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                ออกจากระบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between no-print">
                    <span class="font-medium">{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl no-print">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 no-print">
        สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช | ระบบสลิปเงินเดือนออนไลน์ (PaySlip)
    </footer>
</body>
</html>
