<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Region extends Model {
    // use NodeTrait;

    protected $table = 'wp_gpxRegion';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'parent' => 'integer',
        'lft' => 'integer',
        'rght' => 'integer',
        'featured' => 'boolean',
        'ddHidden' => 'boolean',
        'lng' => 'float',
        'lat' => 'float',
    ];

    public function getLftName()
    {
        return 'lft';
    }

    public function getRgtName()
    {
        return 'rght';
    }

    public function getParentIdName()
    {
        return 'parent';
    }

    public function setParentAttribute($value)
    {
        $this->attributes['parent'] = $value;
    }

    public function scopeTree( $query, $start = [] ) {
        return $query
            ->select( 'wp_gpxRegion.*' )
            ->join( 'wp_gpxRegion as p', fn( $join ) => $join->whereIn( 'p.id', Arr::wrap( $start ) ) )
            ->whereRaw( 'wp_gpxRegion.lft >= p.lft' )
            ->whereRaw( 'wp_gpxRegion.rght <= p.rght' )
            ->orderBy( 'wp_gpxRegion.lft', 'asc' );
    }

    public function scopeRestricted( $query ) {
        return $query
            ->select( 'wp_gpxRegion.id' )
            ->join( 'wp_gpxRegion as p', fn( $join ) => $join->whereRaw( "p.name = 'Southern Coast (California)'" ) )
            ->whereRaw( 'wp_gpxRegion.lft BETWEEN p.lft AND p.rght' )
            ->orderBy( 'wp_gpxRegion.lft', 'asc' );
    }

    public function scopeByName( Builder $query, string $name = null ): Builder {
        if ( ! $name ) {
            return $query;
        }

        return $query->where( function ( Builder $query ) use ( $name ) {
            $query
                ->orWhere( 'name', '=', $name )
                ->orWhere( 'subName', '=', $name )
                ->orWhere( 'displayName', '=', $name );
        } );
    }

    public function scopeChildOf( Builder $query, int $left, int $right): Builder {
        return $query->where(function(Builder $query) use ( $left, $right ) {
            return $query->where('lft', '>', $left)
                         ->where('rght', '<', $right);
        });
    }

    public function scopeActive( Builder $query, bool $active = true ): Builder {
        return $query->hidden(!$active);
    }

    public function scopeHidden( Builder $query, bool $hidden = true ): Builder {
        return $query->where('ddHidden', '=', $hidden);
    }

    public function scopeFeatured( Builder $query, bool $featured = true ): Builder {
        return $query->where('featured', '=', $featured);
    }

    public function scopeNotAll(Builder $query): Builder
    {
        return $query->where('name', '!=', 'All');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->where('parent', '=', 1);
    }
}
