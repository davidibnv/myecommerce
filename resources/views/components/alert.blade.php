@props(['type' => 'info', 'position' => 'top-20 right-4'])

@php
    $typeClasses = [
        'info' => 'bg-blue-500 border-blue-700',
        'warning' => 'bg-yellow-500 border-yellow-700',
        'error' => 'bg-red-500 border-red-700',
        'success' => 'bg-green-500 border-green-700'
    ][$type];

    $positionClasses = [
        'bottom-right' => 'bottom-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        'top-right' => 'top-20 right-4',
        'top-left' => 'top-20 left-4'
    ][$position];
@endphp

<div class="{{ $positionClasses }} fixed">
    <div class="{{ $typeClasses }} max-w-xs text-white rounded-lg px-4 py-2">
        {{ $slot }}
    </div>
</div>
