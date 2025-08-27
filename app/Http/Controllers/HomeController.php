<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // ابدأ بكود بسيط جدًا لضمان عمله
        return view('welcome');
    }
}
