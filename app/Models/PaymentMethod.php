<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PaymentMethod extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'requires_reference',
        'show_in_checkout',
        'is_active',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
        'show_in_checkout' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class);
    }
}
