<?php

namespace GPX\Model;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
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
        'deposit_year' => 'integer',
        'contractID' => 'integer',
        'third_party_deposit_fee_enabled' => 'boolean',
        'gpr' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo( User::class, 'userID', 'ID' );
    }

    public function owner() {
        return $this->belongsTo( Owner::class, 'ownerID', 'Name' );
    }

    public function scopeActive( Builder $query ): Builder {
        return $query->where( 'Contract_Status__c', '=', 'Active' );
    }

    public function scopeCancelled( Builder $query ): Builder {
        return $query->where( 'Contract_Status__c', '=', 'Cancelled' );
    }

    public function getYearBankedAttribute(): ?int {
        if ( $this->Year_Last_Banked__c ) {
            return $this->Year_Last_Banked__c;
        }
        if ( isset( $this->attributes['deposit_year'] ) && (int) $this->attributes['deposit_year'] ) {
            return (int) $this->attributes['deposit_year'];
        }

        return null;
    }

    public function getNextyearAttribute(): CarbonInterface {
        if ( $this->Year_Last_Banked__c ) {
            return Carbon::createFromDate( $this->Year_Last_Banked__c, 1, 1 )->addYear()->startOfYear();
        }
        if ( isset( $this->deposit_year ) && $this->deposit_year ) {
            return Carbon::createFromDate( $this->deposit_year, 1, 1 )->addYear()->startOfYear();
        }

        return Carbon::now()->addDays( 14 )->startOfDay();
    }

    public function scopeWithDepositYear( Builder $query ): Builder {
        return $query->select('wp_owner_interval.*')
                     ->addSelect( \DB::raw("(SELECT MAX(wp_credit.deposit_year) FROM wp_credit WHERE wp_credit.status != 'Pending' AND wp_credit.interval_number=wp_owner_interval.contractID) as deposit_year") );
    }

    public function isDeliquent(  ): bool {
        return $this->Delinquent__c != 'No';
    }

    public function needsUnitType( ?string $room_type = null ): bool {
        $room_type = $room_type ?? $this->Room_Type__c;
        if(empty($room_type)) return true;
        if(!isset($this->ResortName)) return false;
        return in_array( $this->ResortName, ['Channel Island Shores', 'Hilton Grand Vacations Club at MarBrisa', 'RiverPointe Napa Valley'] );
    }

}
