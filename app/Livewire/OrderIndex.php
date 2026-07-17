<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class OrderIndex extends Component
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

    #[On('sale-created')]
    #[On('sale-cancelled')]
    #[On('sale-rejected')]
    #[On('order-status-updated')]
    #[On('echo:sales,SaleCreated')]
    #[On('echo:sales,SaleRejected')]
    #[On('echo:orders,OrderStatusUpdated')]
    public function refreshOrders()
    {
        // El re-render automático actualizará las métricas y la tabla
    }

    public function getCompletedOrdersCount()
    {
        return Order::whereIn('status', ['completed', 'delivered'])->count();
    }

    public function getProcessingOrdersCount()
    {
        return Order::whereIn('status', ['pending', 'processing', 'ready_for_pickup'])->count();
    }

    public function getRejectedOrdersCount()
    {
        return Order::where('status', 'cancelled')->count();
    }

    public function render()
    {
        $searchTerm = '%'.trim($this->search).'%';

        $orders = Order::with(['client', 'details.product', 'details.bulk'])
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where('order_number', 'like', $searchTerm)
                    ->orWhereHas('client', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm)
                          ->orWhere('identification', 'like', $searchTerm);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.order-index', [
            'orders' => $orders,
            'completedCount' => $this->getCompletedOrdersCount(),
            'processingCount' => $this->getProcessingOrdersCount(),
            'rejectedCount' => $this->getRejectedOrdersCount(),
        ]);
    }
}
