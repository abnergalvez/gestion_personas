<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentQuality;
use App\Enums\MaritalStatus;
use App\Enums\Sex;
use App\Enums\StaffLevel;
use App\Http\Requests\EmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use App\Support\EmployeeListing;
use App\Support\SpreadsheetExport;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        return view('employees.index', [
            'employees' => EmployeeListing::from($request)->paginate(),
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
            'sections' => Section::query()->orderBy('name')->pluck('name', 'id'),
            'qualities' => EmploymentQuality::options(),
            'staffLevels' => StaffLevel::options(),
            'sexes' => Sex::options(),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return SpreadsheetExport::download(
            'funcionarios-'.now()->format('Y-m-d').'.xlsx',
            ['RUT', 'Nombres', 'Apellido paterno', 'Apellido materno', 'Cargo', 'Sexo', 'Nacimiento', 'Edad', 'Profesión', 'Estamento', 'Estado civil', 'Grado', 'Calidad', 'Ingreso', 'Correo', 'Departamento', 'Sección'],
            EmployeeListing::from($request)->exportRows(),
        );
    }

    public function create(): View
    {
        return view('employees.create', $this->formData(new Employee));
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $data = $this->payload($request);
        $employee = Employee::query()->create($data);

        return redirect()->route('employees.show', $employee)->with('success', 'Funcionario registrado correctamente.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'department',
            'section',
            'trainingCourses' => fn ($query) => $query->latest('certified_on'),
            'medicalLeaves' => fn ($query) => $query->with('leaveType')->latest('starts_on'),
            'familyDependents' => fn ($query) => $query->orderBy('full_name'),
            'contracts' => fn ($query) => $query->with(['department', 'section', 'documents.type'])->latest('starts_on'),
            'permits' => fn ($query) => $query->with('type')->latest('starts_at'),
            'evaluations' => fn ($query) => $query->latest('period_end_year'),
            'headedDepartments',
            'deputyDepartments',
            'analystDepartments',
            'headedSections',
            'deputySections',
            'analystSections',
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', $this->formData($employee));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->payload($request, $employee));

        return redirect()->route('employees.show', $employee)->with('success', 'Ficha del funcionario actualizada.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Funcionario eliminado junto con su información asociada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Employee $employee): array
    {
        return [
            'employee' => $employee,
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
            'sections' => Section::query()->with('department')->orderBy('name')->get()->mapWithKeys(
                fn (Section $section) => [$section->id => $section->department ? $section->name.' · '.$section->department->name : $section->name]
            ),
            'maritalStatuses' => MaritalStatus::options(),
            'sexes' => Sex::options(),
            'staffLevels' => StaffLevel::options(),
            'qualities' => EmploymentQuality::options(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(EmployeeRequest $request, ?Employee $employee = null): array
    {
        $data = $request->safe()->except(['photo', 'remove_photo']);

        return StoredUpload::apply($request, $data, 'photo', 'photo_path', 'employees/photos', $employee?->photo_path);
    }
}
