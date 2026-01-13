<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Tapp\FilamentMailLog\Models\MailLog;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:generate-sitemap')->dailyAt('02:00');

Schedule::command('activitylog:clean --force')->daily();

Schedule::call(function () {
    if (class_exists(MailLog::class)) {
        MailLog::where('created_at', '<', now()->subMonths(3))->delete();
    }
})->daily();

Schedule::call(function () {
    $storagePath = config('error-mailer.storage_path');
    $files = File::files($storagePath);

    foreach ($files as $file) {
        if ($file->getMTime() < now()->subMonths(3)->timestamp) {
            File::delete($file->getRealPath());
        }
    }
})->daily();
