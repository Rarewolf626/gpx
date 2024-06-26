<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class FailedTransactions extends Model {
    protected $table = 'wp_gpxFailedTransactions';
    protected $primaryKey = 'id';
    const CREATED_AT = 'date';
    const UPDATED_AT = null;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'userID' => 'integer',
        'data' => 'array',
        'returnTime' => 'float',
        'date' => 'datetime',
    ];
}
