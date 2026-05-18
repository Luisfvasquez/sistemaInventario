<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BulkType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relación con las presentaciones
    public function bulks()
    {
        return $this->hasMany(Bulk::class);
    }
}
