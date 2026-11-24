<?php

namespace App\Http\Requests;

use App\Enums\EmploymentQuality;
use App\Enums\MaritalStatus;
use App\Enums\Sex;
use App\Enums\StaffLevel;
use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Rules\ChileanRut;
use App\Support\Rut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    use NullifiesEmptyFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rut' => Rut::normalize((string) $this->input('rut')),
        ]);

        $this->nullify(['profession', 'position', 'grade', 'department_id', 'section_id', 'sex', 'staff_level', 'hired_on', 'email', 'marital_status']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rut' => ['required', 'string', 'max:12', new ChileanRut, Rule::unique('employees', 'rut')->ignore($this->route('employee'))],
            'first_name' => ['required', 'string', 'max:80'],
            'paternal_last_name' => ['required', 'string', 'max:80'],
            'maternal_last_name' => ['required', 'string', 'max:80'],
            'birth_date' => ['required', 'date', 'after:1940-01-01', 'before:today'],
            'sex' => ['nullable', Rule::enum(Sex::class)],
            'profession' => ['nullable', 'string', 'max:180'],
            'position' => ['nullable', 'string', 'max:255'],
            'staff_level' => ['nullable', Rule::enum(StaffLevel::class)],
            'marital_status' => ['nullable', Rule::enum(MaritalStatus::class)],
            'grade' => ['nullable', 'string', 'max:40'],
            'hired_on' => ['nullable', 'date', 'after:1940-01-01', 'before_or_equal:today'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($this->route('employee'))],
            'employment_quality' => ['required', Rule::enum(EmploymentQuality::class)],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
