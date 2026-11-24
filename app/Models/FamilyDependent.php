<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\FamilyRelationship;
use App\Models\Concerns\DeletesStoredFiles;
use App\Support\Rut;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyDependent extends Model
{
    use DeletesStoredFiles;

    protected $fillable = [
        'employee_id',
        'full_name',
        'rut',
        'birth_date',
        'education_level',
        'relationship',
        'birth_certificate_path',
        'resolution_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'education_level' => EducationLevel::class,
            'relationship' => FamilyRelationship::class,
        ];
    }

    public function storedFiles(): array
    {
        return ['birth_certificate_path', 'resolution_path'];
    }

    protected function formattedRut(): Attribute
    {
        return Attribute::get(fn (): string => $this->rut ? Rut::format($this->rut) : '');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
