<?php

use GPX\Model\Week;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use Illuminate\Support\Carbon;
use GPX\DataObject\Resort\AvailabilityCalendarSearch;
use GPX\Model\PreHold;
use GPX\Repository\WeekRepository;
use Illuminate\Support\Arr;

/**
 *
 *
 *
 *
 */
function gpx_import_rooms() {
    global $wpdb;

    $sql = "SELECT * FROM import_rooms WHERE imported=0 LIMIT 200";
    $rows = $wpdb->get_results($sql);

    foreach ($rows as $row) {

        $wpdb->update('import_rooms', ['imported' => 1], ['id' => $row->id]);

        $resortName = $row->ResortName;
        $resortName = str_replace("- VI", "", $resortName);
        $resortName = trim($resortName);
        $sql = $wpdb->prepare("SELECT id, resortID FROM wp_resorts WHERE ResortName=%s", $resortName);
        $resort = $wpdb->get_row($sql);

        if (empty($resort)) {
            $exception = json_encode($row);
            $wpdb->insert("reimport_exceptions", ['type' => 'trade partner inventory resort', 'data' => $exception]);
            continue;
        } else {
            $resortID = $resort->id;
            $daeResortID = $resort->resortID;
        }

        $unitType = $row->Unit_Type;
        $sql = $wpdb->prepare("SELECT record_id FROM wp_unit_type WHERE resort_id=%s AND name=%s", [
            $resortID,
            $unitType,
        ]);
        $unitID = $wpdb->get_var($sql);

        $bs = explode("/", $unitType);
        $beds = $bs[0];
        $beds = str_replace("b", "", $beds);
        if ($beds == 'St') {
            $beds = 'STD';
        }
        $sleeps = $bs[1];
        if (empty($unitID)) {
            $insert = [
                'name' => $unitType,
                'create_date' => date('Y-m-d'),
                'number_of_bedrooms' => $beds,
                'sleeps_total' => $sleeps,
                'resort_id' => $resortID,
            ];
            $wpdb->insert('wp_unit_type', $insert);
            $unitID = $wpdb->insert_id;
        }

        $active = '1';
        if ($row->active == 'FALSE') {
            $active = '0';
        }
        $type = '3';
        if (trim($row->Type) == 'Exchange') {
            $type = '1';
        }
        $spi = '0';
        if (!empty($row->source_partner_id)) {
            $spi = $row->source_partner_id;
        }
        $wpdb->delete('wp_room', ['record_id' => $row->record_id]);
        $record_id = trim($row->record_id);
        $wp_room = [
            'record_id' => $record_id,
            'active_specific_date' => date("Y-m-d 00:00:00"),
            'check_in_date' => date('Y-m-d 00:00:00', strtotime($row->StartDate)),
            'check_out_date' => date('Y-m-d 00:00:00', strtotime($row->StartDate . ' +7 days')),
            'resort' => $resortID,
            'unit_type' => $unitID,
            'source_num' => '2',
            'source_partner_id' => $spi,
            'sourced_by_partner_on' => '',
            'resort_confirmation_number' => '',
            'active' => $active,
            'availability' => '1',
            'available_to_partner_id' => '0',
            'type' => $type,
            'active_rental_push_date' => date('Y-m-d', strtotime($row->active_rental_push_date)),
            'price' => $row->Price,
            'points' => null,
            'note' => 'From: ' . $row->note,
            'given_to_partner_id' => null,
            'import_id' => '0',
            'active_type' => '0',
            'active_week_month' => '0',
            'create_by' => '5',
            'archived' => '0',
        ];

        $sql = $wpdb->prepare("SELECT record_id FROM wp_room WHERE record_id=%s", $record_id);
        $week = $wpdb->get_row($sql);
        if (!empty($week)) {
            $wpdb->update('wp_room', $wp_room, ['record_id' => $record_id]);
        } else {
            $wpdb->insert('wp_room', $wp_room);
        }
    }

    $sql = "SELECT COUNT(id) as cnt FROM import_rooms WHERE imported=0";
    $remain = $wpdb->get_var($sql);

    if ($remain > 0) {
        echo '<script>location.reload();</script>';
        exit;
    }

    wp_send_json(['remaining' => $remain]);
}

add_action('wp_ajax_gpx_import_rooms', 'gpx_import_rooms');


/**
 *
 *
 *
 *
 */
