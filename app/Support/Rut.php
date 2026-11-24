<?php

namespace App\Support;

final class Rut
{
    public static function normalize(string $rut): string
    {
        $rut = strtoupper(str_replace(['.', ' ', '‐', '–', '—'], '', trim($rut)));

        if ($rut === '') {
            return '';
        }

        if (! str_contains($rut, '-') && strlen($rut) > 1) {
            $rut = substr($rut, 0, -1).'-'.substr($rut, -1);
        }

        return $rut;
    }

    public static function isValid(string $rut): bool
    {
        $rut = self::normalize($rut);

        if (! preg_match('/^(\d{7,8})-([\dK])$/', $rut, $matches)) {
            return false;
        }

        return self::verifier((int) $matches[1]) === $matches[2];
    }

    public static function verifier(int $body): string
    {
        $sum = 0;
        $factor = 2;

        while ($body > 0) {
            $sum += ($body % 10) * $factor;
            $body = intdiv($body, 10);
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $remainder = 11 - ($sum % 11);

        return match ($remainder) {
            11 => '0',
            10 => 'K',
            default => (string) $remainder,
        };
    }

    public static function format(string $rut): string
    {
        $rut = self::normalize($rut);

        if (! str_contains($rut, '-')) {
            return $rut;
        }

        [$body, $verifier] = explode('-', $rut, 2);
        $body = ltrim($body, '0') ?: '0';
        $groups = str_split(strrev($body), 3);

        return strrev(implode('.', $groups)).'-'.$verifier;
    }
}
