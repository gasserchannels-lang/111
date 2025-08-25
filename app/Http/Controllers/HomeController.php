<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // ابدأ بكود بسيط جدًا لضمان عمله
        return view('welcome'); 
    }
}
