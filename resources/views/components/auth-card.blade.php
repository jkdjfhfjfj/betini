<div {{ $attributes->merge(['class' => 'min-h-screen flex flex-col items-center pt-6 sm:pt-0']) }}>
    @isset($logo)
        <div class="mb-6">
            {{ $logo }}
        </div>
    @endisset

    <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
