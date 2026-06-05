<?php

namespace App\Livewire;

use App\Models\Inventory;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination; // Importamos el atributo Url

class InventoryIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $searchTerm = '%'.trim($this->search).'%';

        $inventories = Inventory::with(['product.category'])
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->whereHas('product', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('sku', 'like', $searchTerm);
                });
            })
            ->paginate(10);

        $totalOutOfStock = Inventory::where('stock', '<=', 0)->count();
        $totalLowStock = Inventory::whereColumn('stock', '<=', 'minimum_stock')
            ->where('stock', '>', 0)
            ->count();
        $totalStock = Inventory::sum('stock');

        return view('livewire.inventory-index', [
            'inventories' => $inventories,
            'totalOutOfStock' => $totalOutOfStock,
            'totalLowStock' => $totalLowStock,
            'totalStock' => $totalStock,
        ]);
    }
}
