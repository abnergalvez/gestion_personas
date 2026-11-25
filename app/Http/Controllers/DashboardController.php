<?php

namespace App\Http\Controllers;

use App\Support\BirthdayCalendar;
use App\Support\DashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardStats $stats) {}

    public function index(): View
    {
        $birthdayWeek = BirthdayCalendar::current();

        return view('dashboard.index', [
            'cards' => $this->stats->cards(),
            'charts' => $this->stats->charts(),
            'expiringContracts' => $this->stats->expiringContracts(),
            'activeLeaves' => $this->stats->activeLeaves(),
            'birthdayWeek' => $birthdayWeek,
            'birthdays' => $birthdayWeek->entries(),
        ]);
    }
}
