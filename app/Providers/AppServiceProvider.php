<?php

namespace App\Providers;

use App\Models\PayrollBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->runningUnitTests() && ($appUrl = config('app.url'))) {
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
            if (str_starts_with($appUrl, 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        // Ensure pagination links always respect forced root URL (e.g. /salary subdirectory on IIS)
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return url(request()->path());
        });

        \App\Models\ActivityLog::ensureSchemaExists();

        // Auto-import gov_teachers_2569.csv once on the host database if not yet imported
        if (!app()->runningUnitTests()) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('payroll_batches')
                    && PayrollBatch::where('employee_type', 'พนักงานราชการ')->count() === 0
                    && file_exists(storage_path('app/seed_data/gov_teachers_2569.csv'))
                ) {
                    @set_time_limit(300);
                    $admin = User::where('role', 'admin')->first();
                    app(\App\Services\PayrollImportService::class)->importFile(
                        storage_path('app/seed_data/gov_teachers_2569.csv'),
                        'พนักงานราชการ',
                        9,
                        2569,
                        null,
                        null,
                        'gov_teachers_2569.csv',
                        $admin ? $admin->id : 1
                    );
                }
            } catch (\Throwable $e) {
                // Ignore auto-seed errors
            }
        }

        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                $missingCitizenIdCount = User::whereNotIn('role', ['admin', 'finance'])
                    ->where(function ($q) {
                        $q->whereNull('citizen_id')->orWhere('citizen_id', '');
                    })->count();

                $missingBankCount = User::whereNotIn('role', ['admin', 'finance'])
                    ->where(function ($q) {
                        $q->whereNull('bank_account')->orWhere('bank_account', '');
                    })->count();

                $latestBatch = PayrollBatch::orderByDesc('year_be')->orderByDesc('month')->first();
                $totalBatchesCount = PayrollBatch::count();

                $needsStatusReviewCount = 0;
                if ($latestBatch) {
                    $currentMonthUserIds = \App\Models\Payslip::where('payroll_batch_id', $latestBatch->id)
                        ->whereNotNull('user_id')
                        ->pluck('user_id')
                        ->toArray();

                    $targetMonth = $latestBatch->month == 1 ? 12 : $latestBatch->month - 1;
                    $targetYear = $latestBatch->month == 1 ? $latestBatch->year_be - 1 : $latestBatch->year_be;
                    $prevBatch = PayrollBatch::where('year_be', $targetYear)->where('month', $targetMonth)->first();
                    $prevMonthUserIds = $prevBatch ? \App\Models\Payslip::where('payroll_batch_id', $prevBatch->id)->whereNotNull('user_id')->pluck('user_id')->toArray() : [];

                    $recentUserIds = array_unique(array_merge($currentMonthUserIds, $prevMonthUserIds));

                    $needsStatusReviewCount = User::whereNotIn('role', ['admin', 'finance'])
                        ->whereNotIn('id', $recentUserIds)
                        ->where('status', 'ปกติ')
                        ->count();
                }

                $alertCount = 0;
                if ($missingCitizenIdCount > 0) $alertCount++;
                if ($missingBankCount > 0) $alertCount++;
                if ($needsStatusReviewCount > 0) $alertCount++;

                $civilCount = User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'ข้าราชการ')->count();
                $permCount = User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'ลูกจ้างประจำ')->count();
                $govCount = User::whereNotIn('role', ['admin', 'finance'])->where('employee_type', 'พนักงานราชการ')->count();
                $totalEmployeesCount = User::whereNotIn('role', ['admin', 'finance'])->count();

                $statusNormalCount = User::whereNotIn('role', ['admin', 'finance'])->where('status', 'ปกติ')->count();
                $statusTransferredCount = User::whereNotIn('role', ['admin', 'finance'])->where('status', 'ย้าย')->count();
                $statusRetiredCount = User::whereNotIn('role', ['admin', 'finance'])->where('status', 'เกษียณ')->count();
                $statusResignedCount = User::whereNotIn('role', ['admin', 'finance'])->where('status', 'ลาออก')->count();
                $statusDeceasedCount = User::whereNotIn('role', ['admin', 'finance'])->where('status', 'เสียชีวิต')->count();

                $totalUsersAll = User::count();
                $totalStaffAdmins = User::whereIn('role', ['admin', 'finance'])->count();

                $view->with('systemNotifications', [
                    'missing_citizen_id' => $missingCitizenIdCount,
                    'missing_bank' => $missingBankCount,
                    'needs_status_review' => $needsStatusReviewCount,
                    'latest_batch' => $latestBatch,
                    'total_batches' => $totalBatchesCount,
                    'total_alerts' => $alertCount,
                    'count_civil' => $civilCount,
                    'count_permanent' => $permCount,
                    'count_gov' => $govCount,
                    'count_total_employees' => $totalEmployeesCount,
                    'count_users_all' => $totalUsersAll,
                    'count_staff_admins' => $totalStaffAdmins,
                    'status_normal' => $statusNormalCount,
                    'status_transferred' => $statusTransferredCount,
                    'status_retired' => $statusRetiredCount,
                    'status_resigned' => $statusResignedCount,
                    'status_deceased' => $statusDeceasedCount,
                ]);
            }
        });
    }
}
