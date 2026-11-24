<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToEmployeeContext;
use App\Http\Requests\TrainingCourseRequest;
use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingCourseController extends Controller
{
    use RedirectsToEmployeeContext;

    public function index(Request $request): View
    {
        $courses = TrainingCourse::query()
            ->with('employee')
            ->when($request->integer('employee_id'), fn ($query, $id) => $query->where('employee_id', $id))
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->latest('certified_on')
            ->paginate(12)
            ->withQueryString();

        return view('training-courses.index', [
            'courses' => $courses,
            'employees' => Employee::options(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('training-courses.create', $this->formData(new TrainingCourse([
            'employee_id' => $request->integer('employee_id') ?: null,
        ]), $request->integer('employee_id') ?: null));
    }

    public function store(TrainingCourseRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['document', 'remove_document', 'context_employee_id']);
        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'training-courses');
        TrainingCourse::query()->create($data);

        return $this->redirectAfterSave($request, 'training-courses.index', 'Curso registrado.');
    }

    public function edit(Request $request, TrainingCourse $trainingCourse): View
    {
        return view('training-courses.edit', $this->formData($trainingCourse, $request->integer('employee_id') ?: null));
    }

    public function update(TrainingCourseRequest $request, TrainingCourse $trainingCourse): RedirectResponse
    {
        $data = $request->safe()->except(['document', 'remove_document', 'context_employee_id']);
        $data = StoredUpload::apply($request, $data, 'document', 'document_path', 'training-courses', $trainingCourse->document_path);
        $trainingCourse->update($data);

        return $this->redirectAfterSave($request, 'training-courses.index', 'Curso actualizado.');
    }

    public function destroy(Request $request, TrainingCourse $trainingCourse): RedirectResponse
    {
        $trainingCourse->delete();

        return $this->redirectAfterSave($request, 'training-courses.index', 'Curso eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(TrainingCourse $course, ?int $contextEmployeeId): array
    {
        return [
            'course' => $course,
            'employees' => Employee::options(),
            'contextEmployeeId' => $contextEmployeeId,
        ];
    }
}
