# API Implementation Plan (This is now done and reading this is only for references)

# This plan will be implemented step-by-step when the user says 'START NOW!'

## Goals
- Build a mobile API using Laravel Sanctum for authentication.
- Provide special "screen-pack" endpoints for the app (home, asset, rental, blog, etc.).
- Provide standard REST-style APIs for all existing tables/models (with GET/POST only).
- Ensure API responses include required media conversions (Spatie Media Library).
- After implementation, write `how-to-use-api.md` with usage for every route.

## Non-Negotiable Requirements (from request)
- All API routes must live under `routes/api/` and be split into context-based files for readability.
- All new API controllers must be under `app/Http/Controllers/Api`.
- API resources must be under `app/Http/Resources/Api` (create this folder).
- Use only GET/POST for API routes; do not use PUT/PATCH/DELETE.
- All list endpoints must use pagination, with per-page and max-per-request limits.
- Spatie media: always return the conversion URL for images (example: `$this->getFirstMediaUrl('thumbnail', 'thumb')`) using the correct collection per model.
- The `<return>`, `<pack>`, `<input>` notes in ideas are minimum requirements, not the full response.
- For any `<relate>` controller, review the web controller(s) and include all related features/endpoints.
- Some detail pages are not listed but still need APIs (with extra props like `for_you`, `related_products`, etc.).
- Use MCP tools for API testing (Playwright), and leverage Laravel Boost for app context when needed. IF playwright fails, use curl `curl -X POST -H "Content-Type: application/json" -d '{"key":"value"}' https://api.example.com/data` like this to test the APIs, test results have to be made into a new 'test_plans_and_results.md' file in a root folder.
- API base prefix is `/api/...`.
- While you are writing API controllers and methods, frequently look into the folder at: (relative path) "API controller coding practices/*.php" or (absolute path) "/home/mlemingcapoo/good-events/API controller coding practices/*.php"
- Use mcp_server_mysql MCP server for ACCESSING the database, this will be useful for testing, you are free to run any database queries on the database sukientot and even write access for further testing.

## Special Screen/Pack Endpoints (from `ideas-file.md`)
Client APIs
- LoginController
  - `POST /api/login/google`
  - `POST /api/login` (return: `role`)
  - `POST /api/forgot`
  - Relate: `App\Http\Controllers\Auth\AuthenticatedSessionController`
- RegisterController
  - `POST /api/register/partner`
  - `POST /api/register`
  - Relate: `App\Http\Controllers\Auth\RegisteredUserController`
- HomeController
  - `GET /api/event/home` (pack: `is_has_new_noti`, `user`, `current_money`, `pending_orders`, `confirmed_orders`, `pending_partners`, `blogs`)
  - Relate: `App\Http\Controllers\Home\HomeController`
- AssetController
  - `GET /api/asset/home` (pack: `asset`, `categories`, `tags`)
  - `GET /api/asset/search` (filters)
  - Relate: `App\Http\Controllers\Home\AssetHomeController`
- RentalController
  - `GET /api/rental/home` (pack: `asset`, `categories`, `tags`)
  - `GET /api/rental/search` (filters)
  - Relate: `App\Http\Controllers\Home\RentHomeController`
- BlogController
  - `GET /api/blog/category` (input: `blog_type`)
  - `GET /api/blog/search` (filters, includes advanced filters for GoodLocationBlogController)
  - Relate: controllers under `app/Http/Controllers/Blog`
  - Note: blog types are handled via `blog_type` param (see Blog model)
- ProfileController
  - Relate: `App\Http\Controllers\Settings\ProfileController@edit`, `App\Http\Controllers\Settings\PasswordController@edit`, and `App\Http\Controllers\Profile/*`
- OrderController
  - Has many endpoints (see `routes/client/order-history.php`)
  - Relate: `App\Http\Controllers\Client\OrderController` and `App\Http\Controllers\Client\QuickBookingController@fillOrderInfo`
- AssetOrderController
  - Relate: `App\Http\Controllers\AssetOrderController`, `routes/client/asset-order-history.php`
- ChatController
  - Relate: `App\Http\Controllers\Client\ChatController`
- ClientToPartnerController
  - Relate: `App\Http\Controllers\Auth\RegisteredUserController@createPartnerFromClient`

