<?php

namespace App\Http\Requests;

use App\Enums\EvaluationProcess;
use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Http\Requests\Concerns\ValidatesEmployeeContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluationRequest extends FormRequest
{
    use NullifiesEmptyFields, ValidatesEmployeeContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->nullify(['score', 'notes', 'employee_id']);
        $this->prepareEmployeeContext();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'period_start_year' => ['required', 'integer', 'min:1980', 'max:'.(now()->year + 1)],
            'period_end_year' => ['required', 'integer', 'gte:period_start_year', 'max:'.(now()->year + 1)],
            'process' => ['required', Rule::enum(EvaluationProcess::class)],
            'score' => ['nullable', 'numeric', 'between:1,7', 'required_without:notes'],
            'notes' => ['nullable', 'string', 'max:2000', 'required_without:score'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'remove_document' => ['sometimes', 'boolean'],
            ...$this->employeeContextRules(),
        ];
    }
}
