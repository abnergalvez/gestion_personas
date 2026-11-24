<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermitTypeRequest;
use App\Models\PermitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermitTypeController extends Controller
{
    public function index(): View
    {
        $permitTypes = PermitType::query()->withCount('permits')->orderBy('name')->paginate(12);

        return view('permit-types.index', compact('permitTypes'));
    }

    public function create(): View
    {
        return view('permit-types.create', ['permitType' => new PermitType]);
    }

    public function store(PermitTypeRequest $request): RedirectResponse
    {
        PermitType::query()->create($request->validated());

        return redirect()->route('permit-types.index')->with('success', 'Tipo de permiso creado.');
    }

    public function edit(PermitType $permitType): View
    {
        return view('permit-types.edit', compact('permitType'));
    }

    public function update(PermitTypeRequest $request, PermitType $permitType): RedirectResponse
    {
        $permitType->update($request->validated());

        return redirect()->route('permit-types.index')->with('success', 'Tipo de permiso actualizado.');
    }

    public function destroy(PermitType $permitType): RedirectResponse
    {
        if ($permitType->permits()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay permisos que usan este tipo.');
        }

        $permitType->delete();

        return redirect()->route('permit-types.index')->with('success', 'Tipo de permiso eliminado.');
    }
}
