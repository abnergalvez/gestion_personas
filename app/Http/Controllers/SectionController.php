<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $sections = Section::query()
            ->with(['department', 'head', 'deputy'])
            ->withCount(['employees', 'analysts'])
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($request->integer('department_id'), fn ($query, $id) => $query->where('department_id', $id))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('sections.index', [
            'sections' => $sections,
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('sections.create', $this->formData(new Section));
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        $section = Section::query()->create($request->safe()->except('analyst_ids'));
        $section->analysts()->sync($request->input('analyst_ids', []));

        return redirect()->route('sections.index')->with('success', 'Sección creada.');
    }

    public function edit(Section $section): View
    {
        $section->load('analysts');

        return view('sections.edit', $this->formData($section));
    }

    public function update(SectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->safe()->except('analyst_ids'));
        $section->analysts()->sync($request->input('analyst_ids', []));

        return redirect()->route('sections.index')->with('success', 'Sección actualizada.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Sección eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Section $section): array
    {
        return [
            'section' => $section,
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
            'employees' => Employee::options(),
        ];
    }
}
