<?php

namespace GPX\Repository;

use Illuminate\Database\Eloquent\Model;

class CartRepository extends Model
{
    protected $table = 'wp_cart';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'user' => 'integer',
        'propertyID' => 'integer',
        'weekId' => 'integer',
        'datetime' => 'datetime',
    ];
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = null;

}
