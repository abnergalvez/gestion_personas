<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermitType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }
}
