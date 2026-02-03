<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Display the Contact page.
     */
    public function index(): Response
    {
        return Inertia::render('contact/Contact');
    }
}
