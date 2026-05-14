<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
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
        'track_inventory',
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
