<div x-data>
    <p class="text-xl text-gray-700">Color:</p>
    <select dusk="color-dropdown" wire:model="color_id" class="form-control w-full">
        <option value="" selected disabled>Seleccionar un color</option>
        @foreach ($colors as $color)
            <option value="{{$color->id}}">{{ __(ucfirst($color->name)) }}</option>
        @endforeach
    </select>

    <p dusk="available-stock" class="text-gray-700 my-4">
        <span class="font-semibold text-lg">Stock disponible:</span>
        @if($quantity)
            {{ $quantity }}
        @else
            {{ $product->stock }}
        @endif
    </p>

    <div class="flex">
        <div class="mr-4">
            <x-jet-secondary-button
                dusk="decrease-quantity-btn"
                disabled
                x-bind:disabled="$wire.qty <= 1"
                wire:loading.attr="disabled"
                wire:target="decrement"
                wire:click="decrement">
                -
            </x-jet-secondary-button>
            <span dusk="product-quantity" class="mx-2 text-gray-700">{{ $qty }}</span>
            <x-jet-secondary-button
                dusk="increase-quantity-btn"
                x-bind:disabled="$wire.qty >= $wire.quantity"
                wire:loading.attr="disabled"
                wire:target="increment"
                wire:click="increment">
                +
            </x-jet-secondary-button>
        </div>
        <div class="flex-1">
            <x-button
                dusk="add-to-cart-btn"
                x-bind:disabled="$wire.qty > $wire.quantity"
                x-bind:disabled="!$wire.quantity"
                wire:click="addItem"
                wire:loading.attr="disabled"
                wire:target="addItem"
                class="w-full"
                color="orange">
                Agregar al carrito de compras
            </x-button>
        </div>
    </div>
</div>
