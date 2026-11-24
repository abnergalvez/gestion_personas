<?php

namespace App\Models;

use App\Models\Concerns\DeletesStoredFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalLeave extends Model
{
    use DeletesStoredFiles;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'starts_on',
        'ends_on',
        'days_count',
        'document_path',
        'medical_resolution_path',
        'internal_resolution_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'days_count' => 'integer',
        ];
    }

    public function storedFiles(): array
    {
        return ['document_path', 'medical_resolution_path', 'internal_resolution_path'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
