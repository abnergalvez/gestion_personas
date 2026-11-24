<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'internal_code',
        'description',
    ];

    public function medicalLeaves(): HasMany
    {
        return $this->hasMany(MedicalLeave::class);
    }
}
