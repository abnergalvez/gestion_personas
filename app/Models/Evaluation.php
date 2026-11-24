<?php

namespace App\Models;

use App\Enums\EvaluationProcess;
use App\Models\Concerns\DeletesStoredFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use DeletesStoredFiles;

    protected $fillable = [
        'employee_id',
        'period_start_year',
        'period_end_year',
        'process',
        'score',
        'notes',
        'document_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start_year' => 'integer',
            'period_end_year' => 'integer',
            'process' => EvaluationProcess::class,
            'score' => 'decimal:2',
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
