<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ActivityLog extends Model
{
    protected static bool $schemaChecked = false;

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

    public static function ensureSchemaExists(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;

        try {
            if (!Schema::hasTable('activity_logs')) {
                Schema::create('activity_logs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('user_name')->nullable();
                    $table->string('user_role', 50)->nullable();
                    $table->string('action', 50)->index();
                    $table->unsignedBigInteger('target_user_id')->nullable()->index();
                    $table->string('target_name')->nullable();
                    $table->text('description')->nullable();
                    $table->string('ip_address', 45)->nullable();
                    $table->string('user_agent', 500)->nullable();
                    $table->timestamps();
                    $table->index('created_at');
                });
            }

            if (Schema::hasTable('users')) {
                $needsVisiblePass = !Schema::hasColumn('users', 'visible_password');
                $needsLastLogin = !Schema::hasColumn('users', 'last_login_at');
                $needsLastIp = !Schema::hasColumn('users', 'last_login_ip');
                $needsStatus = !Schema::hasColumn('users', 'status');

                if ($needsVisiblePass || $needsLastLogin || $needsLastIp || $needsStatus) {
                    Schema::table('users', function (Blueprint $table) use ($needsVisiblePass, $needsLastLogin, $needsLastIp, $needsStatus) {
                        if ($needsStatus) {
                            $table->string('status', 30)->default('ปกติ');
                            $table->string('status_note', 255)->nullable();
                            $table->string('status_date', 50)->nullable();
                        }
                        if ($needsVisiblePass) {
                            $table->string('visible_password', 100)->nullable()->default('1234');
                        }
                        if ($needsLastLogin) {
                            $table->timestamp('last_login_at')->nullable();
                        }
                        if ($needsLastIp) {
                            $table->string('last_login_ip', 45)->nullable();
                        }
                    });
                }

                if ($needsVisiblePass) {
                    DB::table('users')
                        ->whereNull('visible_password')
                        ->update(['visible_password' => '1234']);

                    // Ensure admin account has known default 1234 on initial migration
                    DB::table('users')
                        ->where('username', 'admin')
                        ->update([
                            'password' => Hash::make('1234'),
                            'visible_password' => '1234',
                        ]);

                    // Sync employees who already have 13-digit citizen_id to use citizen_id as default password
                    $empsWithCid = User::whereNotIn('role', ['admin', 'finance'])
                        ->whereNotNull('citizen_id')
                        ->where('citizen_id', '!=', '')
                        ->get();
                    foreach ($empsWithCid as $emp) {
                        $cleanCid = preg_replace('/\D/', '', (string) $emp->citizen_id);
                        if ($cleanCid !== '') {
                            $emp->update([
                                'password' => Hash::make($cleanCid),
                                'visible_password' => $cleanCid,
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Do not break application flow if schema check fails
        }
    }

    public static function record(
        string $action,
        ?string $description = null,
        ?User $actor = null,
        ?User $targetUser = null,
        ?string $targetName = null
    ): self {
        try {
            self::ensureSchemaExists();

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
        } catch (\Throwable $e) {
            return new self();
        }
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
