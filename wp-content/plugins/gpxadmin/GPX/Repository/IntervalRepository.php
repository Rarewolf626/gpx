<?php

namespace GPX\Repository;

use DB;
use stdClass;
use GPX\Model\Interval;
use Illuminate\Support\Arr;
use GPX\Api\Salesforce\Salesforce;
use Illuminate\Support\Collection;
use Illuminate\Database\Query\JoinClause;
use GPX\Model\SalesForce\Interval as SfInterval;

class IntervalRepository {

    public static function instance(): IntervalRepository {
        return gpx(IntervalRepository::class);
    }

    public function count_intervals(int $member_number, bool $active_only = true): int {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT
                    COUNT(*) as num_intervals
                FROM wp_owner_interval a
                INNER JOIN wp_resorts b ON b.gprID LIKE CONCAT(BINARY a.resortID, '%%')
                WHERE a.ownerID IN
                    (SELECT DISTINCT gpr_oid
                        FROM wp_mapuser2oid
                        WHERE gpx_user_id IN
                            (SELECT DISTINCT gpx_user_id
                            FROM wp_mapuser2oid
                            WHERE gpr_oid=%d))", $member_number);
        if ($active_only) {
            $sql .= " AND a.Contract_Status__c = 'Active'";
        }

        return (int) $wpdb->get_var($sql);
    }

    public function get_member_ownerships($cid, bool $with_intervals = false): Collection {

        $ownerships = Interval::withDepositYear()
                              ->addSelect('wp_resorts.ResortName')
                              ->addSelect('wp_resorts.id as resort_id')
                              ->addSelect('wp_resorts.gprID')
                              ->addSelect('wp_resorts.gpr')
                              ->addSelect('wp_resorts.third_party_deposit_fee_enabled')
                              ->join('wp_resorts', function (JoinClause $join) {
                                  $join->on('wp_resorts.gprID', 'LIKE', DB::raw("CONCAT(BINARY wp_owner_interval.resortID, '%')"))
                                       ->whereRaw("wp_owner_interval.resortID != '' AND wp_owner_interval.resortID IS NOT NULL");
                              })
                              ->where('wp_owner_interval.userID', '=', $cid)
                              ->get();

        $intervals = collect();
        if ($with_intervals) {
            $intervals = $this->getIntervalsFromSalesforce($ownerships->pluck('contractID')->toArray());
        }

        return $ownerships->map(function (Interval $ownership) use ($intervals, $with_intervals) {
            if ($with_intervals) {
                $ownership->interval = $intervals->first(fn($interval) => $interval->Contract_ID__c === $ownership->contractID);
                if ($ownership->interval?->Room_Type__c) $ownership->Room_Type__c = $ownership->interval?->Room_Type__c;
            }
            $ownership->is_delinquent = $ownership->isDeliquent();
            $ownership->needs_unit_type = $ownership->needsUnitType($ownership->interval?->Room_Type__c);

            return $ownership;
        });
    }

    public function get_member_interval(int $cid, int $interval_id, bool $salesforce_merge = false): ?Interval {

        $interval = Interval::select([
            'wp_owner_interval.*',
            "wp_resorts.id as resort_id",
            "wp_resorts.ResortName",
            "wp_resorts.gpr",
            "wp_resorts.gprID",
            "wp_resorts.third_party_deposit_fee_enabled",
            DB::raw("(SELECT MAX(deposit_year) FROM wp_credit WHERE wp_credit.status != 'Pending' AND interval_number=wp_owner_interval.contractID) as deposit_year"),
        ])
                            ->join('wp_resorts', function (JoinClause $join) {
                                $join->on('wp_resorts.gprID', 'LIKE', DB::raw("CONCAT(BINARY wp_owner_interval.resortID, '%')"))
                                     ->whereRaw("wp_owner_interval.resortID != '' AND wp_owner_interval.resortID IS NOT NULL");
                            })
                            ->where('wp_owner_interval.id', '=', $interval_id)
                            ->where('wp_owner_interval.userID', '=', $cid)
                            ->first()
                            ?->append('nextyear');

        if (!$interval) {
            return null;
        }

        if ($salesforce_merge) {
            $interval->interval = $this->getIntervalFromSalesforce($interval->contractID);
            if ($interval->interval) {
                $interval->fill([
                    'ownership_id' => $interval->interval->Name,
                    'Room_Type__c' => $interval->interval->Room_Type__c,
                    'Delinquent__c' => $interval->interval->Delinquent__c,
                    'Week_Type__c' => $interval->interval->Week_Type__c,
                    'Contract_ID__c' => $interval->interval->Contract_ID__c,
                    'RIOD_Key_Full' => $interval->interval->ROID_Key_Full__c,
                    'Property_Owner__c' => $interval->interval->Property_Owner__c,
                    'Account_Name__c' => $interval->interval->Property_Owner__c,
                    'Usage__c' => $interval->interval->Usage__c,
                    'unitweek' => $interval->interval->UnitWeek__c,
                ]);
            }
        }

        return $interval->append('nextyear');
    }

    /**
     * @param array $contracts
     *
     * @return Collection
     */
    public function getIntervalsFromSalesforce(array $contracts = []): Collection {
        if (empty($contracts)) return collect();
        global $wpdb;
        $sf = Salesforce::getInstance();
        $placeholders = implode(',', array_map(fn($id) => $wpdb->prepare('%s', $id), $contracts));
        $query = sprintf("SELECT
        ID, Name, Property_Owner__c, Room_Type__c, Week_Type__c, Owner_ID__c, Contract_ID__c, GPR_Owner_ID__c, GPR_Resort__c, ROID_Key_Full__c,
        GPR_Resort_Name__c, Owner_Status__c, Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c, Delinquent__c
        FROM Ownership_Interval__c
        WHERE Contract_ID__c IN (%s) AND Contract_Status__c = 'Active'", $placeholders);

        return collect($sf->query($query))->pluck('fields')->map(fn(stdClass $interval) => new SfInterval($interval));
    }

    public function getIntervalFromSalesforce($contract_id = null): ?SfInterval {
        if (null === $contract_id) return null;
        global $wpdb;
        $sf = Salesforce::getInstance();

        $query = $wpdb->prepare("SELECT
        ID, Name, Property_Owner__c, Room_Type__c, Week_Type__c, Owner_ID__c, Contract_ID__c, GPR_Owner_ID__c, GPR_Resort__c, ROID_Key_Full__c,
        GPR_Resort_Name__c, Owner_Status__c, Resort_ID_v2__c, UnitWeek__c, Usage__c, Year_Last_Banked__c, Days_Past_Due__c, Delinquent__c
        FROM Ownership_Interval__c
        WHERE Contract_ID__c = %s AND Contract_Status__c = 'Active'", $contract_id);
        $intervals = $sf->query($query);

        if (!$intervals) return null;

        return new SfInterval(Arr::first($intervals)->fields);
    }
}
