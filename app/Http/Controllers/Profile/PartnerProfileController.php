<?php

namespace App\Http\Controllers\Profile;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PartnerProfilePayload;
use Inertia\Inertia;

class PartnerProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadMissing('partnerProfile');

        if (! $user->partnerProfile || ! $user->hasRole(Role::PARTNER)) {
            return to_route('profile.client.show', ['user' => $user->id]);
        }

        $payload = PartnerProfilePayload::for($user);
        return Inertia::render('profile/partner/Partner', $payload);
    }

    public function showJson(User $user)
    {
        $user->loadMissing('partnerProfile');

        if (! $user->partnerProfile || ! $user->hasRole(Role::PARTNER)) {
            abort(404);
        }

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
