<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * @var list<string>
     */
    private array $allowedPrefixes = [
        'avatars/',
        'employees/',
        'training-courses/',
        'medical-leaves/',
        'family-dependents/',
        'contracts/',
        'permits/',
        'evaluations/',
    ];

    public function show(Request $request): StreamedResponse
    {
        $path = str_replace('\\', '/', $request->string('path')->toString());

        if (! $this->isAllowed($path) || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, max-age=0, no-store',
        ]);
    }

    private function isAllowed(string $path): bool
    {
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return false;
        }

        foreach ($this->allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
