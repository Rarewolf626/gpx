<?php

namespace GPX\Repository;

use Illuminate\Database\Eloquent\Model;
/*
 * uses the wp_room table
 */
class WeekRepository extends Model
{

    protected $table = 'wp_room';
    protected $primaryKey = 'record_id';
    protected $guarded = [];

    protected $casts = [
        'record_id' => 'integer',
        'create_date' => 'datetime',
        'active_specific_date' => 'datetime',
        'last_modified_date' => 'datetime',
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'sourced_by_partner_on' => 'datetime',
        'resort' => 'integer',
        'unit_type' => 'integer',
        'active_rental_push_date' => 'date',
        'update_details' => 'array',
        'active' => 'boolean',
        'availablity' => 'boolean',
        'available_to_partner' => 'boolean',
        'price' => 'float',
    ];
    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_modified_date';


    /*
    *  I don't understnd the purpose of base64 encoding the entire row
     * and sticking it into another array.
     * Also don't know the significance of the key
    */
    public function get_details() {
       $key =  array_key_first($this->update_details);
       return  base64_decode($this->update_details[$key]['details']);
    }


}
