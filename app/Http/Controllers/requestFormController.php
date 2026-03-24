<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class requestFormController extends Controller
{
    function requestForm()
    {
        return view('letter_of_request/requestform');
    }
}
