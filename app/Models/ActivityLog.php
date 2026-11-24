<?php

namespace App\Models;

use App\Enums\ActivityAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'status_code',
        'properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayName(): string
    {
        return $this->user?->name ?? $this->user_name ?? $this->user_email ?? 'Sin identificar';
    }

    /**
     * @return array<string, string>
     */
    public function details(): array
    {
        $rows = [
            'Usuario' => $this->displayName(),
            'Correo' => $this->user_email ?: '—',
            'Acción' => $this->action->label(),
            'Descripción' => $this->description,
            'Fecha' => $this->created_at?->format('d-m-Y H:i:s') ?? '—',
            'Método' => $this->method ?: '—',
            'Dirección' => $this->url ?: '—',
            'Respuesta' => $this->status_code ? (string) $this->status_code : '—',
            'IP' => $this->ip_address ?: '—',
            'Navegador' => $this->user_agent ?: '—',
        ];

        foreach ($this->properties['input'] ?? [] as $key => $value) {
            $rows['Dato: '.$this->propertyName((string) $key)] = $this->present($value);
        }

        foreach ($this->properties['query'] ?? [] as $key => $value) {
            $rows['Filtro: '.$this->propertyName((string) $key)] = $this->present($value);
        }

        return $rows;
    }

    private function propertyName(string $key): string
    {
        $label = trans('validation.attributes.'.$key);

        return $label === 'validation.attributes.'.$key ? str_replace('_', ' ', $key) : $label;
    }

    private function present(mixed $value): string
    {
        if (in_array($value, [null, ''], true)) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '—';
        }

        return (string) $value;
    }
}
