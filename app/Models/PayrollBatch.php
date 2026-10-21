<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollBatch extends Model
{
    protected $fillable = [
        'employee_type',
        'month',
        'month_name',
        'year_be',
        'period_text',
        'pay_date_text',
        'source_filename',
        'total_employees',
        'total_income',
        'total_deduction',
        'total_net_pay',
        'is_published',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'total_income' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'total_net_pay' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function thaiMonths(): array
    {
        return [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];
    }

    public static function lastDayOfMonth(int $month, int $yearBe): int
    {
        $yearAd = $yearBe - 543;
        return (int) date('t', mktime(0, 0, 0, $month, 1, $yearAd));
    }

    public static function defaultPeriodText(int $month, int $yearBe): string
    {
        $monthName = self::thaiMonths()[$month] ?? '';
        $lastDay = str_pad((string) self::lastDayOfMonth($month, $yearBe), 2, '0', STR_PAD_LEFT);
        return "01 - {$lastDay} {$monthName} {$yearBe}";
    }
}
