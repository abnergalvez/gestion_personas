<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\PermitRequest;
use App\Models\Employee;
use App\Models\Permit;
use App\Models\PermitType;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermitController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        $permits = Permit::query()
            ->with(['employee', 'type'])
            ->when($request->integer('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->integer('permit_type_id'), fn ($query, $id) => $query->where('permit_type_id', $id))
            ->latest('starts_at')
            ->paginate(12)
            ->withQueryString();

        return view('permits.index', [
            'permits' => $permits,
            'employees' => Employee::options(),
            'permitTypes' => PermitType::query()->orderBy('name')->pluck('name', 'id'),
        ]);
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
