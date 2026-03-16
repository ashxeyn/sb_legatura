<?php

namespace App\Http\Controllers\accounts;

use App\Http\Controllers\Controller;
use App\Models\accounts\accountClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class accountController extends Controller
{
    private accountClass $model;

    public function __construct()
    {
        $this->model = new accountClass();
    }

    /**
     * Resolve authenticated user from request.
     */
    private function resolveUser(Request $request)
    {
        $user = $request->user();
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) $user = $token->tokenable;
            } catch (\Throwable $e) {
                Log::warning('accountController bearer fallback failed: ' . $e->getMessage());
            }
        }
        return $user;
    }

    /**
     * GET /api/account/reasons
     */
    public function getReasons()
    {
        return response()->json([
            'success' => true,
            'deletion_reasons' => accountClass::DELETION_REASONS,
        ]);
    }

    /**
     * POST /api/account/delete
     * Immediate soft-delete. Requires confirmation_text === 'ACCOUNT DELETE'.
     */
    public function deleteAccount(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $confirmationText = trim($request->input('confirmation_text', ''));
        if ($confirmationText !== 'ACCOUNT DELETE') {
            return response()->json(['success' => false, 'message' => 'Please type "ACCOUNT DELETE" to confirm.'], 422);
        }

        $userId = $user->user_id ?? $user->id;
        $role = strtolower(trim($request->input('role', '')));
        $reasonKey = $request->input('reason_key', 'other');
        $reasonText = $request->input('reason_text', '');

        $reasonLabel = accountClass::DELETION_REASONS[$reasonKey] ?? 'Something else';
        $reason = $reasonKey === 'other' && $reasonText
            ? "{$reasonLabel} - {$reasonText}"
            : $reasonLabel;

        return DB::transaction(function () use ($userId, $role, $reason) {
            if (str_contains($role, 'owner') && !str_contains($role, 'staff')) {
                $result = $this->model->softDeleteOwner($userId, $reason);
            } elseif ($role === 'contractor_staff') {
                $result = $this->model->softDeleteStaff($userId, $reason);
            } elseif (str_contains($role, 'contractor')) {
                $result = $this->model->softDeleteContractor($userId, $reason);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid role specified'], 400);
            }

            return response()->json($result, $result['code'] ?? 200);
        });
    }
}
