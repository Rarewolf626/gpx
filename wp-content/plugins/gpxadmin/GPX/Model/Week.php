<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Week extends Model {
    protected $table = 'wp_room';
    protected $primaryKey = 'record_id';

    protected $guarded = [];
    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_modified_date';

    protected $casts = [
        'record_id'               => 'integer',
        'create_date'             => 'datetime',
        'active_specific_date'    => 'datetime',
        'last_modified_date'      => 'datetime',
        'check_in_date'           => 'datetime',
        'check_out_date'          => 'datetime',
        'resort'                  => 'integer',
        'unit_type'               => 'integer',
        'source_num'              => 'integer',
        'source_partner_id'       => 'integer',
        'sourced_by_partner_on'   => 'datetime',
        'active'                  => 'boolean',
        'availablity'             => 'boolean',
        'available_to_partner_id' => 'integer',
        'type'                    => 'integer',
        'active_rental_push_date' => 'date',
        'price'                   => 'float',
        'points'                  => 'float',
        'given_to_partner_id'     => 'integer',
        'import_id'               => 'integer',
        'active_week_month'       => 'integer',
        'create_by'               => 'integer',
        'archived'                => 'integer',
        'booked_status'           => 'integer',
    ];

    public function unit(  ) {
        return $this->belongsTo(UnitType::class, 'unit_type', 'record_id');
    }

    public function theresort(  ) {
        return $this->belongsTo(Resort::class, 'resort', 'id');
    }

    public function getRoomTypeAttribute(  ) {
        switch($this->type){
            case 1:
                return 'Exchange';
            case 2:
                return 'Rental';
            case 3:
                return 'Exchange/Rental';
            default:
                return '--';
        }
    }

    public function getUpdateDetailsAttribute( $value ) {
        if ( empty( $value ) ) {
            return null;
        }
        $value = json_decode( $value, true );
        if ( empty( $value ) ) {
            return null;
        }

        return array_map( function ( $record ) {
            if ( isset( $record['details'] ) ) {
                $record['details'] = base64_decode( $record['details'] );
                if(!empty($record['details'])) $record['details'] = json_decode($record['details'], true);

                return $record;
            }
        }, $value );
    }

    public function scopeActive( Builder $query, bool $active = true): Builder {
        return $query->where('active', '=', $active);
    }
}
