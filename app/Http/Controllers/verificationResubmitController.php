<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationService;

/**
 * Handles re-upload of verification documents after an admin rejection
 * with a RESUBMISSION: reason.
 *
 * Supported roles: property_owner, contractor
 *
 * POST /api/verification/resubmit
 *   Auth: Bearer token (auth:sanctum)
 *   Body (multipart/form-data):
 *     role            string  required  "property_owner" | "contractor"
 *     -- property_owner fields --
 *     valid_id_photo        file  optional
 *     valid_id_back_photo   file  optional
 *     police_clearance      file  optional
 *     -- contractor fields --
 *     dti_sec_registration  file  optional
 */
class verificationResubmitController extends Controller
{
    public function resubmit(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $userId = $user->user_id;

        $request->validate([
            'role' => 'required|string|in:property_owner,contractor',
        ]);

        $role = $request->input('role');

        if ($role === 'property_owner') {
            return $this->resubmitOwner($request, $userId);
        }

        return $this->resubmitContractor($request, $userId);
    }

    // -------------------------------------------------------------------------
    // Property Owner resubmission
    // -------------------------------------------------------------------------

    private function resubmitOwner(Request $request, int $userId)
    {
        $owner = DB::table('property_owners')->where('user_id', $userId)->first();

        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Property owner profile not found.'], 404);
        }

        if ($owner->verification_status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Your verification is not in a rejected state.',
                'current_status' => $owner->verification_status,
            ], 400);
        }

        if (!str_starts_with((string) $owner->rejection_reason, 'RESUBMISSION:')) {
            return response()->json([
                'success' => false,
                'message' => 'This rejection does not allow resubmission. Please contact support.',
            ], 403);
        }

        $updates = [
            'verification_status' => 'pending',
            'rejection_reason'    => null,
        ];

        // Store any newly uploaded documents, keeping existing paths if not replaced
        foreach (['valid_id_photo', 'valid_id_back_photo', 'police_clearance'] as $field) {
            if ($request->hasFile($field)) {
                $folder = $field === 'police_clearance' ? 'policeClearance' : 'validID';
                try {
                    $path = $request->file($field)->store($folder, 'public');
                    $updates[$field] = $path;
                } catch (\Throwable $e) {
                    Log::warning("resubmitOwner: failed to store {$field}", ['error' => $e->getMessage()]);
                }
            }
        }

        DB::table('property_owners')->where('user_id', $userId)->update($updates);

        // Fetch user's first name from users table
        $user = DB::table('users')->where('user_id', $userId)->first();
        $firstName = $user->first_name ?? '';

        $this->notifyResubmitted($userId, 'property owner', $firstName);

        Log::info('Owner verification resubmitted', ['user_id' => $userId]);

        return response()->json([
            'success' => true,
            'message' => 'Your documents have been resubmitted and are pending review.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Contractor resubmission
    // -------------------------------------------------------------------------

    private function resubmitContractor(Request $request, int $userId)
    {
        $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
        $contractor = $ownerId
            ? DB::table('contractors')->where('owner_id', $ownerId)->first()
            : null;

        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor profile not found.'], 404);
        }

        if ($contractor->verification_status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Your verification is not in a rejected state.',
                'current_status' => $contractor->verification_status,
            ], 400);
        }

        if (!str_starts_with((string) $contractor->rejection_reason, 'RESUBMISSION:')) {
            return response()->json([
                'success' => false,
                'message' => 'This rejection does not allow resubmission. Please contact support.',
            ], 403);
        }

        $updates = [
            'verification_status' => 'pending',
            'rejection_reason'    => null,
        ];

        if ($request->hasFile('dti_sec_registration')) {
            try {
                $path = $request->file('dti_sec_registration')->store('contractor_documents', 'public');
                $updates['dti_sec_registration_photo'] = $path;
            } catch (\Throwable $e) {
                Log::warning('resubmitContractor: failed to store dti_sec_registration', ['error' => $e->getMessage()]);
            }
        }

        DB::table('contractors')
            ->where('contractor_id', $contractor->contractor_id)
            ->update($updates);

        // Fetch representative name for the notification
        $firstName = '';
        try {
            $rep = DB::table('contractor_users')
                ->where('contractor_id', $contractor->contractor_id)
                ->where('user_id', $userId)
                ->first();
            $firstName = $rep->first_name ?? ($contractor->company_name ?? '');
        } catch (\Throwable $e) {
            $firstName = $contractor->company_name ?? '';
        }

        $this->notifyResubmitted($userId, 'contractor', $firstName);

        Log::info('Contractor verification resubmitted', ['user_id' => $userId, 'contractor_id' => $contractor->contractor_id]);

        return response()->json([
            'success' => true,
            'message' => 'Your documents have been resubmitted and are pending review.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    private function notifyResubmitted(int $userId, string $roleLabel, string $firstName): void
    {
        // In-app notification
        try {
            NotificationService::create(
                $userId,
                'general',
                'Documents Resubmitted',
                "Your {$roleLabel} verification documents have been resubmitted and are now pending admin review.",
                'high',
                null,
                null,
                ['screen' => 'Home', 'params' => []]
            );
        } catch (\Throwable $e) {
            Log::warning('resubmitVerification: failed to send in-app notification', ['error' => $e->getMessage()]);
        }

        // Email notification
        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if ($user && !empty($user->email)) {
                $body  = "Dear {$firstName},\n\n";
                $body .= "We have received your resubmitted {$roleLabel} verification documents.\n\n";
                $body .= "Our admin team will review them and notify you once a decision has been made.\n\n";
                $body .= "Thank you for your patience.\n\n";
                $body .= "Best regards,\nThe Legatura Team";

                Mail::raw($body, function ($msg) use ($user) {
                    $msg->to($user->email)
                        ->subject('Legatura - Documents Resubmitted Successfully');
                });
            }
        } catch (\Throwable $e) {
            Log::warning('resubmitVerification: failed to send email', ['error' => $e->getMessage()]);
        }
    }
}
