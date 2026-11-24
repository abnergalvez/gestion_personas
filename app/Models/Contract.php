<?php

namespace App\Models;

use App\Enums\ContractModality;
use App\Enums\ContractQuality;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'employee_id',
        'quality',
        'modality',
        'grade',
        'position',
        'starts_on',
        'ends_on',
        'department_id',
        'section_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quality' => ContractQuality::class,
            'modality' => ContractModality::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Contract $contract): void {
            $contract->documents->each->delete();
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }
}
