<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'name',
        'department_id',
        'head_employee_id',
        'deputy_employee_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_employee_id');
    }

    public function deputy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'deputy_employee_id');
    }

    public function analysts(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'section_analysts')
            ->withTimestamps()
            ->orderBy('paternal_last_name')
            ->orderBy('first_name');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
