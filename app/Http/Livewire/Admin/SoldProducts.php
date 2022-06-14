<?php

namespace App\Http\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use Livewire\Component;

class SoldProducts extends Component
{
    public $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function render()
    {
        $quantitySold = 0;

        $orders = Order::select('content')->get()->map(function ($order) {
            return json_decode($order->content, true);
        });

        foreach ($orders as $order) {
            foreach ($order as $data) {
                if ($data['id'] === $this->product->id) {
                    $quantitySold = $quantitySold + $data['qty'];
                }
            }
        }

        return view('livewire.admin.sold-products', compact('quantitySold'));
    }
}
