# Switch Role (Contractor/Owner) — Implementation & Changes

This document summarizes all changes made to implement reliable role switching for stateless mobile clients, ensure the active role is reflected across screens, and fix related inconsistencies.

## Goals
- Support stateless mobile clients using Bearer tokens (no PHP session).
- Persist active role server-side so `/api/role/current` remains consistent.
- Refresh mobile UI (homepage and profiles) to reflect the latest role.
- Ensure contractor profile information (e.g., `company_name`) shows correctly.

---
## Backend Changes

### app/Http/Controllers/contractor/cprocessController.php
- `switchRole(Request $request)`
  - Added Bearer token fallback authentication when session/`$request->user()` is absent.
  - Persists the active role to `users.preferred_role` after a successful switch (via Eloquent or query builder), enabling stateless clients to keep the role across requests.
- `getCurrentRole(Request $request)`
  - Added Bearer token fallback authentication.
  - If no `Session::get('current_role')`, reads `users.preferred_role` from the database and uses it as the current role for stateless clients.
  - Normalizes owner variant names (`property_owner` → `owner`).
- `apiGetMyContractorProfile(Request $request)`
  - New endpoint to return the authenticated contractor profile (minimal payload including `company_name`) for the mobile contractor profile screen.

### routes/api.php
- Role management endpoints (already present):
  - `POST /api/role/switch` → `cprocessController@switchRole`
  - `GET /api/role/current` → `cprocessController@getCurrentRole`
- Added:
  - `GET /api/contractor/me` → `cprocessController@apiGetMyContractorProfile`

### .github/copilot-instructions.md
- Updated implementation notes to document that role switching persists `preferred_role` and that `getCurrentRole` reads it for stateless clients.

---
## Frontend (Mobile) Changes

### screens/both/homepage.tsx
- Adds `AppState` listener and uses `role_service.get_current_role()` on mount and when app becomes active to compute `effectiveUserType` (`contractor` vs `property_owner`).
- Drives dashboard/profile rendering from the latest role so UI updates immediately after switching.

### screens/both/switchRole.tsx
- After a successful switch (`role_service.switch_role()`), re-fetches `/api/role/current` to synchronize local state before navigating.

### screens/owner/profile.tsx
- Fetches and normalizes current role (from `current_role` / `data.current_role` / `user_type`) on mount and when returning to foreground to render the correct badge.

### screens/contractor/profile.tsx
- Fetches and normalizes current role on mount/foreground.
- Loads contractor profile via `contractors_service.get_my_contractor_profile()` and displays `company_name` in the header.
- Fixes payload parsing to read `company_name` from `{ success, data: { company_name } }`.

### services/contractors_service.ts
- Added `get_my_contractor_profile()` to call `/api/contractor/me` (relative path) using Bearer auth via the shared `api_request` wrapper.
- Fixed endpoint to use a relative path `/api/contractor/me` so `api_request` does not double-prepend base URL.

### services/role_service.ts (reference)
- Provides `get_current_role()` and `switch_role()` used by screens; no functional changes required for this task.

### config/api.ts (reference)
- Shared `api_request` function attaches Bearer tokens and builds URLs from relative endpoints.

---
## Why These Changes Were Needed
- Mobile clients are stateless and do not carry PHP sessions. Previously, role/current fell back to a default (often `contractor`) after a switch because the active role was only in session.
- Persisting `preferred_role` fixes the mismatch by storing the choice server-side. Reading it back in `getCurrentRole` ensures consistency for Bearer-authenticated requests.
- UI needed to refresh its role state on mount/focus; otherwise, it could render stale content.
- The contractor profile screen needed a reliable API to fetch `company_name` and parse the payload correctly.

---
## Test Plan

