<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Throwable;

class accountController extends Controller
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
            \Illuminate\Support\Facades\Log::warning('accountController: resolveAdmin called but userType is not admin', [
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
                    admin_id    VARCHAR(20)         NOT NULL,
                    action      VARCHAR(100)    NOT NULL,
                    details     TEXT            NULL,
                    ip_address  VARCHAR(45)     NULL,
                    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_admin_created (admin_id, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        } else {
            // Fix column type: admin_users.admin_id is VARCHAR(20) (e.g. 'ADMIN-1')
            // but the migration may have created admin_activity_logs.admin_id as BIGINT.
            // This mismatch silently drops inserts and breaks joins.
            try {
                $col = DB::selectOne("SHOW COLUMNS FROM admin_activity_logs WHERE Field = 'admin_id'");
                if ($col && stripos($col->Type, 'bigint') !== false) {
                    DB::statement('ALTER TABLE admin_activity_logs MODIFY admin_id VARCHAR(20) NOT NULL');
                }
            } catch (\Throwable $e) {
                // Ignore — insufficient privileges or already correct
            }

            // Ensure collation matches admin_users to prevent join errors
            try {
                DB::statement('ALTER TABLE admin_activity_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (\Throwable $e) {
                // Already correct or insufficient privileges — ignore
            }
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
                ->limit(500)
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

    protected function logActivity(string $adminId, string $action, ?array $details): void
    {
        try {
            DB::table('admin_activity_logs')->insert([
                'admin_id'   => $adminId,
                'action'     => $action,
                'details'    => $details ? json_encode($details) : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // THIS WILL LOG THE REAL ERROR TO storage/logs/laravel.log
            \Illuminate\Support\Facades\Log::error('LogActivity Error: ' . $e->getMessage());
        }
    }

    // ── GET /admin/settings/security/members ──────────────────────────────────
    // Returns all admin accounts except the currently logged-in one.
    public function members(Request $request)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $members = DB::table('admin_users')
                ->where('admin_id', '!=', $admin->admin_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($m) => [
                    'admin_id'    => $m->admin_id,
                    'first_name'  => $m->first_name  ?? '',
                    'last_name'   => $m->last_name   ?? '',
                    'email'       => $m->email        ?? '',
                    'username'    => $m->username     ?? '',
                    'profile_pic' => $m->profile_pic  ?? null,
                    'is_active'   => $m->is_active    ?? 1,
                    'created_at'  => $m->created_at   ?? null,
                ]);

            return response()->json(['success' => true, 'data' => ['members' => $members]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── GET /admin/settings/security/members/{id}/data ─────────────────────────
    // Returns a specific admin's profile + their activity logs.
    public function memberData(Request $request, $id)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $member = DB::table('admin_users')->where('admin_id', $id)->first();
            if (!$member) return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);

            $logs = DB::table('admin_activity_logs')
                ->where('admin_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'admin' => [
                        'admin_id'    => $member->admin_id,
                        'first_name'  => $member->first_name  ?? '',
                        'middle_name' => $member->middle_name ?? '',
                        'last_name'   => $member->last_name   ?? '',
                        'email'       => $member->email        ?? '',
                        'username'    => $member->username     ?? '',
                        'profile_pic' => $member->profile_pic  ?? null,
                        'is_active'   => $member->is_active    ?? 1,
                        'created_at'  => $member->created_at   ?? null,
                    ],
                    'logs' => $logs,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── POST /admin/settings/security/members/create ────────────────────────────
    public function createMember(Request $request)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name'  => 'required|string|max:100',
                'email'      => 'required|email|unique:admin_users,email',
                'username'   => 'required|string|max:50|unique:admin_users,username',
                'password'   => 'required|string|min:8',
            ]);

            // Generate a unique admin_id (e.g. ADM-XXXXXX)
            do {
                $newId = 'ADM-' . strtoupper(substr(md5(uniqid()), 0, 6));
            } while (DB::table('admin_users')->where('admin_id', $newId)->exists());

            DB::table('admin_users')->insert([
                'admin_id'      => $newId,
                'first_name'    => $request->input('first_name'),
                'last_name'     => $request->input('last_name'),
                'email'         => $request->input('email'),
                'username'      => $request->input('username'),
                'password_hash' => bcrypt($request->input('password')),
                'is_active'     => 1,
                'created_at'    => now(),
            ]);

            $this->logActivity($admin->admin_id, 'member_created', [
                'new_admin_id' => $newId,
                'email'        => $request->input('email'),
            ]);

            return response()->json(['success' => true, 'message' => 'Admin account created successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── POST /admin/settings/security/members/{id}/update ──────────────────────
    public function updateMember(Request $request, $id)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $member = DB::table('admin_users')->where('admin_id', $id)->first();
            if (!$member) return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);

            $request->validate([
                'first_name' => 'required|string|max:100',
                'last_name'  => 'required|string|max:100',
                'email'      => 'required|email|unique:admin_users,email,' . $id . ',admin_id',
                'username'   => 'required|string|max:50|unique:admin_users,username,' . $id . ',admin_id',
            ]);

            $payload = [
                'first_name' => $request->input('first_name'),
                'last_name'  => $request->input('last_name'),
                'email'      => $request->input('email'),
                'username'   => $request->input('username'),
            ];

            // Optionally reset password
            if ($request->filled('password')) {
                if (strlen($request->input('password')) < 8) {
                    return response()->json(['success' => false, 'message' => 'Password must be at least 8 characters.'], 422);
                }
                $payload['password_hash'] = bcrypt($request->input('password'));
            }

            DB::table('admin_users')->where('admin_id', $id)->update($payload);

            $this->logActivity($admin->admin_id, 'member_updated', [
                'target_admin_id' => $id,
                'email'           => $payload['email'],
            ]);

            return response()->json(['success' => true, 'message' => 'Admin account updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── POST /admin/settings/security/members/{id}/delete ──────────────────────
    public function deleteMember(Request $request, $id)
    {
        try {
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            if ($id === $admin->admin_id) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account from this panel.'], 422);
            }

            $member = DB::table('admin_users')->where('admin_id', $id)->first();
            if (!$member) return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);

            DB::table('admin_users')->where('admin_id', $id)->update(['is_active' => 0]);

            $this->logActivity($admin->admin_id, 'member_deleted', [
                'deleted_admin_id' => $id,
                'email'            => $member->email ?? '',
            ]);

            return response()->json(['success' => true, 'message' => 'Admin account deactivated.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── GET /admin/settings/security/team-activity ──────────────────────────────
    // Returns combined activity logs of ALL admin accounts, joined with admin names.
    public function teamActivity(Request $request)
    {
        try {
            $this->ensureSchema();
            $admin = $this->resolveAdmin($request);
            if (!$admin) return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);

            $logs = DB::table('admin_activity_logs as aal')
                ->leftJoin('admin_users as au', DB::raw('au.admin_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('aal.admin_id COLLATE utf8mb4_unicode_ci'))
                ->select(
                    'aal.id',
                    'aal.admin_id',
                    DB::raw("CONCAT(COALESCE(au.first_name,''), ' ', COALESCE(au.last_name,'')) as admin_name"),
                    'aal.action',
                    'aal.details',
                    'aal.ip_address',
                    'aal.created_at'
                )
                ->orderBy('aal.created_at', 'desc')
                ->limit(500)
                ->get();

            return response()->json(['success' => true, 'data' => ['logs' => $logs]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}