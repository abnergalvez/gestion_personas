<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Http\Requests\Concerns\ValidatesEmployeeContext;
use Illuminate\Foundation\Http\FormRequest;

class TrainingCourseRequest extends FormRequest
{
    use NullifiesEmptyFields, ValidatesEmployeeContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->nullify(['valid_until', 'employee_id']);
        $this->prepareEmployeeContext();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:180'],
            'duration' => ['required', 'string', 'max:80'],
            'certified_on' => ['required', 'date', 'after:1950-01-01'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:certified_on'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'remove_document' => ['sometimes', 'boolean'],
            ...$this->employeeContextRules(),
        ];
    }
}
