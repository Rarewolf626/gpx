<?php

namespace GPX\Rule;

use DB;
use GPX\Model\Region;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

class SubRegionNameExists implements DataAwareRule, Rule {
    protected array $data = [];
    protected string $field;

    public function __construct(string $field = 'region') {
        $this->field = $field;
    }

    public function passes( $attribute, $value ) {
        $region = $this->getRegion( $this->data[$this->field] ?? null );
        if ( ! $region ) {
            return false;
        }

        return Region::childOf( $region->lft, $region->rght )->active()->exists();
    }

    public function message() {
        return 'Please select from available cities / sub regions.';
    }

    public function setData( $data ) {
        $this->data = $data;

        return $this;
    }

    private function getRegion( string $value = null ): ?Region {
        if(null === $value) return null;
        $category = DB::table( 'wp_gpxCategory' )
                      ->select( 'countryID' )
                      ->where( 'country', '=', $value )
                      ->where( 'CountryID', '<', 1000 )
                      ->take( 1 )
                      ->first();
        if ( $category ) {
            return Region::query()
                         ->select( [ 'wp_gpxRegion.id', 'wp_gpxRegion.lft', 'wp_gpxRegion.rght' ] )
                         ->join( 'wp_daeRegion b', 'wp_gpxRegion.RegionID', '=', 'b.id' )
                         ->where( 'b.CategoryID', '=', $category->countryID )
                         ->take( 1 )
                         ->first();
        }

        return Region::select( [ 'id', 'lft', 'rght' ] )->byName( $value )->take( 1 )->first();
    }
}
