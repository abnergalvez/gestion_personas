@props(['stats'])

<div class="mb-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
    @foreach ($stats as $stat)
        <x-stat compact :label="$stat['label']" :value="$stat['value']" />
    @endforeach
</div>
