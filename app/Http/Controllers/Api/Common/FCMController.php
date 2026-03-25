<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 *  Update user FCM token
 *s
 * Response: { profile: ProfileResource|null }
 *
 * @param Request $request
 * @return JsonResponse
 */
class FCMController extends Controller
{
    public function updateToken(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user->fcm_token = $request->input('fcm_token');
        $user->save();

        return response()->json(['message' => 'FCM token updated successfully.'], 200);
    }
}
