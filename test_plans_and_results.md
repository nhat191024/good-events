# API Test Plans and Results

## Environment
- Local server: php artisan serve --host=0.0.0.0 --port=8002 --no-reload
- Base URL: http://127.0.0.1:8002
- Playwright MCP used

## Test User (for auth)
- email: api-test@example.com
- password: Password123!
- role: client (spatie role, if created)
- Note: created via tinker for testing

## Tests
1) GET /api/health
   - Expected: 200 with status ok
   - Result: 200, ok

2) GET /api/event/home
   - Expected: pack with event_categories, partner_categories, blogs, settings
   - Result: 200, returned pack; user null (unauth), blogs present.

3) GET /api/asset/home
   - Expected: file_products, categories, tags, settings; images use conversion URLs
   - Result: 200, conversion image URLs returned.

4) GET /api/rental/home
   - Expected: rent_products (paginated), categories, tags, settings
   - Result: 200, empty data but response shape ok.

5) POST /api/login
   - Expected: 200 with token/user/role
   - Result: 500 - SQLSTATE[42S02]: Table 'personal_access_tokens' doesn't exist.
   - Action: run Sanctum migration (ensure personal_access_tokens table) before retesting.

## Re-run After Migration
6) POST /api/login
   - Expected: 200 with token/user/role
   - Result: 200, token returned, role null (no roles assigned), user returned.

7) GET /api/profile/me (auth)
   - Expected: 200 with user payload and must_verify_email flag
   - Result: 200, user returned, must_verify_email true.

## Notes / Issues
- Initial run failed due to missing Sanctum table; resolved after migration.
- App assets requested by home page reference https://sukientot.test (APP_URL); local server on 127.0.0.1 triggers CORS and asset failures, but does not block API responses.
