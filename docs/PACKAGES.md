# Tổng hợp các package dự án

Tệp này tóm tắt các package PHP (composer) đang được khai báo trong dự án và đã được sắp xếp, phân loại để dễ tham khảo.

## Tổng quan nhanh

- File nguồn: `composer.json`
- PHP yêu cầu: ^8.2
- Tên dự án: `laravel/vue-starter-kit`
- Loại: `project`
- License: `MIT`

## Cách đọc

- Packages được nhóm theo chức năng (Framework, Filament, Spatie, Tools, ...).
- Phiên bản được giữ nguyên theo `composer.json`.
- Các package `require-dev` là chỉ cho development/testing.

---

## 1) Core & Framework

- php: ^8.2 — Yêu cầu phiên bản PHP
- laravel/framework: ^12.0 — Laravel framework

## 2) Laravel first-party / common helpers

- laravel/tinker: ^2.10.1 — REPL hữu ích cho Laravel
- tightenco/ziggy: ^2.4 — Ziggy: client-side access to Laravel routes

## 3) Filament & related plugins

- filament/filament: ~4.0 — Admin panel / UI toolkit
- leandrocfe/filament-apex-charts: ^4.0@beta — Apex charts cho Filament
- malzariey/filament-daterangepicker-filter: ^4.0 — Date range filter cho Filament
- filament/spatie-laravel-media-library-plugin: ^4.0 — Filament plugin cho medialibrary
- filament/spatie-laravel-settings-plugin: ^4.0 — Filament plugin cho settings
- filament/spatie-laravel-tags-plugin: ^4.0 — Filament plugin cho tags

## 4) Spatie ecosystem

- spatie/laravel-medialibrary: ^11.14 — Media library
- spatie/image-optimizer: ^1.8 — Image optimisation
- spatie/laravel-permission: ^6.21 — Role & permission management
- spatie/laravel-settings: ^3.4 — Settings management
- spatie/laravel-sluggable: ^3.7 — Auto slugs
- spatie/laravel-tags: ^4.10 — Tagging system
- spatie/laravel-searchable: ^1.13 — Searchable utilities
- spatie/laravel-schedule-monitor: ^3.10 — Monitor scheduled tasks
- spatie/laravel-uptime-monitor: ^4.5 — Uptime monitoring

## 5) Messaging, Vouchers & Wallet

- cmgmyr/messenger: ^2.31 — Private messaging package
- beyondcode/laravel-vouchers: \* — Vouchers management
- bavix/laravel-wallet: ^11.4 — Wallet handling for Laravel

## 6) Caching, queue, logs, utils

- predis/predis: ^3.2 — Redis client (predis)
- opcodesio/log-viewer: ^3.19 — Log viewer

---

## Dev packages (require-dev)

Các package chỉ dùng trong môi trường development / testing:

- barryvdh/laravel-debugbar: ^3.16 — Debugbar cho Laravel
- barryvdh/laravel-ide-helper: ^3.6 — IDE helper (hints cho IDE)
- beyondcode/laravel-query-detector: ^2.1 — Detect slow/duplicate queries
- fakerphp/faker: ^1.23 — Fake data generator
- laradumps/laradumps: ^4.5 — Debug dumps
- laravel/pail: ^1.2.2 — (Pail) logging / CLI helper
- laravel/pint: ^1.18 — PHP code style fixer
- laravel/sail: ^1.41 — Local Docker dev environment helper
- mockery/mockery: ^1.6 — Mocking library cho tests
- nunomaduro/collision: ^8.6 — Pretty error reporting cho CLI
- pestphp/pest: ^4.0 — Testing framework
- pestphp/pest-plugin-laravel: ^4.0 — Pest plugin cho Laravel

---

## Cấu hình đáng chú ý

- `minimum-stability`: `dev` — Cho phép package ở trạng thái beta/dev
- `prefer-stable`: `true`
- `optimize-autoloader`: `true`

## Lệnh composer hữu ích

```powershell
# Cài dependency
composer install

# Cập nhật dependency
composer update

# Xem package đã cài (phiên bản hiện tại)
composer show -i

# Kiểm tra package lỗi thời
composer outdated

# Tìm lý do package được cài
composer why <package>
```

## Gợi ý & lưu ý

- Vì `minimum-stability` là `dev`, chú ý khi chạy `composer update` để tránh nâng lên các bản không ổn định ngoài ý muốn.
- Để biết chính xác phiên bản đang cài, dùng `composer show -i`.

---

Trạng thái:

- File đã được sắp xếp, nhóm và lưu: Done
