<?php

namespace App\Providers;

use App\Mail\OAuthSmtpTransport;
use App\Models\Evaluation;
use App\Models\MedicalLeave;
use App\Models\Permit;
use App\Support\ActivityLogger;
use App\Support\PersonnelNotifier;
use App\Support\Settings;
use App\Support\SystemErrorRecorder;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
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
        Settings::applyMail();

        MedicalLeave::created(fn (MedicalLeave $leave) => PersonnelNotifier::medicalLeave($leave));
        Permit::created(fn (Permit $permit) => PersonnelNotifier::permit($permit));
        Evaluation::created(fn (Evaluation $evaluation) => PersonnelNotifier::evaluation($evaluation));

        Event::listen(Login::class, [ActivityLogger::class, 'login']);
        Event::listen(Logout::class, [ActivityLogger::class, 'logout']);
        Event::listen(Failed::class, [ActivityLogger::class, 'failed']);

        Mail::extend('gmail-oauth', fn (array $config) => OAuthSmtpTransport::gmail($config));
        Mail::extend('outlook-oauth', fn (array $config) => OAuthSmtpTransport::outlook($config));

        View::composer('layouts.app', function ($view): void {
            $alert = ['error' => null, 'count' => 0];

            if (auth()->user()?->isSuperAdmin()) {
                $alert = SystemErrorRecorder::alert();
            }

            $view->with('systemAlert', $alert['error'])->with('systemAlertCount', $alert['count']);
        });
    }
}
