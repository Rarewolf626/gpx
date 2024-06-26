<?php

namespace GPX\Model;

use Illuminate\Support\Carbon;
use GPX\Model\Casts\BooleanString;
use Illuminate\Database\Eloquent\Model;

class Property extends Model {
    protected $table = 'wp_properties';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'WeekPoints' => 'integer',
        'Price' => 'float',
        'IsAdvanceNotice' => BooleanString::class,
        'gpxSleepsTotal' => 'integer',
        'weekId' => 'integer',
        'weekNo' => 'integer',
        'noNights' => 'integer',
        'resortJoinID' => 'integer',
        'active' => 'boolean',
    ];

    public function getSpecialFlagAttribute( $value ) {
        if ( $value === null ) return null;
        if ( mb_strtolower( $value ) === 'null' ) return null;
        if ( $value === '' ) return null;

        return $value;
    }

    public function getCheckInAttribute( $value ) {
        if ( $value === '' || $value === null ) return null;
        if ( is_string( $value ) ) {
            $date = Carbon::createFromFormat( 'd M Y', $value );
            if ( $date ) {
                return $date;
            }
            $date = date_create( $value );
            if ( $date ) {
                return $date;
            }
        }

        return null;
    }

    public function setCheckInAttribute( $value ) {
        if ( $value === '' || $value === null ) return '';
        if ( $value instanceof \DateTimeInterface ) return $value->format( 'd M Y' );
        if ( is_string( $value ) ) {
            $date = Carbon::createFromFormat( 'd M Y', $value );
            if ( $date ) return $date->format( 'd M Y' );
            $date = date_create( $value );
            if ( $date ) return $date->format( 'd M Y' );
        }

        return '';
    }
}
