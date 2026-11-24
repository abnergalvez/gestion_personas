<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsToEmployeeContext
{
    protected function redirectAfterSave(Request $request, string $route, string $message): RedirectResponse
    {
        $employeeId = $request->integer('context_employee_id');

        $redirect = $employeeId > 0
            ? redirect()->route('employees.show', $employeeId)
            : redirect()->route($route);

        return $redirect->with('success', $message);
    }
}
