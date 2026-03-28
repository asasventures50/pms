<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Public marketing landing (Blade). JSON APIs can live under routes/api.php later.
     */
    public function __invoke(): View
    {
        return view('landing');
    }
}
