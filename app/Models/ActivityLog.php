<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'target_user_id',
        'target_name',
        'description',
        'ip_address',
        'user_agent',
    ];

    public const ACTION_LABELS = [
        'login' => ['label' => 'เข้าสู่ระบบ', 'color' => 'emerald'],
        'logout' => ['label' => 'ออกจากระบบ', 'color' => 'slate'],
        'login_failed' => ['label' => 'ล็อกอินไม่สำเร็จ', 'color' => 'rose'],
        'password_change' => ['label' => 'เปลี่ยนรหัสผ่าน', 'color' => 'blue'],
        'password_reset' => ['label' => 'รีเซ็ตรหัสผ่าน', 'color' => 'amber'],
        'user_create' => ['label' => 'เพิ่มผู้ใช้งาน', 'color' => 'purple'],
        'user_update' => ['label' => 'แก้ไขผู้ใช้งาน', 'color' => 'indigo'],
        'user_delete' => ['label' => 'ลบผู้ใช้งาน', 'color' => 'rose'],
        'payroll_upload' => ['label' => 'นำเข้าเงินเดือน', 'color' => 'teal'],
        'payroll_delete' => ['label' => 'ลบงวดเงินเดือน', 'color' => 'rose'],
        'settings_update' => ['label' => 'ตั้งค่าระบบ', 'color' => 'purple'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public static function record(
        string $action,
        ?string $description = null,
        ?User $actor = null,
        ?User $targetUser = null,
        ?string $targetName = null
    ): self {
        $actor = $actor ?? Auth::user();
        $request = request();

        return self::create([
            'user_id' => $actor?->id,
            'user_name' => $actor?->name,
            'user_role' => $actor?->role,
            'action' => $action,
            'target_user_id' => $targetUser?->id,
            'target_name' => $targetName ?? $targetUser?->name,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
        ]);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action]['label'] ?? $this->action;
    }

    public function getActionBadgeClassAttribute(): string
    {
        $color = self::ACTION_LABELS[$this->action]['color'] ?? 'slate';
        return match ($color) {
            'emerald' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'rose' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'blue' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'amber' => 'bg-amber-50 text-amber-800 border border-amber-200',
            'purple' => 'bg-purple-50 text-purple-700 border border-purple-200',
            'indigo' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
            'teal' => 'bg-teal-50 text-teal-700 border border-teal-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->user_role) {
            'admin' => 'ผู้ดูแลระบบ (Admin)',
            'finance' => 'เจ้าหน้าที่การเงิน',
            'employee' => 'บุคลากร',
            default => '-',
        };
    }

    public function getDeviceSummaryAttribute(): string
    {
        $ua = (string) $this->user_agent;
        if (!$ua) {
            return '-';
        }

        $os = 'อุปกรณ์ทั่วไป';
        if (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
            $os = 'iOS';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'Mac OS') !== false || stripos($ua, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (stripos($ua, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'Chrome/') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Firefox/') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari/') !== false) {
            $browser = 'Safari';
        } elseif (stripos($ua, 'Line/') !== false) {
            $browser = 'LINE App';
        }

        return "{$browser} ({$os})";
    }
}
