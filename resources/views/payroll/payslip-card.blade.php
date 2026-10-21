@php
    $ytd = $payslip->getYtdSummary();
    $fundLabel = $payslip->employee_type === 'ข้าราชการ' ? 'กองทุน กบข. สะสม' : 'กองทุนสำรองเลี้ยงชีพสะสม';
    $incomeCount = count($payslip->income_items ?? []);
    $deductCount = count($payslip->deduction_items ?? []);
    $maxItems = max($incomeCount, $deductCount, 6);
@endphp

<div class="payslip-sheet bg-white text-black mx-auto px-8 py-7 border border-slate-300 shadow-sm" style="max-width: 900px; font-family: 'Sarabun', sans-serif; font-size: 14.5px; line-height: 1.55;">
    {{-- Header: Logo centered at top with bottom edge above agency name (3cm) --}}
    <div class="flex justify-center mb-2">
        <img
            src="{{ asset($settings['logo_path'] ?? 'images/logo.png') }}"
            alt="ตรากรมส่งเสริมการเรียนรู้"
            style="width: 3cm; height: 3cm;"
            class="object-contain"
        >
    </div>

    <div class="flex justify-between items-start mb-4">
        <div>
            <div class="font-bold text-[17px] leading-snug">
                {{ $settings['agency_name'] ?? 'สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช' }}
            </div>
            <div class="font-bold text-[14.5px] mt-1">
                เลขประจำตัวผู้เสียภาษี: {{ $settings['agency_tax_id'] ?? '0994000550537' }}
            </div>
        </div>

        <div class="text-right">
            <div class="font-bold text-[17px]">
                สลิปเงินเดือน/PaySlip
            </div>
            <div class="text-[14px] text-slate-700 mt-1">
                ประจำงวด: {{ $payslip->batch->month_name }} {{ $payslip->batch->year_be }}
            </div>
        </div>
    </div>

    {{-- Employee & Period Details --}}
    <div class="grid grid-cols-12 gap-2 text-[14.5px] mb-3">
        <div class="col-span-7 space-y-1">
            <div>
                <span>ชื่อ-นามสกุล: <strong>{{ $payslip->full_name }}</strong></span>
            </div>
            <div>
                <span>{{ $payslip->employee_type }} ตำแหน่ง: <strong>{{ $payslip->display_position }}</strong></span>
                @if($payslip->level_or_district && $payslip->employee_type === 'พนักงานราชการ')
                    <span class="text-slate-700">({{ $payslip->level_or_district }})</span>
                @endif
            </div>
            <div>
                <span>เลขประจำตัวผู้เสียภาษี: <strong>{{ $payslip->display_citizen_id }}</strong></span>
            </div>
        </div>

        <div class="col-span-5 pl-8 space-y-1">
            <div>
                <span>รอบเงินเดือน: {{ $payslip->batch->period_text }}</span>
            </div>
            <div>
                <span>วันที่จ่าย: {{ $payslip->batch->pay_date_text }}</span>
            </div>
            <div>
                <span>เลขที่บัญชี: {{ $payslip->formatted_bank_account }} {{ $payslip->bank_name ?: 'ธ.กรุงไทย' }}</span>
            </div>
        </div>
    </div>

    {{-- 2-Column Table: รายการรายได้ | รายการรายหัก (ตรงตามภาพตัวอย่าง) --}}
    <div class="border border-neutral-700">
        {{-- Table Header --}}
        <div class="grid grid-cols-2 border-b border-neutral-700 font-bold bg-[#fafafa]">
            <div class="flex justify-between px-3.5 py-1.5 border-r border-neutral-700">
                <span>รายการรายได้</span>
                <span>บาท</span>
            </div>
            <div class="flex justify-between px-3.5 py-1.5">
                <span>รายการรายหัก</span>
                <span>บาท</span>
            </div>
        </div>

        {{-- Table Content Rows --}}
        <div class="grid grid-cols-2 text-[14px]">
            {{-- รายได้ (Left Column) --}}
            <div class="border-r border-neutral-700 flex flex-col justify-between p-3 min-h-[170px]">
                <div class="space-y-1">
                    @forelse($payslip->income_items ?? [] as $item)
                        <div class="flex justify-between gap-2">
                            <span>{{ $item['label'] }}</span>
                            <span class="tabular-nums">{{ number_format((float) $item['amount'], 2) }}</span>
                        </div>
                    @empty
                        <div class="flex justify-between text-neutral-400">
                            <span>-</span>
                            <span>0.00</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- รายหัก (Right Column) --}}
            <div class="flex flex-col justify-between p-3 min-h-[170px]">
                <div class="space-y-1">
                    @forelse($payslip->deduction_items ?? [] as $item)
                        <div class="flex justify-between gap-2">
                            <span>{{ $item['label'] }}</span>
                            <span class="tabular-nums">{{ number_format((float) $item['amount'], 2) }}</span>
                        </div>
                    @empty
                        <div class="flex justify-between text-neutral-400">
                            <span>-</span>
                            <span>0.00</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Column Totals --}}
        <div class="grid grid-cols-2 border-t border-neutral-700 font-bold bg-white text-[14.5px]">
            <div class="flex justify-between px-3.5 py-1.5 border-r border-neutral-700">
                <span>รวมรายการได้</span>
                <span class="tabular-nums">{{ number_format((float) $payslip->total_income, 2) }}</span>
            </div>
            <div class="flex justify-between px-3.5 py-1.5">
                <span>รวมรายการหัก</span>
                <span class="tabular-nums">{{ number_format((float) $payslip->total_deduction, 2) }}</span>
            </div>
        </div>

        {{-- Net Pay Row (Highlight gray box) --}}
        <div class="grid grid-cols-12 border-t border-neutral-700 bg-[#e5e7eb] font-bold text-[15px]">
            <div class="col-span-3 px-3.5 py-2 border-r border-neutral-700 text-slate-900">
                รายได้สุทธิ
            </div>
            <div class="col-span-9 px-3.5 py-2 text-slate-900 tabular-nums">
                {{ number_format((float) $payslip->net_pay, 2) }} บาท
            </div>
        </div>

        {{-- 4-Column YTD (ยอดสะสม) Table --}}
        <div class="border-t border-neutral-700">
            <div class="grid grid-cols-4 text-center font-bold text-[13.5px] bg-[#fafafa] border-b border-neutral-700">
                <div class="py-1.5 px-2 border-r border-neutral-700">รายได้สะสม</div>
                <div class="py-1.5 px-2 border-r border-neutral-700">ภาษีสะสม</div>
                <div class="py-1.5 px-2 border-r border-neutral-700">ประกันสังคมสะสม</div>
                <div class="py-1.5 px-2">{{ $fundLabel }}</div>
            </div>
            <div class="grid grid-cols-4 text-center tabular-nums text-[14px] bg-white">
                <div class="py-2 px-2 border-r border-neutral-700 font-semibold">{{ number_format((float) $ytd['income'], 2) }}</div>
                <div class="py-2 px-2 border-r border-neutral-700">{{ number_format((float) $ytd['tax'], 2) }}</div>
                <div class="py-2 px-2 border-r border-neutral-700">{{ number_format((float) $ytd['sso'], 2) }}</div>
                <div class="py-2 px-2">{{ number_format((float) $ytd['fund'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Footer & Signature --}}
    <div class="grid grid-cols-12 mt-4 text-[14px] items-start">
        <div class="col-span-6 pt-2">
            <span>หมายเหตุ: {{ $payslip->remark ?: 'ข้อมูลจากระบบบัญชีเงินเดือน' }}</span>
        </div>

        <div class="col-span-6 text-center relative pt-1">
            {{-- Digital Signature --}}
            <div class="h-9 flex items-end justify-center -mb-2">
                @if(!empty($settings['signature_image']))
                    <img src="{{ asset($settings['signature_image']) }}" alt="ลายเซ็น" class="max-h-11 object-contain">
                @else
                    <svg width="115" height="38" viewBox="0 0 150 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="inline-block">
                        <path d="M32 34 C18 33, 18 17, 31 18 C40 19, 43 32, 33 34 C44 26, 48 6, 46 3 C44 10, 40 31, 47 34 C53 36, 59 23, 57 20 C55 25, 55 33, 61 33 C66 33, 71 23, 70 21 C69 26, 70 32, 74 32 C78 32, 83 23, 86 24 C89 25, 89 31, 93 30 C98 29, 106 22, 118 19" stroke="#2818b5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @endif
            </div>
            <div>
                ลงชื่อ________________________________{{ $settings['signer_title'] ?? 'เจ้าหน้าที่การเงิน' }}
            </div>
            <div class="mt-1">
                ({{ $settings['signer_name'] ?? 'นางดารารัตน์ เพ็ชรทวี' }})
            </div>
            <div class="mt-0.5 text-xs text-slate-700">
                {{ $settings['signer_position'] ?? 'ตำแหน่ง นักวิชาการเงินและบัญชี' }}
            </div>
        </div>
    </div>
</div>
