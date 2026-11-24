<?php

namespace App\Http\Requests\Concerns;

trait ValidatesEmployeeContext
{
    protected function prepareEmployeeContext(): void
    {
        if ($this->input('context_employee_id') === '' || $this->input('context_employee_id') === null) {
            $this->merge(['context_employee_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function employeeContextRules(): array
    {
        return [
            'context_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
