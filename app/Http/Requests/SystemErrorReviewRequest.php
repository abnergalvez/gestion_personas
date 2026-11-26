<?php

namespace App\Http\Requests;

use App\Enums\ErrorReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SystemErrorReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'review' => ['required', Rule::in(ErrorReview::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'review.required' => 'Indique si el error quedó visto o solucionado.',
            'review.in' => 'La revisión indicada no es válida.',
        ];
    }
}
