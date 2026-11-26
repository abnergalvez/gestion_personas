<?php

namespace App\Http\Controllers;

use App\Enums\MailProvider;
use App\Enums\OutlookGrant;
use App\Http\Requests\SendTestMailRequest;
use App\Http\Requests\SettingRequest;
use App\Support\MailNotice;
use App\Support\PersonnelNotifier;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'mailer' => (string) Settings::get('mail.mailer', config('mail.default', MailProvider::Log->value)),
            'mailers' => MailProvider::options(),
            'grants' => OutlookGrant::options(),
            'fromName' => (string) Settings::get('mail.from_name', config('mail.from.name')),
            'fromAddress' => (string) Settings::get('mail.from_address', config('mail.from.address')),
            'brevoUsername' => (string) Settings::get('mail.brevo.username', config('mail.mailers.brevo.username')),
            'gmailUsername' => (string) Settings::get('mail.gmail.username', config('mail.mailers.gmail.username')),
            'gmailClientId' => (string) Settings::get('mail.gmail.client_id', config('mail.mailers.gmail.client_id')),
            'outlookUsername' => (string) Settings::get('mail.outlook.username', config('mail.mailers.outlook.username')),
            'outlookTenant' => (string) Settings::get('mail.outlook.tenant', config('mail.mailers.outlook.tenant', 'common')),
            'outlookGrant' => (string) Settings::get('mail.outlook.grant', config('mail.mailers.outlook.grant', OutlookGrant::RefreshToken->value)),
            'outlookClientId' => (string) Settings::get('mail.outlook.client_id', config('mail.mailers.outlook.client_id')),
            'smtpHost' => (string) Settings::get('mail.smtp.host', config('mail.mailers.smtp.host')),
            'smtpPort' => (string) Settings::get('mail.smtp.port', config('mail.mailers.smtp.port')),
            'smtpUsername' => (string) Settings::get('mail.smtp.username', config('mail.mailers.smtp.username')),
            'secrets' => [
                'brevo_password' => Settings::has('mail.brevo.password') || filled(config('mail.mailers.brevo.password')),
                'gmail_client_secret' => Settings::has('mail.gmail.client_secret') || filled(config('mail.mailers.gmail.client_secret')),
                'gmail_refresh_token' => Settings::has('mail.gmail.refresh_token') || filled(config('mail.mailers.gmail.refresh_token')),
                'outlook_client_secret' => Settings::has('mail.outlook.client_secret') || filled(config('mail.mailers.outlook.client_secret')),
                'outlook_refresh_token' => Settings::has('mail.outlook.refresh_token') || filled(config('mail.mailers.outlook.refresh_token')),
                'smtp_password' => Settings::has('mail.smtp.password') || filled(config('mail.mailers.smtp.password')),
            ],
            'recipients' => (string) Settings::get('notify.recipients', ''),
            'contractDays' => Settings::contractDays(),
            'courseDays' => Settings::courseDays(),
            'lowScore' => Settings::lowScore(),
            'notices' => [
                'notify_contracts' => Settings::enabled('notify.contracts'),
                'notify_birthdays' => Settings::enabled('notify.birthdays'),
                'notify_courses' => Settings::enabled('notify.courses'),
                'notify_medical_leaves' => Settings::enabled('notify.medical_leaves'),
                'notify_permits' => Settings::enabled('notify.permits'),
                'notify_evaluations' => Settings::enabled('notify.evaluations'),
            ],
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        Settings::store($request->payload());

        return redirect()->route('settings.edit')->with('success', 'La configuración quedó guardada.');
    }

    public function sendTest(SendTestMailRequest $request): RedirectResponse
    {
        Settings::applyMail();

        try {
            MailNotice::send(
                $request->string('test_email')->toString(),
                'Prueba de correo',
                'El envío de Personas MINVU Ñuble está operativo.',
            );
        } catch (Throwable $exception) {
            return back()->with('error', 'No se pudo enviar la prueba. '.$exception->getMessage());
        }

        return back()->with('success', config('mail.default') === MailProvider::Log->value
            ? 'El canal está en registro: el mensaje quedó anotado y no salió a internet.'
            : 'Se envió un correo de prueba.');
    }

    public function sendNotices(): RedirectResponse
    {
        $result = PersonnelNotifier::digest(force: true);

        return back()->with('success', $result === 'sent'
            ? 'Se enviaron los avisos activados.'
            : 'No hay avisos para enviar. Revise los destinatarios y que exista algo por informar.');
    }
}
