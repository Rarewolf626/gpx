<?php

namespace GPX\Model;

use DB;
use Illuminate\Support\Arr;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use GPX\Model\ValueObject\Admin\Region\RegionSearch;

class Region extends Model {
     use NodeTrait;

    protected $table = 'wp_gpxRegion';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'parent' => 'integer',
        'CountryID' => 'integer',
        'lft' => 'integer',
        'rght' => 'integer',
        'featured' => 'boolean',
        'ddHidden' => 'boolean',
        'show_resort_fees' => 'boolean',
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

    public function parent_region() {
        return $this->belongsTo(Region::class, 'parent', 'id');
    }

    public function setParentAttribute($value)
    {
        $this->setParentIdAttribute($value);
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

    public function scopeAdminSearch(Builder $query, RegionSearch $search): Builder {
        return $query
            ->select([
                'wp_gpxRegion.*',
                'pr.name as parent_region',
                'c.country as parent_country',
                'c.CountryID as CountryID',
                DB::raw("IF(wp_gpxRegion.`RegionID` IS NULL, pr.name, c.country) as parent_name"),
            ])
            ->leftJoin('wp_gpxRegion as pr', 'pr.id', '=', 'wp_gpxRegion.parent')
            ->leftJoin('wp_daeRegion as dr', 'dr.id', '=', 'wp_gpxRegion.RegionID')
            ->leftJoin('wp_gpxCategory as c', 'c.CountryID', '=', 'dr.CountryID')
            ->when($search->id !== '', fn($query) => $query->where('id', 'LIKE', $search->id . '%'))
            ->when($search->gpx === 'yes', fn($query) => $query->whereNull('wp_gpxRegion.RegionID'))
            ->when($search->gpx === 'no', fn($query) => $query->whereNotNull('wp_gpxRegion.RegionID'))
            ->when($search->region !== '', fn($query) => $query->where('wp_gpxRegion.name', 'LIKE', '%' . $search->region . '%'))
            ->when($search->display !== '', fn($query) => $query->where('wp_gpxRegion.displayName', 'LIKE', '%' . $search->display . '%'))
            ->when($search->parent !== '', fn($query) => $query
                ->where(fn($query) => $query
                    ->orWhere(fn($query) => $query
                        ->whereNull('wp_gpxRegion.RegionID')
                        ->where('pr.name', 'LIKE', '%' . $search->parent . '%')
                    )
                    ->orWhere(fn($query) => $query
                        ->whereNotNull('wp_gpxRegion.RegionID')
                        ->where('c.country', 'LIKE', '%' . $search->parent . '%')
                    )
                )
            )
            ->when($search->sort !== 'lft', fn($query) => $query
                ->orderBy(match ($search->sort) {
                    'gpx' => DB::raw('IF(wp_gpxRegion.`RegionID` IS NULL, 1, 0)'),
                    'region' => 'wp_gpxRegion.name',
                    'display' => 'wp_gpxRegion.displayName',
                    'id' => 'wp_gpxRegion.id',
                    default => 'wp_gpxRegion.lft',
                }, $search->dir)
            )
            ->orderBy('wp_gpxRegion.lft', 'asc')
            ;
    }
}
