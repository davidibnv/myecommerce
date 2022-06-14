<?php

namespace App\Http\Livewire\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Filters\ProductFilter;
use App\Models\Size;
use App\Models\Subcategory;
use Livewire\Component;
use Livewire\WithPagination;

class ShowProducts extends Component
{
    use WithPagination;

    public $rowsPerPage = 10, $fieldToOrder, $sortDirection = 'DESC';

    public $columns = ['name', 'vendidos', 'category', 'subcategory', 'brand', 'sizes', 'colors', 'stock', 'status', 'price', 'created_at'];
    public $selectedColumns = [];

    public $categories, $subcategories = [], $brands, $colors, $sizes;

    public $filters = [
        'search',
        'category_id' => '',
        'subcategory_id' => '',
        'brand_id' => '',
        'status',
        'colors' => [],
        'sizes' => [],
        'stock',
        'from' => null,
        'to' => null,
        'price',
    ];

    public function mount()
    {
        $this->categories = Category::all();

        $this->brands = Brand::all();

        $this->colors = Color::all();

        $this->sizes = Size::selectRaw('DISTINCT name')->pluck('name');

        $this->selectedColumns = ['name', 'sold', 'category', 'subcategory', 'brand', 'sizes', 'colors', 'stock', 'status'];
    }

    public function updatedFiltersCategoryId($value)
    {
        $this->subcategories = Subcategory::where('category_id', $value)->get();

        $this->filters['subcategory_id'] = '';
    }

    public function showColumn($column)
    {
        return in_array($column, $this->selectedColumns);
    }

    public function updatingFieldToOrder($column)
    {
        if ($this->fieldToOrder === $column) {
            $this->sortDirection = $this->swapSortDirection($column);
        } else {
            if ($column === 'sizes' || $column === 'colors') {
                $this->sortDirection = 'DESC';
            } else {
                $this->sortDirection = 'ASC';
            }
        }
    }

    public function swapSortDirection()
    {
        return $this->sortDirection === 'ASC' ? 'DESC': 'ASC';
    }

    public function updatedFilters() {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset('filters');

        $this->resetPage();
    }

    public function render(ProductFilter $productFilter)
    {
        $products = Product::filterBy($productFilter, $this->filters)
            ->when($this->fieldToOrder, function ($query) use ($productFilter) {
                $query->orderByField($productFilter, $this->fieldToOrder, $this->sortDirection);
            })
            ->paginate($this->rowsPerPage);

        return view('livewire.admin.show-products', compact('products'))->layout('layouts.admin');
    }
}
