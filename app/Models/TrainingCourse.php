<?php

namespace App\Models;

use App\Models\Concerns\DeletesStoredFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCourse extends Model
{
    use DeletesStoredFiles;

    protected $fillable = [
        'employee_id',
        'name',
        'duration',
        'certified_on',
        'valid_until',
        'document_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'certified_on' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function storedFiles(): array
    {
        return ['document_path'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
