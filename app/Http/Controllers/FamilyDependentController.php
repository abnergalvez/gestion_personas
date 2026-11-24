<?php

namespace App\Http\Controllers;

use App\Enums\EducationLevel;
use App\Enums\FamilyRelationship;
use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\FamilyDependentRequest;
use App\Models\Employee;
use App\Models\FamilyDependent;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyDependentController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        $dependents = FamilyDependent::query()
            ->with('employee')
            ->when($request->integer('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->string('search')->trim()->value(), function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('rut', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('full_name')
            ->paginate(12)
            ->withQueryString();

        return view('family-dependents.index', [
            'dependents' => $dependents,
            'employees' => Employee::options(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('family-dependents.create', $this->formData(new FamilyDependent([
            'employee_id' => $request->integer('employee_id') ?: null,
        ]), $request->integer('employee_id') ?: null));
    }

    public function store(FamilyDependentRequest $request): RedirectResponse
    {
        FamilyDependent::query()->create($this->payload($request));

        return $this->redirectAfterSave($request, 'family-dependents.index', 'Carga familiar registrada.');
    }

    public function edit(Request $request, FamilyDependent $familyDependent): View
    {
        return view('family-dependents.edit', $this->formData($familyDependent, $request->integer('employee_id') ?: null));
    }

    public function update(FamilyDependentRequest $request, FamilyDependent $familyDependent): RedirectResponse
    {
        $familyDependent->update($this->payload($request, $familyDependent));

        return $this->redirectAfterSave($request, 'family-dependents.index', 'Carga familiar actualizada.');
    }

    public function destroy(Request $request, FamilyDependent $familyDependent): RedirectResponse
    {
        $familyDependent->delete();

        return $this->redirectAfterSave($request, 'family-dependents.index', 'Carga familiar eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(FamilyDependentRequest $request, ?FamilyDependent $dependent = null): array
    {
        $data = $request->safe()->except([
            'birth_certificate', 'remove_birth_certificate',
            'resolution', 'remove_resolution',
            'context_employee_id',
        ]);
        $data = StoredUpload::apply($request, $data, 'birth_certificate', 'birth_certificate_path', 'family-dependents', $dependent?->birth_certificate_path);

        return StoredUpload::apply($request, $data, 'resolution', 'resolution_path', 'family-dependents', $dependent?->resolution_path);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(FamilyDependent $dependent, ?int $contextEmployeeId): array
    {
        return [
            'dependent' => $dependent,
            'employees' => Employee::options(),
            'educationLevels' => EducationLevel::options(),
            'relationships' => FamilyRelationship::options(),
            'contextEmployeeId' => $contextEmployeeId,
        ];
    }
}
