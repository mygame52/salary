@extends('layouts.app')

@section('title', 'ตั้งค่าหัวสลิปและลายเซ็นเจ้าหน้าที่การเงิน')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-purple-900 text-white px-6 py-4">
            <h1 class="text-lg font-bold">ตั้งค่าข้อมูลหน่วยงาน โลโก้ และลายเซ็นเจ้าหน้าที่การเงิน</h1>
            <p class="text-xs text-purple-200 mt-0.5">ข้อมูลในหน้านี้จะแสดงบนหัวและท้ายสลิปเงินเดือนทุกใบ</p>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">ชื่อหน่วยงาน (มุมซ้ายบนสลิป)</label>
                    <input type="text" name="agency_name" value="{{ $settings['agency_name'] }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">เลขประจำตัวผู้เสียภาษีหน่วยงาน</label>
                    <input type="text" name="agency_tax_id" value="{{ $settings['agency_tax_id'] }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-purple-600 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-200 pt-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">ชื่อผู้ลงนามท้ายสลิป (ในวงเล็บ)</label>
                    <input type="text" name="signer_name" value="{{ $settings['signer_name'] }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">บรรทัดตำแหน่งผู้ลงนาม</label>
                    <input type="text" name="signer_position" value="{{ $settings['signer_position'] }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">ข้อความหลังเส้นลงชื่อ</label>
                    <input type="text" name="signer_title" value="{{ $settings['signer_title'] }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-slate-200 pt-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">ตราสัญลักษณ์หน่วยงานปัจจุบัน</label>
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ asset($settings['logo_path'] ?? 'images/logo.png') }}" alt="Logo" class="w-20 h-20 object-contain border border-slate-200 rounded-xl p-1 bg-white">
                        <div class="text-xs text-slate-500">ตราสัญลักษณ์กรมส่งเสริมการเรียนรู้</div>
                    </div>
                    <input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-purple-50 file:text-purple-800 file:font-semibold border border-slate-300 rounded-xl p-1">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">รูปลายเซ็นเจ้าหน้าที่การเงิน (พื้นหลังโปร่งใส PNG)</label>
                    <div class="flex items-center gap-4 mb-3 h-20 px-4 border border-slate-200 rounded-xl bg-slate-50">
                        @if(!empty($settings['signature_image']))
                            <img src="{{ asset($settings['signature_image']) }}" alt="Signature" class="max-h-14 object-contain">
                        @else
                            <svg width="115" height="38" viewBox="0 0 150 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M32 34 C18 33, 18 17, 31 18 C40 19, 43 32, 33 34 C44 26, 48 6, 46 3 C44 10, 40 31, 47 34 C53 36, 59 23, 57 20 C55 25, 55 33, 61 33 C66 33, 71 23, 70 21 C69 26, 70 32, 74 32 C78 32, 83 23, 86 24 C89 25, 89 31, 93 30 C98 29, 106 22, 118 19" stroke="#2818b5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-xs text-slate-500">(ลายเซ็นเริ่มต้นตามแบบฟอร์ม)</span>
                        @endif
                    </div>
                    <input type="file" name="signature_file" accept="image/*" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-purple-50 file:text-purple-800 file:font-semibold border border-slate-300 rounded-xl p-1">
                </div>
            </div>

            <div class="pt-3 flex justify-end">
                <button type="submit" class="bg-purple-800 hover:bg-purple-900 text-white font-bold px-6 py-2.5 rounded-xl shadow transition text-sm">
                    บันทึกการตั้งค่า
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
