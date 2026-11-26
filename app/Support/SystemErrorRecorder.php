<?php

namespace App\Support;

use App\Enums\ErrorSeverity;
use App\Models\SystemError;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class SystemErrorRecorder
{
    private const SKIPPED_STATUSES = [401, 403, 404, 419, 422, 429];

    private static bool $writing = false;

    public static function fromResponse(Response $response, Throwable $exception, Request $request): void
    {
        if (self::$writing) {
            return;
        }

        $status = $response->getStatusCode();

        if (! self::shouldStore($status)) {
            return;
        }

        self::$writing = true;

        try {
            $message = trim($exception->getMessage());

            if ($message === '') {
                $message = Response::$statusTexts[$status] ?? 'Error del sistema';
            }

            $file = str_replace('\\', '/', $exception->getFile());
            $base = str_replace('\\', '/', base_path()).'/';

            if (str_starts_with($file, $base)) {
                $file = substr($file, strlen($base));
            }

            $user = $request->user();

            SystemError::query()->create([
                'severity' => $status >= 500 ? ErrorSeverity::Grave : ErrorSeverity::Notice,
                'status_code' => $status,
                'exception' => mb_substr($exception::class, 0, 255),
                'message' => mb_substr($message, 0, 2000),
                'file' => $file === '' ? null : mb_substr($file, 0, 255),
                'line' => $exception->getLine() > 0 ? $exception->getLine() : null,
                'method' => mb_substr($request->method(), 0, 10),
                'url' => mb_substr($request->fullUrl(), 0, 2000),
                'user_id' => $user instanceof User ? $user->id : null,
                'ip_address' => $request->ip(),
                'trace' => mb_substr($exception->getTraceAsString(), 0, 20000),
            ]);
        } catch (Throwable) {
            // El fallo original tiene que llegar igual al navegador.
        } finally {
            self::$writing = false;
        }
    }

    /**
     * @return array{error: ?SystemError, count: int}
     */
    public static function alert(): array
    {
        try {
            $query = SystemError::query()->pendingGrave();

            return [
                'error' => (clone $query)->latest('id')->first(),
                'count' => (clone $query)->count(),
            ];
        } catch (Throwable) {
            return ['error' => null, 'count' => 0];
        }
    }

    private static function shouldStore(int $status): bool
    {
        if (in_array($status, self::SKIPPED_STATUSES, true)) {
            return false;
        }

        return $status === 400 || $status >= 402;
    }
}
