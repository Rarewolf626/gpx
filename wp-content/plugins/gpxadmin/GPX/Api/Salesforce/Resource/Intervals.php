<?php

namespace GPX\Api\Salesforce\Resource;

use Illuminate\Support\Arr;

class Intervals extends AbstractResource {

    /**
     * @param int|int[] $ownerid
     *
     * @return array
     */
    public function get_intervals_by_owner( $ownerid = [] ): array {
        $ownerid = Arr::wrap($ownerid);
        array_walk($ownerid, 'intval');
        $ownerid = array_filter($ownerid, fn($id) => $id !== 0);
        if(empty($ownerid)) return [];

        $query2 = sprintf("SELECT  Owner_ID__c,GPR_Resort__c,Contract_ID__c,UnitWeek__c,
                           Contract_Status__c,Delinquent__c,Days_Past_Due__c,
                           Total_Amount_Past_Due__c,Room_Type__c,ROID_Key_Full__c,
                           Resort_ID_v2__c
                    FROM Ownership_Interval__c
                    WHERE Resort_ID_v2__c != 'GPVC'
                        AND Contract_Status__c = 'Active'
                        AND Owner_ID__c IN (%s)", implode(',', array_map(fn($id) => $this->sf->esc($id), $ownerid)));
        return $this->sf->query( $query2 ) ?? [];
    }
}
