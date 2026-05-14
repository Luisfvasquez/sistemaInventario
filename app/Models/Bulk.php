<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bulk extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'name',
        'description',
        'quantity',
        'purchase_price',
        'sale_price',
        'sku',
        'sku_barcode',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
