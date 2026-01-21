# API Usage Guide

Base URL: `/api`

Auth:
- Use `Authorization: Bearer {token}` for authenticated routes.
- All list endpoints support `page` and `per_page` (max 50 unless noted).
- All write endpoints use `POST` only (no PUT/PATCH/DELETE).
- User payloads include `partner_profile` when available.

## Auth
POST `/api/login`
- Body: `email`, `password`
- Response:
  - `token`, `token_type`, `role`
  - `user`: `UserResource` fields (id, name, email, phone, country_code, bio, `avatar`, `email_verified_at`, timestamps, `roles`, optional `partner_profile`)

POST `/api/login/google`
- Body: `access_token` (Google access token)
- Response: same as `/api/login`

POST `/api/forgot`
- Body: `email`
- Response: `{ "success": true/false }`

POST `/api/register`
- Body: `name`, `email`, `phone`, `password`, `password_confirmation`
- Response: same tokens + `role: client`, `user`

POST `/api/register/partner`
- Body: `name`, `email`, `phone`, `password`, `password_confirmation`, `identity_card_number`, `ward_id`
- Response: same tokens + `role: partner`, `user` (eager-loaded `partner_profile`)

POST `/api/logout` (auth)
- Response: `{ "success": true }`

GET `/api/register/partner/from-client` (auth)
- Response: `user`, `is_partner`, `provinces`

POST `/api/register/partner/from-client` (auth)
- Body: `identity_card_number`, `ward_id`
- Response: `{ "success": true, "message": "Partner registration completed." }` (422 when validation fails, 500 on server errors)

## Home (Event)
GET `/api/event/home`
- Response includes:
  - `event_categories`: array of top-level partner categories (id, name, slug, description, min/max price, `image`)
  - `partner_categories`: map by category id with child listings (same shape as above)
  - `pagination`: `{ total, initial_limit, batch_size, child_limit }`
  - `blogs`: `BlogResource` outputs (id, title, slug, type, location, author, thumbnail)
  - `settings`: hero/app name/banner arrays
  - `user`: `UserResource` or `null`, plus `is_has_new_noti`, `current_money`, `pending_orders`, `confirmed_orders`, `pending_partners`

GET `/api/event/home/categories`
- Query: `offset`, `limit`
- Response: same `event_categories`/`partner_categories` shape plus `has_more`

GET `/api/event/home/children`
- Query: `category_slug`, `offset`, `limit`
- Response: `children`, `has_more`, `total`

GET `/api/event/home/search`
- Query: `q`
- Response: `event_categories`, `partner_categories`, `has_more`, `filters`

## Asset
GET `/api/asset/home`
- Query: `page`, `per_page`
- Response:
  - `file_products`: paginated `FileProductResource` (id, name, slug, price, category, tags, image)
  - `tags`: list from `TagResource`
  - `categories`: `CategoryResource` outputs for design categories used in filters
  - `settings`: hero/app info + banner images

GET `/api/asset/search`
- Query: `q`, `tags[]`, `tag` (fallback), `category_slug`, `page`, `per_page`
- Response:
  - `file_products`: paginated results (same fields)
  - `categories`, `tags`
  - `category`: matched category info or `null`
  - `child_categories`: list for the selected parent
  - `filters`: `{ q, tags, category_slug }`

GET `/api/asset/detail/{categorySlug}/{fileProductSlug}`
- Response:
  - `file_product`: extended `FileProductResource` with `long_description`, `highlights`, `preview_images`, `included_files`, `download_count`, `updated_human`, and `tags`
  - `related`: other `FileProductResource` items in same category
  - `is_purchased`: `boolean`
  - `download_zip_url`: URL to asset zip when purchased

GET `/api/asset/purchase/{slug}` (auth)
- Response:
  - `file_product`: `FileProductResource`
  - `buyer`: defaults populated from the logged-in user (`name`, `email`, `phone`, `company`, `tax_code`, `note`, `payment_method`)
  - `payment_methods`: list of `{ value, label }`
  - `totals`: `{ subtotal, discount, vat, total }`

