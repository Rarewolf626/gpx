<?php

namespace GPX\Repository;

use GPX\Api\Salesforce\Salesforce;

class TransactionRepository {

    public static function instance(): TransactionRepository {
        return gpx( TransactionRepository::class );
    }

    public function get_member_transactions($cid)
    {
        global $wpdb;
        $transactions = [];
        $sf = Salesforce::getInstance();

        //get the booking transactions
        $sql = $wpdb->prepare("SELECT t.id, t.transactionType, t.depositID, t.cartID, t.weekId, t.paymentGatewayID, t.data, t.cancelled, u.name as room_type FROM wp_gpxTransactions t
                LEFT OUTER JOIN wp_room r on r.record_id=t.weekId
                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type
                WHERE t.userID=%s", $cid);
        $results = $wpdb->get_results($sql, ARRAY_A);
        $depositIDs = [];

        foreach($results as $k=>$result)
        {
            if(!empty($result['depositID']))
            {
                $sql = $wpdb->prepare("SELECT * FROM wp_gpxDepostOnExchange WHERE id=%s", $result['depositID']);
                $row = $wpdb->get_row($sql);
                if($row) {
                    $dd                        = json_decode($row->data);
                    $depositIDs[$result['id']] = $dd->GPX_Deposit_ID__c ?? null;
                }
            }
            $data = json_decode($result['data'], true);
            unset($results[$k]['data']);

            if(isset($data['creditweekid']))
            {

                //get the deposit details
                $sql = $wpdb->prepare("SELECT * FROM wp_credit WHERE id=%s", $data['creditweekid']);
                $data['depositDetails'] = $wpdb->get_row($sql);
            }
            if(isset($data['resortName']))
            {
                $data['ResortName'] = $data['resortName'];
            }
            $wktype = trim(strtolower($data['WeekType'] ?? ''));
            if($result['transactionType'] != 'booking')
            {
                $wktype = 'misc';
                $data['type'] = ucwords($result['transactionType']);

                //if this is a guest then we need the id of the transaction
                if($data['type'] == 'Guest')
                {
                    $sql = $wpdb->prepare("SELECT weekId, cancelled FROM wp_gpxTransactions WHERE id=%s", $data['transactionID']);
                    $week = $wpdb->get_row($sql);
                    $results[$k]['id'] = $week->weekId;
                    $results[$k]['cancelled'] = $week->cancelled;
                }
                if($data['type'] == 'Deposit')
                {
                    $results[$k]['id'] = $data['Resort_Unit_Week__c'];
                    if(isset($data['creditid']))
                    {

                        $results[$k]['id'] = $data['creditid'];
                    }
                }
                if($data['type'] == 'Extension')
                {
                    $interval = $data['interval'];
                    $creditid = $data['id'];
                    $results[$k]['id'] = $creditid;
                    $data['id'] = $creditid;
                }
            }
            $transactions[$wktype][$result['id']] = array_merge($results[$k], $data);

        }

        //get the deposits
        $sql = $wpdb->prepare("SELECT
            a.*, a.record_id as sfid,
            (SELECT b.unitweek from wp_mapuser2oid b where b.gpx_user_id = a.owner_id ORDER BY b.id LIMIT 1) as unitweek
        FROM wp_credit a
        WHERE
	        a.status != 'DOE'
            AND a.owner_id = %d
            AND ( (a.status != 'Approved') OR (credit_expiration_date IS NOT NULL) )
        ORDER BY a.status, a.id", $cid);
        $results = $wpdb->get_results($sql, ARRAY_A);
        foreach($results as $k=>$result)
        {
            if($result['extension_date'] == '' && strtotime('NOW') < strtotime($result['credit_expiration_date'].' 23:59:59'))
            {
                $results[$k]['extension_valid'] = 1;
            }
            $results[$k]['credit'] = $result['credit_amount'] - $result['credit_used'];

            if(empty($result['unitinterval']))
            {
                //get the unitweek from SF
                $query = $wpdb->prepare("SELECT Resort_Unit_Week__c FROM GPX_Deposit__c where ID = %s", $result['sfid']);
                $sfUnitWeek =  $sf->query($query);
                $UnitWeek = $sfUnitWeek ? $sfUnitWeek[0]->fields : null;
                if(!empty($UnitWeek))
                {
                    $results[$k]['unitinterval'] = $UnitWeek->Resort_Unit_Week__c;
                    $wpdb->update('wp_credit', array('unitinterval'=>$UnitWeek->Resort_Unit_Week__c), array('id'=>$result['id']));
                }
            }

            $depositType = 'depositused';
            if($result['status'] == 'Pending' || ($result['status'] == 'Approved' && $results[$k]['credit'] > 0 && strtotime('NOW') < strtotime($result['credit_expiration_date'].' 23:59:59')))
            {
                $depositType = 'deposit';
            }

            if(!empty($result['credit_action']))
            {
                $results[$k]['status'] = ucwords($result['credit_action']);
            }

            $transactions[$depositType][$k] = $results[$k];


            //if this is a deposit on exchange and it's still pending then don't display the transaction
            if($result['status'] == 'Pending')
            {
                if(in_array($result['id'], $depositIDs))
                {
                    foreach($depositIDs as $ddK=>$ddv)
                    {

                        if($result['id'] == $ddv)
                        {
                            $transactions['exchange'][$ddK]['pending'] = $ddv;
                        }
                    }
                }
            }
        }

        return $transactions;
    }
}
