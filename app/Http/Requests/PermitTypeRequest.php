<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Models\PermitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermitTypeRequest extends FormRequest
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
        /** @var PermitType|null $permitType */
        $permitType = $this->route('permit_type');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('permit_types', 'name')->ignore($permitType)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
