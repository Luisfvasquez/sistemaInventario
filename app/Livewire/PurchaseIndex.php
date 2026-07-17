<?php

namespace App\Livewire;

use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

class PurchaseIndex extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch($value)
    {
        $this->search = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-\.\_\@]/u', '', $value);
        $this->resetPage();
    }

    #[On('purchase-created')]
    #[On('purchase-cancelled')]
    #[On('echo:purchases,PurchaseCreated')]
    #[On('echo:purchases,PurchaseCancelled')]
    public function refreshPurchases()
    {
        // El re-render del componente actualizará automáticamente los listados y métricas
    }

    public function getTotalPurchases()
    {
        return Purchase::count();
    }

    public function getTotalInvestmentBs()
    {
        return Purchase::sum('total');
    }

    public function getTotalInvestmentUsd()
    {
        // Evita división por cero si alguna tasa es errónea, aunque teóricamente no debería pasar
        return Purchase::sum(DB::raw('CASE WHEN exchange_rate > 0 THEN total / exchange_rate ELSE 0 END'));
    }

    public function render()
    {
        $searchTerm = '%'.trim($this->search).'%';

        $purchases = Purchase::with(['supplier', 'user', 'details.product', 'details.bulk'])
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('purchase_code', 'like', $searchTerm)
                    ->orWhereHas('supplier', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm)
                          ->orWhere('rif', 'like', $searchTerm);
                    });
            })
            ->orderBy('purchased_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.purchase-index', [
            'purchases' => $purchases,
            'totalPurchases' => $this->getTotalPurchases(),
            'totalInvestmentBs' => $this->getTotalInvestmentBs(),
            'totalInvestmentUsd' => $this->getTotalInvestmentUsd(),
        ]);
    }
}
