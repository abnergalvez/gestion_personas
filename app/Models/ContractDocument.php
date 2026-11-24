<?php

namespace App\Models;

use App\Models\Concerns\DeletesStoredFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocument extends Model
{
    use DeletesStoredFiles;

    protected $fillable = [
        'contract_id',
        'contract_document_type_id',
        'document_path',
    ];

    public function storedFiles(): array
    {
        return ['document_path'];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContractDocumentType::class, 'contract_document_type_id');
    }
}
