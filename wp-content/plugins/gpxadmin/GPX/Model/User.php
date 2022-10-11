<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model{
    protected $table = 'wp_users';
    protected $primaryKey = 'ID';
    protected $guarded = [];

    protected $casts = [
        'ID' => 'integer',
        'user_status' => 'boolean',
        'user_registered' => 'datetime',
    ];
    const CREATED_AT = 'user_registered';
    const UPDATED_AT = null;

    public function carts( ): HasMany {
        return $this->hasMany(Cart::class, 'user', 'ID');
    }

    public function owner(  ): HasOne {
        return $this->hasOne(Owner::class, 'user_id', 'ID');
    }
}
