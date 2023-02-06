<?php
namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class Resort extends Model
{
    protected $table = 'wp_resorts';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'lastUpdate' => 'datetime',
        'gpxRegionID' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'featured' => 'boolean',
        'gpr' => 'boolean',
        'guestFeesEnabled' => 'boolean',
        'store_d' => 'boolean',
        'ai' => 'boolean',
        'taxMethod' => 'integer',
        'taxID' => 'integer',
        'taID' => 'integer',
        'active' => 'boolean',
        'geocode_status' => 'integer',

    ];

    const CREATED_AT = false;
    const UPDATED_AT = 'lastUpdate';

}
