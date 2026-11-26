<?php

namespace App\Http\Controllers;

use App\Enums\ErrorReview;
use App\Enums\ErrorSeverity;
use App\Http\Requests\SystemErrorReviewRequest;
use App\Models\SystemError;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemErrorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $severity = ErrorSeverity::tryFrom($request->string('severity')->toString());
        $review = $request->string('review')->toString();

        $errors = SystemError::query()
            ->select([
                'id', 'severity', 'status_code', 'exception', 'message', 'file', 'line',
                'method', 'url', 'user_id', 'review', 'reviewed_at', 'created_at',
            ])
            ->with('user')
            ->when($severity, fn ($query) => $query->where('severity', $severity))
            ->when($review === 'pending', fn ($query) => $query->whereNull('reviewed_at'))
            ->when(in_array($review, ErrorReview::values(), true), fn ($query) => $query->where('review', $review))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';
                $query->where(function ($query) use ($like) {
                    $query->where('message', 'like', $like)
                        ->orWhere('exception', 'like', $like)
                        ->orWhere('url', 'like', $like)
                        ->orWhere('file', 'like', $like);
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('system-errors.index', [
            'errors' => $errors,
            'severities' => ErrorSeverity::options(),
            'reviews' => [
                'pending' => 'Pendientes',
                'seen' => 'Vistos',
                'resolved' => 'Solucionados',
            ],
        ]);
    }

    public function show(SystemError $systemError): View
    {
        $systemError->load(['user', 'reviewer']);

        return view('system-errors.show', ['error' => $systemError]);
    }

    public function review(SystemErrorReviewRequest $request, SystemError $systemError): RedirectResponse
    {
        if ($systemError->reviewed_at !== null) {
            return back()->with('success', 'Este error ya estaba revisado.');
        }

        $review = ErrorReview::from($request->string('review')->toString());

        $systemError->update([
            'review' => $review,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('success', $review === ErrorReview::Resolved
            ? 'El error quedó marcado como solucionado.'
            : 'El error quedó marcado como visto.');
    }
}
