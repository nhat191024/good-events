<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PartnerProfilePayload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PartnerProfileController extends Controller
{
    public function show(User $user)
    {
        $payload = PartnerProfilePayload::for($user);
        return Inertia::render('profile/partner/Partner', $payload);
    }

    public function showJson(User $user)
    {
        return response()->json(PartnerProfilePayload::for($user));
    }

    private function calcCancelPct(User $user): string
    {
        $total = $user->partnerBillsAsPartner()->count();
        if ($total === 0) return '0%';
        $completed = $user->partnerBillsAsPartner()->where('status','paid')->count();
        $cancelled = $total - $completed;
        return round($cancelled * 100 / $total) . '%';
    }
}
