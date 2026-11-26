<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationProcess;
use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\EvaluationRequest;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Support\EvaluationListing;
use App\Support\ReportFilters;
use App\Support\SectionStats;
use App\Support\SpreadsheetExport;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvaluationController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        return view('evaluations.index', [
            'evaluations' => EvaluationListing::from($request)->paginate(),
            'processes' => EvaluationProcess::options(),
            'stats' => SectionStats::evaluations(),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return SpreadsheetExport::download(
            'calificaciones-'.now()->format('Y-m-d').'.xlsx',
            [...ReportFilters::IDENTITY, 'Año de inicio', 'Año de término', 'Proceso', 'Nota', 'Descripción', 'Documento'],
            EvaluationListing::from($request)->exportRows(),
        );
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
