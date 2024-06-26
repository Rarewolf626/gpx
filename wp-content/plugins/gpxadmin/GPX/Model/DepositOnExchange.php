<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class DepositOnExchange extends Model {
    protected $table = 'wp_gpxDepostOnExchange';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'transactionID' => 'integer',
        'data' => 'array',
        'creditID' => 'integer',
    ];

    public function credit(  ) {
        return $this->belongsTo(Credit::class, 'creditID', 'id');
    }
}
