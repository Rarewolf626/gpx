<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class Interval extends Model {
    protected $table = 'wp_owner_interval';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'ownerID' => 'integer',
        'id' => 'integer',
        'userID' => 'integer',
        'Year_Last_Banked__c' => 'integer',
        'contractID' => 'integer',
    ];

    public function user(  ) {
        return $this->belongsTo(User::class, 'userID', 'ID');
    }

    public function owner(  ) {
        return $this->belongsTo(Owner::class, 'ownerID', 'Name');
    }
}
