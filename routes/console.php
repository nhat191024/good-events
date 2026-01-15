<?php

use Tapp\FilamentMailLog\Models\MailLog;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Foundation\Inspiring;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('activitylog:clean --force')->daily();

Schedule::call(function () {
    if (class_exists(MailLog::class)) {
        MailLog::where('created_at', '<', now()->subDays(7))->delete();
    }

    if (class_exists(Activity::class)) {
        Activity::where('created_at', '<', now()->subDays(7))->delete();
    }
})->daily();

Schedule::call(function () {
    $storagePath = config('error-mailer.storage_path');
    $files = File::files($storagePath);

    foreach ($files as $file) {
        if ($file->getMTime() < now()->subDays(30)->timestamp) {
            File::delete($file->getRealPath());
        }
    }
})->daily();