### Backend
1. Clear caches and start server:
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan serve
   ```

### Mobile (Expo)
1. Start the app:
   ```powershell
   npm run start
   ```
2. Log in to a `both` user and open `Switch Role`.
3. Switch to Owner; confirm the success alert.
4. Navigate to homepage and profile:
   - `/api/role/current` should return `current_role: "owner"`.
   - Owner/Contractor badges reflect the switched role.
5. Switch back to Contractor:
   - Confirm `current_role: "contractor"` on logs.
   - Contractor profile shows `company_name` (from `/api/contractor/me`).

### Expected Logs
- `Raw response: {"success":true,"user_type":"both","current_role":"owner","can_switch_roles":true}` after switching to owner.
- `Raw response: {"success":true,"data":{"contractor_id":...,"company_name":"..."}}` on contractor profile.

---
## Files Edited & Purpose
- Backend:
  - `app/Http/Controllers/contractor/cprocessController.php` — Bearer fallback; persist/read `preferred_role`; add contractor profile API.
  - `routes/api.php` — Register `/api/contractor/me`.
  - `.github/copilot-instructions.md` — Document role persistence behavior.
- Frontend:
  - `frontend/mobile_app/src/screens/both/homepage.tsx` — Role refresh and `effectiveUserType` derivation.
  - `frontend/mobile_app/src/screens/both/switchRole.tsx` — Re-fetch role/current after switching.
  - `frontend/mobile_app/src/screens/owner/profile.tsx` — Update badge via role/current.
  - `frontend/mobile_app/src/screens/contractor/profile.tsx` — Fetch contractor profile; display `company_name`; normalize payload.
  - `frontend/mobile_app/src/services/contractors_service.ts` — New API method and endpoint fix.

---
## Notes & Gotchas
- For users with `user_type = both`, `getCurrentRole` uses `users.preferred_role` when session `current_role` is absent; otherwise defaults to `contractor`.
- The mobile app relies on Bearer tokens; avoid CSRF and cookie flows for `/api/*` endpoints.
- Use relative endpoints (`/api/...`) in services so `api_request` builds the full URL correctly.

---
## Future Enhancements
- Add React Navigation `useFocusEffect` to re-sync role state when screens gain focus.
- Extend contractor profile API to include contractor type and other public fields for richer profile header.



Add accurate “In Progress” counts in My Projects, show “Completed” on cards, and make Business Permit City a searchable PSGC dropdown

My Projects: remove default “in_progress” normalization; compute stats and filter from real project_status, require milestones for “In Progress”, and map status synonyms so cards render “Completed” reliably.
Registration (Contractor Step 2): replace free-text “Business Permit City” with a searchable modal listing all PH cities/municipalities via PSGC; selection sets business_permit_city.
Auth Service: add get_all_cities() using PSGC cities-municipalities endpoint; sort and normalize {code,name}.
UI Styles: add selector and modal search input styles for the new city dropdown.
Docs: SwitchRole README reviewed; role persistence (preferred_role) and current role API flow unchanged and compatible.
Files touched

myProjects.tsx
addRoleRegistration.tsx
auth_service.ts
Notes

No backend changes required; Step 2 still submits business_permit_city as a name string.
Status mapping now recognizes “complete”, “finished”, “done”, and “ongoing” synonyms for consistent badges and filters.
Optional test steps

In My Projects, verify the “In Progress” chip count and tab show only ongoing projects with milestones; completed cards display “Completed”.
In Contractor Step 2, tap “Business Permit City”, search, and select a city; confirm it appears in the preview and submits with Step 2.
GPT-5 • 1xAdd accurate “In Progress” counts in My Projects, show “Completed” on cards, and make Business Permit City a searchable PSGC dropdown

My Projects: remove default “in_progress” normalization; compute stats and filter from real project_status, require milestones for “In Progress”, and map status synonyms so cards render “Completed” reliably.
Registration (Contractor Step 2): replace free-text “Business Permit City” with a searchable modal listing all PH cities/municipalities via PSGC; selection sets business_permit_city.
Auth Service: add get_all_cities() using PSGC cities-municipalities endpoint; sort and normalize {code,name}.
UI Styles: add selector and modal search input styles for the new city dropdown.
Docs: SwitchRole README reviewed; role persistence (preferred_role) and current role API flow unchanged and compatible.
Files touched

myProjects.tsx
addRoleRegistration.tsx
auth_service.ts
Notes

No backend changes required; Step 2 still submits business_permit_city as a name string.
Status mapping now recognizes “complete”, “finished”, “done”, and “ongoing” synonyms for consistent badges and filters.
Optional test steps

In My Projects, verify the “In Progress” chip count and tab show only ongoing projects with milestones; completed cards display “Completed”.
In Contractor Step 2, tap “Business Permit City”, search, and select a city; confirm it appears in the preview and submits with Step 2.
