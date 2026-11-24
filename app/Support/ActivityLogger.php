<?php

namespace App\Support;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

final class ActivityLogger
{
    private static bool $writing = false;

    /**
     * Pantallas sueltas. El tercer texto se usa cuando la operación no se guardó.
     *
     * @var array<string, array{0: ActivityAction, 1: string, 2?: string}>
     */
    private const PAGES = [
        'login' => [ActivityAction::Viewed, 'Abrió la pantalla de ingreso.'],
        'dashboard' => [ActivityAction::Viewed, 'Consultó el inicio.'],
        'files.show' => [ActivityAction::Downloaded, 'Descargó un archivo.'],
        'profile.edit' => [ActivityAction::Viewed, 'Consultó su ficha.'],
        'profile.update' => [ActivityAction::Updated, 'Actualizó su ficha.', 'No se pudo actualizar su ficha.'],
        'profile.password' => [ActivityAction::Updated, 'Cambió su contraseña.', 'No se pudo cambiar la contraseña.'],
        'activity-logs.index' => [ActivityAction::Viewed, 'Consultó el registro de acciones.'],
        'activity-logs.show' => [ActivityAction::Viewed, 'Consultó el detalle de una acción registrada.'],
        'contracts.documents.store' => [ActivityAction::Created, 'Agregó un documento a un contrato.', 'No se pudo agregar un documento al contrato.'],
        'contracts.documents.destroy' => [ActivityAction::Deleted, 'Eliminó un documento de un contrato.', 'No se pudo eliminar el documento del contrato.'],
    ];

    /**
     * @var array<string, array{0: string, 1: string}>
     */
    private const RESOURCES = [
        'employees' => ['el listado de funcionarios', 'un funcionario'],
        'departments' => ['el listado de departamentos', 'un departamento'],
        'sections' => ['el listado de secciones', 'una sección'],
        'training-courses' => ['el listado de cursos y capacitaciones', 'un curso o capacitación'],
        'medical-leaves' => ['el listado de licencias médicas', 'una licencia médica'],
        'family-dependents' => ['el listado de cargas familiares', 'una carga familiar'],
        'contracts' => ['el listado de contratos', 'un contrato'],
        'permits' => ['el listado de permisos', 'un permiso'],
        'evaluations' => ['el listado de calificaciones', 'una calificación'],
        'leave-types' => ['el listado de tipos de licencia', 'un tipo de licencia'],
        'permit-types' => ['el listado de tipos de permiso', 'un tipo de permiso'],
        'contract-document-types' => ['el listado de tipos de documento', 'un tipo de documento'],
        'users' => ['el listado de usuarios', 'un usuario'],
    ];

    /**
     * @var array<string, array{0: ActivityAction, 1: string, 2?: string}>
     */
    private const VERBS = [
        'index' => [ActivityAction::Viewed, 'Consultó %s.'],
        'create' => [ActivityAction::Viewed, 'Abrió el formulario para registrar %s.'],
        'store' => [ActivityAction::Created, 'Registró %s.', 'No se pudo registrar %s.'],
        'show' => [ActivityAction::Viewed, 'Consultó %s.'],
        'edit' => [ActivityAction::Viewed, 'Abrió la edición de %s.'],
        'update' => [ActivityAction::Updated, 'Actualizó %s.', 'No se pudo actualizar %s.'],
        'destroy' => [ActivityAction::Deleted, 'Eliminó %s.', 'No se pudo eliminar %s.'],
    ];

    public static function login(Login $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        self::write(ActivityAction::Login, 'Ingresó al sistema.', $user, $user?->email);
    }

    public static function logout(Logout $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        self::write(ActivityAction::Logout, 'Cerró la sesión.', $user, $user?->email);
    }

    public static function failed(Failed $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;
        $email = $event->credentials['email'] ?? null;

        self::write(
            ActivityAction::LoginFailed,
            'Intento de acceso rechazado.',
            $user,
            is_string($email) ? $email : null,
        );
    }

