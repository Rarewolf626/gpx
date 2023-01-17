<?php

namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function scopeActive( Builder $query ): Builder {
        return $query->where('Contract_Status__c', '=', 'Active');
    }

    public function scopeCancelled( Builder $query ): Builder {
        return $query->where('Contract_Status__c', '=', 'Cancelled');
    }
}