function gpx_tp_inventory() {
    global $wpdb;
    $data = [];

    /** @var ?array $search */
    $search = isset($_REQUEST['filter']) ? json_decode(stripslashes($_REQUEST['filter']), true) : null;

    $query = DB::table('wp_room', 'a')
               ->where(fn($query) => $query
                   ->where('a.check_in_date', '!=', '0000-00-00 00:00:00')
                   ->orWhere('a.check_out_date', '!=', '0000-00-00 00:00:00')
               )
               ->whereRaw('DATE(a.check_in_date) >= CURRENT_DATE()')
               ->where('a.resort', '!=', '0')
               ->whereNotNull('a.resort')
               ->whereNotNull('a.unit_type')
               ->where('a.archived', '=', 0)
               ->when($search, fn($query) => $query->where(function ($query) use ($search) {
                   foreach ($search as $sk => $sv) {
                       if ($sk == 'record_id') {
                           $query->orWhereRaw('CAST(a.record_id as CHAR) LIKE ?', gpx_esc_like($sv) . '%');
                       } elseif ($sk == 'check_in_date') {
                           $query->orWhereDate('a.check_in_date', '=', date('Y-m-d', strtotime($sv)));
                       } elseif ($sk == 'ResortName') {
                           $query->orWhereRaw("EXISTS (SELECT b.id FROM wp_resorts b WHERE b.id = a.resort AND b.ResortName LIKE ? LIMIT 1)", '%' . gpx_esc_like($sv) . '%');
                       } elseif ($sk == 'status') {
                           $query->when($sv === 'Available', fn($query) => $query
                               ->where(fn($query) => $query
                                   ->where('a.active', '=', '1')
                                   ->orWhere(fn($query) => $query
                                       ->whereRaw('NOT EXISTS (SELECT h.id FROM wp_gpxPreHold h WHERE h.propertyID=a.record_id AND h.released=0 LIMIT 1)')
                                       ->whereRaw('NOT EXISTS (SELECT t.id FROM wp_gpxTransactions t WHERE t.weekId=a.record_id AND (t.cancelled=0 OR t.cancelled IS NULL) LIMIT 1)')
                                   )
                               )
                           );
                           $query->when($sv === 'Held', fn($query) => $query
                               ->where(fn($query) => $query
                                   ->where('a.active', '=', '0')
                                   ->whereRaw('EXISTS (SELECT h.id FROM wp_gpxPreHold h WHERE h.propertyID=a.record_id AND h.released=0 LIMIT 1)')
                               )
                           );
                           $query->when($sv === 'Booked', fn($query) => $query
                               ->where(fn($query) => $query
                                   ->where('a.active', '=', '0')
                                   ->whereRaw('EXISTS (SELECT t.id FROM wp_gpxTransactions t WHERE t.weekId=a.record_id AND (t.cancelled=0 OR t.cancelled IS NULL) LIMIT 1)')
                               )
                           );
                       } else {
                           $query->orWhere('a.' . $sk, 'LIKE', '%' . gpx_esc_like($sv) . '%');
                       }
                   }

                   return $query;
               }));

    $data['total'] = $query->count('a.record_id');

    $results = $query
        ->selectRaw('a.*, b.ResortName')
        ->addSelect(DB::raw('(SELECT u.name FROM wp_unit_type u WHERE u.record_id=a.unit_type LIMIT 1) as unit_type_id'))
        ->addSelect(DB::raw('EXISTS (SELECT h.id FROM wp_gpxPreHold h WHERE h.propertyID=a.record_id AND h.released=0 LIMIT 1) as held'))
        ->addSelect(DB::raw('EXISTS (SELECT t.id FROM wp_gpxTransactions t WHERE t.weekId=a.record_id AND (t.cancelled=0 OR t.cancelled IS NULL) LIMIT 1) as booked'))
        ->join('wp_resorts as b', 'b.id', '=', 'a.resort')
        ->when(isset($_REQUEST['offset']), fn($query) => $query->skip($_REQUEST['offset']))
        ->when(isset($_REQUEST['limit']), fn($query) => $query->take($_REQUEST['limit']))
        ->when(isset($_REQUEST['sort']), fn($query) => $query->orderBy($_REQUEST['sort'], gpx_esc_orderby($_REQUEST['order'])))
        ->get()->toArray();

    $i = 0;

    foreach ($results as $result) {
        if ($result->active) {
            $result->status = 'Available';
        } elseif ($result->booked) {
            $result->status = 'Booked';
        } elseif ($result->held) {
            $result->status = 'Held';
        } else {
            $result->status = 'Available';
        }

        $data['rows'][$i]['active'] = $result->active ? 'Yes' : 'No';
        if (!$result->active && !empty($_REQUEST['user'])) {
            //was this held by this owner
            $sql = $wpdb->prepare("SELECT id FROM wp_gpxPreHold WHERE propertyID=%s AND user=%s AND released=0", [
                $result->record_id,
                $_REQUEST['user'],
            ]);
            $held = $wpdb->get_row($sql);
            if ($held) $data['rows'][$i]['active'] = 'Held';
        }
        $data['rows'][$i]['status'] = $result->status;
        $data['rows'][$i]['record_id'] = $result->record_id;
        $data['rows'][$i]['create_date'] = $result->create_date;
        $data['rows'][$i]['last_modified_date'] = $result->last_modified_date;
        $data['rows'][$i]['create_date'] = '<span data-date="' . date('Y-m-d', strtotime($result->create_date)) . '">' . date('m/d/Y', strtotime($result->create_date)) . '</span>';
        $data['rows'][$i]['last_modified_date'] = '<span data-date="' . date('Y-m-d', strtotime($result->last_modified_date)) . '">' . date('m/d/Y', strtotime($result->last_modified_date)) . '</span>';
        $data['rows'][$i]['check_in_date'] = '<span data-date="' . date('Y-m-d', strtotime($result->check_in_date)) . '">' . date('m/d/Y', strtotime($result->check_in_date)) . '</span>';
        $data['rows'][$i]['check_out_date'] = '<span data-date="' . date('Y-m-d', strtotime($result->check_out_date)) . '">' . date('m/d/Y', strtotime($result->check_out_date)) . '</span>';
        $data['rows'][$i]['price'] = ($result->type != '1' && !empty($result->price)) ? '$' . $result->price : '';
        $data['rows'][$i]['unit_type_id'] = $result->unit_type_id;

        if ($result->source_partner_id) {
            $spid = $wpdb->prepare("SELECT display_name FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND ID = %s LIMIT 1", $result->source_partner_id);
            $spid_result = $wpdb->get_var($spid);
        } else {
            $spid_result = '';
        }
        $data['rows'][$i]['source_partner_id'] = $spid_result;
        $data['rows'][$i]['ResortName'] = $result->ResortName;

        if ($result->availability == 0) {
            $availability = "--";
        } elseif ($result->availability == 1) {
            $availability = "All";
        } elseif ($result->availability == 2) {
            $availability = "Owner Only";
        } else {
            $availability = "Partner Only";
        }


        $data['rows'][$i]['availability'] = $availability;

        $avltop = $wpdb->prepare("SELECT * FROM `wp_partner` WHERE record_id = %s", $result->available_to_partner_id);
        $avltop_result = $wpdb->get_results($avltop);

        $data['rows'][$i]['available_to_partner_id'] = Arr::first($avltop_result)?->name;

        $type = "";
        if (isset($result->type)) {
            if ($result->type == 1) {
                $type = "Exchange";
            } elseif ($result->type == 2) {
                $type = "Rental";
            } elseif ($result->type == 3) {
                $type = 'Exchange/Rental';
            } else {
                $type = "--";
            }

        }

        $data['rows'][$i]['type'] = $type;

        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_tp_inventory', 'gpx_tp_inventory');
add_action('wp_ajax_nopriv_gpx_tp_inventory', 'gpx_tp_inventory');


/**
 *
 *
 *
 *
 */
function gpx_tp_activity() {
    global $wpdb;

    $data = [];
    $table = [];

    $id = $_GET['id'];
    //get the rooms added
    $sql = $wpdb->prepare("SELECT a.record_id, a.check_in_date, a.resort_confirmation_number, a.sourced_by_partner_on, b.ResortName, c.name AS unit_type  FROM wp_room a
              INNER JOIN wp_resorts b ON b.id=a.resort
              INNER JOIN wp_unit_type c ON c.record_id=a.unit_type
              WHERE source_partner_id=%s and archived=0 ORDER BY sourced_by_partner_on", $id);
    $results = $wpdb->get_results($sql);

    $i = 0;
    foreach ($results as $rv) {
        $k = strtotime($rv->sourced_by_partner_on) . $i;

        $checkin = '';
        if (!empty($rv->check_in_date)) {
            $checkin = date('m/d/Y', strtotime($rv->check_in_date));
        }

        $table[$k]['edit'] = '<a data-back="#tp_id_' . $id . '" href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id=' . $rv->record_id . '" target="_blank"><i class="fa fa-pencil"></i></a>';
        $table[$k]['ID'] = $rv->record_id;
        $table[$k]['activity'] = 'Deposit';
        $table[$k]['check_in_date'] = $checkin;
        $table[$k]['resort'] = $rv->ResortName;
        $table[$k]['unit_type'] = $rv->unit_type;
        $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
        $table[$k]['debit'] = '';

        $i++;
    }
    //get the rooms booked
    $sql = $wpdb->prepare("SELECT t.id, t.transactionType, t.data, t.datetime, a.record_id, a.price, a.check_in_date, a.resort_confirmation_number, b.ResortName, c.name AS unit_type  FROM
              wp_gpxTransactions t
              LEFT OUTER JOIN wp_room a ON t.weekID=a.record_id
              LEFT OUTER JOIN wp_resorts b ON b.id=a.resort
              LEFT OUTER JOIN wp_unit_type c on c.record_id=a.unit_type
              WHERE t.userID=%s
              AND t.cancelled = 0
              ORDER BY t.datetime", $id);
    $results = $wpdb->get_results($sql);

    foreach ($results as $rv) {
        $k = strtotime($rv->datetime) . $i;

        $data = json_decode($rv->data);

        $debit = '';
        if (mb_strtolower($data->WeekType ?? '') == 'rental') {
            $debit = "-$" . $data->Paid;
        }

        $activity = ucwords($rv->transactionType);
        if ($rv->transactionType == 'pay_debit') {
            $activity = 'Pay Debit';
            $debit = "$" . $data->Paid;
        }

        $checkin = '';
        if (!empty($rv->check_in_date)) {
            $checkin = date('m/d/Y', strtotime($rv->check_in_date));
        }
        $table[$k]['edit'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_view&id=' . esc_attr($rv->id) . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a>';
        $table[$k]['ID'] = $rv->record_id;
        $table[$k]['activity'] = $activity;
        $table[$k]['check_in_date'] = $checkin;
        $table[$k]['resort'] = $rv->ResortName;
        $table[$k]['unit_type'] = $rv->unit_type;
        $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number ?? null;
        $table[$k]['guest_name'] = $data->GuestName ?? null;
        $table[$k]['debit'] = $debit;

        $i++;
    }

    $sql = $wpdb->prepare("SELECT t.id, t.release_on, a.record_id, a.check_in_date, a.resort_confirmation_number, b.ResortName, c.name AS unit_type FROM wp_gpxPreHold t
              INNER JOIN wp_room a ON t.weekID=a.record_id
              INNER JOIN wp_resorts b ON b.id=a.resort
              INNER JOIN wp_unit_type c on c.record_id=a.unit_type
              WHERE t.user=%s AND t.released=0 ORDER BY t.release_on", $id);
    $results = $wpdb->get_results($sql);

    foreach ($results as $rv) {
        $k = strtotime($rv->release_on) . $i;

        $table[$k]['edit'] = '<a href="#" data-id="' . $rv->id . '" class="release-week" title="release"><i class="fa fa-calendar-times-o" aria-hidden="true"></i></a>';
        $table[$k]['ID'] = $rv->record_id;
        $table[$k]['activity'] = 'Held';
        $table[$k]['check_in_date'] = date('m/d/Y', strtotime($rv->check_in_date));
        $table[$k]['resort'] = $rv->ResortName;
        $table[$k]['unit_type'] = $rv->unit_type;
        $table[$k]['resort_confirmation_number'] = $rv->resort_confirmation_number;
        $table[$k]['guest_name'] = '';
        $table[$k]['debit'] = '';

        $i++;
    }
    ksort($table);

    $table = array_values($table);

    wp_send_json($table);
}

add_action('wp_ajax_gpx_tp_activity', 'gpx_tp_activity');


/**
 *
 *
 *
 *
 */
function gpx_Room() {
    global $wpdb;
    $data = [];
    $search = isset($_REQUEST['filter']) ? json_decode(stripslashes($_REQUEST['filter']), true) : null;
    $query = DB::table('wp_room', 'r')
               ->join('wp_unit_type as u', 'u.record_id', '=', 'r.unit_type')
               ->join('wp_resorts as rs', 'rs.id', '=', 'r.resort')
               ->leftJoin('wp_partner as ps', 'r.source_partner_id', '=', 'ps.user_id')
               ->leftJoin('wp_partner as pg', 'r.given_to_partner_id', '=', 'ps.user_id')
               ->when(isset($_REQUEST['Archived']), fn($query) => $query->where('r.archived', '=', $_REQUEST['Archived']))
               ->when(!isset($_REQUEST['future_dates']) || $_REQUEST['future_dates'] != '0', fn($query) => $query->whereRaw('DATE(r.check_in_date) >= CURRENT_DATE()'))
               ->when($search, function ($query) use ($search) {
                   foreach ($search as $sk => $sv) {
                       $query->when($sk == 'record_id', fn($query) => $query->where('r.record_id', 'LIKE', '%' . gpx_esc_like($sv) . '%'));
                       $query->when($sk == 'check_in_date', fn($query) => $query->whereDate('check_in_date', '=', date('Y-m-d', strtotime($sv))));
                       $query->when($sk == 'active', fn($query) => $query->where('r.active', '=', mb_strtolower($sv) == 'yes' ? 1 : 0));
                       $query->when(!in_array($sk, [
                           'record_id',
                           'check_in_date',
                           'active',
                       ]), fn($query) => $query->where($sk, 'LIKE', '%' . gpx_esc_like($sv) . '%'));
                   }
               });

    $data['total'] = $query->count('r.record_id');

    $results = $query
        ->selectRaw('r.*, u.name as room_type, rs.ResortName, ps.name as source_name, pg.name as given_name,
            exists (select `weekId` from `wp_gpxTransactions` where `weekId` = r.`record_id` AND `cancelled` = 0 LIMIT 1) as booked,
            exists (select `weekId` from `wp_gpxPreHold` where `released` = 0 AND `propertyID` = r.`record_id` LIMIT 1) as held')
        ->when(isset($_REQUEST['offset']), fn($query) => $query->skip($_REQUEST['offset']))
        ->when(isset($_REQUEST['limit']), fn($query) => $query->take($_REQUEST['limit']))
        ->when((isset($_REQUEST['from_date']) && isset($_REQUEST['to_date'])), fn($query) => $query->take(20))
        ->when(isset($_REQUEST['sort']), fn($query) => $query->orderBy($_REQUEST['sort'], gpx_esc_orderby($_REQUEST['order'])))
        ->get()->toArray();

    $i = 0;
    foreach ($results as $result) {
        //what is the status
        $result->status = 'Available';
        if ($result->archived) $result->status = 'Archived';
        if ($result->held) {
            $result->status = 'Held';
        }
        if ($result->booked) {
            $result->status = 'Booked';
        }

        $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id=' . $result->record_id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
        $data['rows'][$i]['action'] .= '&nbsp;&nbsp;<a href="#" class="deleteWeek" data-id=' . $result->record_id . '"><i class="fa fa-trash" aria-hidden="true" style="color: #d9534f;"></i></a>';
        $data['rows'][$i]['record_id'] = $result->record_id;
        $data['rows'][$i]['create_date'] = $result->create_date;
        $data['rows'][$i]['last_modified_date'] = $result->last_modified_date;
        $data['rows'][$i]['check_in_date'] = date('m/d/Y', strtotime($result->check_in_date));
        $data['rows'][$i]['check_out_date'] = date('m/d/Y', strtotime($result->check_out_date));
        $data['rows'][$i]['price'] = '';
        $data['rows'][$i]['room_type'] = $result->room_type;
        $data['rows'][$i]['unit_type_id'] = $result->room_type;
        if ($result->type != '1' && !empty($result->price)) {
            $data['rows'][$i]['price'] = '$' . $result->price;
        }
        $data['rows'][$i]['source_partner_id'] = $result->source_name;
        $data['rows'][$i]['ResortName'] = $result->ResortName;

        $data['rows'][$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
        $data['rows'][$i]['resort_confirmation_number'] = $result->resort_confirmation_number;
        $data['rows'][$i]['active'] = $result->active;

        $data['rows'][$i]['available_to_partner_id'] = $result->given_name;
        $data['rows'][$i]['room_status'] = $result->status;


        $active = "";
        if (isset($result->active)) {

            if ($result->active == 1) {
                $active = "Yes";
            } else {
                $active = "No";
                if (isset($result->Held) && $result->Held > 0) {
                    $active = 'Held';

                    $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id=' . $result->record_id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';

                }
            }
        }

        $archive = "";
        if (isset($result->archived)) {

            if ($result->archived == 1) {
                $archive = "Yes";
                $data['rows'][$i]['action'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id=' . $result->record_id . '"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
            } else {
                $archive = "No";
            }
        }

        $data['rows'][$i]['active'] = $active;
        $data['rows'][$i]['archived'] = $archive;

        $type = "";
        if (isset($result->type)) {
            if ($result->type == 1) {
                $type = "Exchange";
            } elseif ($result->type == 2) {
                $type = "Rental";
            } elseif ($result->type == 3) {
                $type = 'Exchange/Rental';
            } else {
                $type = "--";
            }

        }

        $data['rows'][$i]['type'] = $type;

        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_Room', 'gpx_Room');
add_action('wp_ajax_nopriv_gpx_Room', 'gpx_Room');

/**
 *
 *
 *
 *
 */
function gpx_remove_room() {
    global $wpdb;

    $return['success'] = false;
    if (!empty($_REQUEST['id'])) {
        $return['success'] = true;

        $sql = $wpdb->prepare("SELECT source_partner_id, update_details FROM wp_room WHERE record_id=%s", $_REQUEST['id']);
        $roomRow = $wpdb->get_row($sql);

        $sql = $wpdb->prepare("SELECT id FROM wp_gpxTransactions WHERE weekId=%s", $_REQUEST['id']);
        $row = $wpdb->get_row($sql);

        //Need to add capability to delete/archive weeks. If a week has had a booking on it, it should only be able to be archived (to keep the history intact). Weeks without a booking can be truly deleted from the database.
        if (empty($row)) {
            $wpdb->delete('wp_room', ['record_id' => $_REQUEST['id']]);
            $return['deleted'] = true;
        } else {
            $row = $roomRow;

            $updateDets = json_decode($row->update_details, ARRAY_A);

            $updateDets[strtotime('NOW')] = [
                'update_by' => get_current_user_id(),
                'details' => base64_encode(json_encode(['room_archived' => date('m/d/Y H:i:s')])),
            ];

            $data = [
                'active' => '0',
                'archived' => '1',
                'update_details' => json_encode($updateDets),
            ];

            $wpdb->update('wp_room', $data, ['record_id' => $_REQUEST['id']]);

            $return['success'] = true;
            $return['archived'] = true;
        }
        //if this was a trade partner then adjust their rooms given
        if ($roomRow->source_partner_id != 0) {
            $sql = $wpdb->prepare("UPDATE wp_partner set no_of_rooms_given = no_of_rooms_given - 1, trade_balance = trade_balance - 1 WHERE user_id=%s", $roomRow->source_partner_id);
            $wpdb->query($sql);
        }
    }

    wp_send_json($return);
}

add_action('wp_ajax_gpx_remove_room', 'gpx_remove_room');


/**
 *
 *
 *
 *
 */
function gpx_Room_error_ajax() {
    global $wpdb;
    $sql = "SELECT *  FROM `wp_room` WHERE `check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null'";
    $results = $wpdb->get_results($sql);
    wp_send_json($results);
}

add_action('wp_ajax_gpx_Room_error_ajax', 'gpx_Room_error_ajax');
add_action('wp_ajax_nopriv_gpx_Room_error_ajax', 'gpx_Room_error_ajax');


/**
 *
 *
 *
 *
 */
function gpx_Room_error_page() {
    global $wpdb;
    $sql = "SELECT *  FROM `wp_room` WHERE `check_in_date` = '0000-00-00 00:00:00' or `check_out_date` = '0000-00-00 00:00:00' or resort ='0' or resort ='null' or unit_type ='null'";
    $results = $wpdb->get_results($sql);
    $i = 0;
    $data = [];

    foreach ($results as $result) {

        $data[$i]['record_id'] = '<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_edit&id=' . $result->record_id . '"><i class="fa fa-pencil" aria-hidden="true"></i><i class="fa fa-warning" aria-hidden="true" style="font-size:18px;color:red"></i></a>';
        $data[$i]['ID'] = $result->record_id;
        $data[$i]['create_date'] = $result->create_date;
        $data[$i]['last_modified_date'] = $result->last_modified_date;
        $data[$i]['check_in_date'] = $result->check_in_date;
        $data[$i]['check_out_date'] = $result->check_out_date;

        $unit_type = $wpdb->prepare("SELECT * FROM `wp_unit_type` WHERE `record_id` = %s", $result->unit_type);
        $unit = $wpdb->get_results($unit_type);
        $data[$i]['unit_type_id'] = $unit[0]->name;

        $spid = $wpdb->prepare("SELECT * FROM wp_users a INNER JOIN wp_usermeta b on a.ID=b.user_id WHERE b.meta_key='DAEMemberNo' AND ID = %s", $result->source_partner_id);
        $spid_result = $wpdb->get_results($spid);

        $res = $wpdb->prepare("SELECT *  FROM `wp_resorts` WHERE `id` = %s", $result->resort);
        $res_result = $wpdb->get_results($res);

        $data[$i]['resort'] = $res_result[0]->ResortName;

        $data[$i]['source_partner_id'] = $spid_result[0]->display_name;


        $data[$i]['sourced_by_partner_on'] = $result->sourced_by_partner_on;
        $data[$i]['resort_confirmation_number'] = $result->resort_confirmation_number;
        $active = "";
        if (isset($result->active)) {

            if ($result->active = 1) {
                $active = "Yes";
            } else {
                $active = "No";
            }
        }

        $availability = "";

        if (isset($result->availability)) {

            if ($result->availability = 0) {
                $availability = "--";
            } elseif ($result->availability = 1) {
                $availability = "All";
            } elseif ($result->availability = 2) {
                $availability = "Owner Only";
            } else {
                $availability = "Partner Only";
            }

        }


        $data[$i]['active'] = $active;
        $data[$i]['availability'] = $availability;

        $avltop = $wpdb->prepare("SELECT * FROM `wp_partner` WHERE record_id = %s", $result->available_to_partner_id);
        $avltop_result = $wpdb->get_results($avltop);

        $data[$i]['available_to_partner_id'] = $avltop_result[0]->name;

        $type = "";
        if (isset($result->type)) {

            if ($result->type == 1) {
                $type = "Exchange";
            } elseif ($result->type == 2) {
                $type = "Rental";
            } elseif ($result->type == 3) {
                $type = "Exchange/Rental";
            }

        }

        $data[$i]['type'] = $type;

        $i++;
    }

    wp_send_json($data);
}

add_action('wp_ajax_gpx_Room_error_page', 'gpx_Room_error_page');
add_action('wp_ajax_nopriv_gpx_Room_error_page', 'gpx_Room_error_page');

function gpx_release_week(): void {
    $hold_id = gpx_request('id');
    $hold = PreHold::find($hold_id);
    if (!$hold) {
        wp_send_json(['success' => false, 'error' => 'Hold not found'], 404);
    }

    $user = UserMeta::load(get_current_user_id());

    $details = $hold->data;
    $details[time()] = [
        'action' => 'released',
        'by' => $user->getName(),
    ];

    $hold->update([
        'released' => true,
        'data' => $details,
    ]);

    // Check if week is booked
    if (Transaction::forWeek($hold->propertyID)->cancelled(false)->doesntExist()) {
        $week = Week::find($hold->propertyID);
        if ($week) {
            //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
            if ($week->active_specific_date?->isPast()) {
                $week->update(['active' => true]);
            }
        }

    }

    wp_send_json(['success' => true]);
}

add_action('wp_ajax_gpx_release_week', 'gpx_release_week');

function gpx_extend_week(): void {
    $hold_id = gpx_request('id');
    $hold = PreHold::find($hold_id);
    if (!$hold) {
        wp_send_json(['success' => false, 'error' => 'Hold not found']);
    }
    if ($hold->released) {
        wp_send_json(['success' => false, 'error' => 'Hold is released']);
    }

    if (PreHold::forWeek($hold->weekId)->where('user', '!=', $hold->user)->released(false)->exists()) {
        wp_send_json([
            'success' => false,
            'error' => 'Another owner has this week on hold.',
        ]);
    }

    $newdate = gpx_request('newdate');
    $newdate = $newdate ? Carbon::parse($newdate)->endOfDay() : Carbon::now()->addDay()->endOfDay();

    // set the release date
    $hold->update([
        'release_on' => $newdate,
        'released' => false,
    ]);

    // deactivate the week
    Week::where('record_id', $hold->propertyID)->update(['active' => false]);

    wp_send_json([
        'success' => true,
        'cid' => $hold->user,
    ]);
}

add_action('wp_ajax_gpx_extend_week', 'gpx_extend_week');


function resort_availability_calendar() {
    $search = new AvailabilityCalendarSearch($_GET);
    if (!$search->hasResort() || !$search->hasWeekType()) {
        wp_send_json([
            'success' => false,
            'events' => [],
            'search' => $search,
        ]);
    }

    $events = WeekRepository::instance()->resort_availability_calendar($search);

    wp_send_json([
        'success' => true,
        'events' => $events,
        'search' => $search,
    ]);
}

add_action("wp_ajax_resort_availability_calendar", "resort_availability_calendar");
add_action("wp_ajax_nopriv_resort_availability_calendar", "resort_availability_calendar");


function gpx_get_next_availability_date(int $resort_id, string|int $month = null, string|int $year = null): array {
    $month = (int) $month ?: null;

    $currentYear = (int) date('Y');
    $maxyear = (int) date('Y', strtotime('+3 years'));
    $year = $year ? max(min((int) $year, $maxyear), $currentYear) : null;
    if ($month && $year) {
        return [
            'year' => $year,
            'month' => $month,
        ];
    }

    $event = WeekRepository::instance()->getNextAvailability($resort_id, $year, $month);
    if (!$event) {
        return [
            'year' => $year ?: $currentYear,
            'month' => $month ?: date('m'),
        ];
    }

    return [
        'year' => $event->check_in_date->format('Y') ?? ($year ?: $currentYear),
        'month' => $event->check_in_date->format('m') ?? ($month ?: date('m')),
    ];
}

function gpx_bonus_week_details() {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT WeekType, WeekEndpointID, weekId, WeekType, checkIn, WeekPrice, Price  FROM wp_properties WHERE id=%s", $_GET['id']);
    $row = $wpdb->get_row($sql);

    $WeekEndpointID = $_GET['weekendpointid'];
    $weekId = $_GET['weekid'];
    $weekType = str_replace(" ", "", $_GET['weektype']);

    if (!empty($row)) {
        $WeekEndpointID = $row->WeekEndpointID;
        $weekId = $row->weekId;
        $weekType = $row->WeekType;
    }

    $data = ['success' => true];

    $cid = gpx_get_switch_user_cookie();

    $DAEMemberNo = '646169';

    $usermeta = (object) array_map(function ($a) {
        return $a[0];
    }, get_user_meta($cid));
    $DAEMemberNo = $usermeta->DAEMemberNo;

    $gpx = new GpxRetrieve(GPXADMIN_API_URI, GPXADMIN_API_DIR);


    $weekDetails = $gpx->DAEGetWeekDetails($DAEMemberNo, $WeekEndpointID, $weekId, $weekType);
    if (isset($weekDetails->ReturnCode) && $weekDetails->ReturnCode != 0) {
        $data['Unavailable'] = "This week is no longer available.  Please select another week.";
        $wpdb->update('wp_properties', ['active' => 0], ['id' => $_GET['id']]);
    }
    if (isset($weekDetails->WeekPrice) && $weekDetails->WeekPrice != $row->Price) {
        $data['PriceChange'] = $weekDetails->WeekPrice;
        $weekPrice = $weekDetails->Currency . $weekDetails->WeekPrice;
        $updatedPrice = ['WeekPrice' => $weekPrice, 'Price' => $weekDetails->WeekPrice];
        if ($weekPrice == ' $') {
            $updatedPrice['active'] = 0;
        }
        $wpdb->update('wp_properties', $updatedPrice, ['id' => $_GET['id']]);
    }

    wp_send_json($data);

}

add_action("wp_ajax_gpx_bonus_week_details", "gpx_bonus_week_details");
add_action("wp_ajax_nopriv_gpx_bonus_week_details", "gpx_bonus_week_details");

function gpx_held_week_change_type() {
    if (empty($_POST['HoldID'])) {
        wp_send_json([
            'success' => false,
            'message' => 'Held week not provided',
        ]);
    }
    if (empty($_POST['WeekType']) || !in_array($_POST['WeekType'], ['RentalWeek', 'ExchangeWeek'])) {
        wp_send_json([
            'success' => false,
            'message' => 'Valid week type not provided',
        ]);
    }
    $cid = gpx_get_switch_user_cookie();
    $hold = PreHold::with('week')
                   ->where('user', $cid)
                   ->released(false)
                   ->find($_POST['HoldID']);
    if (!$hold) {
        wp_send_json([
            'success' => false,
            'message' => 'Held week not found',
        ]);
    }
    if ($hold->week->type != 3) {
        wp_send_json([
            'success' => false,
            'message' => 'Week type cannot be changed.',
            'value' => $hold->weekType,
            'label' => $hold->weekType == 'RentalWeek' ? 'Rental Week' : 'Exchange Week',
        ]);

    }

    $hold->weekType = $_POST['WeekType'] == 'RentalWeek' ? 'RentalWeek' : 'ExchangeWeek';
    $hold->save();

    wp_send_json([
        'success' => true,
        'message' => 'Week type updated.',
        'url' => '/booking-path/?' . http_build_query(['book' => $hold->weekId, 'type' => $hold->weekType]),
        'value' => $hold->weekType,
        'label' => $hold->weekType == 'RentalWeek' ? 'Rental Week' : 'Exchange Week',
    ]);
}

add_action("wp_ajax_gpx_held_week_change_type", "gpx_held_week_change_type");

function gpx_is_week_booked($weekID): bool {
    global $wpdb;

    $booked = false;

    $sql = $wpdb->prepare("SELECT cancelled FROM wp_gpxTransactions WHERE weekId=%s AND cancelled = 0", $weekID);
    $rows = $wpdb->get_results($sql);
    //if we have any rows the this transaction is booked
    if (count($rows) > 0) {
        $booked = true;
    }

    return $booked;
}

function gpx_get_featured_properties(): array {
    global $wpdb;
    $sql = "SELECT
                `a`.`record_id` AS `id`, `a`.`check_in_date` AS `checkIn`, `a`.`check_out_date` AS `checkOut`, `a`.`price` AS `Price`,
                `a`.`record_id` AS `weekID`, `a`.`record_id` AS `weekId`, `a`.`resort` AS `resortId`, `a`.`resort` AS `resortID`,
                `a`.`availability` AS `StockDisplay`, `a`.`type` AS `WeekType`, DATEDIFF(`a`.`check_out_date`, `a`.`check_in_date`) AS `noNights`,
                `a`.`active_rental_push_date` AS `active_rental_push_date`,
                `b`.`Country` AS `Country`, `b`.`Region` AS `Region`, `b`.`Town` AS `Town`, `b`.`ResortName` AS `ResortName`,
                `b`.`ImagePath1` AS `ImagePath1`, `b`.`AlertNote` AS `AlertNote`, `b`.`AdditionalInfo` AS `AdditionalInfo`,
                `b`.`HTMLAlertNotes` AS `HTMLAlertNotes`, `b`.`ResortID` AS `ResortID`, `b`.`taxMethod` AS `taxMethod`,
                `b`.`taxID` AS `taxID`, `b`.`gpxRegionID` AS `gpxRegionID`,
                `c`.`number_of_bedrooms` AS `bedrooms`, `c`.`sleeps_total` AS `sleeps`, `c`.`name` AS `Size`,
                `a`.`record_id` AS `PID`, `b`.`id` AS `RID`
            FROM `wp_room` AS `a`
            INNER JOIN `wp_resorts` AS `b` ON `a`.`resort` = `b`.`id`
            INNER JOIN `wp_unit_type` AS `c` ON `a`.`unit_type` = `c`.`record_id`
            WHERE `b`.`featured` = 1 AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1";

    $featuredprops = $wpdb->get_results($sql);

    $featuredresorts = [];
    foreach ($featuredprops as $featuredprop) {
        $featuredresorts[$featuredprop->ResortID]['resort'] = $featuredprop;
        $featuredresorts[$featuredprop->ResortID]['props'][] = $featuredprop;
    }

    return $featuredresorts;
}
