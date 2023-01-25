<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class DaeRegion extends Model {
    protected $table = 'wp_daeRegion';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'resortPull' => 'integer',
        'active'     => 'boolean',
        'exchange'   => 'datetime',
        'rental'     => 'datetime',
    ];
}
