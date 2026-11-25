<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\PermitRequest;
use App\Models\Employee;
use App\Models\Permit;
use App\Models\PermitType;
use App\Support\PermitListing;
use App\Support\ReportFilters;
use App\Support\SpreadsheetExport;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermitController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        return view('permits.index', [
            'permits' => PermitListing::from($request)->paginate(),
            'permitTypes' => PermitType::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return SpreadsheetExport::download(
            'permisos-'.now()->format('Y-m-d').'.xlsx',
            [...ReportFilters::IDENTITY, 'Tipo', 'Inicio', 'Término', 'Documento'],
            PermitListing::from($request)->exportRows(),
        );
    }

    public function create(Request $request): View
    {
        return view('permits.create', $this->formData(new Permit([
            'employee_id' => $request->integer('employee_id') ?: null,
        ]), $request->integer('employee_id') ?: null));
    }

    public function store(PermitRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['document', 'remove_document', 'context_employee_id']);
        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'permits');
        Permit::query()->create($data);

        return $this->redirectAfterSave($request, 'permits.index', 'Permiso registrado.');
    }

    public function edit(Request $request, Permit $permit): View
    {
        return view('permits.edit', $this->formData($permit, $request->integer('employee_id') ?: null));
    }

    public function update(PermitRequest $request, Permit $permit): RedirectResponse
    {
        $data = $request->safe()->except(['document', 'remove_document', 'context_employee_id']);
        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'permits', $permit->document_path);
        $permit->update($data);

        return $this->redirectAfterSave($request, 'permits.index', 'Permiso actualizado.');
    }

    public function destroy(Request $request, Permit $permit): RedirectResponse
    {
        $permit->delete();

        return $this->redirectAfterSave($request, 'permits.index', 'Permiso eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Permit $permit, ?int $contextEmployeeId): array
    {
        return [
            'permit' => $permit,
            'employees' => Employee::options(),
            'permitTypes' => PermitType::query()->orderBy('name')->pluck('name', 'id'),
            'contextEmployeeId' => $contextEmployeeId,
        ];
    }
}
