## Quick context for AI coding assistants

This is a Laravel 12 (PHP 8.2) web application scaffolded from the Laravel starter. The repository mixes idiomatic Laravel components with a few project-specific conventions you must know before making changes.

Key places to read first:
- `routes/web.php` — app routing and important debug/storage fallback routes (see `/debug/check-projects` and `/storage/{path}` handlers).
- `app/Http/Controllers/authController.php` — heavy use of session-based multi-step signup flows, in-controller validation rules, and the Windows/XAMPP storage fallback serving method.
- `app/Http/Controllers/contractor/cprocessController.php` — role switching persists `preferred_role` in `users` for stateless clients; `getCurrentRole` reads DB when session is absent.
- `app/Services/authService.php` and `app/Services/psgcApiService.php` — business logic for authentication, OTP/email sending, and the PSGC external API adapter (with built-in caching and fallback data).
- `app/Models` and `database/migrations` — database shape and where to find fields referenced in controllers (e.g. `users`, `contractors`, `property_owners`, `projects`).
- `composer.json` and `package.json` — developer scripts for setup, dev and test workflows.

Project-specific patterns (concrete, discoverable):
- Multi-step registration stores intermediate state in Session keys like `contractor_step1`, `contractor_step2`, `owner_step1`, `signup_step`. When changing signup flows, update these keys and any front-end code that reads them.
- Controllers perform validation inline (see `authController` and Request classes embedded in the same file). Prefer updating those validation rules there rather than searching only for separate `FormRequest` classes.
- Business logic lives in `app/Services/*` (e.g., `authService`, `psgcApiService`) — keep controller actions thin and call service methods when adding behavior.
- Some DB access uses query builder (`DB::table(...)`) via service or controller code rather than Eloquent everywhere. When modifying queries, check for both models in `app/Models` and raw queries in `app/Services` / controllers.
- External integrations:
  - PSGC API: `app/Services/psgcApiService.php` (cached responses and a hard-coded fallback list if the API fails).
  - Mail: OTPs are logged and sent via Laravel Mail in `authService::sendOtpEmail` (logged for debugging).
  - Storage: public disk is used and there's a Windows/XAMPP fallback route in `authController::serve` that must be tested on Windows.

Developer workflows & commands (verified from repo files):
- Setup (first time):
```powershell
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```
- Local dev (concurrent services): use the composer `dev` script which runs server, queue listener, logs and vite concurrently:
```powershell
composer run-script dev
```
- Quick local serve (single process):
```powershell
php artisan serve
npm run dev
```
- Tests: repository uses PHPUnit via `php artisan test` and the `phpunit.xml` config runs tests using sqlite in-memory. Run:
```powershell
composer test
```

Important implementation notes and gotchas:
- The project targets PHP 8.2 and Laravel 12 (see `composer.json`). Use language features and types accordingly.
- Controllers rely on session-heavy multi-step flows — unit tests should mock Session or use HTTP feature tests to emulate multi-step state.
- The repo includes a storage fallback because symlinks may fail on Windows/XAMPP; when testing file serving locally, prefer the `/storage/{path}` route (implemented in `authController::serve`) or run `php artisan storage:link` when symlinks are available.
- PSGC API calls are cached for 24h; updates to PSGC behavior should consider cache keys: `psgc_provinces`, `psgc_cities_{code}`, `psgc_barangays_{code}`.
- Be conservative changing validation messages and file size limits — they are enforced in controller `Request` rules (see `StoreProjectRequest` embedded in `authController.php`).

What to update here and how to ask for clarification
- If you change any session keys, validation rules, external API cache keys, or database column names, update this file with a one-line note and a reference to the modified files.
- Role switching persistence added: `preferred_role` is written on switch and read in `getCurrentRole` for Sanctum. See `app/Http/Controllers/contractor/cprocessController.php`.
 - Session keys update: `userType` (high-level type, e.g., `user`/`admin`) is stored on web login and `current_role` now falls back to `userType` when `user.user_type` is absent. See updates in `app/Http/Controllers/authController.php` and guarded checks in `app/Http/Controllers/owner/projectsController.php`.
- Pagination implementation: Both `/api/contractors` and `/api/contractor/projects` endpoints now support `page` and `per_page` query parameters (default: 15 per page). Responses include `pagination` metadata with `current_page`, `per_page`, `total`, `total_pages`, and `has_more`. Mobile app implements infinite scroll using these endpoints. See `app/Http/Controllers/owner/projectsController.php`, `frontend/mobile_app/src/services/contractors_service.ts`, `frontend/mobile_app/src/services/projects_service.ts`, and `frontend/mobile_app/src/screens/both/homepage.tsx`.
- If behavior depends on a platform (Windows/XAMPP vs Linux), include a short test plan: commands to run, expected outputs, and any files to inspect.

Files that exemplify the most important patterns:
- `routes/web.php` — routing map and debug/storage routes
- `app/Http/Controllers/authController.php` — session flows, validation, file serve fallback
- `app/Services/authService.php` — OTP/password helpers and email
- `app/Services/psgcApiService.php` — external API adapter + cache + fallback
- `composer.json` / `package.json` / `phpunit.xml` — setup, dev, test scripts and environment

If anything above is unclear or you'd like a shorter or longer version of any section, tell me which area to expand (architecture, run/test commands, examples) and I will iterate.
