<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'payroll_batch_id',
        'user_id',
        'seq_no',
        'position_number',
        'full_name',
        'employee_type',
        'position',
        'level_or_district',
        'citizen_id',
        'bank_account',
        'bank_name',
        'income_items',
        'deduction_items',
        'total_income',
        'total_deduction',
        'net_pay',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'income_items' => 'array',
            'deduction_items' => 'array',
            'total_income' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedBankAccountAttribute(): string
    {
        $acct = $this->bank_account ?: $this->user?->bank_account;
        return User::formatBankAccount($acct);
    }

    public function getDisplayCitizenIdAttribute(): string
    {
        return $this->citizen_id ?: ($this->user?->citizen_id ?: '-');
    }

    public function getDisplayPositionAttribute(): string
    {
        if ($this->position) {
            return $this->position;
        }
        if ($this->user?->position) {
            return $this->user->position;
        }
        return $this->employee_type === 'พนักงานราชการ' ? 'นักวิชาการศึกษา' : ($this->level_or_district ?: '-');
    }

    /**
     * Calculate Year-to-Date (YTD) accumulations up to this payslip in the same year
     */
    public function getYtdSummary(): array
    {
        $yearBe = $this->batch?->year_be;
        $currentMonth = $this->batch?->month ?? 12;

        if (!$yearBe) {
            return [
                'income' => (float) $this->total_income,
                'tax' => $this->extractItemAmount('ภาษี'),
                'sso' => $this->extractItemAmount('สมทบ') ?: $this->extractItemAmount('ประกันสังคม'),
                'fund' => $this->extractItemAmount('กบข') ?: $this->extractItemAmount('กองทุน'),
            ];
        }

        // Find all payslips for this employee or bank account in the same year up to current month
        $slips = Payslip::whereHas('batch', function ($q) use ($yearBe, $currentMonth) {
            $q->where('year_be', $yearBe)
              ->where('month', '<=', $currentMonth);
        })
        ->where(function ($q) {
            if ($this->user_id) {
                $q->where('user_id', $this->user_id);
            }
            if ($this->bank_account) {
                $q->orWhere('bank_account', $this->bank_account);
            }
            $q->orWhere('full_name', $this->full_name);
        })
        ->get();

        $ytdIncome = 0;
        $ytdTax = 0;
        $ytdSso = 0;
        $ytdFund = 0;

        foreach ($slips as $slip) {
            $ytdIncome += (float) $slip->total_income;
            $ytdTax += $slip->extractItemAmount('ภาษี');
            $ytdSso += $slip->extractItemAmount('สมทบ') ?: $slip->extractItemAmount('ประกันสังคม');
            $ytdFund += $slip->extractItemAmount('กบข') ?: $slip->extractItemAmount('กองทุน');
        }

        return [
            'income' => $ytdIncome > 0 ? $ytdIncome : (float) $this->total_income,
            'tax' => $ytdTax,
            'sso' => $ytdSso,
            'fund' => $ytdFund,
        ];
    }

    public function extractItemAmount(string $keyword): float
    {
        $total = 0;
        foreach ($this->deduction_items ?? [] as $item) {
            if (str_contains($item['label'] ?? '', $keyword)) {
                $total += (float) ($item['amount'] ?? 0);
            }
        }
        foreach ($this->income_items ?? [] as $item) {
            if (str_contains($item['label'] ?? '', $keyword)) {
                $total += (float) ($item['amount'] ?? 0);
            }
        }
        return $total;
    }
}
