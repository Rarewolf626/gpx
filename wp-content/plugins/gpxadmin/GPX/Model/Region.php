<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class Region extends Model {
    protected $table = 'wp_gpxRegion';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function scopeTree( $query, $start = [] ) {
        return $query
            ->select('wp_gpxRegion.*')
            ->join( 'wp_gpxRegion as p', fn( $join ) => $join->whereIn( 'p.id', Arr::wrap( $start ) ) )
            ->whereRaw( 'wp_gpxRegion.lft >= p.lft' )
            ->whereRaw( 'wp_gpxRegion.rght <= p.rght' )
            ->orderBy( 'wp_gpxRegion.lft', 'asc' );
    }

    public function scopeRestricted( $query ) {
        return $query
            ->select('wp_gpxRegion.id')
            ->join( 'wp_gpxRegion as p', fn( $join ) => $join->whereRaw( "p.name = 'Southern Coast (California)'" ) )
            ->whereRaw( 'wp_gpxRegion.lft BETWEEN p.lft AND p.rght' )
            ->orderBy( 'wp_gpxRegion.lft', 'asc' );
    }
}
