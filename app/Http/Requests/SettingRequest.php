<?php

namespace App\Http\Requests;

use App\Enums\MailProvider;
use App\Enums\OutlookGrant;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mailer' => ['required', Rule::enum(MailProvider::class)],
            'from_name' => ['required', 'string', 'max:120'],
            'from_address' => ['required', 'email', 'max:150'],
            'brevo_username' => ['nullable', 'email', 'max:150', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Brevo->value)],
            'brevo_password' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn (): bool => $this->missingSecret(MailProvider::Brevo->value, 'mail.brevo.password', 'mail.mailers.brevo.password'))],
            'gmail_username' => ['nullable', 'email', 'max:150', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Gmail->value)],
            'gmail_client_id' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn (): bool => $this->missingSecret(MailProvider::Gmail->value, 'mail.gmail.client_id', 'mail.mailers.gmail.client_id'))],
            'gmail_client_secret' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn (): bool => $this->missingSecret(MailProvider::Gmail->value, 'mail.gmail.client_secret', 'mail.mailers.gmail.client_secret'))],
            'gmail_refresh_token' => ['nullable', 'string', 'max:2000', Rule::requiredIf(fn (): bool => $this->missingSecret(MailProvider::Gmail->value, 'mail.gmail.refresh_token', 'mail.mailers.gmail.refresh_token'))],
            'outlook_username' => ['nullable', 'email', 'max:150', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Outlook->value)],
            'outlook_tenant' => ['nullable', 'string', 'max:80', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Outlook->value)],
            'outlook_grant' => ['nullable', Rule::enum(OutlookGrant::class), Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Outlook->value)],
            'outlook_client_id' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn (): bool => $this->missingSecret(MailProvider::Outlook->value, 'mail.outlook.client_id', 'mail.mailers.outlook.client_id'))],
            'outlook_client_secret' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn (): bool => $this->missingSecret(MailProvider::Outlook->value, 'mail.outlook.client_secret', 'mail.mailers.outlook.client_secret'))],
            'outlook_refresh_token' => ['nullable', 'string', 'max:2000', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Outlook->value && $this->input('outlook_grant') === OutlookGrant::RefreshToken->value && ! Settings::has('mail.outlook.refresh_token') && blank(config('mail.mailers.outlook.refresh_token')))],
            'smtp_host' => ['nullable', 'string', 'max:150', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Smtp->value)],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535', Rule::requiredIf(fn (): bool => $this->input('mailer') === MailProvider::Smtp->value)],
            'smtp_username' => ['nullable', 'string', 'max:150'],
            'smtp_password' => ['nullable', 'string', 'max:500'],
            'recipients' => ['nullable', 'string', 'max:2000'],
            'notify_contracts' => ['required', 'boolean'],
            'notify_birthdays' => ['required', 'boolean'],
            'notify_courses' => ['required', 'boolean'],
            'notify_medical_leaves' => ['required', 'boolean'],
            'notify_permits' => ['required', 'boolean'],
            'notify_evaluations' => ['required', 'boolean'],
            'contract_days' => ['required', 'integer', 'min:1', 'max:365'],
            'course_days' => ['required', 'integer', 'min:1', 'max:365'],
            'low_score' => ['required', 'numeric', 'min:1', 'max:7'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mailer' => 'canal de correo',
            'from_name' => 'nombre del remitente',
            'from_address' => 'correo del remitente',
            'brevo_username' => 'usuario Brevo',
            'brevo_password' => 'clave SMTP de Brevo',
            'gmail_username' => 'buzón de Gmail',
            'gmail_client_id' => 'cliente de Gmail',
            'gmail_client_secret' => 'secreto de Gmail',
            'gmail_refresh_token' => 'token de Gmail',
            'outlook_username' => 'buzón de Outlook',
            'outlook_tenant' => 'directorio de Outlook',
            'outlook_grant' => 'tipo de acceso de Outlook',
            'outlook_client_id' => 'cliente de Outlook',
            'outlook_client_secret' => 'secreto de Outlook',
            'outlook_refresh_token' => 'token de Outlook',
            'smtp_host' => 'servidor SMTP',
            'smtp_port' => 'puerto SMTP',
            'recipients' => 'destinatarios',
            'contract_days' => 'días de aviso de contratos',
            'course_days' => 'días de aviso de cursos',
            'low_score' => 'nota de alerta',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->recipientList() as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $validator->errors()->add('recipients', 'El correo '.$email.' no es válido.');
                }
            }

            if ($this->notificationsEnabled() && $this->recipientList() === []) {
                $validator->errors()->add('recipients', 'Indique al menos un correo para los avisos activados.');
            }
        });
    }

    /**
     * @return array<string, ?string>
     */
    public function payload(): array
    {
        $data = [
            'mail.mailer' => $this->string('mailer')->toString(),
            'mail.from_name' => $this->string('from_name')->toString(),
            'mail.from_address' => $this->string('from_address')->toString(),
            'mail.brevo.username' => $this->nullable('brevo_username'),
            'mail.gmail.username' => $this->nullable('gmail_username'),
            'mail.gmail.client_id' => $this->nullable('gmail_client_id'),
            'mail.outlook.username' => $this->nullable('outlook_username'),
            'mail.outlook.tenant' => $this->nullable('outlook_tenant'),
            'mail.outlook.grant' => $this->nullable('outlook_grant'),
            'mail.outlook.client_id' => $this->nullable('outlook_client_id'),
            'mail.smtp.host' => $this->nullable('smtp_host'),
            'mail.smtp.port' => $this->filled('smtp_port') ? (string) $this->integer('smtp_port') : null,
            'mail.smtp.username' => $this->nullable('smtp_username'),
            'notify.recipients' => implode("\n", $this->recipientList()),
            'notify.contracts' => $this->boolean('notify_contracts') ? '1' : '0',
            'notify.birthdays' => $this->boolean('notify_birthdays') ? '1' : '0',
            'notify.courses' => $this->boolean('notify_courses') ? '1' : '0',
            'notify.medical_leaves' => $this->boolean('notify_medical_leaves') ? '1' : '0',
            'notify.permits' => $this->boolean('notify_permits') ? '1' : '0',
            'notify.evaluations' => $this->boolean('notify_evaluations') ? '1' : '0',
            'management.contract_days' => (string) $this->integer('contract_days'),
            'management.course_days' => (string) $this->integer('course_days'),
            'management.low_score' => number_format((float) $this->input('low_score'), 1, '.', ''),
        ];

        foreach ([
            'mail.brevo.password' => 'brevo_password',
            'mail.gmail.client_secret' => 'gmail_client_secret',
            'mail.gmail.refresh_token' => 'gmail_refresh_token',
            'mail.outlook.client_secret' => 'outlook_client_secret',
            'mail.outlook.refresh_token' => 'outlook_refresh_token',
            'mail.smtp.password' => 'smtp_password',
        ] as $key => $field) {
            $value = trim($this->string($field)->toString());

            if ($value !== '') {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function missingSecret(string $provider, string $settingKey, string $configKey): bool
    {
        return $this->input('mailer') === $provider
            && ! Settings::has($settingKey)
            && blank(config($configKey));
    }

    private function notificationsEnabled(): bool
    {
        foreach (['notify_contracts', 'notify_birthdays', 'notify_courses', 'notify_medical_leaves', 'notify_permits', 'notify_evaluations'] as $field) {
            if ($this->boolean($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function recipientList(): array
    {
        $lines = preg_split('/[\s,;]+/', $this->string('recipients')->toString()) ?: [];

        return array_values(array_filter($lines, fn (string $email): bool => $email !== ''));
    }

    private function nullable(string $field): ?string
    {
        $value = trim($this->string($field)->toString());

        return $value === '' ? null : $value;
    }
}
