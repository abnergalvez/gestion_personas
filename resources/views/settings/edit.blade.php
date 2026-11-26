@extends('layouts.app')

@section('title', 'Configuración general')

@section('content')
    <x-page-header title="Configuración general" description="Correo, avisos y parámetros que usa el resto del sistema. Solo el super administrador puede cambiarlos." />

    @if ($errors->any())
        <div class="mb-4"><x-form.errors /></div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="grid gap-4" x-data="{ mailer: @js(old('mailer', $mailer)) }">
        @csrf
        @method('PUT')

        <x-card>
            <h2 class="font-semibold">Correo</h2>
            <p class="mt-1 text-sm text-slate-500">El canal elegido reemplaza al del servidor cuando guarda esta pantalla. Las claves no se vuelven a mostrar.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.select name="mailer" label="Canal" :options="$mailers" :selected="old('mailer', $mailer)" :placeholder="false" x-model="mailer" />
                <x-form.input name="from_name" label="Nombre del remitente" :value="old('from_name', $fromName)" />
                <x-form.input name="from_address" type="email" label="Correo del remitente" :value="old('from_address', $fromAddress)" />
            </div>

            <div x-show="mailer === 'log'" x-cloak class="mt-4 text-sm text-slate-500">Los mensajes quedan en el registro del sistema y no salen a internet.</div>

            <div x-show="mailer === 'brevo'" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.input name="brevo_username" type="email" label="Usuario SMTP de Brevo" :value="old('brevo_username', $brevoUsername)" />
                <x-form.input name="brevo_password" type="password" label="Clave SMTP de Brevo" autocomplete="new-password" :hint="$secrets['brevo_password'] ? 'Ya hay una clave guardada. Déjela vacía para conservarla.' : 'Es la clave SMTP de Brevo, no la de la cuenta.'" />
            </div>

            <div x-show="mailer === 'gmail'" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.input name="gmail_username" type="email" label="Buzón de Gmail" :value="old('gmail_username', $gmailUsername)" />
                <x-form.input name="gmail_client_id" label="Cliente OAuth" :value="old('gmail_client_id', $gmailClientId)" />
                <x-form.input name="gmail_client_secret" type="password" label="Secreto del cliente" autocomplete="new-password" :hint="$secrets['gmail_client_secret'] ? 'Ya hay un secreto guardado.' : null" />
                <x-form.input name="gmail_refresh_token" type="password" label="Token de actualización" autocomplete="new-password" :hint="$secrets['gmail_refresh_token'] ? 'Ya hay un token guardado.' : 'Alcance https://mail.google.com/.'" />
            </div>

            <div x-show="mailer === 'outlook'" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.input name="outlook_username" type="email" label="Buzón de Outlook" :value="old('outlook_username', $outlookUsername)" />
                <x-form.input name="outlook_tenant" label="Directorio (tenant)" :value="old('outlook_tenant', $outlookTenant)" />
                <x-form.select name="outlook_grant" label="Tipo de acceso" :options="$grants" :selected="old('outlook_grant', $outlookGrant)" :placeholder="false" />
                <x-form.input name="outlook_client_id" label="Cliente de la aplicación" :value="old('outlook_client_id', $outlookClientId)" />
                <x-form.input name="outlook_client_secret" type="password" label="Secreto del cliente" autocomplete="new-password" :hint="$secrets['outlook_client_secret'] ? 'Ya hay un secreto guardado.' : null" />
                <x-form.input name="outlook_refresh_token" type="password" label="Token de actualización" autocomplete="new-password" :hint="$secrets['outlook_refresh_token'] ? 'Ya hay un token guardado. No hace falta si usa credencial de aplicación.' : 'Solo si el acceso es con token de actualización.'" />
            </div>

            <div x-show="mailer === 'smtp'" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-form.input name="smtp_host" label="Servidor" :value="old('smtp_host', $smtpHost)" placeholder="smtp.ejemplo.cl" />
                <x-form.input name="smtp_port" type="number" label="Puerto" :value="old('smtp_port', $smtpPort)" min="1" max="65535" />
                <x-form.input name="smtp_username" label="Usuario" :value="old('smtp_username', $smtpUsername)" />
                <x-form.input name="smtp_password" type="password" label="Clave" autocomplete="new-password" :hint="$secrets['smtp_password'] ? 'Ya hay una clave guardada.' : null" />
            </div>
        </x-card>

        <x-card>
            <h2 class="font-semibold">Notificaciones</h2>
            <p class="mt-1 text-sm text-slate-500">Los avisos salen al correo indicado. Licencias, permisos y calificaciones se avisan al registrarlos. Contratos, cumpleaños y cursos se resumen una vez al día, a las 08:00, si el servidor ejecuta el programador.</p>
            <div class="mt-4 grid gap-4">
                <x-form.textarea name="recipients" label="Destinatarios" :value="old('recipients', $recipients)" hint="Un correo por línea. Reciben todos los avisos activados." />
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-form.checkbox name="notify_contracts" label="Contratos por vencer" :checked="$notices['notify_contracts']" hint="Usa los días de anticipación de más abajo." />
                    <x-form.checkbox name="notify_birthdays" label="Cumpleaños de la semana" :checked="$notices['notify_birthdays']" hint="La misma semana sábado a viernes del inicio." />
                    <x-form.checkbox name="notify_courses" label="Cursos por vencer" :checked="$notices['notify_courses']" />
                    <x-form.checkbox name="notify_medical_leaves" label="Nueva licencia médica" :checked="$notices['notify_medical_leaves']" />
                    <x-form.checkbox name="notify_permits" label="Nuevo permiso" :checked="$notices['notify_permits']" />
                    <x-form.checkbox name="notify_evaluations" label="Calificación registrada" :checked="$notices['notify_evaluations']" />
                </div>
            </div>
        </x-card>

        <x-card>
            <h2 class="font-semibold">Parámetros de gestión</h2>
            <p class="mt-1 text-sm text-slate-500">Estos valores alimentan el inicio, las cifras de cada sección y los avisos.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-form.input name="contract_days" type="number" label="Días de aviso de contratos" :value="old('contract_days', $contractDays)" min="1" max="365" />
                <x-form.input name="course_days" type="number" label="Días de aviso de cursos" :value="old('course_days', $courseDays)" min="1" max="365" />
                <x-form.input name="low_score" type="number" label="Nota de alerta" :value="old('low_score', $lowScore)" min="1" max="7" step="0.1" hint="Las calificaciones del periodo bajo esta nota se cuentan aparte." />
            </div>
        </x-card>

        <div class="flex justify-end">
            <x-button>Guardar configuración</x-button>
        </div>
    </form>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <x-card>
            <h2 class="font-semibold">Probar el correo</h2>
            <p class="mt-1 text-sm text-slate-500">Usa la configuración ya guardada.</p>
            <form method="POST" action="{{ route('settings.mail-test') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="min-w-0 flex-1">
                    <x-form.input name="test_email" type="email" label="Enviar prueba a" :value="old('test_email', $fromAddress)" />
                </div>
                <x-button variant="secondary">Enviar prueba</x-button>
            </form>
        </x-card>
        <x-card>
            <h2 class="font-semibold">Enviar avisos ahora</h2>
            <p class="mt-1 text-sm text-slate-500">Dispara el resumen de contratos, cumpleaños y cursos activados, aunque hoy ya se haya enviado.</p>
            <form method="POST" action="{{ route('settings.notices') }}" class="mt-4">
                @csrf
                <x-button variant="secondary">Enviar avisos ahora</x-button>
            </form>
        </x-card>
    </div>
@endsection
