<?php

namespace App\Providers;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use App\Support\AssignatoryPersonnelResolver;
use Illuminate\Database\Eloquent\Builder;
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
        View::share('activeAssignatory', AssignatoryPersonnelResolver::resolve());

        $adminRoles = collect(['admin', 'chief_of_motorpool_section']);
        $userRoles = collect(['user', 'operator', 'driver']);

        View::composer('layouts.footer', function ($view) use ($adminRoles, $userRoles) {
            $returnedRequestMessages = collect();
            $authUser = Auth::user();
            $pendingUserEvaluationCount = 0;
            $pendingUserEvaluationSignature = '';

            if ($authUser) {
                $roles = collect(explode(',', (string) $authUser->role))
                    ->map(function ($value) {
                        return strtolower(trim((string) $value));
                    })
                    ->filter();

                $hasAdminRole = $roles->intersect($adminRoles)->isNotEmpty();
                $hasUserAreaRole = $roles->intersect($userRoles)->isNotEmpty();
                $hasNonAdminRole = $roles->diff($adminRoles)->isNotEmpty();

                if (!($hasAdminRole && !$hasNonAdminRole)) {
                    $returnedRequestMessages = TransportationRequestFormModel::query()
                        ->where('form_creator_id', $authUser->personnel_id)
                        ->where('status', 'Rejected')
                        ->whereNotNull('rejection_reason')
                        ->where('rejection_reason', '!=', '')
                        ->orderByDesc('updated_at')
                        ->limit(20)
                        ->get([
                            'id',
                            'form_id',
                            'rejection_reason',
                            'attachments',
                            'updated_at',
                        ]);
                }

                if ($hasUserAreaRole) {
                    $pendingUserEvaluationsQuery = DriverPerformanceEvaluation::query()
                        ->where('status', 'Pending')
                        ->whereHas('transportationRequestForm', function (Builder $query) use ($authUser) {
                            $query->where('form_creator_id', (string) $authUser->personnel_id)
                                ->where('status', 'For Evaluation');
                        });

                    $pendingUserEvaluationCount = (clone $pendingUserEvaluationsQuery)->count();

                    if ($pendingUserEvaluationCount > 0) {
                        $latestPendingEvaluation = (clone $pendingUserEvaluationsQuery)
                            ->select(['id', 'updated_at'])
                            ->orderByDesc('updated_at')
                            ->orderByDesc('id')
                            ->first();

                        $pendingUserEvaluationSignature = implode('|', [
                            (string) $pendingUserEvaluationCount,
                            (string) ($latestPendingEvaluation?->id ?? 0),
                            (string) (optional($latestPendingEvaluation?->updated_at)->timestamp ?? 0),
                        ]);
                    }
                }
            }

            $view->with('returnedRequestMessages', $returnedRequestMessages);
            $view->with('returnedRequestMessageUserId', (int) ($authUser->id ?? 0));
            $view->with('pendingUserEvaluationCount', (int) $pendingUserEvaluationCount);
            $view->with('pendingUserEvaluationSignature', (string) $pendingUserEvaluationSignature);
        });

        View::composer('layouts.admin_footer', function ($view) use ($adminRoles) {
            $authUser = Auth::user();
            $adminPendingTransportationRequestCount = 0;
            $adminPendingTransportationRequestSignature = '';

            if ($authUser) {
                $roles = collect(explode(',', (string) $authUser->role))
                    ->map(function ($value) {
                        return strtolower(trim((string) $value));
                    })
                    ->filter();

                $hasAdminRole = $roles->intersect($adminRoles)->isNotEmpty();

                if ($hasAdminRole) {
                    $pendingTransportationRequestsQuery = TransportationRequestFormModel::query()
                        ->whereIn('status', ['To be Signed', 'Pending']);

                    $adminPendingTransportationRequestCount = (clone $pendingTransportationRequestsQuery)->count();

                    if ($adminPendingTransportationRequestCount > 0) {
                        $latestPendingTransportationRequest = (clone $pendingTransportationRequestsQuery)
                            ->select(['id', 'updated_at'])
                            ->orderByDesc('updated_at')
                            ->orderByDesc('id')
                            ->first();

                        $adminPendingTransportationRequestSignature = implode('|', [
                            (string) $adminPendingTransportationRequestCount,
                            (string) ($latestPendingTransportationRequest?->id ?? 0),
                            (string) (optional($latestPendingTransportationRequest?->updated_at)->timestamp ?? 0),
                        ]);
                    }
                }
            }

            $view->with('adminPendingTransportationRequestCount', (int) $adminPendingTransportationRequestCount);
            $view->with('adminPendingTransportationRequestSignature', (string) $adminPendingTransportationRequestSignature);
            $view->with('adminPendingTransportationRequestUserId', (int) ($authUser->id ?? 0));
        });
    }
}