Partner APIs
- DashboardController
  - `GET /api/partner/dashboard` (pack: `user`, `is_has_new_noti`, `statistical_data`, `popular_services`)
  - Relate: `app/Filament/Partner/Pages/Dashboard.php`
- ChatController
  - `GET /api/partner/chat`
  - `GET /api/partner/chat/search`
  - Relate: `app/Filament/Partner/Pages/Chat.php`
- PartnerBillController
  - `GET /api/partner/real-time-bill`
  - Relate: `app/Filament/Partner/Pages/RealtimePartnerBill.php`
- All other Filament partner pages under `app/Filament/Partner/Pages/*` (read/write actions)
  - Files: `CalendarPage.php`, `ConfirmedPartnerBill.php`, `PendingPartnerBill.php`, `ProfileSettings.php`, `ViewPartnerBill.php`

## Standard REST APIs (all existing tables/models)
Planned coverage (from `app/Models` and migrations, to be verified):
- Models found: Banner, Blog, BlogCategory, Category, Customer, DesignCategory, Event, EventOrganizationGuide, FailedJob, FileProduct, FileProductBill, GoodLocation, Location, Partner, PartnerBill, PartnerBillDetail, PartnerCategory, PartnerMedia, PartnerProfile, PartnerService, RentalCategory, RentProduct, Report, Statistical, Tag, Taggable, Thread, User, VocationalKnowledge, Voucher.
- Migrations also include: cache, jobs, threads/messages/participants, transactions/transfers/wallets, settings, media, permissions, schedule monitor, pulse, notifications, reviews/ratings, banners, reports, personal_access_tokens, etc.
- Action: confirm which of these are meant to be public APIs vs system/internal tables. If required, create models/resources/controllers for missing tables and expose CRUD in the standard API format.

## Route/File Organization (planned)
- `routes/api/auth.php` (login/register/forgot/logout/token refresh)
- `routes/api/home.php` (event home pack + home-related feeds)
- `routes/api/asset.php` (asset home, asset search, asset details)
- `routes/api/rental.php` (rental home, rental search, rental details)
- `routes/api/blog.php` (blog category, blog search, blog detail, blog type handling)
- `routes/api/profile.php` (profile edit/update/password/change, client-to-partner)
- `routes/api/orders.php` (order history, details, cancel, choose partner, reviews, vouchers)
- `routes/api/asset-orders.php` (asset orders list/detail/repay/download)
- `routes/api/chat.php` (client chat threads/messages)
- `routes/api/notifications.php` (list/read/read-all/delete -> POST-based)
- `routes/api/reports.php` (report user/bill)
- `routes/api/partner/*.php` (dashboard, chat, bill pages, calendar)
- `routes/api/resources/*.php` (standard REST endpoints for each model)
- Keep `routes/api.php` minimal and require the above files.

