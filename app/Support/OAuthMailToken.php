<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OAuthMailToken
{
    /**
     * @param  array<string, string>  $fields
     */
    public static function refresh(string $provider, string $url, array $fields): string
    {
        foreach (['client_id', 'client_secret'] as $required) {
            if (trim($fields[$required] ?? '') === '') {
                throw new RuntimeException("Falta la credencial {$required} del correo {$provider}.");
            }
        }

        if (($fields['grant_type'] ?? '') === 'refresh_token' && trim($fields['refresh_token'] ?? '') === '') {
            throw new RuntimeException("Falta el refresh token del correo {$provider}.");
        }

        $cacheKey = 'mail.oauth.'.$provider.'.'.hash('sha256', $url.'|'.($fields['client_id'] ?? '').'|'.($fields['grant_type'] ?? ''));
        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()->timeout(15)->post($url, $fields);

        $token = $response->json('access_token');

        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new RuntimeException("No se pudo obtener el acceso OAuth del correo {$provider}.");
        }

        $ttl = max(60, ((int) $response->json('expires_in', 3600)) - 60);
        Cache::put($cacheKey, $token, $ttl);

        return $token;
    }
}
