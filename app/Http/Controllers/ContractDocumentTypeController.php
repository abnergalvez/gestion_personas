<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractDocumentTypeRequest;
use App\Models\ContractDocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContractDocumentTypeController extends Controller
{
    public function index(): View
    {
        $types = ContractDocumentType::query()->withCount('documents')->orderBy('name')->paginate(12);

        return view('contract-document-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('contract-document-types.create', ['type' => new ContractDocumentType]);
    }

    public function store(ContractDocumentTypeRequest $request): RedirectResponse
    {
        ContractDocumentType::query()->create($request->validated());

        return redirect()->route('contract-document-types.index')->with('success', 'Tipo de documento creado.');
    }

    public function edit(ContractDocumentType $contractDocumentType): View
    {
        return view('contract-document-types.edit', ['type' => $contractDocumentType]);
    }

    public function update(ContractDocumentTypeRequest $request, ContractDocumentType $contractDocumentType): RedirectResponse
    {
        $contractDocumentType->update($request->validated());

        return redirect()->route('contract-document-types.index')->with('success', 'Tipo de documento actualizado.');
    }

    public function destroy(ContractDocumentType $contractDocumentType): RedirectResponse
    {
        if ($contractDocumentType->documents()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay documentos de contrato que usan este tipo.');
        }

        $contractDocumentType->delete();

        return redirect()->route('contract-document-types.index')->with('success', 'Tipo de documento eliminado.');
    }
}
