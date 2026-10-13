@props(['errors'])

@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'text-sm text-red-600']) }} role="alert">
        <div>{{ __('Whoops! Something went wrong.') }}</div>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