## Implementation Checklist (step-by-step plan)
1) [x] Discovery and mapping
   - Read all related web controllers and route files referenced above to extract required props and actions.
   - Inventory all models and tables; decide which are public vs internal.
   - Identify Spatie media collections per model and required conversions (thumb, etc.).
   - Identify relationships and filters needed by screen endpoints (home, asset, rental, blog).
   - Notes captured:
     - Special web controllers reviewed: `app/Http/Controllers/Home/HomeController.php`, `app/Http/Controllers/Home/AssetHomeController.php`, `app/Http/Controllers/Home/RentHomeController.php`, `app/Http/Controllers/Blog/*`, `app/Http/Controllers/FileProductController.php`, `app/Http/Controllers/RentController.php`, `app/Http/Controllers/PartnerCategory/PartnerCategoryController.php`, `app/Http/Controllers/Client/*`, `app/Http/Controllers/Settings/*`, `app/Http/Controllers/Profile/*`, `app/Http/Controllers/Auth/*`, `app/Http/Controllers/SocialiteController.php`.
     - Filament partner pages reviewed: `app/Filament/Partner/Pages/Dashboard.php`, `Chat.php`, `CalendarPage.php`, `RealtimePartnerBill.php`, `PendingPartnerBill.php`, `ConfirmedPartnerBill.php`, `ViewPartnerBill.php`, `ProfileSettings.php`.
     - Media collections + conversions (must return conversion URLs in API):
       - Blog: collection `thumbnail`, conversion `thumb`.
       - Category / DesignCategory / RentalCategory: collection `images`, conversion `thumb` (also `mobile_optimized`).
       - FileProduct: collection `thumbnails`, conversion `thumb` (also `mobile_optimized`).
       - RentProduct: collection `thumbnails`, conversion `thumb` (also `mobile_optimized`).
       - Banner: collection `banners`, conversion `thumb`.
       - PartnerCategory: collection `images`, conversion `thumb` (also `mobile_optimized`).
       - PartnerService: collection `service_images`, conversion `thumb`.
       - PartnerBill: collection `arrival_photo` (no conversion defined yet; API should return a conversion, so add one if needed).
     - Existing API route file: `routes/api/location.php` (wards endpoint).
     - Existing API-ish routes: `routes/api/calendar.php` uses `/api/calendar/*` paths.
     - Web routes to mirror for API: `routes/home.php`, `routes/client/*.php`, `routes/client-profile.php`, `routes/partner-profile.php`.
     - Model inventory (app/Models):
       - Banner, Blog, BlogCategory, Category, Customer, DesignCategory, Event, EventOrganizationGuide, FailedJob,
         FileProduct, FileProductBill, GoodLocation, Location, Partner, PartnerBill, PartnerBillDetail, PartnerCategory,
         PartnerMedia, PartnerProfile, PartnerService, RentalCategory, RentProduct, Report, Statistical, Tag, Taggable,
         Thread, User, VocationalKnowledge, Voucher.
     - Assumption for REST scope (unless you say otherwise): expose REST endpoints for the models listed above; skip pure
       system/package tables without local models (cache/jobs/pulse/media/permissions/etc.).

2) [x] API resource layer
   - Create `app/Http/Resources/Api/` and add resources per model.
   - Ensure media URLs always use conversion (thumb) and correct collection.
   - Standardize pagination format and shared meta payloads.

3) [x] Auth via Sanctum
   - Implement token-based login/register (email + password, Google).
   - Add token issuance, revoke/logout, and token guard middleware.
   - Align with existing `AuthenticatedSessionController` and `RegisteredUserController`.

4) [x] Special screen controllers (packs)
   - Implement Home/Asset/Rental/Blog/Profile/Order/Chat packs in Api controllers.
   - Match props described in ideas, plus any required extras from web controllers.
   - Include hidden detail pages (with `related_*`, `for_you`, etc.).

5) [x] Standard REST controllers
   - Build CRUD endpoints (GET/POST only) for each model/table in scope.
   - Enforce pagination on all index endpoints with max per request.
   - Apply validation via FormRequests and policy/role guards where needed.

6) [x] Partner APIs (Filament pages)
   - Mirror data/actions from each partner page (Dashboard, Chat, Bills, Calendar, ProfileSettings).
   - Expose any read/write actions as GET/POST endpoints.

7) [x] Testing (MCP Playwright)
   - Ran GET checks: `/api/health`, `/api/event/home`, `/api/asset/home`, `/api/rental/home`.
   - Login attempt via `/api/login` failed due to missing `personal_access_tokens` table in DB.
   - Results recorded in `test_plans_and_results.md`.

8) [x] Documentation
   - `how-to-use-api.md` written for all API routes.

## Open Questions / Decisions to Confirm
- Which system/internal tables (cache, jobs, media, permissions, pulse, schedule monitor, etc.) should NOT have public APIs?
User answer: i think all the internal tables like that should not be included
- Should any endpoints be admin-only vs client/partner-only (role guarding)?
User answer: yes, there ARE actually role-guarded roles, just like current routes in this project describes in the routes folder.
- Exact pagination defaults and max per-page limit you want (e.g., 15 default, 100 max).
User answer: defaults? maybe 10 per pages and always get all records available if the api caller does not specify how much is max.
- Preferred token expiration/refresh behavior for Sanctum tokens.
User answer: no, just use default options, look into the folder at relative path: "API controller coding practices/AuthController.php" for more info on the prefered practice for generating tokens

(This is now done and reading this is only for references)
