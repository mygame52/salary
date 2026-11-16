@extends('layouts.app')

@section('title', 'ตั้งค่าหัวสลิปและลายเซ็นเจ้าหน้าที่การเงิน')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-900 border border-purple-200 mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>การกำหนดค่าระบบ</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            ตั้งค่าข้อมูลหน่วยงาน หัวสลิป และลายเซ็น
        </h1>
        <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
            ข้อมูลในหน้านี้จะนำไปแสดงบนหัวใบสลิปเงินเดือนและตำแหน่งลงชื่อท้ายสลิปทุกใบในระบบโดยอัตโนมัติ
        </p>
    </div>

    {{-- Settings Form Card --}}
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Section 1: ข้อมูลหน่วยงาน --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800">1. ข้อมูลหน่วยงาน & หัวสลิป</h2>
                <p class="text-xs text-slate-500 mt-0.5">ระบุชื่อทางการและเลขประจำตัวผู้เสียภาษี 13 หลัก</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ชื่อหน่วยงาน (มุมซ้ายบนสลิป)
                    </label>
                    <input
                        type="text"
                        name="agency_name"
                        value="{{ $settings['agency_name'] }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        เลขประจำตัวผู้เสียภาษีหน่วยงาน (13 หลัก)
                    </label>
                    <input
                        type="text"
                        name="agency_tax_id"
                        value="{{ $settings['agency_tax_id'] }}"
                        required
                        maxlength="13"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>
            </div>

            {{-- Logo Section --}}
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                    ตราสัญลักษณ์หน่วยงาน (Logo)
                </label>
                <div class="flex flex-wrap items-center gap-5 p-4 rounded-2xl bg-slate-50/70 border border-slate-200">
                    <div class="w-20 h-20 rounded-2xl bg-white p-2 border border-slate-200 shadow-xs flex items-center justify-center shrink-0">
                        <img src="{{ asset($settings['logo_path'] ?? 'images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-1 min-w-[220px] space-y-2">
                        <div class="text-xs text-slate-600">
                            ขนาดมาตรฐานพิมพ์บนสลิป: <strong>3 เซนติเมตร (3cm)</strong> แสดงกึ่งกลางด้านบนชื่อหน่วยงาน
                        </div>
                        <input
                            type="file"
                            name="logo_file"
                            accept="image/*"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-900 hover:file:bg-purple-200 border border-slate-300 rounded-xl p-1 bg-white"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: ผู้ลงนามท้ายสลิป --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-800">2. ข้อมูลเจ้าหน้าที่การเงินผู้ลงนามท้ายสลิป</h2>
                <p class="text-xs text-slate-500 mt-0.5">ระบุชื่อและตำแหน่งที่จะพิมพ์ใต้ช่องลงลายมือชื่อ</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ชื่อ-สกุลผู้ลงนาม (ในวงเล็บ)
                    </label>
                    <input
                        type="text"
                        name="signer_name"
                        value="{{ $settings['signer_name'] }}"
                        required
                        placeholder="เช่น นางดารารัตน์ เพ็ชรทวี"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        บรรทัดตำแหน่งผู้ลงนาม
                    </label>
                    <input
                        type="text"
                        name="signer_position"
                        value="{{ $settings['signer_position'] }}"
                        required
                        placeholder="เช่น ตำแหน่ง นักวิชาการเงินและบัญชี"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        ข้อความหลังเส้นลงชื่อ
                    </label>
                    <input
                        type="text"
                        name="signer_title"
                        value="{{ $settings['signer_title'] }}"
                        required
                        placeholder="เช่น เจ้าหน้าที่การเงิน"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-600 outline-none transition"
                    >
                </div>
            </div>

            {{-- Signature image --}}
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                    รูปลายมือชื่ออิเล็กทรอนิกส์ (PNG พื้นหลังโปร่งใส)
                </label>
                <div class="flex flex-wrap items-center gap-5 p-4 rounded-2xl bg-slate-50/70 border border-slate-200">
                    <div class="h-16 w-36 rounded-xl bg-white p-2 border border-slate-200 shadow-xs flex items-center justify-center shrink-0">
                        @if(!empty($settings['signature_image']))
                            <img src="{{ asset($settings['signature_image']) }}" alt="Signature" class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-xs text-slate-400">ยังไม่มีลายเซ็น</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-[220px] space-y-2">
                        <div class="text-xs text-slate-600">
                            หากอัปโหลดไฟล์ลายเซ็น ระบบจะนำไปประทับบนเส้นลงชื่อท้ายสลิปเงินเดือนโดยอัตโนมัติ
                        </div>
                        <input
                            type="file"
                            name="signature_file"
                            accept="image/png"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-900 hover:file:bg-purple-200 border border-slate-300 rounded-xl p-1 bg-white"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end pt-2">
            <button
                type="submit"
                class="bg-purple-900 hover:bg-purple-950 text-white font-bold py-3.5 px-8 rounded-2xl shadow-md hover:shadow-lg transition flex items-center gap-2 text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>บันทึกการตั้งค่าทั้งหมด</span>
            </button>
        </div>
    </form>

    {{-- Section: สำรองข้อมูลระบบ (Database Backup) --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span>สำรองฐานข้อมูลระบบ (Database Backup)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    ดาวน์โหลดไฟล์ฐานข้อมูล (.sqlite) เก็บไว้ในเครื่องหรือ Flash Drive เพื่อป้องกันข้อมูลสูญหาย
                </p>
            </div>
            <a
                href="{{ route('settings.backup') }}"
                class="bg-slate-900 hover:bg-black text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-xs transition inline-flex items-center gap-2"
            >
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>ดาวน์โหลดไฟล์สำรองฐานข้อมูล (.sqlite)</span>
            </a>
        </div>
        <div class="text-[11px] text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-200/60 leading-relaxed">
            💡 <strong>คำแนะนำความปลอดภัย:</strong> เจ้าหน้าที่การเงินควรดาวน์โหลดไฟล์สำรองเก็บไว้อย่างน้อยเดือนละ 1 ครั้งหลังทำการอัปโหลดข้อมูลงวดใหม่เสร็จสิ้น หากเกิดเหตุขัดข้อง สามารถนำไฟล์นี้มากู้คืนระบบได้ทันที
        </div>
    </div>
</div>
@endsection