    public static function fromRequest(Request $request, Response $response): void
    {
        $route = $request->route()?->getName();

        if (in_array($route, ['login.store', 'logout'], true) || $request->is('up')) {
            return;
        }

        $status = $response->getStatusCode();
        [$action, $description] = self::describe($request, $response, $route, $status);
        $user = self::actor($request);

        self::write(
            $action,
            $description,
            $user,
            $user?->email,
            self::subject($request),
            self::context($request),
            $request,
            $status,
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function write(
        ActivityAction $action,
        string $description,
        ?User $user = null,
        ?string $email = null,
        ?Model $subject = null,
        array $properties = [],
        ?Request $request = null,
        ?int $status = null,
    ): void {
        if (self::$writing) {
            return;
        }

        self::$writing = true;

        try {
            $request ??= request();

            ActivityLog::query()->create([
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email ?? $email,
                'action' => $action,
                'description' => mb_substr($description, 0, 250),
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'method' => $request?->method(),
                'url' => $request ? mb_substr($request->fullUrl(), 0, 2000) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => ($agent = $request?->userAgent()) ? mb_substr($agent, 0, 1000) : null,
                'status_code' => $status,
                'properties' => $properties === [] ? null : $properties,
            ]);
        } finally {
            self::$writing = false;
        }
    }

    /**
     * @return array{0: ActivityAction, 1: string}
     */
    private static function describe(Request $request, Response $response, ?string $route, int $status): array
    {
        if ($request->attributes->get('activity.denied')) {
            return [ActivityAction::Denied, 'Intentó operar un mantenedor sin ser super administrador.'];
        }

        if ($route === 'files.show' && $status === 404) {
            return [ActivityAction::Failed, 'Intentó descargar un archivo que no existe o no está permitido.'];
        }

        if ($status === 419) {
            return [ActivityAction::Failed, 'La sesión expiró al enviar el formulario.'];
        }

        if ($status >= 500) {
            return [ActivityAction::Error, 'El sistema encontró un error al procesar la solicitud.'];
        }

        if ($status === 404) {
            return [ActivityAction::Failed, 'Intentó abrir una página que no existe.'];
        }

        if ($request->user() === null && $response->isRedirection() && $route !== 'login') {
            return [ActivityAction::AccessDenied, 'Intentó ingresar sin sesión.'];
        }

        $failed = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $request->hasSession()
            && ($request->session()->get('errors')?->any() || filled($request->session()->get('error')));

        return self::known($route, $failed)
            ?? [self::fallbackAction($request, $failed), 'Realizó una acción sobre '.$request->path().'.'];
    }

    /**
     * @return array{0: ActivityAction, 1: string}|null
     */
    private static function known(?string $route, bool $failed): ?array
    {
        if ($route === null) {
            return null;
        }

        if (isset(self::PAGES[$route])) {
            return self::phrase(self::PAGES[$route], $failed);
        }

        $position = strrpos($route, '.');

        if ($position === false || ! isset(self::RESOURCES[substr($route, 0, $position)], self::VERBS[substr($route, $position + 1)])) {
            return null;
        }

        $resource = self::RESOURCES[substr($route, 0, $position)];
        $verb = self::VERBS[substr($route, $position + 1)];
        $noun = substr($route, $position + 1) === 'index' ? $resource[0] : $resource[1];
        $success = sprintf($verb[1], $noun);
        $failure = isset($verb[2]) ? sprintf($verb[2], $noun) : null;

        return self::phrase([$verb[0], $success, $failure], $failed);
    }

    /**
     * @param  array{0: ActivityAction, 1: string, 2?: string|null}  $definition
     * @return array{0: ActivityAction, 1: string}
     */
    private static function phrase(array $definition, bool $failed): array
    {
        if ($failed && ! empty($definition[2])) {
            return [ActivityAction::Failed, $definition[2]];
        }

        return [$definition[0], $definition[1]];
    }

    private static function fallbackAction(Request $request, bool $failed): ActivityAction
    {
        if ($failed) {
            return ActivityAction::Failed;
        }

        return match ($request->method()) {
            'POST' => ActivityAction::Created,
            'PUT', 'PATCH' => ActivityAction::Updated,
            'DELETE' => ActivityAction::Deleted,
            default => ActivityAction::Viewed,
        };
    }

    private static function actor(Request $request): ?User
    {
        $user = $request->user();

        if ($user instanceof User) {
            return $user;
        }

        $id = $request->attributes->get('activity.actor_id');

        return $id ? User::query()->find($id) : null;
    }

    private static function subject(Request $request): ?Model
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function context(Request $request): array
    {
        $properties = [];

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $input = self::sanitize($request->except(['password', 'password_confirmation', 'current_password', '_token', '_method']));

            if ($input !== []) {
                $properties['input'] = $input;
            }
        }

        $query = self::sanitize($request->query());
        unset($query['_token']);

        if ($query !== []) {
            $properties['query'] = $query;
        }

        return $properties;
    }

    /**
     * @param  array<mixed>  $input
     * @return array<mixed>
     */
    private static function sanitize(array $input): array
    {
        $hidden = ['password', 'password_confirmation', 'current_password', '_token', '_method'];
        $clean = function (mixed $value) use (&$clean, $hidden): mixed {
            if (! is_array($value)) {
                if ($value instanceof UploadedFile) {
                    return $value->getClientOriginalName();
                }

                if (is_string($value) && mb_strlen($value) > 500) {
                    return mb_substr($value, 0, 500).'…';
                }

                return is_object($value) ? null : $value;
            }

            $result = [];

            foreach ($value as $key => $item) {
                if (is_string($key) && in_array($key, $hidden, true)) {
                    continue;
                }

                $result[$key] = $clean($item);
            }

            return $result;
        };

        return $clean($input);
    }
}
