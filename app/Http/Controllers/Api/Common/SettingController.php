<?php

namespace App\Http\Controllers\Api\Common;

use App\Settings\AppSettings;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get application settings for the client app.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings(Request $request)
    {
        $appSettings = app(AppSettings::class);

        $settings = [
            'hotline' => $appSettings->contact_hotline,
            'zalo' => $appSettings->social_zalo,
        ];

        return response()->json($settings);
    }
}
