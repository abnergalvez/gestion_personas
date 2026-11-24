@extends('layouts.guest')

@section('title', 'Ingreso')

@section('content')
    <div class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden bg-brand-800 text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-accent-500"></div>
            <div>
                <p class="text-sm font-semibold tracking-wide text-blue-100">SEREMI Región de Ñuble</p>
                <p class="mt-1 text-sm text-blue-100/80">Ministerio de Vivienda y Urbanismo</p>
                <h1 class="mt-10 max-w-md text-4xl font-semibold tracking-tight">Gestión de personas</h1>
                <p class="mt-4 max-w-md text-blue-100/90">Registro interno de funcionarios, organización, contratos, licencias, permisos y calificaciones.</p>
            </div>
            <img src="{{ asset('images/logo-seremi.jpg') }}" alt="Trabajando para usted" class="w-full max-w-md rounded-xl bg-white object-contain">
        </section>
        <section class="flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <img src="{{ asset('images/logo-seremi.jpg') }}" alt="SEREMI Región de Ñuble" class="mb-6 h-14 w-auto max-w-full rounded-md bg-white object-contain object-left lg:hidden">
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Ingreso al sistema</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Acceso reservado a las cuentas de administración. No hay registro público.</p>
                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
                    @csrf
                    <x-form.input name="email" type="email" label="Correo" value="" autocomplete="username" required />
                    <x-form.input name="password" type="password" label="Contraseña" autocomplete="current-password" required />
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600">
                        Recordar este equipo
                    </label>
                    <x-button class="w-full">Ingresar</x-button>
                </form>
            </div>
        </section>
    </div>
@endsection
