<x-filament-panels::page>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
</x-filament-panels::page>
