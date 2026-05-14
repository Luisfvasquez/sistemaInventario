<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class OrderDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'order_id',
        'product_id',
        'bulk_id',
        'quantity',
        'base_quantity',
        'unit_price',
        'unit_cost',
        'subtotal',
        'discount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'base_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function bulk()
    {
        return $this->belongsTo(Bulk::class);
    }
}
