<?php

namespace App\Livewire;

use App\Models\Inventory;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class InventoryIndex extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    public function updatedSearch($value)
    {
        $this->search = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-\.\_\@]/u', '', $value);
        $this->resetPage();
    }

    #[On('purchase-created')]
    #[On('purchase-cancelled')]
    #[On('sale-created')]
    #[On('sale-rejected')]
    #[On('inventory-updated')]
    #[On('echo:purchases,PurchaseCreated')]
    #[On('echo:purchases,PurchaseCancelled')]
    #[On('echo:sales,SaleCreated')]
    #[On('echo:sales,SaleRejected')]
    #[On('echo:inventory,InventoryUpdated')]
    public function refreshInventoryStats()
    {
        // El re-render automático actualizará las métricas de la vista
    }

    public function getTotalOutOfStock()
    {
        return Inventory::where('stock', '<=', 0)->count();
    }

    public function getTotalLowStock()
    {
        return Inventory::whereColumn('stock', '<=', 'minimum_stock')
            ->where('stock', '>', 0)
            ->count();
    }

    public function getTotalStock()
    {
        // Los productos por gramo se suman como '1' por cada registro encontrado, el resto suma su stock normal
        return Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->sum(DB::raw("CASE WHEN products.unit_type = 'gram' THEN 1 ELSE inventories.stock END"));
    }

    public function render()
    {
        $searchTerm = '%'.trim($this->search).'%';

        $inventories = Inventory::select('inventories.*')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->with(['product.category'])
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where(function ($subQ) use ($searchTerm) {
                    $subQ->where('products.name', 'like', $searchTerm)
                         ->orWhere('products.sku', 'like', $searchTerm);
                });
            })
            ->paginate(10);

        return view('livewire.inventory-index', [
            'inventories' => $inventories,
            'totalOutOfStock' => $this->getTotalOutOfStock(),
            'totalLowStock' => $this->getTotalLowStock(),
            'totalStock' => $this->getTotalStock(),
        ]);
    }
}
