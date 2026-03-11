<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Shared activity-logging helper for all admin controllers.
 *
 * Usage (from any controller):
 *   AdminActivityLog::log('bid_approved', ['bid_id' => $id]);
 */
class AdminActivityLog
{
    public static function log(string $action, ?array $details = null, ?string $adminId = null): void
    {
        try {
            if (!$adminId) {
                $adminId = static::currentAdminId();
            }

            if (!$adminId) {
                return;
            }

            DB::table('admin_activity_logs')->insert([
                'admin_id'   => $adminId,
                'action'     => $action,
                'details'    => $details ? json_encode($details) : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never let logging crash the actual request
        }
    }

    public static function currentAdminId(): ?string
    {
        $sess = Session::get('user');

        if ($sess) {
            if (is_object($sess) && property_exists($sess, 'admin_id') && $sess->admin_id) {
                return (string) $sess->admin_id;
            }
            if (is_array($sess) && !empty($sess['admin_id'])) {
                return (string) $sess['admin_id'];
            }
        }

        return null;
    }
}
