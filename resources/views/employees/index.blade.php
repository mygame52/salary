@extends('layouts.app')

@section('title', 'ทะเบียนบุคลากร (กรอกเลขบัตรประชาชน 13 หลัก)')

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">ทะเบียนบุคลากร (กรอกเลขประจำตัวผู้เสียภาษี 13 หลัก / ตำแหน่ง)</h1>
            <p class="text-xs text-slate-500 mt-1">
                เมื่อกรอกเลขบัตรประชาชน 13 หลักหรือแก้ไขตำแหน่งที่นี่ ระบบจะอัปเดตลงในสลิปเงินเดือนทุกงวดของคนนั้นให้อัตโนมัติ และบุคลากรจะสามารถใช้เลข 13 หลักล็อกอินได้ทันที
            </p>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
        <form action="{{ route('employees.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="ค้นหาชื่อ-สกุล, เลขบัตร 13 หลัก, เลขที่บัญชี..."
                class="flex-1 min-w-[220px] px-4 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-purple-600"
            >

            <select name="type" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-purple-600">
                <option value="">-- ทุกประเภทบุคลากร --</option>
                <option value="พนักงานราชการ" {{ $type === 'พนักงานราชการ' ? 'selected' : '' }}>พนักงานราชการ</option>
                <option value="ข้าราชการ" {{ $type === 'ข้าราชการ' ? 'selected' : '' }}>ข้าราชการ</option>
            </select>

            <select name="district" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-purple-600">
                <option value="">-- ทุกอำเภอ/สังกัด/ระดับ --</option>
                @foreach($districts as $d)
                    <option value="{{ $d }}" {{ $district === $d ? 'selected' : '' }}>{{ $d }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold px-4 py-2 rounded-xl">
                ค้นหา
            </button>

            @if($search !== '' || $type !== '' || $district !== '')
                <a href="{{ route('employees.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm px-3 py-2 rounded-xl">
                    ล้างค่า
                </a>
            @endif
        </form>
    </div>

    {{-- Bulk Edit Form --}}
    <form action="{{ route('employees.bulk-update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-700">
                    แสดงรายชื่อ {{ $employees->firstItem() ?? 0 }} - {{ $employees->lastItem() ?? 0 }} จากทั้งหมด {{ $employees->total() }} คน
                </span>
                <button
                    type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-xl shadow transition"
                >
                    บันทึกข้อมูลที่แก้ไขในหน้านี้ทั้งหมด
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-600 text-xs uppercase">
                            <th class="py-3 px-3 text-center w-12">#</th>
                            <th class="py-3 px-4">ชื่อ - นามสกุล</th>
                            <th class="py-3 px-3">ประเภท / สังกัด</th>
                            <th class="py-3 px-3 w-52">เลขประจำตัวผู้เสียภาษี (13 หลัก)</th>
                            <th class="py-3 px-3 w-52">ตำแหน่งที่แสดงบนสลิป</th>
                            <th class="py-3 px-3 w-44">เลขที่บัญชี 10 หลัก</th>
                            <th class="py-3 px-3 text-center w-24">จำนวนสลิป</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 text-center text-slate-500">
                                    {{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-2.5 px-4 font-semibold text-slate-800">
                                    {{ $emp->name }}
                                </td>
                                <td class="py-2.5 px-3 text-xs">
                                    <span class="inline-block px-2 py-0.5 rounded-full font-semibold {{ $emp->employee_type === 'พนักงานราชการ' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $emp->employee_type }}
                                    </span>
                                    @if($emp->level_or_district)
                                        <span class="text-slate-600 ml-1">({{ $emp->level_or_district }})</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <input
                                        type="text"
                                        name="employees[{{ $emp->id }}][citizen_id]"
                                        value="{{ $emp->citizen_id }}"
                                        maxlength="13"
                                        placeholder="กรอกเลข 13 หลัก"
                                        class="w-full px-3 py-1.5 rounded-lg border {{ $emp->citizen_id ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-300' }} font-mono text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                                    >
                                </td>
                                <td class="py-2.5 px-3">
                                    <input
                                        type="text"
                                        name="employees[{{ $emp->id }}][position]"
                                        value="{{ $emp->position }}"
                                        placeholder="เช่น นักวิชาการศึกษา"
                                        class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                                    >
                                </td>
                                <td class="py-2.5 px-3">
                                    <input
                                        type="text"
                                        name="employees[{{ $emp->id }}][bank_account]"
                                        value="{{ $emp->bank_account }}"
                                        placeholder="เลขบัญชี 10 หลัก"
                                        class="w-full px-3 py-1.5 rounded-lg border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-purple-600 outline-none"
                                    >
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('dashboard', ['q' => $emp->name]) }}" class="text-purple-700 hover:underline font-semibold text-xs">
                                        {{ $emp->payslips_count }} งวด
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">ไม่พบรายชื่อบุคลากร</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    {{ $employees->links() }}
                </div>
                <button
                    type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-2.5 rounded-xl shadow transition"
                >
                    บันทึกข้อมูลที่แก้ไขในหน้านี้ทั้งหมด
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
