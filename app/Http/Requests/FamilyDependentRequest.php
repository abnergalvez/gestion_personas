<?php

namespace App\Http\Requests;

use App\Enums\EducationLevel;
use App\Enums\FamilyRelationship;
use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Http\Requests\Concerns\ValidatesEmployeeContext;
use App\Rules\ChileanRut;
use App\Support\Rut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamilyDependentRequest extends FormRequest
{
    use NullifiesEmptyFields, ValidatesEmployeeContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rut' => Rut::normalize((string) $this->input('rut')),
        ]);
        $this->nullify(['employee_id']);
        $this->prepareEmployeeContext();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'full_name' => ['required', 'string', 'max:160'],
            'rut' => [
                'required',
                'string',
                'max:12',
                new ChileanRut,
                Rule::unique('family_dependents', 'rut')
                    ->where(fn ($query) => $query->where('employee_id', $this->input('employee_id')))
                    ->ignore($this->route('family_dependent')),
            ],
            'birth_date' => ['required', 'date', 'after:1920-01-01', 'before:today'],
            'education_level' => ['required', Rule::enum(EducationLevel::class)],
            'relationship' => ['required', Rule::enum(FamilyRelationship::class)],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'resolution' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
            'remove_birth_certificate' => ['sometimes', 'boolean'],
            'remove_resolution' => ['sometimes', 'boolean'],
            ...$this->employeeContextRules(),
        ];
    }
}
