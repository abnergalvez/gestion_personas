<?php

namespace Tests\Feature;

use App\Notifications\PersonnelNotice;
use App\Support\MailNotice;
use App\Support\OAuthMailToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\TestCase;

class MailTransportTest extends TestCase
{
    public function test_a_notice_can_be_sent_to_an_address(): void
    {
        Notification::fake();

        MailNotice::send('ana@minvu.cl', 'Licencia registrada', 'La licencia quedó ingresada.');

        Notification::assertSentOnDemand(PersonnelNotice::class, function (PersonnelNotice $notice): bool {
            return $notice->subjectLine === 'Licencia registrada'
                && str_contains($notice->toMail(new \stdClass)->render(), 'La licencia quedó ingresada.');
        });
    }

    public function test_gmail_oauth_builds_an_smtp_transport_with_the_access_token(): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token-gmail', 'expires_in' => 3600]),
        ]);

        config([
            'mail.mailers.gmail.username' => 'aviso@minvu.cl',
            'mail.mailers.gmail.client_id' => 'cliente',
            'mail.mailers.gmail.client_secret' => 'secreto',
            'mail.mailers.gmail.refresh_token' => 'refresco',
        ]);

        $transport = app('mail.manager')->createSymfonyTransport(config('mail.mailers.gmail'));

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame('aviso@minvu.cl', $transport->getUsername());
        $this->assertSame('token-gmail', $transport->getPassword());
    }

    public function test_outlook_oauth_uses_the_tenant_token_endpoint(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'token-outlook', 'expires_in' => 3600]),
        ]);

        config([
            'mail.mailers.outlook.username' => 'aviso@minvu.cl',
            'mail.mailers.outlook.tenant' => 'tenant-nuble',
            'mail.mailers.outlook.grant' => 'client_credentials',
            'mail.mailers.outlook.client_id' => 'cliente',
            'mail.mailers.outlook.client_secret' => 'secreto',
        ]);

        $transport = app('mail.manager')->createSymfonyTransport(config('mail.mailers.outlook'));

        $this->assertSame('token-outlook', $transport->getPassword());
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'login.microsoftonline.com/tenant-nuble/oauth2/v2.0/token')
            && $request['grant_type'] === 'client_credentials');
    }

    public function test_oauth_refuses_to_start_without_credentials(): void
    {
        $this->expectException(RuntimeException::class);

        OAuthMailToken::refresh('gmail', 'https://oauth2.googleapis.com/token', [
            'client_id' => '',
            'client_secret' => '',
            'grant_type' => 'refresh_token',
        ]);
    }
}
