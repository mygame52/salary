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
        'position_number',
        'position',
        'level_or_district',
        'bank_account',
        'bank_name',
        'account_name',
        'remark',
    ];

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
