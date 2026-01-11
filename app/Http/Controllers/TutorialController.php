<?php

namespace App\Http\Controllers;

use App\Enum\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TutorialController extends Controller
{
    /**
     * Display the Tutorial page.
     */
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->hasRole(Role::PARTNER) && $user->partnerProfile) {
            return redirect()->route('tutorial.partner');
        }

        return redirect()->route('tutorial.client');
    }

    /**
     * Display the Client Tutorial page.
     */
    public function client(): Response
    {
        return Inertia::render('tutorial/Client');
    }

    /**
     * Display the Partner Tutorial page.
     */
    public function partner(): Response
    {
        return Inertia::render('tutorial/Partner');
    }
}
