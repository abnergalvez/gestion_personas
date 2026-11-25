<?php

namespace App\Providers;

use App\Mail\OAuthSmtpTransport;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(App::getLocale());
        Paginator::defaultView('pagination.default');

        Event::listen(Login::class, [ActivityLogger::class, 'login']);
        Event::listen(Logout::class, [ActivityLogger::class, 'logout']);
        Event::listen(Failed::class, [ActivityLogger::class, 'failed']);

        Mail::extend('gmail-oauth', fn (array $config) => OAuthSmtpTransport::gmail($config));
        Mail::extend('outlook-oauth', fn (array $config) => OAuthSmtpTransport::outlook($config));
    }
}
