<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Client extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'last_name',
        'phone',
        'phone_number',
        'email',
        'user_id',
        'identification',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getPhoneAttribute()
    {
        return $this->phone_number;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone_number'] = $value;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function accountsReceivable()
    {
        return $this->hasMany(AccountReceivable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
