<?php

namespace GPX\Repository;

use DB;
use SObject;
use Shiftfour;
use Money\Money;
use GPX\Model\Credit;
use GPX\Model\Partner;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GPX\Model\DepositOnExchange;
use GPX\Model\OwnerCreditCoupon;
use GPX\Api\Salesforce\Salesforce;
use GPX\Model\OwnerCreditCouponActivity;
use GPX\DataObject\Transaction\RefundResult;
use GPX\DataObject\Transaction\RefundRequest;

class TransactionRepository {

    public static function instance(): TransactionRepository {
        return gpx(TransactionRepository::class);
    }

    public function get_member_transactions($cid) {
        global $wpdb;
        $sf = Salesforce::getInstance();

        //get the booking transactions
        $sql = $wpdb->prepare("SELECT
                    t.id, t.transactionType, t.depositID, t.cartID, t.weekId, t.paymentGatewayID,
                    t.data, t.cancelled, u.name as room_type
                FROM wp_gpxTransactions t
                LEFT OUTER JOIN wp_room r on r.record_id=t.weekId
                LEFT OUTER JOIN wp_unit_type u on u.record_id=r.unit_type
                WHERE t.userID=%s", $cid);
        $results = $wpdb->get_results($sql, ARRAY_A);
        $depositIDs = [];
        $transactions = [];
        foreach ($results as $k => $result) {
            if (!empty($result['depositID'])) {
                $sql = $wpdb->prepare("SELECT * FROM wp_gpxDepostOnExchange WHERE id=%s", $result['depositID']);
                $row = $wpdb->get_row($sql);
                if ($row) {
                    $dd = json_decode($row->data);
                    $depositIDs[$result['id']] = $dd->GPX_Deposit_ID__c ?? null;
                }
            }
            $data = json_decode($result['data'], true);
            unset($results[$k]['data']);

            if (isset($data['creditweekid'])) {
                //get the deposit details
                $sql = $wpdb->prepare("SELECT * FROM wp_credit WHERE id=%s", $data['creditweekid']);
                $data['depositDetails'] = $wpdb->get_row($sql);
            }
            if (isset($data['resortName'])) {
                $data['ResortName'] = $data['resortName'];
            }
            $wktype = trim(strtolower($data['WeekType'] ?? ''));
            if ($result['transactionType'] != 'booking') {
                $wktype = 'misc';
                $data['type'] = ucwords($result['transactionType']);

                //if this is a guest then we need the id of the transaction
                if ($data['type'] == 'Guest') {
                    $sql = $wpdb->prepare("SELECT weekId, cancelled FROM wp_gpxTransactions WHERE id=%s", $data['transactionID']);
                    $week = $wpdb->get_row($sql);
                    $results[$k]['id'] = $week->weekId;
                    $results[$k]['cancelled'] = $week->cancelled;
                }
                if ($data['type'] == 'Deposit') {
                    $results[$k]['id'] = $data['Resort_Unit_Week__c'];
                    if (isset($data['creditid'])) {

                        $results[$k]['id'] = $data['creditid'];
                    }
                }
                if ($data['type'] == 'Extension') {
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
        foreach ($results as $k => $result) {
            if ($result['extension_date'] == '' && strtotime('NOW') < strtotime($result['credit_expiration_date'] . ' 23:59:59')) {
                $results[$k]['extension_valid'] = 1;
            }
            $result['credit'] = $result['credit_amount'] - $result['credit_used'];
            $results[$k]['credit'] = $result['credit'];

            if (empty($result['unitinterval'])) {
                //get the unitweek from SF
                $query = $wpdb->prepare(/** @lang sfquery */ "SELECT Resort_Unit_Week__c FROM GPX_Deposit__c where ID = %s", $result['sfid']);
                $sfUnitWeek = $sf->query($query);
                $UnitWeek = $sfUnitWeek ? $sfUnitWeek[0]->fields : null;
                if (!empty($UnitWeek)) {
                    $results[$k]['unitinterval'] = $UnitWeek->Resort_Unit_Week__c;
                    $wpdb->update('wp_credit', ['unitinterval' => $UnitWeek->Resort_Unit_Week__c], ['id' => $result['id']]);
                }
            }

            $depositType = 'depositused';
            if ($result['status'] == 'Pending' || ($result['status'] == 'Approved' && $results[$k]['credit'] > 0 && strtotime('NOW') < strtotime($result['credit_expiration_date'] . ' 23:59:59'))) {
                $depositType = 'deposit';
            }

            if (!empty($result['credit_action'])) {
                $results[$k]['status'] = ucwords($result['credit_action']);
            }

            $transactions[$depositType][$k] = $results[$k];


            //if this is a deposit on exchange and it's still pending then don't display the transaction
            if ($result['status'] == 'Pending') {
                if (in_array($result['id'], $depositIDs)) {
                    foreach ($depositIDs as $ddK => $ddv) {

                        if ($result['id'] == $ddv) {
                            $transactions['exchange'][$ddK]['pending'] = $ddv;
                        }
                    }
                }
            }
        }

        return $transactions;
    }

    public function send_to_salesforce(Transaction|int $transaction): array {
        $transaction = $transaction instanceof Transaction ? $transaction : Transaction::find($transaction);
        if (!$transaction) {
            return [
                'success' => false,
                'message' => [
                    'type' => 'nag-fail',
                    'text' => 'Transaction not found',
                ],
            ];
        }
        $sf = Salesforce::getInstance();
        global $wpdb;

        $user = UserMeta::load($transaction->userID);
        $row = $transaction->data;
        $credit = null;
        $crid = match (true) {
            !empty($row['creditweekID']) => $row['creditweekID'],
            !empty($row['creditweekid']) => $row['creditweekid'],
            !empty($row['creditid']) => $row['creditid'],
            !empty($transaction->depositID) => $transaction->depositID,
            !empty($row['ExchangeDepositID']) => DepositOnExchange::select([
                'id',
                'creditID',
            ])->find($row['ExchangeDepositID'])?->creditID,
            !empty($row['actextensionFee']) && !empty($row['id']) => $row['id'],
            default => null,
        };
        if ($crid) {
            hook_credit_import($crid);

            //get the status
            $credit = Credit::find($crid);
            $row['CreditStatus'] = $credit->status;
            $row['CreditSFID'] = $credit->record_id;
            $row['CreditSFName'] = $credit->sf_name;
        }
        $weekDetails = null;
        if ($transaction->weekId) {
            $weekDetails = WeekRepository::instance()->get_week_data($transaction->weekId);
        }

        $row['transactionType'] = $transaction->transactionType;
        $row['cartID'] = $transaction->cartID;
        $row['sessionID'] = $transaction->sessionID;
        $row['userID'] = $transaction->userID;
        $row['resortID'] = $transaction->resortID;
        $row['weekId'] = $transaction->weekId;
        $row['check_in_date'] = $transaction->check_in_date;
        $row['depositID'] = $transaction->depositID;
        $row['paymentGatewayID'] = $transaction->paymentGatewayID;
        $row['transactionRequestId'] = $transaction->transactionRequestId;
        $row['datetime'] = $transaction->datetime;
        $row['authorization_number'] = $transaction->authorization_number;
        $row['merchant_response'] = $transaction->merchant_response;
        $row['cancelled'] = $transaction->cancelled;
        $row['cancelledDate'] = $transaction->cancelledDate;
        $row['source_num'] = $weekDetails?->source_num ?? null;
        $row['source_name'] = $weekDetails?->source_name ?? null;
        $row['source_account'] = $weekDetails?->source_account ?? null;


        $tts = [
            'Exchange' => [
                'actWeekPrice',
                'EXCH240',
                'EXCH241',
                'TRADE250',
                'EXCH',
                'TRADEINT',
                'GPXPPEXCH',
                'EXPROMO',
                'INTERNALI',
                'IEXCH',
                'TRADE260',
                'TRADE262',
                'INTERNALD',
                'EXCHPROMO',
                'RC_62',
                'TRADE261',
                'TRADE251',
            ],
            'CPO' => [
                'CPO242',
                'CPO240',
                'CPO241',
                'CPOINT',
                'CPS',
                'ICPOINT',
            ],
            'Upgrade' => [
                'UNITUPG',
                'Unitupg24',
                'UPSELLPROMO',
            ],
            'Extension' => [
                'EXTEN',
                'EXTEN24',
            ],
            'Late Deposit' => [
                'LATEDEPGPX',
                'LATEDEP',
            ],
            'Rental' => [
                'BONUS',
                'BONUS24',
                'INTRENTAL',
                'BONUS26',
                'RENTAL',
                'RENTPROMO',
            ],
            'Tax' => [
                'TAXCODEGPX',
                'ST10',
                'GPXTAX',
            ],
            'Adjustments' => ['CLEARDEB'],
            'Misc' => ['MISCNGST'],
            'Guest Certificate' => ['GUEST CERT'],
            'GUEST NAME CHANGE' => ['GUEST NAME CHANGE'],
        ];

        //these need to be included in the cancelled to confirmed conditional statement
        $allIncludeConfirmed = array_merge($tts['Exchange'], $tts['Rental']);

        //these need to be excluded from the function that changes the status from Canceled to Confirmed
        $cancelledCheckExcludeConfirmed = array_merge($tts['Extension'], $tts['Late Deposit'], $tts['Adjustments'], $tts['Misc']);

        $extraTransactionTypes = [
            'creditextension' => '0121W000000E02nQAC',
            'guestfee' => '0121W000000E02oQAC',
            'latedepositfee' => '0121W000000E02pQAC',
            'thirdpartydepositfee' => '012Nq0000020q8jIAA',
            'upgradefee' => '0121W000000E02qQAC',
            'booking' => '0121W0000005jWTQAY',
        ];

        $sfData = [
            'GPXTransaction__c' => $transaction->id,
            'EMS_Account__c' => $transaction->userID,
            'RecordTypeId' => match ($row['transactionType']) {
                'booking' => $extraTransactionTypes['booking'],
                'deposit' => $extraTransactionTypes['latedepositfee'],
                'extension' => $extraTransactionTypes['creditextension'],
                'guest' => $extraTransactionTypes['guestfee'],
            },
            'GPX_Deposit__c' => $credit?->record_id ?? $row['CreditSFID'] ?? '',
            'Transaction_Book_Date__c' => $transaction->datetime->startOfDay()->format('Y-m-d H:i:s'),
            'Resort_ID__c' => $weekDetails?->sf_GPX_Resort__c,
            'GPX_Resort__c' => substr($weekDetails?->sf_GPX_Resort__c, 0, 15),
            'Resort_Name__c' => preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;','&'], ' and ', $weekDetails?->ResortName)),
            'GPX_Promo_Code__c' => $row['promoName'] ?? null,
            'Coupon_Discount__c' => (gpx_parse_number($row['couponDiscount'] ?? 0.00) + gpx_parse_number($row['discount'] ?? 0.00) + gpx_parse_number($row['ownerCreditCouponAmount'] ?? 0.00)) ?: 0.00,
            'Purchase_Price__c' => $row['actWeekPrice'] ?? 0,
            'CPO_Fee__c' => $row['CPOFee'] ?? 0,
            'Credit_Extension_Fee__c' => $row['actextensionFee'] ?? 0,
            'Guest_Fee__c' => $row['actguestFee'] ?? $row['GuestFeeAmount'] ?? 0,
            'Late_Deposit_Fee__c' => $row['lateDepositFee'] ?? 0,
            'Third_Party_Fee__c' => $row['thirdPartyDepositFee'] ?? $tsData['actthirdpartydepositFee'] ?? 0,
            'Tax_Paid__c' => $row['acttax'] ?? 0,
            'Upgrade_Fee__c' => $row['actupgradeFee'] ?? 0,
            'Member_First_Name__c' => preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;',' & '], ' and ', $user->getFirstName())),
            'Member_Last_Name__c' => preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;',' & '], ' and ', $user->getLastName())),
            'Member_Email__c' => $user->getEmailAddress(),
            'Account_Type__c' => 'USA GPX Member',
            'Account_Name__c' => $user->Property_Owner,
            'Inventory_Source__c' => match ((int) $row['source_num'] ?? '') {
                1 => 'Owner',
                2 => 'GPR',
                3 => 'Trade Partner',
                default => preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;','&'], ' and ', $row['source_num'] ?? '')),
            },
            'Shift4_Invoice_ID__c' => $row['PaymentID'] ?? null,
            'Resort_Reservation__c' => $row['transactionType'] === 'deposit' ? 'deposit' : $crid,
            'Check_in_Date__c' => $transaction->check_in_date?->format('Y-m-d'),
            'Check_out_Date__c' => $transaction->check_in_date?->clone()?->addDays(7)?->format('Y-m-d'),
            'Week_Type__c' => ucwords($row['transactionType']),
            'Reservation_Reference__c' => $row['deposit'] ?? null,
            'Booked_By__c' => $row['processedBy'] ?? null,
        ];

        if (mb_strtolower($row['WeekType'] ?? '') === 'exchange') {
            $sfData['Purchase_Type__c'] = 'Exchange';
        }
        if (mb_strtolower($row['WeekType'] ?? '') === 'rental') {
            $sfData['Purchase_Type__c'] = 'Rental';
        }
        if ($row['transactionType'] === 'deposit') {
            $sfData['Deposit_Status__c'] = 'Confirmed';
            $sfData['GPX_Deposit__c'] = $credit?->record_id ?? '';
            $sfData['Deposit_Resort_Name__c'] = $credit?->resort_name;
            $sfData['Deposit_Check_In_Date__c'] = $credit?->check_in_date?->format('Y-m-d') ?? $row['Check_In_Date__c'] ?? null;
            $sfData['Deposit_Entitlement_Year__c'] = $credit?->check_in_date?->format('Y') ?? date('Y', strtotime($row['Check_In_Date__c'] ?? 'now'));
            $sfData['Deposit_Unit_Type__c'] = $credit?->unit_type ?? $row['Unit_Type__c'] ?? null;
            $sfData['Deposit_Reference__c'] = $credit?->reservation_number ?? $row['Reservation__c'] ?? null;
        } else {
            $sfData['Unit_Type__c'] = Str::before($row['Size'] ?? '', '/');
        }
        $sfData['GPX_Coupon_Code__c'] = $row['GPX_Coupon_Code__c'] ?? null;
        if (!$transaction->cancelled) {
            if ($row['transactionType'] === 'deposit') {
                $sfData['Deposit_Status__c'] = 'Approved';
            } else {
                $sfData['Reservation_Status__c'] = 'Confirmed';
                if ($row['transactionType'] === 'booking' && mb_strtolower($row['WeekType'] ?? '') === 'exchange' && !empty($row['ExchangeDepositID'])) {
                    $sfData['Reservation_Status__c'] = 'Pending Deposit';
                }
            }
        }
        if (!empty($row['source_account']) && ($row['source_num'] ?? null) == 3) {
            $sfData['Inventory_Owned_by__c'] = $row['source_account'];
        }
        if ($transaction->isBooking()) {
            $sfData['Guest_First_Name__c'] = preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;','&'], ' and ', $row['GuestFirstName'] ?? ''));
            $sfData['Guest_Last_Name__c'] = preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;','&'], ' and ', $row['GuestLastName'] ?? ''));
            $sfData['Guest_Email__c'] = $row['GuestEmail'] ?? '';
            $sfData['Guest_Phone__c'] = substr(preg_replace('/[^0-9]/', '', $row['GuestPhone'] ?? ''), 0, 18);
            $sfData['of_Adults__c'] = $row['Adults'] ?? 1;
            $sfData['of_Children__c'] = $row['Children'] ?? 0;
            $sfData['Special_Requests__c'] = preg_replace('/[^ \w\-\.,]/', '', str_replace(['&amp;','&'], ' and ', $row['specialRequest'] ?? ''));
        }
        if (($row['CPO'] ?? '') === 'Taken') {
            $sfData['CPO_Opt_in__c'] = true;
            $sfData['Flex_Booking__c'] = true;
        }
        if (isset($row['processedBy'])) {
            if ($transaction->userID == $row['processedBy']) {
                $sfData['Booked_By__c'] = 'Owner';
            } else {
                //get the name of the person that booked this
                $bookedby_user_info = get_userdata($row['processedBy']);
                if ($bookedby_user_info) {
                    $sfData['Booked_By__c'] = $bookedby_user_info->first_name . " " . $bookedby_user_info->last_name;
                }
            }
        }

        // send week data
        $sfWeekData = [
            'GpxWeekRefId__c' => (string) $transaction->weekId,
            'Name' => (string) $transaction->weekId,
            'Date_Last_Synced_with_GPX__c' => date('Y-m-d'),
            'of_Adults__c' => $sfData['of_Adults__c'] ?? null,
            'of_Children__c' => $sfData['of_Children__c'] ?? null,
            'Guest_First_Name__c' => $sfData['Guest_First_Name__c'] ?? null,
            'Guest_Last_Name__c' => $sfData['Guest_Last_Name__c'] ?? null,
            'Guest_Email__c' => $sfData['Guest_Email__c'] ?? null,
            'Guest_Phone__c' => $sfData['Guest_Phone__c'] ?? null,
            'Special_Requests__c' => $sfData['Special_Requests__c'] ?? null,
            'Inventory_Source__c' => $sfData['Inventory_Source__c'] ?? null,
            'Inventory_Owned_by__c' => $sfData['Inventory_Owned_by__c'] ?? null,
            'GPX_Resort__c' => $sfData['GPX_Resort__c'],
            'Check_in_Date__c' => $sfData['Check_in_Date__c'],
            'Check_out_Date__c' => $sfData['Check_out_Date__c'],
            'Country__c' => $weekDetails?->Country ?? '',
            'Resort_ID__c' => $sfData['Resort_ID__c'] ?? null,
            'Resort_Reservation__c' => $weekDetails?->resort_confirmation_number ?? '',
            'Resort_Name__c' => $sfData['Resort_Name__c'],
            'Stock_Display__c' => $weekDetails?->StockDisplay ?? '',
            'Unit_Sleeps__c' => $weekDetails?->sleeps ?? '',
            'Unit_Type__c' => $sfData['Unit_Type__c'] ?? null,
            'Week_Type__c' => $sfData['Purchase_Type__c'] ?? '',
            'Flex_Booking__c' => $sfData['Flex_Booking__c'] ?? null,
        ];

        if (isset($row['CreditStatus']) && !in_array($row['CreditStatus'], ['Available', 'Approved', 'Booked'])) {
            $sfData['Reservation_Status__c'] = 'Pending Deposit';
            $sfWeekData['Status__c'] = 'Pending';
        }

        if ($row['transactionType'] !== 'deposit') {
            $sfWeekData['Status__c'] = 'Booked';
            if (($sfData['Reservation_Status__c'] ?? '') == 'Pending Deposit') {
                $sfWeekData['Status__c'] = 'Pending';
            }
            if (($sfData['Reservation_Status__c'] ?? '') == 'Cancelled') {
                $sfWeekData['Status__c'] = 'Available';
            }
        }

        $partner = Partner::where('user_id', $transaction->userID)->first();
        if ($partner) {
            $sfData['Booked_by_TP__c'] = 1;
            $sfWeekData['Booked_by_TP__c'] = 1;
            $sfData['Account_Type__c'] = 'USA GPX Trade Partner';
            $sfData['Account_Name__c'] = $partner->sf_account_id;
            $sfData['Member_Last_Name__c'] = str_replace( '&', 'and', $partner->name);
            $sfData['Purchase_Price__c'] = 0;
            $sfData['CPO_Fee__c'] = 0;
            $sfData['Tax_Paid__c'] = 0;
            $sfData['Upgrade_Fee__c'] = 0;
            $sfData['Guest_Fee__c'] = 0;
            $sfData['Credit_Extension_Fee__c'] = 0;
            $sfData['Late_Deposit_Fee__c'] = 0;
            $sfData['Coupon_Discount__c'] = 0;

            $sfData['Guest_First_Name__c'] = 'Partner';
            $sfData['Guest_Last_Name__c'] = 'Hold';
        }

        // remove empty values
        $sfWeekData = array_filter($sfWeekData, fn($v) => $v !== null && $v !== '');

        if ($transaction->weekId && $transaction->isBooking()) {

            $sfFields = new SObject();
            $sfFields->fields = $sfWeekData;
            $sfFields->type = 'GPX_Week__c';
            $sfWeekAdd = $sf->gpxUpsert('GpxWeekRefId__c', [$sfFields]);
            if (empty($sfWeekAdd[0]->id) || isset($sfWeekAdd[0]->errors)) {
                $errorData = ['error' => $sfWeekAdd, 'upsert' => $sfWeekData];
                $wpdb->update('wp_gpxTransactions', [
                    'sfid' => null,
                    'sfData' => json_encode($errorData),
                ], ['id' => $transaction->id]);

                $to = gpx_admin_notification_email();
                $subject = 'GPX Transaction to SF error on ' . get_site_url();
                $body = '<h2>Transaction: ' . $transaction->id . '</h2><h2>Error</h2><pre>' . print_r($errorData, true) . '</pre>';
                $headers = ['Content-Type: text/html; charset=UTF-8'];
                wp_mail($to, $subject, $body, $headers);
                $message = sprintf("Record %s couldn't be added: %s",
                    $sfData['Reservation_Reference__c'] ?? '',
                    collect($sfWeekAdd[0]->errors)->pluck('message')->join(' & ')
                );

                return [
                    'success' => false,
                    'message' => [
                        'type' => 'nag-fail',
                        'text' => $message,
                    ],
                ];
            }
            $sfData['GPX_Ref__c'] = $sfWeekAdd[0]->id;

        }

        $fields = [
            'GPX_Ref__c',
            'GPX_Deposit__c',
            'GPXTransaction__c',
            'Transaction_Book_Date__c',
            'Booked_By__c',
            'Account_Type__c',
            'Account_Name__c',
            'Shift4_Invoice_ID__c',
            // price / fees
            'Purchase_Price__c',
            'CPO_Fee__c',
            'Tax_Paid__c',
            'Upgrade_Fee__c',
            'Guest_Fee__c',
            'Credit_Extension_Fee__c',
            'Late_Deposit_Fee__c',
            'Third_Party_Fee__c',
            'Coupon_Discount__c',
            // transaction details
            'CPO_Opt_in__c',
            'EMS_Account__c',
            'Purchase_Type__c',
            'Reservation_Status__c',
            'Transaction_On_hold__c',
            'GPX_Coupon_Code__c',
            'GPX_Promo_Code__c',
            //guest details
            'Guest_Cell_Phone__c',
            //don't fill in here either???
            'Guest_Email__c',
            'Guest_Cell_Phone__c',
            'Guest_First_Name__c',
            'Guest_Last_Name__c',
            'Guest_Home_Phone__c',
            'Member_Cell_Phone__c',
            'Member_Email__c',
            'Member_Home_Phone__c',
            'Member_First_Name__c',
            'Member_Last_Name__c',
            'Special_Requests__c',
            'RecordTypeId',
            'Name',
        ];

        $sfTransData = Arr::only($sfData, $fields);
        $sfTransData['Name'] = $sfTransData['GPXTransaction__c'];
        // remove empty values
        $sfTransData = array_filter($sfTransData, fn($v) => $v !== null && $v !== '');

        // send transaction data
        $sfFields = new SObject();
        $sfFields->fields = $sfTransData;
        $sfFields->type = 'GPX_Transaction__c';
        $sfAdd = $sf->gpxUpsert('GPXTransaction__c', [$sfFields]);

        if (empty($sfAdd[0]->id) || isset($sfAdd[0]->errors)) {
            $errorData = ['error' => $sfAdd, 'upsert' => $sfTransData];
            $wpdb->update('wp_gpxTransactions', [
                'sfid' => null,
                'sfData' => json_encode($errorData),
            ], ['id' => $transaction->id]);

            $to = gpx_admin_notification_email();
            $subject = 'GPX Transaction to SF error on ' . get_site_url();
            $body = '<h2>Transaction: ' . $transaction->id . '</h2><h2>Error</h2><pre>' . print_r($errorData, true) . '</pre>';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($to, $subject, $body, $headers);
            $message = sprintf("Record %s couldn't be added: %s",
                $sfData['Reservation_Reference__c'] ?? '',
                collect($sfAdd[0]->errors)->pluck('message')->join(' & ')
            );


            return [
                'success' => false,
                'message' => [
                    'type' => 'nag-fail',
                    'text' => $message,
                ],
            ];
        }

        $wpdb->update('wp_gpxTransactions', [
            'sfid' => $sfAdd[0]->id,
            'sfData' => json_encode(['insert' => $sfData]),
        ], ['id' => $transaction->id]);

        return [
            'success' => true,
            'sfid' => $sfAdd[0]->id,
            'message' => [
                'type' => 'nag-success',
                'text' => sprintf("Record %s added.", $transaction->weekId),
            ],
        ];
    }

    public function cancelTransaction(Transaction $transaction, string $origin = 'system'): void {
    //    dd($transaction);

        if ($transaction->cancelled) {
            // already cancelled
            return;
        }
        if (!$transaction->sfid) {
            $this->send_to_salesforce($transaction);
            $transaction->refresh();
        }

        $sf = Salesforce::getInstance();
        $transData = $transaction->data;

        if ($transaction->isExchange() || $transaction->isDeposit()) {
            //need to refresh the credit
            $credit_id = $transData['creditweekid'] ?? $transData['creditweekID'] ?? $transData['creditid'] ?? 0;
            if ($credit_id) {
                $credit = Credit::find($credit_id);
                if ($credit) {
                    $credit->decrement('credit_used', 1, [
                        'modified_date' => Carbon::now()->format('Y-m-d'),
                    ]);
                    $credit->refresh();
                    $sfFields = new SObject();
                    $sfFields->fields = [
                        'GPX_Deposit_ID__c' => $credit->id,
                        'Credits_Used__c' => $credit->credit_used,
                    ];
                    $sfFields->type = 'GPX_Deposit__c';
                    if ($credit->record_id) {
                        $sf->gpxUpsert('GPX_Deposit_ID__c', [$sfFields]);
                    }
                }
            }
        }

        if ($transaction->isBooking()) {
            // mark the week as available
            $sfFields = new SObject();
            $sfFields->type = 'GPX_Week__c';
            $sfFields->fields = [
                'Status__c' => 'Available',
                'Name' => $transaction->weekId,
                'Booked_by_TP__c' => 0,
                'of_Children__c' => '0',
                'Flex_Booking__c' => '0',
                'Special_Requests__c' => 'N/A',
            ];
            $sf->gpxUpsert('Name', [$sfFields]);
        }

        // push transaction changes
        $sfFields = new SObject();
        $sfFields->type = 'GPX_Transaction__c';
        $sfFields->fields = [
            'GPXTransaction__c' => $transaction->id,
            'Reservation_Status__c' => 'Cancelled',
            'Cancel_Date__c' => date('Y-m-d'),
        ];
        if ($transaction->isCreditTransfer()) {
            $sfFields->fields['Status__c'] = 'Denied';
        }
        if ($transaction->sfid) {
            $sf->gpxUpsert('GPXTransaction__c', [$sfFields]);
        }

        // @todo add the cancellation data to the transaction
        if (is_user_logged_in()) {
            $agent = UserMeta::load(get_current_user_id());
            $agent_id = $agent->id;
            $agent_name = $agent->getName();
        } else {
            $agent_id = 'system';
            $agent_name = 'system';
        }

        // get the cancelledData from the Transaction table
        $canceledData = $transaction->cancelledData ?? [];

        $time = time();
        $canceledData[$time] = [
            'type' => 'cancelled',
            'origin' => $origin,
            'userid' => $agent_id,
            'date' => date('Y-m-d H:i:s', $time),
            'refunded' => 0,
            'coupon' => null,
            'action' => 'cancelled',
            'amount' => 0,
            'by' => $agent_id,
            'name' => $agent_name,
            'agent_name' => $agent_name,
        ];
        // cancelledData

        dump('calling...  $transaction->update',$canceledData);

        $transaction->update([
            'cancelled' => true,
            'cancelledDate' => date('Y-m-d'),
            'cancelledData' => $canceledData,
        ]);

        if ($transaction->isBooking()) {
            $is_booked = Transaction::where('weekId', $transaction->weekId)->cancelled(false)->exists();

            if (!$is_booked) {
                //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
                $week = $transaction->week;

                if ($week->active_specific_date->isPast()) {
                    $week->update(['active' => true]);
                }
            }
        }
    }

    /**
     * @param Transaction $transaction
     * @param RefundRequest $request
     * @param UserMeta|null $agent Agent performing the refund
     *
     * @return RefundResult
     */
    public function refundTransaction(Transaction $transaction, RefundRequest $request, UserMeta|string $agent = null): RefundResult {
        $transData = $transaction->data;
        if ($agent instanceof UserMeta) {
            $agent_id = $agent->id;
            $agent_name = $agent->getName();
        } elseif ($agent === null && is_user_logged_in()) {
            $agent = UserMeta::load(get_current_user_id());
            $agent_id = $agent->id;
            $agent_name = $agent->getName();
        }
        if (!$agent) {
            $agent = 'system';
            $agent_id = 'system';
            $agent_name = 'system';
        }

        $partner = Partner::where('user_id', $transaction->userID)->first();
        $refunds = new RefundResult();

        if ($partner && $transaction->isBooking()) {
            if ($transaction->isExchange()) {
                $partner->update([
                    'no_of_rooms_received_taken' => DB::raw('no_of_rooms_received_taken - 1'),
                    'trade_balance' => DB::raw('trade_balance + 1'),
                ]);
            } else {
                //adjust the balance
                $tpTransData = $transData;
                $tpTransData['cancelled'] = date('m/d/Y');

                $pdid = DB::table('wp_partner_debit_balance')->insertGetId([
                    'user' => $partner->user_id,
                    'data' => json_encode($tpTransData),
                    'amount' => (float) $tpTransData['Paid'],
                ]);

                $debit_id = $partner->debit_id;
                $adjData = $partner->adjData;

                $debit_id[] = $pdid;
                $adjData[time()] = 'cancelled';
                $debit_balance = (float) $partner->debit_balance - (float) $tpTransData['Paid'];

                $partner->update([
                    'adjData' => $adjData,
                    'debit_id' => $debit_id,
                    'debit_balance' => (int) $debit_balance,
                ]);
            }

        }

        $time = time();
        $canceledData = $transaction->cancelledData ?? [];
        $cancellations = collect(array_values($canceledData));

        if ($partner || ($agent_id !== 'system' && !gpx_is_administrator(false, $agent_id))) {
            // if the transaction was made by a partner or the cancellation done by a non-administrator, only refund as credit.
            $request->cancel = true;
            $request->amount = 0.00;
            $has_flex = $transaction->hasFlexBooking();
            $request->booking = $has_flex;
            $request->booking_amount = $has_flex ? round(($transData['actWeekPrice'] ?? 0.00) - ($cancellations->where('type', '==', 'erFee')->sum('amount')), 2) : 0.00;
            $request->cpo = false;
            $request->cpo_amount = 0.00;
            $request->upgrade = $has_flex;
            $request->upgrade_amount = $has_flex ? round(($transData['actupgradeFee'] ?? 0.00) - ($cancellations->where('type', '==', 'upgradefee')->sum('amount')), 2) : 0.00;
            $request->guest = $has_flex;
            $request->guest_amount = $has_flex ? round(($transData['actguestFee'] ?? $transData['GuestFeeAmount'] ?? 0.00) - ($cancellations->where('type', '==', 'guestfeeamount')->sum('amount')), 2) : 0.00;
            $request->late = false;
            $request->late_amount = 0.00;
            $request->third_party = false;
            $request->third_party_amount = 0.00;
            $request->extension = false;
            $request->extension_amount = 0.00;
            $request->tax = false;
            $request->tax_amount = 0.00;
        }

        // the already refunded amounts
        $refunded = gpx_money($cancellations->sum('amount'));
        $refunded_card = gpx_money($cancellations->where('action', '!=', 'credit')->sum('amount'));
        $refunds->previous = $refunded;

        // the amount paid on the credit card
        $paid = gpx_money($transData['Paid'] ?? 0.00);
        // the amount paid with credit
        $occoupon = gpx_money($transData['ownerCreditCouponAmount'] ?? 0.00);
        // the total amount paid
        $total = $paid->add($occoupon);
        // the remaining balance is the total amount paid minus the amount already refunded
        $balance = $total->subtract($refunded);

        $refund = gpx_money(0.00);
        if ($request->booking) {
            $max = gpx_money($transData['actWeekPrice'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'erFee')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->booking_amount));
            $request->booking_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->booking_amount = 0.00;
        }
        if ($request->cpo) {
            $max = gpx_money($transData['actcpoFee'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'cpofee')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->cpo_amount));
            $request->cpo_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->cpo_amount = 0.00;
        }
        if ($request->upgrade) {
            $max = gpx_money($transData['actupgradeFee'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'upgradefee')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->upgrade_amount));
            $request->upgrade_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->upgrade_amount = 0.00;
        }
        if ($request->guest) {
            $max = gpx_money($transData['actguestFee'] ?? $transData['GuestFeeAmount'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'guestfeeamount')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->guest_amount));
            $request->guest_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->guest_amount = 0.00;
        }
        if ($request->late) {
            $max = gpx_money($transData['lateDepositFee'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'latedepositfee')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->late_amount));
            $request->late_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->late_amount = 0.00;
        }
        if ($request->third_party) {
            $max = gpx_money($transData['thirdPartyDepositFee'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'thirdpartydepositfee')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->third_party_amount));
            $request->third_party_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->third_party_amount = 0.00;
        }
        if ($request->extension) {
            $max = gpx_money($transData['actextensionFee'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'creditextensionfee')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->extension_amount));
            $request->extension_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->extension_amount = 0.00;
        }
        if ($request->tax) {
            $max = gpx_money($transData['acttax'] ?? 0.00)
                ->subtract(gpx_money($cancellations->where('type', '==', 'tax')->sum('amount')));
            $amount = Money::min($balance, $max, gpx_money($request->tax_amount));
            $request->tax_amount = round($amount->getAmount() / 100, 2);
            $balance = $balance->subtract($amount);
            $refund = $refund->add($amount);
        } else {
            $request->tax_amount = 0.00;
        }

        // the maximum amount that can be refunded to credit card is the amount paid minus the amount already refunded to credit card
        // it also cannot be more than the remaining balance
        $refundable = Money::min($paid->subtract($refunded_card), $total->subtract($refunded));
        $refunds->setRequested($request);
        // the amount to be refunded to credit card
        // cannot refund more than the max refundable amount
        $refunds->refund = Money::min($refund, $refundable, gpx_money($request->amount));
        // the remaining amount will be refunded as credit
        $refunds->credit = $refund->subtract($refunds->refund);

        $message = [];

        if ($refunds->hasCardRefund()) {
            // refund to credit card
            try {
                $shift4 = new Shiftfour();
                $shift4->shift_refund($transaction->id, $refunds->card());
                $message[] = sprintf('%s has been refunded to the credit card.', gpx_currency($refunds->refund));
            } catch (\Exception $e) {
                gpx_logger()->error('Shift4 refund failed', [
                    'exception' => $e,
                    'transaction' => $transaction->id,
                    'amount' => $refunds->card(),
                ]);
                $refunds->setResult(false, $e->getMessage());

                return $refunds;
            }

            try {
                if (!$transaction->sfid) {
                    throw new \Exception('Transaction not in Salesforce');
                }
                // send to salesforce
                $sf = Salesforce::getInstance();
                $sfFields = new SObject();
                $sfFields->type = 'GPX_Transaction__c';
                $sfFields->fields = [
                    'GPXTransaction__c' => $transaction->id,
                    'EMS_Account__c' => $transaction->userID,
                    // total refunded to card (previous + current)
                    'Credit_Card_Refund__c' => round($refunds->card() + ($refunded_card->getAmount() / 100), 2),
                ];
                $sf->gpxUpsert('GPXTransaction__c', [$sfFields]);
            } catch (\Exception $e) {
                gpx_logger()->error('Failed to send refund details to salesforce', [
                    'exception' => $e,
                    'transaction' => $transaction->id,
                    'amount' => $refunds->card(),
                ]);
                $refunds->setResult(false, 'The card was refunded successfully but pushing the refund to salesforce failed.');
            }
        }

        if ($refunds->hasCreditRefund()) {
            // generate a coupon for the amount refunded
            $slug = OwnerCreditCoupon::generateUniqueSlug($transaction->weekId . $transaction->userID);

            $coupon = new OwnerCreditCoupon([
                'name' => $transaction->weekId ?? $transaction->id,
                'couponcode' => $slug,
                'active' => true,
                'singleuse' => false,
                'expirationDate' => Carbon::now()->addYear()->format('Y-m-d'),
                'comments' => 'Refund issued on transaction ' . $transaction->id,
            ]);
            $coupon->save();

            $activity = new OwnerCreditCouponActivity([
                'couponID' => $coupon->id,
                'amount' => $refunds->credit(),
                'activity' => 'created',
                'xref' => $transaction->id,
                'activity_comments' => date('m/d/Y H:i') . ': ' . $coupon->comments,
                'userID' => $agent_id,
            ]);
            $activity->save();

            DB::table('wp_gpxOwnerCreditCoupon_owner')->insert([
                'couponID' => $coupon->id,
                'ownerID' => $transaction->userID,
            ]);

            $message[] = sprintf('A %s credit coupon %s has been generated.', gpx_currency($refunds->credit), $slug);
            $refunds->setCoupon($coupon);
        }

        $card = $refunds->card();
        $credit = $refunds->credit();

        $fees = [
            'erFee' => $request->booking_amount,
            'cpofee' => $request->cpo_amount,
            'upgradefee' => $request->upgrade_amount,
            'guestfeeamount' => $request->guest_amount,
            'latedepositfee' => $request->late_amount,
            'thirdpartydepositfee' => $request->third_party_amount,
            'creditextensionfee' => $request->extension_amount,
            'tax' => $request->tax_amount,
        ];

        foreach ($fees as $type => $val) {
            if ($val > 0) {
                $value = min($card, $val);
                if ($value) {
                    $canceledData[$time] = [
                        'type' => $type,
                        'origin' => $request->origin,
                        'userid' => $agent_id,
                        'date' => date('Y-m-d H:i:s', $time),
                        'refunded' => $value,
                        'coupon' => null,
                        'action' => 'refund',
                        'amount' => $value,
                        'by' => get_current_user_id(),
                        'name' => $agent_name,
                        'agent_name' => $agent_name,
                    ];
                    $time++;
                    $val -= $value;
                    $card = round($card - $value, 2);
                }

                if ($val > 0) {
                    $value = min($credit, $val);
                    $canceledData[$time] = [
                        'type' => $type,
                        'origin' => $request->origin,
                        'userid' => $agent_id,
                        'date' => date('Y-m-d H:i:s', $time),
                        'refunded' => 0.00,
                        'coupon' => $refunds->coupon?->id,
                        'action' => 'credit',
                        'amount' => $value,
                        'by' => get_current_user_id(),
                        'name' => $agent_name,
                        'agent_name' => $agent_name,
                    ];
                    $time++;
                    $credit = round($credit - $value, 2);
                }
            }
        }

        $transData = $transaction->data ?? [];
        $transData['refunded'] = $refunds->total();
        $transaction->update([
            'cancelledData' => $canceledData,
            'data' => $transData,
        ]);


        $refunds->setResult(true, implode("\n", $message));

        return $refunds;
    }
}
