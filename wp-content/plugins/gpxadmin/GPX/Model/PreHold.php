<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PreHold extends Model {
    protected $table = 'wp_gpxPreHold';
    protected $guarded = [];
    public $timestamps = false;
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $casts = [
        'propertyID' => 'integer',
        'weekId' => 'integer',
        'user' => 'integer',
        'lpid' => 'integer',
        'data' => 'array',
        'released' => 'boolean',
        'release_on' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user', 'ID');
    }

    public function property(  ) {
        return $this->belongsTo(Week::class, 'propertyID', 'record_id');
    }

    public function week(  ) {
        return $this->belongsTo(Week::class, 'weekId', 'record_id');
    }

    public function scopeReleased( Builder $query, bool $released = true): Builder {
        return $query->where('released', '=', $released);
    }
}
