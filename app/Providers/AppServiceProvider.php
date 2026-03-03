<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\subs\platformPaymentController;

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
        // Use custom PersonalAccessToken model to handle user_id primary key
        Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);

        // DO NOT register default Broadcast::routes() - using custom implementation
        // Share subscription modal data with the subscription partial so it can render server-side
        // Also share with contractor navbar to control AI Analytics visibility
        try {
            View::composer(['partials.subscription_Modal', 'partials.boost_Modal', 'partials.navbar_Contractor'], function ($view) {
                $data = platformPaymentController::shareModalData();
                $view->with($data);
            });
        }
        catch (\Throwable $e) {
            // Fail silently to avoid breaking page renders if subscription helper errors
            \Illuminate\Support\Facades\Log::error('View Composer Error: ' . $e->getMessage());
        }
    }
}
