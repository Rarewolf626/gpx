<?php

namespace GPX\Repository;

use GPX\Model\Credit;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Capsule\Manager as DB;

class CreditRepository {
    public static function instance(): CreditRepository {
        return gpx( CreditRepository::class );
    }

    public function getOwnerCreditWeeks( int $cid, string|\DateTimeInterface $checkin = null ): Builder {
        if ( null === $checkin ) $checkin = Carbon::now();
        $checkin = $checkin instanceof \DateTimeInterface ? Carbon::instance( $checkin ) : Carbon::parse( $checkin );

        return Credit::select( [
            'wp_credit.*',
            DB::raw( "(SELECT Delinquent__c FROM wp_mapuser2oid WHERE gpx_user_id = wp_credit.owner_id LIMIT 1) as Delinquent__c" ),
        ] )
                     ->forUser( $cid )
                     ->notExpired()
                     ->notUsed()
                     ->whereRaw( "(wp_credit.status != 'Approved' OR IFNULL(wp_credit.credit_action, '') != 'transferred')" )
                     ->where( fn( $query ) => $query
                         ->whereNull( 'extension_date' )
                         ->orWhereDate( 'credit_expiration_date', '>=', $checkin->format( 'Y-m-d' ) )
                     );
    }

    public function getOwnerCredit( int $cid, int $credit_id, string|\DateTimeInterface $checkin = null ): ?Credit {
        return $this->getOwnerCreditWeeks( $cid, $checkin )->find( $credit_id );
    }
}