POST `/api/asset/purchase` (auth)
- Body: `slug`, `name`, `email`, `phone`, `company`, `tax_code`, `note`, `payment_method`
- Response (on success): `{ "checkout_url": string, "bill_id": int }`
- Error: 500 with `{ "message": "Unable to start payment." }`
- Response: `checkout_url`, `bill_id`

## Rental
GET `/api/rental/home`
- Query: `page`, `per_page`
- Response:
  - `rent_products`: paginated `RentProductResource` (id, name, slug, price, category, tags, image)
  - `tags`, `categories`
  - `settings`: hero title, banner images

GET `/api/rental/search`
- Query: `q`, `tags[]`, `tag`, `category_slug`, `page`, `per_page`
- Response: same shape as rental home plus:
  - `category`: selected, `child_categories`
  - `filters`: `{ q, tags, category_slug }`

GET `/api/rental/detail/{categorySlug}/{rentProductSlug}`
- Response:
  - `rent_product`: extended `RentProductResource` with `long_description`, `highlights`, `preview_images`, `tags`, `updated_human`
  - `related`: additional `RentProductResource` items
  - `contact_hotline`: support phone number

## Blog
Blog types: `good_location`, `event_organization_guide`, `vocational_knowledge`

GET `/api/blog/category`
- Query: `blog_type`
- Response: `categories` (`id`, `name`, `slug`, `description`)

GET `/api/blog/search`
- Query: `blog_type`, `q`, `category_slug`, `page`
- Extra filters for `good_location`: `province_id`, `district_id`, `max_people`, `location_detail`
- Response:
  - `blogs`: paginated `BlogResource` (id, title, slug, type, location, category, author, thumbnail)
  - `categories`, `category`
  - `filters`: includes provided filters; `locations.provinces` for `good_location`

GET `/api/blog/detail`
- Response: `blog` (detail resource) and `related`
- Query: `blog_type`, `category_slug`, `blog_slug`

## Profile (Client/General)
GET `/api/profile/me` (auth)
- Response: `{ user: UserResource, must_verify_email: bool }`

POST `/api/profile/update` (auth, multipart/form-data)
- Body: `name`, `email`, `country_code`, `phone`, `bio`, `avatar` (file)
- Response: `{ success: true, user: UserResource }`

POST `/api/profile/password` (auth)
- Body: `current_password`, `password`, `password_confirmation`
- Response: `{ success: true }`

GET `/api/profile/{user}`
- Response:
  - `profile_type`: `client` or `partner`
  - `payload`: differs per role
    - `client`: `user` (id, name, avatar_url, email, phone, partner_profile_name, bio, email_verified_at, is_verified), `stats` (order counts, total spent, avg rating, cancellation pct), `recent_bills`, `recent_reviews`, `intro`
    - `partner`: `user` (id, name, avatar_url, joined_year, bio, email status, `is_legit`), `quick`, `contact`, `services`, `reviews`, `intro`

## Orders (Client)
GET `/api/orders` (auth)
- Query: `page`, `per_page`
- Response: `orders` (paginated `PartnerBillResource`: id, code, status, totals, event info, partner info, category, `details`)

GET `/api/orders/history` (auth)
- Query: `page`, `per_page`
- Response: `orders` (paginated `PartnerBillHistoryResource`, similar fields for completed/cancelled/expired bills)

GET `/api/orders/{order}` (auth)
- Response: `{ order: PartnerBillResource | null }`

GET `/api/orders/{order}/details` (auth)
- Response: `{ bill_id: int, items: PartnerBillDetailResource[], version: timestamp }`

GET `/api/orders/partner-profile/{user}` (auth)
- Response: `{ user: UserResource, partner_profile: PartnerProfileResource, services: PartnerServiceResource[] }`

POST `/api/orders/cancel` (auth)
- Body: `order_id`
- Response: `{ success: true }` or 422/422 message if cancellation rules fail

POST `/api/orders/choose-partner` (auth)
- Body: `order_id`, `partner_id`, `voucher_code` (optional)
- Response: `{ success: true }` or error message

POST `/api/orders/submit-review` (auth)
- Body: `partner_id`, `order_id`, `rating`, `comment` (optional)
- Response: `{ success: true }`

