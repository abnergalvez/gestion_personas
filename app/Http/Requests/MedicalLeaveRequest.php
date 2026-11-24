<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Http\Requests\Concerns\ValidatesEmployeeContext;
use Illuminate\Foundation\Http\FormRequest;

class MedicalLeaveRequest extends FormRequest
{
    use NullifiesEmptyFields, ValidatesEmployeeContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->nullify(['employee_id', 'leave_type_id']);
        $this->prepareEmployeeContext();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'days_count' => ['required', 'integer', 'min:1', 'max:400'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'medical_resolution' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'internal_resolution' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'remove_document' => ['sometimes', 'boolean'],
            'remove_medical_resolution' => ['sometimes', 'boolean'],
            'remove_internal_resolution' => ['sometimes', 'boolean'],
            ...$this->employeeContextRules(),
        ];
    }
}
