<?php

namespace App\Http\Controllers;

use App\Support\BirthdayCalendar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BirthdayController extends Controller
{
    public function index(Request $request): View
    {
        return view('birthdays.index', BirthdayCalendar::search($request));
    }
}
