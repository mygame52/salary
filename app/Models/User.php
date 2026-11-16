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
        'visible_password',
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
        'last_login_at',
        'last_login_ip',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'finance'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFinanceOfficer(): bool
    {
        return $this->role === 'finance';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'ผู้ดูแลระบบ (Admin)',
            'finance' => 'เจ้าหน้าที่การเงิน',
            default => 'บุคลากร',
        };
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'bg-purple-100 text-purple-800 border border-purple-200',
            'finance' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function getDefaultPasswordValue(): string
    {
        if (!in_array($this->role, ['admin', 'finance'], true) && !empty($this->citizen_id)) {
            $clean = preg_replace('/\D/', '', (string) $this->citizen_id);
            if ($clean !== '') {
                return $clean;
            }
        }

        return '1234';
    }

    public function isDefaultPassword(): bool
    {
        $current = (string) ($this->visible_password ?? '1234');
        $cleanCitizen = !empty($this->citizen_id) ? preg_replace('/\D/', '', (string) $this->citizen_id) : '';

        return $current === '1234' || ($cleanCitizen !== '' && $current === $cleanCitizen);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
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
