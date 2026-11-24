<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $leaveTypes = LeaveType::query()->withCount('medicalLeaves')->orderBy('name')->paginate(12);

        return view('leave-types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        return view('leave-types.create', ['leaveType' => new LeaveType]);
    }

    public function store(LeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::query()->create($request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Tipo de licencia creado.');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(LeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Tipo de licencia actualizado.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        if ($leaveType->medicalLeaves()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay licencias médicas que usan este tipo.');
        }

        $leaveType->delete();

        return redirect()->route('leave-types.index')->with('success', 'Tipo de licencia eliminado.');
    }
}
