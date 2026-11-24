<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Models\ContractDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractDocumentTypeRequest extends FormRequest
{
    use NullifiesEmptyFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->nullify(['description']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ContractDocumentType|null $type */
        $type = $this->route('contract_document_type');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('contract_document_types', 'name')->ignore($type)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
