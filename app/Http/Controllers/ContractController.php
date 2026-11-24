<?php

namespace App\Http\Controllers;

use App\Enums\ContractModality;
use App\Enums\ContractQuality;
use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\ContractDocumentRequest;
use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractDocumentType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        $contracts = Contract::query()
            ->with(['employee', 'department', 'section'])
            ->when($request->integer('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->string('quality')->value(), fn ($query, $quality) => $query->where('quality', $quality))
            ->latest('starts_on')
            ->paginate(12)
            ->withQueryString();

        return view('contracts.index', [
            'contracts' => $contracts,
            'employees' => Employee::options(),
            'qualities' => ContractQuality::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $employeeId = $request->integer('employee_id') ?: null;
        $employee = $employeeId ? Employee::query()->find($employeeId) : null;

        $contract = new Contract([
            'employee_id' => $employeeId,
            'department_id' => $employee?->department_id,
            'section_id' => $employee?->section_id,
            'grade' => $employee?->grade,
            'quality' => $employee?->employment_quality?->value,
        ]);

        return view('contracts.create', $this->formData($contract, $employeeId));
    }

    public function store(ContractRequest $request): RedirectResponse
    {
        $contract = Contract::query()->create($request->safe()->except('context_employee_id'));

        return redirect()->route('contracts.show', $contract)->with('success', 'Contrato registrado. Puede adjuntar sus documentos.');
    }

    public function show(Contract $contract): View
    {
        $contract->load(['employee', 'department', 'section', 'documents.type']);

        return view('contracts.show', [
            'contract' => $contract,
            'documentTypes' => ContractDocumentType::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function edit(Request $request, Contract $contract): View
    {
        return view('contracts.edit', $this->formData($contract, $request->integer('employee_id') ?: null));
    }

    public function update(ContractRequest $request, Contract $contract): RedirectResponse
    {
        $contract->update($request->safe()->except('context_employee_id'));

        if ($request->integer('context_employee_id') > 0) {
            return redirect()
                ->route('employees.show', $request->integer('context_employee_id'))
                ->with('success', 'Contrato actualizado.');
        }

        return redirect()->route('contracts.show', $contract)->with('success', 'Contrato actualizado.');
    }

    public function destroy(Request $request, Contract $contract): RedirectResponse
    {
        $contract->delete();

        return $this->redirectAfterSave($request, 'contracts.index', 'Contrato eliminado.');
    }

    public function storeDocument(ContractDocumentRequest $request, Contract $contract): RedirectResponse
    {
        $contract->documents()->create([
            'contract_document_type_id' => $request->integer('contract_document_type_id'),
            'document_path' => StoredUpload::store($request->file('document'), 'contracts/documents'),
        ]);

        return redirect()->route('contracts.show', $contract)->with('success', 'Documento agregado al contrato.');
    }

    public function destroyDocument(Contract $contract, ContractDocument $document): RedirectResponse
    {
        abort_unless($document->contract_id === $contract->id, 404);
        $document->delete();

        return redirect()->route('contracts.show', $contract)->with('success', 'Documento eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Contract $contract, ?int $contextEmployeeId): array
    {
        return [
            'contract' => $contract,
            'employees' => Employee::options(),
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
            'sections' => Section::query()->with('department')->orderBy('name')->get()->mapWithKeys(
                fn (Section $section) => [$section->id => $section->department ? $section->name.' · '.$section->department->name : $section->name]
            ),
            'qualities' => ContractQuality::options(),
            'modalities' => ContractModality::options(),
            'contextEmployeeId' => $contextEmployeeId,
        ];
    }
}
