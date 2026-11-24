<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->with(['head', 'deputy'])
            ->withCount(['employees', 'analysts', 'sections'])
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('departments.create', $this->formData(new Department));
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $department = Department::query()->create($request->safe()->except('analyst_ids'));
        $department->analysts()->sync($request->input('analyst_ids', []));

        return redirect()->route('departments.index')->with('success', 'Departamento creado.');
    }

    public function edit(Department $department): View
    {
        $department->load('analysts');

        return view('departments.edit', $this->formData($department));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->safe()->except('analyst_ids'));
        $department->analysts()->sync($request->input('analyst_ids', []));

        return redirect()->route('departments.index')->with('success', 'Departamento actualizado.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Departamento eliminado. Los funcionarios quedaron sin esa asignación.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Department $department): array
    {
        return [
            'department' => $department,
            'employees' => Employee::options(),
        ];
    }
}
