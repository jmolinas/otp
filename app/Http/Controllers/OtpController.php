<?php

namespace App\Http\Controllers;

class OtpController extends Controller
{
    public function index()
    {
        return view('auth.otp');
    }
}
