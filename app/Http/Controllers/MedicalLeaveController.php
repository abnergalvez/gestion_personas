<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\MedicalLeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\MedicalLeave;
use App\Support\MedicalLeaveListing;
use App\Support\SpreadsheetExport;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MedicalLeaveController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        return view('medical-leaves.index', [
            'leaves' => MedicalLeaveListing::from($request)->paginate(),
            'leaveTypes' => LeaveType::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return SpreadsheetExport::download(
            'licencias-medicas-'.now()->format('Y-m-d').'.xlsx',
            ['Funcionario', 'RUT', 'Tipo', 'Inicio', 'Término', 'Días', 'Cargo', 'Departamento', 'Sección', 'Calidad', 'Estamento', 'Sexo', 'Correo', 'Documento', 'Resolución médica', 'Resolución interna'],
            MedicalLeaveListing::from($request)->exportRows(),
        );
    }

    public function create(Request $request): View
    {
        return view('medical-leaves.create', $this->formData(new MedicalLeave([
            'employee_id' => $request->integer('employee_id') ?: null,
        ]), $request->integer('employee_id') ?: null));
    }

    public function store(MedicalLeaveRequest $request): RedirectResponse
    {
        MedicalLeave::query()->create($this->payload($request));

        return $this->redirectAfterSave($request, 'medical-leaves.index', 'Licencia médica registrada.');
    }

    public function edit(Request $request, MedicalLeave $medicalLeave): View
    {
        return view('medical-leaves.edit', $this->formData($medicalLeave, $request->integer('employee_id') ?: null));
    }

    public function update(MedicalLeaveRequest $request, MedicalLeave $medicalLeave): RedirectResponse
    {
        $medicalLeave->update($this->payload($request, $medicalLeave));

        return $this->redirectAfterSave($request, 'medical-leaves.index', 'Licencia médica actualizada.');
    }

    public function destroy(Request $request, MedicalLeave $medicalLeave): RedirectResponse
    {
        $medicalLeave->delete();

        return $this->redirectAfterSave($request, 'medical-leaves.index', 'Licencia médica eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(MedicalLeaveRequest $request, ?MedicalLeave $leave = null): array
    {
        $data = $request->safe()->except([
            'document', 'remove_document',
            'medical_resolution', 'remove_medical_resolution',
            'internal_resolution', 'remove_internal_resolution',
            'context_employee_id',
        ]);

        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'medical-leaves', $leave?->document_path);
        $data = StoredUpload::apply($request, $data, 'medical_resolution', 'medical_resolution_path', 'medical-leaves', $leave?->medical_resolution_path);

        return StoredUpload::apply($request, $data, 'internal_resolution', 'internal_resolution_path', 'medical-leaves', $leave?->internal_resolution_path);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MedicalLeave $leave, ?int $contextEmployeeId): array
    {
        return [
            'leave' => $leave,
            'employees' => Employee::options(),
            'leaveTypes' => LeaveType::query()->orderBy('name')->pluck('name', 'id'),
            'contextEmployeeId' => $contextEmployeeId,
        ];
    }
}
