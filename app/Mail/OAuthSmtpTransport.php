<?php

namespace App\Mail;

use App\Support\OAuthMailToken;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

final class OAuthSmtpTransport
{
    /**
     * @param  array<string, mixed>  $config
     */
    public static function gmail(array $config): EsmtpTransport
    {
        return self::connect(
            (string) ($config['host'] ?? 'smtp.gmail.com'),
            (int) ($config['port'] ?? 587),
            (string) ($config['username'] ?? ''),
            OAuthMailToken::refresh('gmail', 'https://oauth2.googleapis.com/token', [
                'client_id' => (string) ($config['client_id'] ?? ''),
                'client_secret' => (string) ($config['client_secret'] ?? ''),
                'refresh_token' => (string) ($config['refresh_token'] ?? ''),
                'grant_type' => 'refresh_token',
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function outlook(array $config): EsmtpTransport
    {
        $tenant = trim((string) ($config['tenant'] ?? 'common'));
        $tenant = $tenant === '' ? 'common' : $tenant;
        $grant = ($config['grant'] ?? 'refresh_token') === 'client_credentials' ? 'client_credentials' : 'refresh_token';
        $fields = [
            'client_id' => (string) ($config['client_id'] ?? ''),
            'client_secret' => (string) ($config['client_secret'] ?? ''),
            'grant_type' => $grant,
            'scope' => $grant === 'client_credentials'
                ? 'https://outlook.office365.com/.default'
                : 'https://outlook.office365.com/SMTP.Send offline_access',
        ];

        if ($grant === 'refresh_token') {
            $fields['refresh_token'] = (string) ($config['refresh_token'] ?? '');
        }

        return self::connect(
            (string) ($config['host'] ?? 'smtp.office365.com'),
            (int) ($config['port'] ?? 587),
            (string) ($config['username'] ?? ''),
            OAuthMailToken::refresh('outlook', 'https://login.microsoftonline.com/'.$tenant.'/oauth2/v2.0/token', $fields),
        );
    }

    private static function connect(string $host, int $port, string $username, string $token): EsmtpTransport
    {
        $transport = (new EsmtpTransportFactory)->create(new Dsn('smtp', $host, $username, null, $port));

        if (! $transport instanceof EsmtpTransport) {
            throw new \RuntimeException('No se pudo preparar el transporte de correo.');
        }

        $transport->setAuthenticators([new XOAuth2Authenticator]);
        $transport->setUsername($username);
        $transport->setPassword($token);

        return $transport;
    }
}