POST `/api/orders/validate-voucher` (auth)
- Body: `voucher_input`, `order_id`
- Response: `{ status: bool, message: string, details: { code, discount_percent, max_discount_amount, min_order_amount, usage_limit, times_used, is_unlimited, starts_at, expires_at } }`

POST `/api/orders/voucher-discount` (auth)
- Body: `voucher_input`, `order_id`, `partner_id`
- Response: `{ status: bool, message: string, discount: float }`

## Quick Booking (Client)
GET `/api/quick-booking/categories` (auth)
- Response: `partner_categories` array (id, name, slug, min/max price, description, `media` URL)

GET `/api/quick-booking/{partnerCategorySlug}/children` (auth)
- Response: `partner_category` summary + `partner_children_list` (same structure)

GET `/api/quick-booking/{partnerCategorySlug}/{partnerChildCategorySlug}/form` (auth)
- Response:
  - `partner_category`, `partner_children_category`
  - `event_list`: `EventResource`
  - `provinces`: `LocationResource` collection

POST `/api/quick-booking/submit` (auth)
- Body: `order_date`, `start_time`, `end_time`, `province_id`, `ward_id`, `event_id`, `custom_event`, `location_detail`, `note`, `category_id`
- Response: `{ success: true, bill: PartnerBillResource }`

GET `/api/quick-booking/finish/{billCode}` (auth)
- Response: `{ partner_bill: PartnerBillResource, category_name: string }`

## Asset Orders (Client)
GET `/api/asset-orders` (auth)
- Query: `page`, `per_page`
- Response: `orders` paginated `FileProductBillResource` (bill meta, file_product info, totals, status)

GET `/api/asset-orders/{bill}` (auth)
- Response: `{ order: FileProductBillResource }`

POST `/api/asset-orders/{bill}/repay` (auth)
- Response: `{ checkout_url: string }` or 422/500 with `message`

GET `/api/asset-orders/{bill}/download` (auth)
- Streams a ZIP if paid and within rate limits.
- Error responses send `{ message, available_in? }` when blocked

## Chat (Client)
GET `/api/chat` (auth)
- Query: `search`
- Response: `{ threads: [{ id, subject, updated_at, is_unread, other_participants, participants, latest_message, bill }], has_more }`

GET `/api/chat/threads` (auth)
- Query: `search`, `page`
- Response: same pagination + `hasMore`

GET `/api/chat/threads/{thread}/messages` (auth)
- Query: `page`
- Response: `{ data: [{ id, thread_id, user_id, body, created_at, updated_at, user }], hasMore, thread }`

POST `/api/chat/threads/{thread}/messages` (auth)
- Body: `body`
- Response: `{ success: true, message: {...} }` or `{ success: false, message: 'Failed to send message.' }`

## Notifications (Client)
GET `/api/notifications` (auth)
- Query: `per_page`, `unread`
- Response: paginated `NotificationResource` entries (id, title, message, unread, created_at, href, payload) with `meta.unread_count`

POST `/api/notifications/{id}/read` (auth)
- Response: `{ success: true, data: NotificationResource }`

POST `/api/notifications/read-all` (auth)
- Response: `{ success: true }`

POST `/api/notifications/{id}/delete` (auth)
- Response: `{ success: true }`

## Reports (Client)
POST `/api/report/user` (auth)
- Body: `reported_user_id`, `title`, `description`
- Response: `{ success: true }` or `422`/message if duplicate report

POST `/api/report/bill` (auth)
- Body: `reported_bill_id`, `title`, `description`
- Response: `{ success: true }` or `422`/message when already reported

## Partner APIs
GET `/api/partner/dashboard` (auth, partner)
- Response:
  - `user`: `UserResource`
  - `is_has_new_noti`: boolean
  - `statistical_data`: map of metrics (`number_customer`, `satisfaction_rate`, `revenue_generated`, etc.)
  - `popular_services`: list of categories with `order_count`, `total_revenue`, `latest_order`

GET `/api/partner/chat` (auth, partner)
- Query: `search`
- Response: `{ threads[], has_more }`

GET `/api/partner/chat/search` (auth, partner)
- Query: `q` or `search`, `page`
- Response: `{ data, hasMore }`

GET `/api/partner/chat/threads/{thread}/messages` (auth, partner)
- Query: `page`
- Response: `{ data: messages[], hasMore, thread }`

