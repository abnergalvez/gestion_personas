<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractDocumentType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }
}
