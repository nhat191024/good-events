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
- Response: `token`, `token_type`, `role`, `user`

POST `/api/login/google`
- Body: `access_token` (Google access token)
- Response: `token`, `token_type`, `role`, `user`

POST `/api/forgot`
- Body: `email`

POST `/api/register`
- Body: `name`, `email`, `phone`, `password`, `password_confirmation`

POST `/api/register/partner`
- Body: `name`, `email`, `phone`, `password`, `password_confirmation`, `identity_card_number`, `ward_id`

POST `/api/logout` (auth)

GET `/api/register/partner/from-client` (auth)
- Response: `user`, `is_partner`, `provinces`

POST `/api/register/partner/from-client` (auth)
- Body: `identity_card_number`, `ward_id`

## Home (Event)
GET `/api/event/home`
- Response includes: `is_has_new_noti`, `user`, `current_money`, `pending_orders`, `confirmed_orders`, `pending_partners`, `blogs`

GET `/api/event/home/categories`
- Query: `offset`, `limit`

GET `/api/event/home/children`
- Query: `category_slug`, `offset`, `limit`

GET `/api/event/home/search`
- Query: `q`

## Asset
GET `/api/asset/home`
- Query: `page`, `per_page`

GET `/api/asset/search`
- Query: `q`, `tags[]`, `tag` (fallback), `category_slug`, `page`, `per_page`

GET `/api/asset/detail/{categorySlug}/{fileProductSlug}`

GET `/api/asset/purchase/{slug}` (auth)

POST `/api/asset/purchase` (auth)
- Body: `slug`, `name`, `email`, `phone`, `company`, `tax_code`, `note`, `payment_method`
- Response: `checkout_url`, `bill_id`

## Rental
GET `/api/rental/home`
- Query: `page`, `per_page`

GET `/api/rental/search`
- Query: `q`, `tags[]`, `tag`, `category_slug`, `page`, `per_page`

GET `/api/rental/detail/{categorySlug}/{rentProductSlug}`

## Blog
Blog types: `good_location`, `event_organization_guide`, `vocational_knowledge`

GET `/api/blog/category`
- Query: `blog_type`

GET `/api/blog/search`
- Query: `blog_type`, `q`, `category_slug`, `page`
- Extra filters for `good_location`: `province_id`, `district_id`, `max_people`, `location_detail`

GET `/api/blog/detail`
- Query: `blog_type`, `category_slug`, `blog_slug`

## Profile (Client/General)
GET `/api/profile/me` (auth)

POST `/api/profile/update` (auth, multipart/form-data)
- Body: `name`, `email`, `country_code`, `phone`, `bio`, `avatar` (file)

POST `/api/profile/password` (auth)
- Body: `current_password`, `password`, `password_confirmation`

GET `/api/profile/{user}`

## Orders (Client)
GET `/api/orders` (auth)
- Query: `page`, `per_page`

GET `/api/orders/history` (auth)
- Query: `page`, `per_page`

GET `/api/orders/{order}` (auth)

GET `/api/orders/{order}/details` (auth)

GET `/api/orders/partner-profile/{user}` (auth)

POST `/api/orders/cancel` (auth)
- Body: `order_id`

POST `/api/orders/choose-partner` (auth)
- Body: `order_id`, `partner_id`, `voucher_code` (optional)

POST `/api/orders/submit-review` (auth)
- Body: `partner_id`, `order_id`, `rating`, `comment` (optional)

POST `/api/orders/validate-voucher` (auth)
- Body: `voucher_input`, `order_id`

POST `/api/orders/voucher-discount` (auth)
- Body: `voucher_input`, `order_id`, `partner_id`

## Quick Booking (Client)
GET `/api/quick-booking/categories` (auth)

GET `/api/quick-booking/{partnerCategorySlug}/children` (auth)

GET `/api/quick-booking/{partnerCategorySlug}/{partnerChildCategorySlug}/form` (auth)

POST `/api/quick-booking/submit` (auth)
- Body: `order_date`, `start_time`, `end_time`, `province_id`, `ward_id`, `event_id`, `custom_event`, `location_detail`, `note`, `category_id`

GET `/api/quick-booking/finish/{billCode}` (auth)

## Asset Orders (Client)
GET `/api/asset-orders` (auth)
- Query: `page`, `per_page`

GET `/api/asset-orders/{bill}` (auth)

POST `/api/asset-orders/{bill}/repay` (auth)

GET `/api/asset-orders/{bill}/download` (auth)
- Streams a ZIP if paid and within rate limits.

## Chat (Client)
GET `/api/chat` (auth)
- Query: `search`

GET `/api/chat/threads` (auth)
- Query: `search`, `page`

GET `/api/chat/threads/{thread}/messages` (auth)
- Query: `page`

POST `/api/chat/threads/{thread}/messages` (auth)
- Body: `body`

## Notifications (Client)
GET `/api/notifications` (auth)
- Query: `per_page`, `unread`

POST `/api/notifications/{id}/read` (auth)

POST `/api/notifications/read-all` (auth)

POST `/api/notifications/{id}/delete` (auth)

## Reports (Client)
POST `/api/report/user` (auth)
- Body: `reported_user_id`, `title`, `description`

POST `/api/report/bill` (auth)
- Body: `reported_bill_id`, `title`, `description`

## Partner APIs
GET `/api/partner/dashboard` (auth, partner)

GET `/api/partner/chat` (auth, partner)
- Query: `search`

GET `/api/partner/chat/search` (auth, partner)
- Query: `q` or `search`, `page`

GET `/api/partner/chat/threads/{thread}/messages` (auth, partner)
- Query: `page`

POST `/api/partner/chat/threads/{thread}/messages` (auth, partner)
- Body: `body`

GET `/api/partner/bills/realtime` (auth, partner)
- Query: `search`, `date_filter`, `category_id`

POST `/api/partner/bills/{bill}/accept` (auth, partner)
- Body: `price`

GET `/api/partner/bills/pending` (auth, partner)
- Query: `search`, `date_filter`, `sort`, `page`, `per_page`

GET `/api/partner/bills/confirmed` (auth, partner)
- Query: `search`, `date_filter`, `sort`, `page`, `per_page`

GET `/api/partner/bills/{bill}` (auth, partner)

POST `/api/partner/bills/{bill}/mark-in-job` (auth, partner, multipart/form-data)
- Body: `arrival_photo` (file)

POST `/api/partner/bills/{bill}/complete` (auth, partner)

GET `/api/partner/calendar/events` (auth, partner)

GET `/api/partner/calendar/locale` (auth, partner)

GET `/api/partner/profile` (auth, partner)
- Response: `city_name`, `province_name`, `wards`

POST `/api/partner/profile` (auth, partner, multipart/form-data)
- Body: `name`, `email`, `country_code`, `phone`, `bio`, `partner_name`, `identity_card_number`, `city_id`, `location_id`,
  `avatar` (file), `selfie_image` (file), `front_identity_card_image` (file), `back_identity_card_image` (file)

## Partner Categories (Detail Page)
GET `/api/partner-categories/{slug}`

## Locations
GET `/api/locations/{location}/wards`
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
