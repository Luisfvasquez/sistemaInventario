<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Product extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'category_id',
        'uuid',
        'name',
        'slug',
        'description',
        'sku',
        'sku_barcode',
        'brand',
        'cost',
        'price',
        'unit_type',
        'track_inventory',
        'exchange_rate',
        'allow_negative_stock',
        'has_variants',
        'status',
        'created_by',
    ];

    protected $casts = [
        'track_inventory' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'has_variants' => 'boolean',
    ];

    // Atributos adicionales para mostrar en vistas
    protected $appends = [
        'display_price',
        'unit_label',
    ];

    public function getTotalUsdAttribute()
    {
        if ($this->exchange_rate && $this->exchange_rate > 0) {
            return round($this->total / $this->exchange_rate, 2);
        }

        return 0;
    }

    public function getSubtotalUsdAttribute()
    {
        if ($this->exchange_rate && $this->exchange_rate > 0) {
            return round($this->subtotal / $this->exchange_rate, 2);
        }

        return 0;
    }

    /**
     * Retorna el precio "display" (por kilo si es pesable, por unidad si no).
     */
    public function getDisplayPriceAttribute(): float
    {
        if ($this->unit_type === 'gram') {
            return round($this->price * 1000, 2);
        }

        return (float) $this->price;
    }

    /**
     * Retorna el costo "display" (por kilo si es pesable, por unidad si no).
     */
    public function getDisplayCostAttribute(): float
    {
        if ($this->unit_type === 'gram') {
            return round($this->cost * 1000, 2);
        }

        return (float) $this->cost;
    }

    /**
     * Etiqueta de la unidad para mostrar en vistas.
     */
    public function getUnitLabelAttribute(): string
    {
        return $this->unit_type === 'gram' ? '/Kg' : '/Und';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function bulks()
    {
        return $this->hasMany(Bulk::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
