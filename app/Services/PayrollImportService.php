<?php

namespace App\Services;

use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PayrollImportService
{
    public static array $thaiMonthMap = [
        'มกราคม' => 1,
        'กุมภาพันธ์' => 2,
        'มีนาคม' => 3,
        'เมษายน' => 4,
        'พฤษภาคม' => 5,
        'มิถุนายน' => 6,
        'กรกฎาคม' => 7,
        'สิงหาคม' => 8,
        'กันยายน' => 9,
        'ตุลาคม' => 10,
        'พฤศจิกายน' => 11,
        'ธันวาคม' => 12,
    ];

    /**
     * Import from an uploaded file path (.xlsx, .xls, .csv)
     *
     * @return array<int, PayrollBatch>
     */
    public function importFile(
        string $filePath,
        string $defaultEmployeeType = 'ข้าราชการ',
        int $defaultMonth = 12,
        int $defaultYearBe = 2568,
        ?string $customPeriodText = null,
        ?string $customPayDateText = null,
        ?string $originalFilename = null,
        ?int $uploadedBy = null
    ): array {
        $ext = strtolower(pathinfo($originalFilename ?: $filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            $rowsBySheet = [$this->readCsvRows($filePath)];
        } else {
            $reader = IOFactory::createReaderForFile($filePath);
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            $spreadsheet = $reader->load($filePath);
            $rowsBySheet = [];
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $rowsBySheet[] = $worksheet->toArray(null, true, false, false);
            }
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);
            gc_collect_cycles();
        }

        $batches = [];
        foreach ($rowsBySheet as $rows) {
            $imported = $this->importRows(
                $rows,
                $defaultEmployeeType,
                $defaultMonth,
                $defaultYearBe,
                $customPeriodText,
                $customPayDateText,
                $originalFilename,
                $uploadedBy
            );
            foreach ($imported as $b) {
                $batches[$b->id] = $b;
            }
        }

        return array_values($batches);
    }

    /**
     * Preview and detect duplicate batches or duplicate names without writing to database
     */
    public function previewFile(
        string $filePath,
        string $defaultEmployeeType = 'ข้าราชการ',
        int $defaultMonth = 12,
        int $defaultYearBe = 2568,
        ?string $originalFilename = null
    ): array {
        $ext = strtolower(pathinfo($originalFilename ?: $filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            $rowsBySheet = [$this->readCsvRows($filePath)];
        } else {
            $reader = IOFactory::createReaderForFile($filePath);
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            $spreadsheet = $reader->load($filePath);
            $rowsBySheet = [];
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $rowsBySheet[] = $worksheet->toArray(null, true, false, false);
            }
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);
            gc_collect_cycles();
        }

        $sectionPreviews = [];
        $hasDuplicateBatches = false;
        $hasDuplicatePersons = false;

        foreach ($rowsBySheet as $rows) {
            $sections = $this->splitIntoSections($rows, $defaultEmployeeType, $defaultMonth, $defaultYearBe);

            foreach ($sections as $section) {
                $info = $this->analyzeSection($section);
                if (!$info || $info['employee_count'] === 0) {
                    continue;
                }

                $existing = PayrollBatch::where('employee_type', $info['employee_type'])
                    ->where('year_be', $info['year_be'])
                    ->where('month', $info['month'])
                    ->first();

                $isDuplicate = $existing !== null;
                if ($isDuplicate) {
                    $hasDuplicateBatches = true;
                }

                if (!empty($info['duplicate_names'])) {
                    $hasDuplicatePersons = true;
                }

                $sectionPreviews[] = [
                    'employee_type' => $info['employee_type'],
                    'month' => $info['month'],
                    'month_name' => $info['month_name'],
                    'year_be' => $info['year_be'],
                    'employee_count' => $info['employee_count'],
                    'total_income' => $info['total_income'],
                    'total_net_pay' => $info['total_net_pay'],
                    'duplicate_names' => $info['duplicate_names'],
                    'is_duplicate' => $isDuplicate,
                    'existing_batch' => $existing ? [
                        'id' => $existing->id,
                        'total_employees' => $existing->total_employees,
                        'total_net_pay' => (float) $existing->total_net_pay,
                        'uploaded_at' => $existing->created_at?->format('d/m/Y H:i น.') ?: '-',
                    ] : null,
                ];
            }
        }

        return [
            'has_duplicate_batches' => $hasDuplicateBatches,
            'has_duplicate_persons' => $hasDuplicatePersons,
            'sections' => $sectionPreviews,
        ];
    }

    protected function analyzeSection(array $section): ?array
    {
        $rows = $section['rows'];
        $employeeType = $section['employee_type'];
        $month = (int) $section['month'];
        $yearBe = (int) $section['year_be'];

        $headerRowIdx = null;
        foreach ($rows as $idx => $row) {
            $joined = implode('|', array_map(fn ($c) => trim((string) $c), $row));
            if (str_contains($joined, 'รวมรายรับ') || str_contains($joined, 'รวมเบิก')) {
                $headerRowIdx = $idx;
                if (str_contains($joined, 'รวมรายรับ')) {
                    $employeeType = 'ข้าราชการ';
                } elseif (str_contains($joined, 'รวมเบิก')) {
                    $employeeType = 'พนักงานราชการ';
                }
                break;
            }
        }

        if ($headerRowIdx === null) {
            return null;
        }

        $header1 = $rows[$headerRowIdx] ?? [];
        $header2 = $rows[$headerRowIdx + 1] ?? [];
        $isTwoRowHeader = !is_numeric(trim((string) ($header2[0] ?? ''))) && !is_numeric(trim((string) ($header2[1] ?? '')));

        $combinedHeaders = [];
        $maxCols = max(count($header1), count($header2));
        for ($c = 0; $c < $maxCols; $c++) {
            $h1 = trim(preg_replace('/\s+/u', ' ', (string) ($header1[$c] ?? '')));
            $h2 = $isTwoRowHeader ? trim(preg_replace('/\s+/u', ' ', (string) ($header2[$c] ?? ''))) : '';
            $combinedHeaders[$c] = trim($h1 . ' ' . $h2);
        }

        $dataStartIdx = $isTwoRowHeader ? $headerRowIdx + 2 : $headerRowIdx + 1;
        $namesSeen = [];
        $duplicateNames = [];
        $count = 0;
        $sumIncome = 0;
        $sumNet = 0;

        for ($i = $dataStartIdx; $i < count($rows); $i++) {
            $row = $rows[$i];
            $parsed = $employeeType === 'ข้าราชการ'
                ? $this->parseCivilServantRow($row, $combinedHeaders)
                : $this->parseGovEmployeeRow($row, $combinedHeaders);

            if (!$parsed) {
                continue;
            }

            $nameKey = $this->normalizePersonNameForMatch($parsed['full_name']);
            if (isset($namesSeen[$nameKey])) {
                $duplicateNames[] = $parsed['full_name'];
            } else {
                $namesSeen[$nameKey] = true;
            }

            $count++;
            $sumIncome += $parsed['total_income'];
            $sumNet += $parsed['net_pay'];
        }

        return [
            'employee_type' => $employeeType,
            'month' => $month,
            'month_name' => PayrollBatch::thaiMonths()[$month] ?? 'มกราคม',
            'year_be' => $yearBe,
            'employee_count' => $count,
            'total_income' => round($sumIncome, 2),
            'total_net_pay' => round($sumNet, 2),
            'duplicate_names' => array_unique($duplicateNames),
        ];
    }

    public function readCsvRows(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-874,TIS-620,ISO-8859-1');
        }
        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $content);
        rewind($temp);

        $rows = [];
        while (($data = fgetcsv($temp)) !== false) {
            $rows[] = $data;
        }
        fclose($temp);
        return $rows;
    }

    /**
     * Parse 2D array of rows (supports single or multi-month blocks in one sheet)
     *
     * @return array<int, PayrollBatch>
     */
    public function importRows(
        array $rows,
        string $defaultEmployeeType = 'พนักงานราชการ',
        int $defaultMonth = 12,
        int $defaultYearBe = 2568,
        ?string $customPeriodText = null,
        ?string $customPayDateText = null,
        ?string $originalFilename = null,
        ?int $uploadedBy = null
    ): array {
        $sections = $this->splitIntoSections($rows, $defaultEmployeeType, $defaultMonth, $defaultYearBe);
        $createdBatches = [];

        foreach ($sections as $section) {
            $batch = $this->processSection(
                $section,
                $customPeriodText,
                $customPayDateText,
                $originalFilename,
                $uploadedBy
            );
            if ($batch) {
                $createdBatches[] = $batch;
            }
        }

        return $createdBatches;
    }

    protected function splitIntoSections(array $rows, string $defaultType, int $defaultMonth, int $defaultYearBe): array
    {
        $sections = [];
        $currentMeta = [
            'employee_type' => $defaultType,
            'month' => $defaultMonth,
            'year_be' => $defaultYearBe,
            'has_explicit_period' => false,
        ];
        $currentRows = [];

        foreach ($rows as $rowIndex => $row) {
            $lineText = implode(' ', array_map(fn ($v) => trim((string) $v), $row));

            if (preg_match('/(เงินเดือนข้าราชการ|ค่าตอบแทนพนักงานราชการ).*?ประจำเดือน\s*([^\s0-9]+)\s*(\d{4})/u', $lineText, $m)) {
                if (!empty($currentRows)) {
                    $sections[] = array_merge($currentMeta, ['rows' => $currentRows]);
                    $currentRows = [];
                }

                $type = str_contains($m[1], 'ข้าราชการ') && !str_contains($m[1], 'พนักงานราชการ')
                    ? 'ข้าราชการ'
                    : 'พนักงานราชการ';
                $monthName = trim($m[2]);
                $monthNum = self::$thaiMonthMap[$monthName] ?? $defaultMonth;
                $yearBe = (int) $m[3];

                $currentMeta = [
                    'employee_type' => $type,
                    'month' => $monthNum,
                    'year_be' => $yearBe,
                    'has_explicit_period' => true,
                ];
            }

            $currentRows[] = $row;
        }

        if (!empty($currentRows)) {
            $sections[] = array_merge($currentMeta, ['rows' => $currentRows]);
        }

        return $sections;
    }

    protected function processSection(
        array $section,
        ?string $customPeriodText,
        ?string $customPayDateText,
        ?string $originalFilename,
        ?int $uploadedBy
    ): ?PayrollBatch {
        $rows = $section['rows'];
        $employeeType = $section['employee_type'];
        $month = (int) $section['month'];
        $yearBe = (int) $section['year_be'];

        // Detect header row
        $headerRowIdx = null;
        foreach ($rows as $idx => $row) {
            $joined = implode('|', array_map(fn ($c) => trim((string) $c), $row));
            if (str_contains($joined, 'รวมรายรับ') || str_contains($joined, 'รวมเบิก')) {
                $headerRowIdx = $idx;
                if (str_contains($joined, 'รวมรายรับ')) {
                    $employeeType = 'ข้าราชการ';
                } elseif (str_contains($joined, 'รวมเบิก')) {
                    $employeeType = 'พนักงานราชการ';
                }
                break;
            }
        }

        if ($headerRowIdx === null) {
            return null;
        }

        $header1 = $rows[$headerRowIdx] ?? [];
        $header2 = $rows[$headerRowIdx + 1] ?? [];

        // Check if header2 is actually a subheader row or already data
        $firstColH2 = trim((string) ($header2[0] ?? ''));
        $secondColH2 = trim((string) ($header2[1] ?? ''));
        $isTwoRowHeader = !is_numeric($firstColH2) && !is_numeric($secondColH2);

        $combinedHeaders = [];
        $maxCols = max(count($header1), count($header2));
        for ($c = 0; $c < $maxCols; $c++) {
            $h1 = trim(preg_replace('/\s+/u', ' ', (string) ($header1[$c] ?? '')));
            $h2 = $isTwoRowHeader ? trim(preg_replace('/\s+/u', ' ', (string) ($header2[$c] ?? ''))) : '';
            $combined = trim($h1 . ' ' . $h2);
            $combinedHeaders[$c] = $combined;
        }

        $dataStartIdx = $isTwoRowHeader ? $headerRowIdx + 2 : $headerRowIdx + 1;

        return DB::transaction(function () use (
            $rows,
            $dataStartIdx,
            $combinedHeaders,
            $employeeType,
            $month,
            $yearBe,
            $section,
            $customPeriodText,
            $customPayDateText,
            $originalFilename,
            $uploadedBy
        ) {
            $monthName = PayrollBatch::thaiMonths()[$month] ?? 'มกราคม';
            $periodText = (!$section['has_explicit_period'] && $customPeriodText)
                ? $customPeriodText
                : PayrollBatch::defaultPeriodText($month, $yearBe);
            $payDateText = (!$section['has_explicit_period'] && $customPayDateText)
                ? $customPayDateText
                : "25 {$monthName} {$yearBe}";

            $batch = PayrollBatch::updateOrCreate(
                [
                    'employee_type' => $employeeType,
                    'month' => $month,
                    'year_be' => $yearBe,
                ],
                [
                    'month_name' => $monthName,
                    'period_text' => $periodText,
                    'pay_date_text' => $payDateText,
                    'source_filename' => $originalFilename,
                    'is_published' => true,
                    'uploaded_by' => $uploadedBy,
                ]
            );

            // Clear old payslips in this batch before re-importing
            $batch->payslips()->delete();

            $count = 0;
            $sumIncome = 0;
            $sumDeduction = 0;
            $sumNet = 0;

            for ($i = $dataStartIdx; $i < count($rows); $i++) {
                $row = $rows[$i];
                $parsed = $employeeType === 'ข้าราชการ'
                    ? $this->parseCivilServantRow($row, $combinedHeaders)
                    : $this->parseGovEmployeeRow($row, $combinedHeaders);

                if (!$parsed) {
                    continue;
                }

                $user = $this->findOrCreateEmployeeUser($parsed, $employeeType);

                Payslip::create([
                    'payroll_batch_id' => $batch->id,
                    'user_id' => $user->id,
                    'seq_no' => $parsed['seq_no'],
                    'position_number' => $parsed['position_number'],
                    'full_name' => $parsed['full_name'],
                    'employee_type' => $employeeType,
                    'position' => $user->position ?: $parsed['position'],
                    'level_or_district' => $parsed['level_or_district'],
                    'citizen_id' => $user->citizen_id,
                    'bank_account' => $parsed['bank_account'] ?: $user->bank_account,
                    'bank_name' => $user->bank_name ?: 'ธ.กรุงไทย',
                    'income_items' => $parsed['income_items'],
                    'deduction_items' => $parsed['deduction_items'],
                    'total_income' => $parsed['total_income'],
                    'total_deduction' => $parsed['total_deduction'],
                    'net_pay' => $parsed['net_pay'],
                    'remark' => $parsed['remark'],
                ]);

                $count++;
                $sumIncome += $parsed['total_income'];
                $sumDeduction += $parsed['total_deduction'];
                $sumNet += $parsed['net_pay'];
            }

            $batch->update([
                'total_employees' => $count,
                'total_income' => round($sumIncome, 2),
                'total_deduction' => round($sumDeduction, 2),
                'total_net_pay' => round($sumNet, 2),
            ]);

            return $count > 0 ? $batch : null;
        });
    }

    protected function parseCivilServantRow(array $row, array $headers): ?array
    {
        // Locate key boundary columns dynamically
        $totalIncomeIdx = null;
        $totalDeductIdx = null;
        $netPayIdx = null;

        foreach ($headers as $idx => $h) {
            if (str_contains($h, 'รวมรายรับ')) {
                $totalIncomeIdx = $idx;
            } elseif (str_contains($h, 'รวมรายจ่าย')) {
                $totalDeductIdx = $idx;
            } elseif (str_contains($h, 'รับจริง')) {
                $netPayIdx = $idx;
            }
        }

        if ($totalIncomeIdx === null || $totalDeductIdx === null || $netPayIdx === null) {
            return null;
        }

        $posNo = $this->cleanIntString($row[0] ?? '');
        $seqNo = (int) $this->toFloat($row[1] ?? 0);
        $name1 = $this->cleanName($row[2] ?? '');
        $name2 = $this->cleanName($row[$totalIncomeIdx + 1] ?? '');
        $name3 = $this->cleanName($row[$netPayIdx + 3] ?? '');
        $fullName = $name1 ?: ($name2 ?: $name3);

        if (!$fullName || str_contains($fullName, 'รวม')) {
            return null;
        }

        $totalIncome = $this->toFloat($row[$totalIncomeIdx] ?? 0);
        $totalDeduction = $this->toFloat($row[$totalDeductIdx] ?? 0);
        $netPay = $this->toFloat($row[$netPayIdx] ?? 0);

        if ($totalIncome <= 0 && $netPay <= 0) {
            return null;
        }

        $level = trim((string) ($row[3] ?? ''));

        // Income items: columns 4 .. $totalIncomeIdx - 1
        $incomeItems = [];
        for ($c = 4; $c < $totalIncomeIdx; $c++) {
            $val = $this->toFloat($row[$c] ?? 0);
            if ($val > 0) {
                $label = $this->normalizeIncomeLabel($headers[$c] ?? 'รายได้อื่นๆ');
                $incomeItems[] = [
                    'label' => $label,
                    'amount' => round($val, 2),
                ];
            }
        }

        // Deduction items: columns ($totalIncomeIdx + 2) .. ($totalDeductIdx - 1)
        $deductionItems = [];
        for ($c = $totalIncomeIdx + 2; $c < $totalDeductIdx; $c++) {
            $val = $this->toFloat($row[$c] ?? 0);
            if ($val > 0) {
                $label = $this->normalizeDeductionLabel($headers[$c] ?? 'รายจ่ายอื่นๆ');
                $deductionItems[] = [
                    'label' => $label,
                    'amount' => round($val, 2),
                ];
            }
        }

        $bankAccount = $this->cleanBankAccount($row[$netPayIdx + 4] ?? '');
        $remarkParts = [];
        for ($c = $netPayIdx + 6; $c <= $netPayIdx + 8; $c++) {
            $r = trim((string) ($row[$c] ?? ''));
            if ($r !== '' && $r !== '0' && $r !== '0.0') {
                // If it looks like a 10-digit account number, skip or format
                $remarkParts[] = $this->cleanIntString($r);
            }
        }

        return [
            'seq_no' => $seqNo ?: null,
            'position_number' => $posNo ?: null,
            'full_name' => $fullName,
            'account_name' => $name3 ?: $fullName,
            'position' => $this->inferCivilServantPosition($level),
            'level_or_district' => $level,
            'bank_account' => $bankAccount,
            'income_items' => $incomeItems,
            'deduction_items' => $deductionItems,
            'total_income' => round($totalIncome, 2),
            'total_deduction' => round($totalDeduction, 2),
            'net_pay' => round($netPay, 2),
            'remark' => implode(' ', $remarkParts) ?: null,
        ];
    }

    protected function parseGovEmployeeRow(array $row, array $headers): ?array
    {
        $seqRaw = trim((string) ($row[0] ?? ''));
        $fullName = $this->cleanName($row[1] ?? '');

        if (!$fullName || str_contains($fullName, 'รวม') || !is_numeric($seqRaw)) {
            return null;
        }

        $seqNo = (int) $this->toFloat($seqRaw);
        $district = trim((string) ($row[2] ?? ''));

        $salary = $this->toFloat($row[3] ?? 0);
        $retroPay = $this->toFloat($row[4] ?? 0);
        $totalIncome = $this->toFloat($row[5] ?? 0);

        $totalDeduction = $this->toFloat($row[14] ?? 0);
        $netPay = $this->toFloat($row[15] ?? 0);

        if ($totalIncome <= 0 && $netPay <= 0) {
            return null;
        }

        $incomeItems = [];
        if ($salary > 0) {
            $incomeItems[] = ['label' => 'เงินเดือน', 'amount' => round($salary, 2)];
        }
        if ($retroPay > 0) {
            $incomeItems[] = ['label' => 'ตกเบิก', 'amount' => round($retroPay, 2)];
        }

        $deductionMap = [
            6 => 'เงินสมทบ 5%',
            7 => 'ชพค.',
            8 => 'ชพส.',
            9 => 'สหกรณ์',
            10 => 'บังคับคดี',
            11 => 'ธ.อาคารสงเคราะห์',
            12 => 'ธ.ออมสิน',
            13 => 'กยศ.',
        ];

        $deductionItems = [];
        foreach ($deductionMap as $colIdx => $label) {
            $val = $this->toFloat($row[$colIdx] ?? 0);
            if ($val > 0) {
                $deductionItems[] = [
                    'label' => $label,
                    'amount' => round($val, 2),
                ];
            }
        }

        $accountName = $this->cleanName($row[18] ?? '');
        $bankAccount = $this->cleanBankAccount($row[19] ?? '');
        $remark = trim((string) ($row[21] ?? ''));

        return [
            'seq_no' => $seqNo ?: null,
            'position_number' => null,
            'full_name' => $fullName,
            'account_name' => $accountName ?: $fullName,
            'position' => 'นักวิชาการศึกษา',
            'level_or_district' => $district,
            'bank_account' => $bankAccount,
            'income_items' => $incomeItems,
            'deduction_items' => $deductionItems,
            'total_income' => round($totalIncome, 2),
            'total_deduction' => round($totalDeduction, 2),
            'net_pay' => round($netPay, 2),
            'remark' => $remark ?: null,
        ];
    }

    protected function findOrCreateEmployeeUser(array $parsed, string $employeeType): User
    {
        $bankAccount = $parsed['bank_account'];
        $normalizedName = $this->normalizePersonNameForMatch($parsed['full_name']);

        $user = null;

        // 1. Match by Full Name first (within same employee type)
        $candidates = User::where('role', '!=', 'admin')
            ->where('employee_type', $employeeType)
            ->get();

        foreach ($candidates as $candidate) {
            if ($this->normalizePersonNameForMatch($candidate->name) === $normalizedName) {
                $user = $candidate;
                break;
            }
        }

        // 2. Fallback: match by bank account ONLY if user's name is similar (e.g. title variation)
        if (!$user && $bankAccount && strlen($bankAccount) >= 9) {
            $accountHolder = User::where('bank_account', $bankAccount)
                ->where('role', '!=', 'admin')
                ->first();

            if ($accountHolder) {
                $hName = $this->normalizePersonNameForMatch($accountHolder->name);
                // Check if one contains the other (e.g. นาย vs ว่าที่ ร.ต. or surname spelling)
                if (str_contains($hName, $normalizedName) || str_contains($normalizedName, $hName)) {
                    $user = $accountHolder;
                }
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $parsed['full_name'],
                'password' => Hash::make('1234'),
                'role' => 'employee',
                'employee_type' => $employeeType,
                'position_number' => $parsed['position_number'],
                'position' => $parsed['position'],
                'level_or_district' => $parsed['level_or_district'],
                'bank_account' => $bankAccount,
                'bank_name' => 'ธ.กรุงไทย',
                'account_name' => $parsed['account_name'],
                'remark' => $parsed['remark'],
            ]);
        } else {
            $updates = [];
            if (!$user->bank_account && $bankAccount) {
                $updates['bank_account'] = $bankAccount;
            }
            if (!$user->position_number && $parsed['position_number']) {
                $updates['position_number'] = $parsed['position_number'];
            }
            if ($parsed['level_or_district']) {
                $updates['level_or_district'] = $parsed['level_or_district'];
            }
            if (!empty($updates)) {
                $user->update($updates);
            }
        }

        return $user;
    }

    protected function normalizeIncomeLabel(string $raw): string
    {
        $raw = trim(preg_replace('/\s+/u', ' ', $raw));
        return match (true) {
            str_contains($raw, 'เงินเดือน') => 'เงินเดือน',
            str_contains($raw, 'เงินตำแหน่ง') => 'เงินประจำตำแหน่ง',
            str_contains($raw, 'วิทยฐานะ') => 'ค่าวิทยฐานะ',
            str_contains($raw, 'ค่าครองชีพ') => 'ค่าครองชีพชั่วคราว',
            str_contains($raw, 'พิเศษ') => 'ค่าตอบแทนพิเศษ',
            str_contains($raw, 'ตกเบิก') => 'ตกเบิก',
            str_contains($raw, 'พสร') => 'เงิน พ.ส.ร.',
            str_contains($raw, 'ค่าตอบแทน') => 'เงินค่าตอบแทน',
            default => $raw ?: 'รายได้อื่นๆ',
        };
    }

    protected function normalizeDeductionLabel(string $raw): string
    {
        $raw = trim(preg_replace('/\s+/u', ' ', $raw));
        return match (true) {
            str_contains($raw, 'ภาษี') => 'ภาษีหัก ณ ที่จ่าย',
            str_contains($raw, 'ชพค') => 'ชพค.',
            str_contains($raw, 'ชพส') => 'ชพส.',
            str_contains($raw, 'สหกรณ์') => 'สหกรณ์',
            str_contains($raw, 'ออมสิน') => 'ธ.ออมสิน',
            str_contains($raw, 'อาคาร') => 'ธ.อาคารสงเคราะห์',
            str_contains($raw, 'หนี้ครู') => 'หนี้ครูช่วยราชการ',
            str_contains($raw, 'ส่วนเพิ่ม') || str_contains($raw, 'กยศ') => 'กยศ./กบข. ส่วนเพิ่ม',
            str_contains($raw, 'กบข') => 'กบข.',
            str_contains($raw, 'กรุงไทย') => 'ธ.กรุงไทย',
            default => $raw ?: 'รายจ่ายอื่นๆ',
        };
    }

    protected function inferCivilServantPosition(string $level): string
    {
        $l = trim($level);
        if (str_contains($l, 'คศ') || str_contains($l, 'ครู')) {
            return "ครู ({$l})";
        }
        if (in_array($l, ['ชก.', 'ชก', 'ชกพ.', 'ปก.', 'ปก'], true)) {
            return "นักวิชาการศึกษา ({$l})";
        }
        return $l ?: 'ข้าราชการ';
    }

    protected function cleanName(mixed $val): string
    {
        $str = trim((string) $val);
        // Normalize multiple spaces to 1 space
        return preg_replace('/\s+/u', ' ', $str);
    }

    protected function normalizePersonNameForMatch(string $name): string
    {
        return preg_replace('/\s+/u', '', trim($name));
    }

    protected function cleanIntString(mixed $val): string
    {
        $str = trim((string) $val);
        if ($str === '') {
            return '';
        }
        if (preg_match('/^(\d+)\.0+$/', $str, $m)) {
            return $m[1];
        }
        return $str;
    }

    protected function cleanBankAccount(mixed $val): ?string
    {
        $str = $this->cleanIntString($val);
        $digits = preg_replace('/\D/', '', $str);
        return strlen($digits) >= 9 ? $digits : null;
    }

    protected function toFloat(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }
        $clean = str_replace([',', ' '], '', trim((string) $val));
        return is_numeric($clean) ? (float) $clean : 0.0;
    }
}
