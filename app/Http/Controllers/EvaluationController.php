<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationProcess;
use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\EvaluationRequest;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        $evaluations = Evaluation::query()
            ->with('employee')
            ->when($request->integer('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->string('process')->value(), fn ($query, $process) => $query->where('process', $process))
            ->orderByDesc('period_end_year')
            ->orderByDesc('period_start_year')
            ->paginate(12)
            ->withQueryString();

        return view('evaluations.index', [
            'evaluations' => $evaluations,
            'employees' => Employee::options(),
            'processes' => EvaluationProcess::options(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('evaluations.create', $this->formData(new Evaluation([
            'employee_id' => $request->integer('employee_id') ?: null,
            'period_start_year' => now()->year - 1,
            'period_end_year' => now()->year,
        ]), $request->integer('employee_id') ?: null));
    }

    public function store(EvaluationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['document', 'remove_document', 'context_employee_id']);
        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'evaluations');
        Evaluation::query()->create($data);

        return $this->redirectAfterSave($request, 'evaluations.index', 'Calificación registrada.');
    }

    public function edit(Request $request, Evaluation $evaluation): View
    {
        return view('evaluations.edit', $this->formData($evaluation, $request->integer('employee_id') ?: null));
    }

    public function update(EvaluationRequest $request, Evaluation $evaluation): RedirectResponse
    {
        $data = $request->safe()->except(['document', 'remove_document', 'context_employee_id']);
        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'evaluations', $evaluation->document_path);
        $evaluation->update($data);

        return $this->redirectAfterSave($request, 'evaluations.index', 'Calificación actualizada.');
    }

    public function destroy(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $evaluation->delete();

        return $this->redirectAfterSave($request, 'evaluations.index', 'Calificación eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Evaluation $evaluation, ?int $contextEmployeeId): array
    {
        return [
            'evaluation' => $evaluation,
            'employees' => Employee::options(),
            'processes' => EvaluationProcess::options(),
            'contextEmployeeId' => $contextEmployeeId,
        ];
    }
}
