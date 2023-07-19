<?php

namespace GPX\Model;

use GPX\Model\Trait\HasResortFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Resort extends Model
{
    use HasResortFields;

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

    public static function findByResortId(string $resort_id): ?Resort
    {
        return Resort::byResortId($resort_id)->first();
    }

    public function scopeByResortId(Builder $query, string $resort_id): Builder
    {
        return $query->where('ResortID', $resort_id);
    }
}
