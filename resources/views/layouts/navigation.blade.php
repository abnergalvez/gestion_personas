<nav class="space-y-6 px-3 py-4" aria-label="Secciones">
    @foreach (\App\Support\Navigation::groups() as $group)
        <div>
            <p class="px-3 text-xs font-semibold tracking-wider text-slate-400 uppercase">{{ $group['label'] }}</p>
            <div class="mt-2 space-y-1">
                @foreach ($group['items'] as $item)
                    @php $active = request()->routeIs($item['active']); @endphp
                    <a href="{{ route($item['route']) }}" @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium',
                        'bg-brand-50 text-brand-800 dark:bg-brand-600/20 dark:text-white' => $active,
                        'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! $active,
                    ]) @if ($active) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
    <p class="px-3 text-xs text-slate-400">Uso interno de la administración.</p>
</nav>
