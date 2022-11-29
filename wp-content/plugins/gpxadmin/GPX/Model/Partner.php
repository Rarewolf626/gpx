<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model {
    protected $table = 'wp_partner';
    protected $primaryKey = 'record_id';

    protected $guarded = [];
    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_modified_date';

    protected $casts = [
        'record_id'                  => 'integer',
        'create_date'                => 'datetime',
        'last_modified_date'         => 'datetime',
        'type'                       => 'integer',
        'sfid'                       => 'integer',
        'no_of_rooms_given'          => 'integer',
        'no_of_rooms_received_taken' => 'integer',
        'trade_balance'              => 'integer',
        'adjData'                    => 'array',
        'debit_id'                   => 'array',
        'debit_balance'              => 'integer',
    ];
}
