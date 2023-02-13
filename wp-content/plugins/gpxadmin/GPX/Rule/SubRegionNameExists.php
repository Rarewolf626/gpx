<?php

namespace GPX\Rule;

use DB;
use Countable;
use GPX\Model\Region;
use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Contracts\Validation\DataAwareRule;

class SubRegionNameExists implements DataAwareRule, Rule {
    protected array $data = [];
    protected string $field;

    public function __construct( string $region_field = 'region' ) {
        $this->field = $region_field;
    }

    public function passes( $attribute, $value ) {
        $region = $this->getRegion( $this->data[ $this->field ] ?? null );
        if ( ! $region ) {
            return false;
        }

        return Region::active()
                     ->childOf( $region->lft, $region->rght )
                     ->byName( $value )
                     ->exists();
    }

    public function isRequired( string $region = null ): bool {
        $region = $this->getRegion( $region );
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

    public function getRegion( string $value = null ): ?Region {
        if ( $this->isEmpty( $value ) ) {
            return null;
        }
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
                         ->active()
                         ->take( 1 )
                         ->first();
        }

        return Region::select( [ 'id', 'lft', 'rght' ] )
                     ->active()
                     ->byName( $value )
                     ->take( 1 )
                     ->first();
    }

    public function isEmpty( $value ): bool {
        if ( is_null( $value ) ) {
            return true;
        } elseif ( is_string( $value ) && trim( $value ) === '' ) {
            return true;
        } elseif ( ( is_array( $value ) || $value instanceof Countable ) && count( $value ) < 1 ) {
            return true;
        } elseif ( $value instanceof File ) {
            return (string) $value->getPath() === '';
        }

        return false;
    }
}
