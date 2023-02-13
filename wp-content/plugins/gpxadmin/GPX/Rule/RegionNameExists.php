<?php

namespace GPX\Rule;

use DB;
use GPX\Model\Region;
use Illuminate\Contracts\Validation\Rule;

class RegionNameExists implements Rule {

    public function passes( $attribute, $value ) {
        $category = DB::table( 'wp_gpxCategory' )
                      ->select( 'countryID' )
                      ->where( 'country', '=', $value )
                      ->where( 'CountryID', '<', 1000 )
                      ->take( 1 )
                      ->first();
        if ( $category ) {
            return Region::query()
                         ->join( 'wp_daeRegion b', 'a.RegionID', '=', 'b.id' )
                         ->where( 'b.CategoryID', '=', $category->countryID )
                         ->active()
                         ->exists();
        }

        return Region::byName( $value )->active()->exists();
    }

    public function message() {
        return 'Please select from available regions.';
    }
}
