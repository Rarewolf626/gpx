<?php

namespace GPX\Model;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model {
    protected $table = 'wp_gpxTransactions';
    protected $primaryKey = 'id';
    const CREATED_AT = 'datetime';
    const UPDATED_AT = null;
    protected $guarded = [];

    protected $casts = [
        'id'                   => 'integer',
        'userID'               => 'integer',
        'weekId'               => 'integer',
        'depositID'            => 'integer',
        'transactionRequestId' => 'integer',
        'transactionData'      => 'array',
        'paymentGatewayID'     => 'integer',
        'sfid'                 => 'integer',
        'sfData'               => 'array',
        'data'                 => 'array',
        'card_number'          => 'integer',
        'datetime'             => 'datetime',
        'check_in_date'        => 'date',
        'cancelledDate'        => 'date',
        'cancelledData'        => 'array',
        'cancelled'            => 'boolean',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'userID', 'ID' );
    }

    public function scopeBooking( Builder $query ): Builder {
        return $query->type( 'booking' );
    }

    /** @param string[]|string $type */
    public function scopeType( Builder $query, $type = [] ): Builder {
        $type = Arr::wrap( $type );

        return $query->whereIn( 'transactionType', $type );
    }

    public function scopeCancelled( Builder $query, bool $cancelled = true ): Builder {
        return $query->when( $cancelled, fn( $query ) => $query->where( 'cancelled', '=', true ) )
                     ->when( ! $cancelled, fn( $query ) => $query
                         ->where( fn( $query ) => $query
                             ->whereNull( 'cancelled' )->orWhere( 'cancelled', '=', false )
                         )
                     );
    }

    public function scopeUpcoming( Builder $query ): Builder {
        return $query->whereRaw( 'check_in_date >= CURRENT_DATE()' );
    }

    public function scopePast( Builder $query ): Builder {
        return $query->whereRaw( 'check_in_date < CURRENT_DATE()' );
    }
}
