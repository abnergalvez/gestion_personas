<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') — {{ config('app.name') }}</title>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100" x-data="{ sidebar: false, dark: document.documentElement.classList.contains('dark') }" x-on:keydown.escape.window="sidebar = false">
    <div class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
        <div class="h-1 bg-gradient-to-r from-brand-600 via-brand-700 to-accent-500"></div>
        <header class="flex h-16 items-center gap-3 px-3 sm:px-5">
            <button type="button" class="inline-flex rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-200 dark:hover:bg-slate-800" x-on:click.stop="sidebar = !sidebar" x-bind:aria-expanded="sidebar.toString()" aria-controls="sidebar">
                <span class="sr-only">Abrir menú</span>
                <x-icon name="menu" x-show="!sidebar" />
                <x-icon name="close" x-show="sidebar" x-cloak />
            </button>
            <a href="{{ route('dashboard') }}" class="min-w-0">
                <img src="{{ asset('images/logo-seremi.jpg') }}" alt="SEREMI Región de Ñuble. Ministerio de Vivienda y Urbanismo. Trabajando para usted." class="h-10 w-36 rounded-md bg-white object-cover object-left sm:h-12 sm:w-auto sm:max-w-sm sm:object-contain">
            </a>
            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" x-on:click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')" x-bind:aria-label="dark ? 'Activar tema claro' : 'Activar tema oscuro'">
                    <x-icon name="moon" x-show="!dark" />
                    <x-icon name="sun" x-show="dark" x-cloak />
                </button>
                <div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
                    <button type="button" class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 hover:bg-slate-100 dark:hover:bg-slate-800" x-on:click="open = !open" x-bind:aria-expanded="open.toString()">
                        <x-avatar :name="auth()->user()->name" :path="auth()->user()->avatar_path" size="sm" class="!rounded-full" />
                        <span class="hidden max-w-40 truncate text-sm font-semibold sm:block">{{ auth()->user()->name }}</span>
                    </button>
                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="absolute right-0 z-50 mt-2 w-72 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                            <p class="truncate font-semibold">{{ auth()->user()->name }}</p>
                            <p class="truncate text-sm text-slate-500">{{ auth()->user()->email }}</p>
                            <p class="mt-2"><x-badge :tone="auth()->user()->role->tone()">{{ auth()->user()->role->label() }}</x-badge></p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">Mi ficha</a>
                        <a href="{{ route('profile.edit') }}#contrasena" class="block px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">Cambiar contraseña</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-200 dark:border-slate-700">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-left text-sm font-semibold text-accent-600 hover:bg-accent-50 dark:text-red-300 dark:hover:bg-slate-800">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <div x-show="sidebar" x-cloak x-on:click="sidebar = false" class="fixed inset-0 top-[4.25rem] z-30 bg-slate-900/50 lg:hidden"></div>
    <aside id="sidebar" x-cloak class="fixed top-[4.25rem] bottom-0 left-0 z-40 w-72 overflow-y-auto border-r border-slate-200 bg-white transition-transform lg:hidden dark:border-slate-800 dark:bg-slate-900" x-bind:class="sidebar ? 'translate-x-0' : '-translate-x-full'" x-bind:inert="!sidebar" x-on:click="if ($event.target.closest('a')) sidebar = false">
        @include('layouts.navigation')
    </aside>

    <div class="lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="sticky top-[4.25rem] hidden h-[calc(100vh-4.25rem)] overflow-y-auto border-r border-slate-200 bg-white lg:block dark:border-slate-800 dark:bg-slate-900">
            @include('layouts.navigation')
        </aside>
        <main class="min-w-0 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-100" role="alert">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
