<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomRequest extends Model {
    protected $table = 'wp_gpxCustomRequest';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'id'       => 'integer',
        'userID'   => 'integer',
        'datetime' => 'datetime',
        'checkIn'  => 'date',
        'checkIn2' => 'date',
    ];
    const CREATED_AT = 'datetime';

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
}
