<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Special extends Model {
    protected $table = 'wp_specials';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'id'              => 'integer',
        'master'          => 'integer',
        'Active'          => 'boolean',
        'StartDate'       => 'datetime',
        'EndDate'         => 'datetime',
        'TravelStartDate' => 'date',
        'TravelEndDate'   => 'date',
        'redeemed'        => 'integer',
        'reworked'        => 'integer',
        'Properties'      => 'array',
        'revisedBy'       => 'array',
    ];



    public function getTransactionTypeAttribute(): ?string {
        $properties = $this->Properties;
        if ( empty( $properties->transactionType ) ) {
            return null;
        }
        $type = array_filter( Arr::wrap( $properties->transactionType ) );
        if ( empty( $type ) ) {
            return null;
        }
        if ( count( $type ) === 1 ) {
            return Arr::first( $type );
        }

        return implode( ', ', $type );
    }

    public function getPropertiesAttribute( $value ) {
        if ( null === $value || $value === '' ) {
            return new \stdClass();
        }
        if ( is_object( $value ) ) {
            return $value;
        }
        if ( is_array( $value ) ) {
            return (object) $value;
        }
        if ( is_string( $value ) ) {
            return json_decode( $value, false );
        }

        return new \stdClass();
    }

    public function setPropertiedAttribute( $value ) {
        if ( $value === null || $value === '' ) {
            $this->attributes['Properties'] = json_encode( '{}' );
        } elseif ( is_string( $value ) ) {
            $this->attributes['Properties'] = json_encode( json_decode( $value, false ) );
        } elseif ( is_array( $value ) || is_object( $value ) ) {
            $this->attributes['Properties'] = json_encode( $value );
        }
    }

    public function scopeActive( Builder $query, bool $active = true ): Builder {
        return $query->where( 'Active', '=', $active );
    }

    public function scopeCurrent( Builder $query, \DateTimeInterface $now = null ): Builder {
        $now = $now ? Carbon::instance($now) : Carbon::now();
        return $query->where(fn($query) => $query
            ->where('StartDate', '<=', $now)
            ->where('EndDate', '>=', $now)
        );
    }

    public function scopeSlug( Builder $query, string $slug ): Builder {
        return $query->where( 'Slug', '=', $slug );
    }
}
