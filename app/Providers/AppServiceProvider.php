<?php

namespace App\Providers;

use App\Models\TransportationRequestFormModel;
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
        View::composer('layouts.footer', function ($view) {
            $returnedRequestMessages = collect();
            $authUser = Auth::user();

            if ($authUser) {
                $roles = collect(explode(',', (string) $authUser->role))
                    ->map(function ($value) {
                        return strtolower(trim((string) $value));
                    })
                    ->filter();

                $adminRoles = collect(['admin', 'chief_of_motorpool_section']);
                $hasAdminRole = $roles->intersect($adminRoles)->isNotEmpty();
                $hasNonAdminRole = $roles->diff($adminRoles)->isNotEmpty();

                if (!($hasAdminRole && !$hasNonAdminRole)) {
                    $returnedRequestMessages = TransportationRequestFormModel::query()
                        ->where('form_creator_id', $authUser->personnel_id)
                        ->where('status', 'Rejected')
                        ->whereNotNull('rejection_reason')
                        ->orderByDesc('updated_at')
                        ->limit(5)
                        ->get();
                }
            }

            $view->with('returnedRequestMessages', $returnedRequestMessages);
            $view->with('returnedRequestMessageUserId', (int) ($authUser->id ?? 0));
        });
    }
}
