@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1.5 space-y-0.5 text-sm text-red-600']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-x-1">
                <i class="bx bx-error-circle mt-0.5 text-xs"></i>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
