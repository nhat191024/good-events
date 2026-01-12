<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Settings\AppSettings;

class ContactController extends Controller
{
    /**
     * Display the Contact page.
     */
    public function index(): Response
    {
        $appSettings = app(AppSettings::class);

        return Inertia::render('contact/Contact', [
            'app_settings' => [
                'contact_hotline' => $appSettings->contact_hotline,
                'contact_email' => $appSettings->contact_email,
                'social_facebook' => $appSettings->social_facebook,
                'social_facebook_group' => $appSettings->social_facebook_group,
                'social_zalo' => $appSettings->social_zalo,
                'social_youtube' => $appSettings->social_youtube,
                'social_tiktok' => $appSettings->social_tiktok,
            ],
        ]);
    }
}
