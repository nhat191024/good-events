<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class TutorialController extends Controller
{
    /**
     * Display the Tutorial page.
     */
    public function index(): Response
    {
        return Inertia::render('tutorial/Index');
    }

    /**
     * Display the Other Tutorial page.
     */
    public function other(): Response
    {
        return Inertia::render('tutorial/Other');
    }
}
