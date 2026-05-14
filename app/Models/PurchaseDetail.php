<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PurchaseDetail extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'bulk_id',
        'quantity',
        'base_quantity',
        'unit_cost',
        'subtotal',
        'previous_cost',
        'new_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'base_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'previous_cost' => 'decimal:2',
        'new_cost' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
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
