<?php

namespace App\Http\Controllers\subs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\subs\platformPaymentClass;

class platformPaymentController extends Controller
{
    /**
     * Return an array of data for the modal partials (safe, non-throwing)
     */
    public static function shareModalData(): array
    {
        $user = Session::get('user');

        if (!$user) {
            $user = auth()->user();
        }

        if (!$user && request()->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken(request()->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            }
            catch (\Throwable $e) {
                $user = null;
            }
        }

        // Normalize user id (support both `user_id` and `id` shapes, focusing on `user_id` first)
        $userId = null;
        if ($user) {
            if (is_object($user)) {
                $userId = $user->user_id ?? $user->id ?? null;
            }
            elseif (is_array($user)) {
                $userId = $user['user_id'] ?? $user['id'] ?? null;
            }
        }

        $subscription = platformPaymentClass::getSubscriptionForUser($userId);
        $boostAnalytics = platformPaymentClass::getBoostAnalytics($userId);
        $boostedPosts = platformPaymentClass::getBoostedPosts($userId);
        $boostableProjects = platformPaymentClass::getBoostableProjects($userId);

        return [
            'subscription' => $subscription,
            'boostAnalytics' => $boostAnalytics,
            'boostedPosts' => $boostedPosts,
            'boostableProjects' => $boostableProjects,
        ];
    }

    /**
     * Optional JSON endpoint for frontend fetch if needed
     */
    public function modalData(Request $request)
    {
        return response()->json(self::shareModalData());
    }
}
