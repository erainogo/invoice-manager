<x-filament::page>
    <form method="POST" action="{{ route('admin.upload-payment-file') }}" enctype="multipart/form-data">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Upload Payment File</label>
                <input type="file" name="file" required class="mt-2 block w-full">
            </div>

            <x-filament::button type="submit">
                Upload
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