POST `/api/partner/chat/threads/{thread}/messages` (auth, partner)
- Body: `body`
- Response: `{ success: true, message }` or `{ success: false, message }`

GET `/api/partner/bills/realtime` (auth, partner)
- Query: `search`, `date_filter`, `category_id`
- Response:
  - `partner_bills`: pending list (id, code, address, phone, date, times, status, client, event, category)
  - `available_categories`: `{ id, name }[]`
  - `last_updated`: timestamp

POST `/api/partner/bills/{bill}/accept` (auth, partner)
- Body: `price`
- Response: `{ success: true }` or `{ message: string }`

GET `/api/partner/bills/pending` (auth, partner)
- Query: `search`, `date_filter`, `sort`, `page`, `per_page`
- Response: `bills` paginated `PartnerBillResource`

GET `/api/partner/bills/confirmed` (auth, partner)
- Query: `search`, `date_filter`, `sort`, `page`, `per_page`
- Response: `bills` paginated `PartnerBillResource`

GET `/api/partner/bills/{bill}` (auth, partner)
- Response: `{ bill: PartnerBillResource }`

POST `/api/partner/bills/{bill}/mark-in-job` (auth, partner, multipart/form-data)
- Body: `arrival_photo`
- Response: `{ success: true }`

POST `/api/partner/bills/{bill}/complete` (auth, partner)
- Response: `{ success: true }`

GET `/api/partner/calendar/events` (auth, partner)
- Response: array of events (id, title, start, end, colors, `extendedProps` with code, client, category, address, phone, total, status, raw_date, raw_start_time, raw_end_time)

GET `/api/partner/calendar/locale` (auth, partner)
- Response: `{ locale, translations }`

GET `/api/partner/profile` (auth, partner)
- Response: `{ user: UserResource, city_name, province_name }`

POST `/api/partner/profile` (auth, partner, multipart/form-data)
- Body: `name`, `email`, `country_code`, `phone`, `bio`, `partner_name`, `identity_card_number`, `city_id`, `location_id`, `avatar`, `selfie_image`, `front_identity_card_image`, `back_identity_card_image`
- Response: `{ success: true, user: UserResource, partner_profile: PartnerProfileResource }`
## Partner Categories (Detail Page)
GET `/api/partner-categories/{slug}`

## Locations
GET `/api/locations/{location}/wards`
- Response: list of wards `[{ id, name, code, codename, short_codename, type, phone_code, parent_id }]`
GET `/api/locations` (auth, same as `/api/resources/locations`)
GET `/api/locations/{id}` (auth)
POST `/api/locations` (auth, create)
POST `/api/locations/{id}` (auth, update)
POST `/api/locations/{id}/delete` (auth, delete)

## Standard REST Resources
Base: `/api/resources/{resource}`

Routes (auth required):
- GET `/api/resources/{resource}`
- GET `/api/resources/{resource}/{id}`
- POST `/api/resources/{resource}` (create)
- POST `/api/resources/{resource}/{id}` (update)
- POST `/api/resources/{resource}/{id}/delete` (delete)

- Response shapes:
  - `GET /api/resources/{resource}`: `{ "items": [Resource], "meta": { current_page, per_page, total, last_page } }`
  - `GET /api/resources/{resource}/{id}`: `{ "item": Resource }`
  - POST siblings return `{ "item": Resource }` on success; delete returns `{ "success": true }`

Resources:
- `banners`
- `blogs`
- `blog-categories`
- `categories`
- `customers`
- `design-categories`
- `events`
- `event-organization-guides`
- `failed-jobs`
- `file-products`
- `file-product-bills`
- `good-locations`
- `locations`
- `partners`
- `partner-bills`
- `partner-bill-details`
- `partner-categories`
- `partner-media`
- `partner-profiles`
- `partner-services`
- `rental-categories`
- `rent-products`
- `reports`
- `statistics`
- `tags`
- `taggables`
- `threads`
- `users`
- `vocational-knowledges`
- `vouchers`

Notes:
- Image fields always return media conversion URLs (thumbs) per Spatie collections.
- Some create/update routes only accept attributes listed in each model's `$fillable`.
