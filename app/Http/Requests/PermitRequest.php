<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Http\Requests\Concerns\ValidatesEmployeeContext;
use Illuminate\Foundation\Http\FormRequest;

class PermitRequest extends FormRequest
{
    use NullifiesEmptyFields, ValidatesEmployeeContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->nullify(['employee_id', 'permit_type_id']);
        $this->prepareEmployeeContext();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'permit_type_id' => ['required', 'integer', 'exists:permit_types,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'remove_document' => ['sometimes', 'boolean'],
            ...$this->employeeContextRules(),
        ];
    }
}
