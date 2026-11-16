<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'citizen_id',
        'name',
        'email',
        'password',
        'role',
        'employee_type',
        'status',
        'status_note',
        'status_date',
        'position_number',
        'position',
        'level_or_district',
        'bank_account',
        'bank_name',
        'account_name',
        'remark',
    ];

    public const STATUS_OPTIONS = [
        'ปกติ' => ['label' => 'ปกติ', 'color' => 'emerald', 'icon' => '🟢'],
        'ย้าย' => ['label' => 'ย้าย', 'color' => 'blue', 'icon' => '🔄'],
        'เกษียณ' => ['label' => 'เกษียณ', 'color' => 'purple', 'icon' => '🎖️'],
        'ลาออก' => ['label' => 'ลาออก', 'color' => 'amber', 'icon' => '📤'],
        'เสียชีวิต' => ['label' => 'เสียชีวิต', 'color' => 'slate', 'icon' => '⚫'],
    ];

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'ย้าย' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'เกษียณ' => 'bg-purple-50 text-purple-700 border border-purple-200',
            'ลาออก' => 'bg-amber-50 text-amber-800 border border-amber-200',
            'เสียชีวิต' => 'bg-slate-100 text-slate-700 border border-slate-300',
            default => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        };
    }

    public function latestPayslipWithBatch()
    {
        return $this->payslips()
            ->join('payroll_batches', 'payslips.payroll_batch_id', '=', 'payroll_batches.id')
            ->orderByDesc('payroll_batches.year_be')
            ->orderByDesc('payroll_batches.month')
            ->select(
                'payslips.*',
                'payroll_batches.month as batch_month',
                'payroll_batches.year_be as batch_year',
                'payroll_batches.month_name as batch_month_name'
            )
            ->first();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public static function formatBankAccount(?string $raw): string
    {
        if (!$raw) {
            return '-';
        }
        $digits = preg_replace('/\D/', '', (string) $raw);
        if (strlen($digits) === 10) {
            return substr($digits, 0, 3) . '-' . substr($digits, 3, 1) . '-' . substr($digits, 4, 5) . '-' . substr($digits, 9, 1);
        }
        return trim($raw);
    }

    public function getFormattedBankAccountAttribute(): string
    {
        return self::formatBankAccount($this->bank_account);
    }
}
