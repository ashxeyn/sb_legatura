<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Throwable;

class AccountController extends Controller
{
    /**
     * Resolve the currently authenticated admin.
     * Tries multiple session keys and falls back to auth()->user() email match.
     */
    protected function resolveAdmin(Request $request): ?object
    {
        // authController::login() always does: Session::put('user', $result['user'])
        // where $result['user'] is the admin_users row (has admin_id field).
        // This is the definitive lookup — match exactly what authController stores.

        $sess = Session::get('user');

        if ($sess) {
            // stdClass object from DB::table() query
            if (is_object($sess)) {
                $adminId = property_exists($sess, 'admin_id') ? $sess->admin_id : null;
                if ($adminId) {
                    $row = DB::table('admin_users')->where('admin_id', $adminId)->first();
                    if ($row) return $row;
                }
                // Fallback: match by email in case object shape differs
                $email = property_exists($sess, 'email') ? $sess->email : null;
                if ($email) {
                    $row = DB::table('admin_users')->where('email', $email)->first();
                    if ($row) return $row;
                }
            }

            // Array shape
            if (is_array($sess)) {
                $adminId = $sess['admin_id'] ?? null;
                $email   = $sess['email']    ?? null;
                if ($adminId) {
                    $row = DB::table('admin_users')->where('admin_id', $adminId)->first();
                    if ($row) return $row;
                }
                if ($email) {
                    $row = DB::table('admin_users')->where('email', $email)->first();
                    if ($row) return $row;
                }
            }
        }

        // authController also stores userType — double-check we are handling an admin session
        $userType = Session::get('userType');
        if ($userType !== 'admin') {
            // Not an admin session at all — log for debugging
            \Illuminate\Support\Facades\Log::warning('AccountController: resolveAdmin called but userType is not admin', [
                'userType'    => $userType,
                'sessionKeys' => array_keys(Session::all()),
            ]);
        }

        // Last resort: Laravel auth guard (only works if admin_users is the auth model)
        try {
            $authUser = $request->user();
            if ($authUser) {
                $adminId = property_exists($authUser, 'admin_id') ? $authUser->admin_id : null;
                $email   = property_exists($authUser, 'email')    ? $authUser->email    : null;
                if ($adminId) {
                    $row = DB::table('admin_users')->where('admin_id', $adminId)->first();
                    if ($row) return $row;
                }
                if ($email) {
                    $row = DB::table('admin_users')->where('email', $email)->first();
                    if ($row) return $row;
                }
            }
        } catch (\Throwable) {}

        return null;
    }

    /**
     * Auto-create missing columns/tables so the page never hard-crashes.
     */
    protected function ensureSchema(): void
    {
        if (!Schema::hasColumn('admin_users', 'profile_pic')) {
            DB::statement('ALTER TABLE admin_users ADD COLUMN profile_pic VARCHAR(255) NULL');
        }

        if (!Schema::hasTable('admin_activity_logs')) {
            DB::statement('
                CREATE TABLE admin_activity_logs (
                    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    admin_id    INT(11)         NOT NULL,
                    action      VARCHAR(100)    NOT NULL,
                    details     TEXT            NULL,
                    ip_address  VARCHAR(45)     NULL,
                    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_admin_created (admin_id, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ');
        }
    }

    // ── GET /admin/settings/security/data ──────────────────────
    public function data(Request $request)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);

            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
            }

            $logs = DB::table('admin_activity_logs')
                ->where('admin_id', $admin->admin_id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'admin' => [
                        'admin_id'    => $admin->admin_id,
                        'first_name'  => $admin->first_name  ?? '',
                        'middle_name' => $admin->middle_name ?? '',
                        'last_name'   => $admin->last_name   ?? '',
                        'email'       => $admin->email       ?? '',
                        'username'    => $admin->username    ?? '',
                        'profile_pic' => $admin->profile_pic ?? null,
                        'created_at'  => $admin->created_at  ?? null,
                    ],
                    'logs' => $logs,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── POST /admin/settings/security/update ───────────────────
    public function update(Request $request)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $request->validate([
                'email'       => 'required|email|unique:admin_users,email,' . $admin->admin_id . ',admin_id',
                'username'    => 'required|string|max:50|unique:admin_users,username,' . $admin->admin_id . ',admin_id',
                'first_name'  => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name'   => 'required|string|max:100',
                'avatar'      => 'nullable|image|max:2048',
            ]);

            $payload = [
                'email'       => $request->input('email'),
                'username'    => $request->input('username'),
                'first_name'  => $request->input('first_name'),
                'middle_name' => $request->input('middle_name') ?: null,
                'last_name'   => $request->input('last_name'),
            ];

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $path = $file->storeAs('profiles', time() . '_admin_' . $file->getClientOriginalName(), 'public');
                $payload['profile_pic'] = $path;
            }

            DB::table('admin_users')->where('admin_id', $admin->admin_id)->update($payload);
            $this->logActivity($admin->admin_id, 'profile_updated', ['email' => $payload['email'], 'username' => $payload['username']]);

            // Refresh session
            Session::put('admin', DB::table('admin_users')->where('admin_id', $admin->admin_id)->first());

            return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── POST /admin/settings/security/change-password ──────────
    public function changePassword(Request $request)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $request->validate([
                'current_password' => 'required|string',
                'new_password'     => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $admin->password_hash)) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
            }

            DB::table('admin_users')
                ->where('admin_id', $admin->admin_id)
                ->update(['password_hash' => bcrypt($request->new_password)]);

            $this->logActivity($admin->admin_id, 'password_changed', null);

            return response()->json(['success' => true, 'message' => 'Password changed successfully.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── POST /admin/settings/security/delete ───────────────────
    public function delete(Request $request)
    {
        try {
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $this->logActivity($admin->admin_id, 'account_deleted', null);
            DB::table('admin_users')->where('admin_id', $admin->admin_id)->update(['is_active' => 0]);
            Session::flush();
            auth()->logout();

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── GET /admin/settings/security/debug  (REMOVE IN PRODUCTION) ──
    public function debug(Request $request)
    {
        $allSession = Session::all();
        // Mask passwords
        array_walk_recursive($allSession, function(&$v, $k) {
            if (str_contains(strtolower((string)$k), 'pass')) $v = '***';
        });
        return response()->json([
            'session_keys'   => array_keys($allSession),
            'session_data'   => $allSession,
            'auth_user'      => $request->user(),
            'auth_check'     => auth()->check(),
            'resolved_admin' => $this->resolveAdmin($request),
        ]);
    }

    protected function logActivity(int $adminId, string $action, ?array $details): void
    {
        try {
            DB::table('admin_activity_logs')->insert([
                'admin_id'   => $adminId,
                'action'     => $action,
                'details'    => $details ? json_encode($details) : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {}
    }
}