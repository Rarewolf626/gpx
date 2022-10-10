<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class MappedInterval extends Model {
    protected $table = 'wp_mapuser2oid';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'gpx_user_id' => 'integer',
        'gpr_oid' => 'integer',
        'gpr_oid_interval' => 'integer',
    ];

    public function user(  ) {
        return $this->belongsTo(User::class, 'gpx_user_id', 'ID');
    }

    public function interval(  ) {
        return $this->belongsTo(Interval::class, 'RIOD_Key_Full', 'RIOD_Key_Full');
    }
}
