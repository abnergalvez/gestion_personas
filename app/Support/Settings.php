<?php

namespace App\Support;

use App\Enums\MailProvider;
use App\Models\Setting;
use Throwable;

final class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function has(string $key): bool
    {
        $value = self::all()[$key] ?? null;

        return is_string($value) && $value !== '';
    }

    public static function enabled(string $key): bool
    {
        return self::get($key) === '1';
    }

    public static function contractDays(): int
    {
        return self::boundedInt('management.contract_days', 60);
    }

    public static function courseDays(): int
    {
        return self::boundedInt('management.course_days', 30);
    }

    public static function lowScore(): float
    {
        $score = (float) self::get('management.low_score', 4);

        return max(1, min(7, $score));
    }

    /**
     * @return list<string>
     */
    public static function recipients(): array
    {
        $lines = preg_split('/[\s,;]+/', (string) self::get('notify.recipients', '')) ?: [];

        return array_values(array_filter($lines, fn (string $email): bool => $email !== ''));
    }

    /**
     * @param  array<string, ?string>  $values
     */
    public static function store(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        app()->forgetInstance('settings.values');
        self::applyMail();
    }

    public static function applyMail(): void
    {
        $mailer = self::get('mail.mailer');

        if (! is_string($mailer) || MailProvider::tryFrom($mailer) === null) {
            return;
        }

        config(['mail.default' => $mailer]);
        self::overlay('mail.from.address', 'mail.from_address');
        self::overlay('mail.from.name', 'mail.from_name');

        foreach ([
            'brevo' => [
                'mail.mailers.brevo.username' => 'mail.brevo.username',
                'mail.mailers.brevo.password' => 'mail.brevo.password',
            ],
            'gmail' => [
                'mail.mailers.gmail.username' => 'mail.gmail.username',
                'mail.mailers.gmail.client_id' => 'mail.gmail.client_id',
                'mail.mailers.gmail.client_secret' => 'mail.gmail.client_secret',
                'mail.mailers.gmail.refresh_token' => 'mail.gmail.refresh_token',
            ],
            'outlook' => [
                'mail.mailers.outlook.username' => 'mail.outlook.username',
                'mail.mailers.outlook.tenant' => 'mail.outlook.tenant',
                'mail.mailers.outlook.grant' => 'mail.outlook.grant',
                'mail.mailers.outlook.client_id' => 'mail.outlook.client_id',
                'mail.mailers.outlook.client_secret' => 'mail.outlook.client_secret',
                'mail.mailers.outlook.refresh_token' => 'mail.outlook.refresh_token',
            ],
            'smtp' => [
                'mail.mailers.smtp.host' => 'mail.smtp.host',
                'mail.mailers.smtp.port' => 'mail.smtp.port',
                'mail.mailers.smtp.username' => 'mail.smtp.username',
                'mail.mailers.smtp.password' => 'mail.smtp.password',
            ],
        ][$mailer] ?? [] as $configKey => $settingKey) {
            self::overlay($configKey, $settingKey);
        }
    }

    /**
     * @return array<string, ?string>
     */
    private static function all(): array
    {
        if (app()->bound('settings.values')) {
            return app('settings.values');
        }

        try {
            $values = Setting::query()->get()->mapWithKeys(
                fn (Setting $setting): array => [$setting->key => $setting->value],
            )->all();
        } catch (Throwable) {
            $values = [];
        }

        app()->instance('settings.values', $values);

        return $values;
    }

    private static function overlay(string $configKey, string $settingKey): void
    {
        if (self::has($settingKey)) {
            config([$configKey => self::get($settingKey)]);
        }
    }

    private static function boundedInt(string $key, int $default): int
    {
        $value = (int) self::get($key, $default);

        return max(1, min(365, $value > 0 ? $value : $default));
    }
}
