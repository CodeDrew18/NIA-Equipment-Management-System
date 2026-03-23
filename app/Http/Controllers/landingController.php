<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class landingController extends Controller
{
    function landingPage()
    {
        return view('landing');
    }
}
