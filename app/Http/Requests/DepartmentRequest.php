<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DepartmentRequest extends FormRequest
{
    use NullifiesEmptyFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->nullify(['head_employee_id', 'deputy_employee_id']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Department|null $department */
        $department = $this->route('department');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('departments', 'name')->ignore($department)],
            'head_employee_id' => ['nullable', 'integer', 'exists:employees,id', 'different:deputy_employee_id'],
            'deputy_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'analyst_ids' => ['nullable', 'array'],
            'analyst_ids.*' => ['integer', 'distinct', 'exists:employees,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $analysts = array_map('strval', $this->input('analyst_ids', []));
            $reserved = array_filter([
                $this->input('head_employee_id'),
                $this->input('deputy_employee_id'),
            ], fn ($id) => $id !== null && $id !== '');

            if (array_intersect($analysts, array_map('strval', $reserved)) !== []) {
                $validator->errors()->add('analyst_ids', 'El jefe y el subrogante no deben repetirse como analistas.');
            }
        });
    }
}
