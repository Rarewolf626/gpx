<?php

namespace GPX\Model;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomRequest extends Model {
    protected $table = 'wp_gpxCustomRequest';
    //protected $primaryKey = 'id';
    protected $guarded = [];
    const CREATED_AT = 'datetime';
    const UPDATED_AT = null;

    protected $casts = [
        'userID'                  => 'integer',
        'emsID'                   => 'integer',
        'datetime'                => 'timestamp',
        'match_date_time'         => 'datetime',
        'match_release_date_time' => 'datetime',
        'checkIn'                 => 'date',
        'checkIn2'                => 'date',
        'miles'                   => 'integer',
        'adults'                  => 'integer',
        'children'                => 'integer',
        'nearby'                  => 'boolean',
        'larger'                  => 'boolean',
        'BOD'                     => 'boolean',
        'active'                  => 'boolean',
        'forCron'                 => 'boolean',
        'matchedOnSubmission'     => 'boolean',
        'week_on_hold'            => 'boolean',
        'match_duplicate_order'   => 'integer',
        'sixtydayemail'           => 'boolean',
        'matchConverted'          => 'integer',
        'matchEmail'              => 'datetime',
    ];

    protected $attributes = [
        'country'               => '',
        'state'                 => '',
        'region'                => '',
        'city'                  => '',
        'resort'                => '',
        'nearby'                => false,
        'miles'                 => 0,
        'checkIn'               => '',
        'checkIn2'              => '',
        'checkIn3'              => '',
        'emsID'                 => '',
        'firstName'             => '',
        'lastName'              => '',
        'email'                 => '',
        'phone'                 => '',
        'mobile'                => '',
        'ada'                   => '',
        'adults'                => 0,
        'children'              => 0,
        'roomType'              => 'Any',
        'larger'                => false,
        'preference'            => 'Any',
        'comments'              => '',
        'who'                   => '',
        'matched'               => '',
        'week_on_hold'          => false,
        'match_duplicate_order' => 1000,
        'BOD'                   => false,
        'active'                => true,
        'forCron'               => false,
        'sixtydayemail'         => false,
        'matchedOnSubmission'   => false,
        'matchConverted'        => 0,
    ];

    public function scopeActive( Builder $query, bool $active = true ): Builder {
        return $query->where( 'active', '=', $active );
    }

    public function scopeOwner( Builder $query ): Builder {
        return $query->where( 'who', '=', 'Owner' );
    }

    public function scopeByUser( Builder $query, int $emsid, int $userid ): Builder {
        return $query->where( function ( $query ) use ( $userid, $emsid ) {
            $query->orWhere( 'emsID', '=', $emsid )
                  ->orWhere( 'userID', '=', $userid );
        } );
    }

    public function getMatchedAttribute( $value ) {
        if ( empty( $value ) ) {
            return [];
        }

        return explode( ',', $value );
    }

    public function setMatchedAttribute( $value ) {
        if ( empty( $value ) ) {
            $this->attributes['matched'] = '';

            return;
        }
        if ( ! is_array( $value ) ) {
            throw new \InvalidArgumentException( 'Must be an array of matched ids' );
        }
        $this->attributes['matched'] = implode( ',', $value );
    }

    public function getCheckInAttribute( $value ) {
        return $this->parseDate( $value );
    }

    public function setCheckInAttribute( $value ) {
        $date = $this->parseDate( $value );
        if ( $date ) {
            $this->attributes['checkIn'] = $date->format( 'm/d/Y' );
        } else {
            $this->attributes['checkIn'] = '';
        }
    }

    public function getCheckIn2Attribute( $value ) {
        return $this->parseDate( $value );
    }

    public function setCheckIn2Attribute( $value ) {
        $date = $this->parseDate( $value );
        if ( $date ) {
            $this->attributes['checkIn2'] = $date->format( 'm/d/Y' );
        } else {
            $this->attributes['checkIn2'] = '';
        }
    }

    protected function parseDate( $value ): ?\DateTimeInterface {
        if ( empty( $value ) ) {
            return null;
        }
        if ( $value instanceof \DateTimeInterface ) {
            return Carbon::instance( $value );
        }
        if ( preg_match( "/^\d{4}-\d{2}-\d{2}/", $value ) ) {
            return Carbon::createFromFormat( 'Y-m-d', $value );
        }
        if ( preg_match( "/^\d{2}\/\d{2}\/\d{4}/", $value ) ) {
            return Carbon::createFromFormat( 'm/d/Y', $value );
        }
        $date = Carbon::parse( $value );
        if ( ! $date->isValid() ) {
            return null;
        }

        return $date;
    }

    public function findLikeThis() {
        $query = CustomRequest::query()
                              ->where( 'userID', '=', $this->userID )
                              ->where( 'active', '=', $this->active ? '1' : '0' )
                              ->where( 'forCron', '=', $this->forCron ? '1' : '0' )
                              ->where( 'BOD', '=', $this->BOD ? '1' : '0' )
                              ->where( 'adults', '=', $this->adults )
                              ->where( 'children', '=', $this->children )
                              ->where( 'checkIn', '=', $this->checkIn ? $this->checkIn->format( 'm/d/Y' ) : '' )
                              ->where( 'checkIn2', '=', $this->checkIn2 ? $this->checkIn2->format( 'm/d/Y' ) : '' )
                              ->where( 'roomType', '=', $this->roomType ?? 'Any' )
                              ->where( 'larger', '=', $this->larger ? '1' : '0' )
                              ->where( 'preference', '=', $this->preference ?? 'Any' )
                              ->where( 'nearby', '=', $this->nearby ? '1' : '0' )
                              ->where( 'miles', '=', $this->miles )
                              ->when( $this->resort, fn( $query ) => $query
                                  ->where( 'resort', '=', $this->resort )
                              )
                              ->when( $this->city && ! $this->resort, fn( $query ) => $query
                                  ->where( 'city', '=', $this->city )
                              )
                              ->when( $this->region && ! $this->city && ! $this->resort,
                                  fn( $query ) => $query
                                      ->where( 'region', '=', $this->region )
                              );
//        dd( $query->toSql(), $query->getBindings() );

        return $query->first();
    }

    public function toFilters(): array {
        return [
            'adults'     => $this->adults,
            'children'   => $this->children,
            'checkIn'    => $this->attributes['checkIn'] ? $this->checkIn->format( 'm/d/Y' ) : null,
            'checkIn2'   => $this->attributes['checkIn2'] ? $this->checkIn2->format( 'm/d/Y' ) : null,
            'roomType'   => $this->roomType,
            'larger'     => $this->larger,
            'preference' => $this->preference,
            'nearby'     => $this->nearby,
            'miles'      => $this->miles,
            'region'     => $this->region,
            'city'       => $this->city,
            'resort'     => $this->resort,
        ];
    }
}
