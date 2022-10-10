<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    protected $table = 'wp_unit_type';
    protected $primaryKey = 'record_id';
    protected $guarded = [];

    protected $casts = [
        'user' => 'integer',
        'resort_id' => 'integer',
        'weekId' => 'integer',
        'create_date' => 'datetime',
        'last_modified_date' => 'datetime',
    ];

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_modified_date';

    public function weeks(  ) {
        return $this->hasMany(Week::class, 'unit_type', 'record_id');
    }
}
