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
        if ($appUrl = config('app.url')) {
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
            if (str_starts_with($appUrl, 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        // Ensure pagination links always respect forced root URL (e.g. /salary subdirectory on IIS)
        \Illuminate\Pagination\Paginator::currentPathResolver(function () {
            return url(request()->path());
        });

        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                $missingCitizenIdCount = User::where('role', '!=', 'admin')
                    ->where(function ($q) {
                        $q->whereNull('citizen_id')->orWhere('citizen_id', '');
                    })->count();

                $missingBankCount = User::where('role', '!=', 'admin')
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

                    $needsStatusReviewCount = User::where('role', '!=', 'admin')
                        ->whereNotIn('id', $recentUserIds)
                        ->where('status', 'ปกติ')
                        ->count();
                }

                $alertCount = 0;
                if ($missingCitizenIdCount > 0) $alertCount++;
                if ($missingBankCount > 0) $alertCount++;
                if ($needsStatusReviewCount > 0) $alertCount++;

                $civilCount = User::where('role', '!=', 'admin')->where('employee_type', 'ข้าราชการ')->count();
                $permCount = User::where('role', '!=', 'admin')->where('employee_type', 'ลูกจ้างประจำ')->count();
                $govCount = User::where('role', '!=', 'admin')->where('employee_type', 'พนักงานราชการ')->count();
                $totalEmployeesCount = User::where('role', '!=', 'admin')->count();

                $statusNormalCount = User::where('role', '!=', 'admin')->where('status', 'ปกติ')->count();
                $statusTransferredCount = User::where('role', '!=', 'admin')->where('status', 'ย้าย')->count();
                $statusRetiredCount = User::where('role', '!=', 'admin')->where('status', 'เกษียณ')->count();
                $statusResignedCount = User::where('role', '!=', 'admin')->where('status', 'ลาออก')->count();
                $statusDeceasedCount = User::where('role', '!=', 'admin')->where('status', 'เสียชีวิต')->count();

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
