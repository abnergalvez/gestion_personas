<?php

namespace App\Models;

use App\Enums\ErrorReview;
use App\Enums\ErrorSeverity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemError extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'severity',
        'status_code',
        'exception',
        'message',
        'file',
        'line',
        'method',
        'url',
        'user_id',
        'ip_address',
        'trace',
        'review',
        'reviewed_at',
        'reviewed_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => ErrorSeverity::class,
            'review' => ErrorReview::class,
            'reviewed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePendingGrave(Builder $query): Builder
    {
        return $query->where('severity', ErrorSeverity::Grave)->whereNull('reviewed_at');
    }

    public function summary(): string
    {
        $status = $this->status_code ? $this->status_code.' ' : '';
        $message = preg_replace('/\s+/u', ' ', $this->message) ?: '';

        return trim($status.class_basename($this->exception).': '.$message);
    }

    /**
     * @return array<string, string>
     */
    public function details(): array
    {
        $review = 'Pendiente';

        if ($this->reviewed_at !== null && $this->review instanceof ErrorReview) {
            $review = $this->review->label().' el '.$this->reviewed_at->format('d-m-Y H:i');

            if ($this->reviewer !== null) {
                $review .= ' por '.$this->reviewer->name;
            }
        }

        return [
            'Fecha' => $this->created_at?->format('d-m-Y H:i:s') ?? '—',
            'Gravedad' => $this->severity->label(),
            'Estado HTTP' => (string) ($this->status_code ?? '—'),
            'Excepción' => $this->exception,
            'Mensaje' => $this->message,
            'Archivo' => $this->file ? $this->file.':'.$this->line : '—',
            'Petición' => trim(($this->method ?? '').' '.($this->url ?? '')) ?: '—',
            'Usuario' => $this->user?->name ?? 'Sin sesión',
            'IP' => $this->ip_address ?: '—',
            'Revisión' => $review,
        ];
    }
}
