<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NullifiesEmptyFields;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
{
    use NullifiesEmptyFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('internal_code')) {
            $this->merge([
                'internal_code' => strtoupper(trim((string) $this->input('internal_code'))),
            ]);
        }

        $this->nullify(['description']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var LeaveType|null $leaveType */
        $leaveType = $this->route('leave_type');

        return [
            'name' => ['required', 'string', 'max:150'],
            'internal_code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('leave_types', 'internal_code')->ignore($leaveType)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
