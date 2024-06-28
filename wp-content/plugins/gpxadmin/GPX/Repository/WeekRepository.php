<?php

namespace GPX\Repository;

use DB;
use GPX\Model\UserMeta;
use GPX\Model\Transaction;
use GPX\DataObject\Resort\AvailabilityCalendarSearch;
use stdClass;
use GPX\Model\Week;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class WeekRepository
{
    public static function instance(): WeekRepository
    {
        return gpx(WeekRepository::class);
    }

    public function get_week($id)
    {
        return Week::with('unit')->find($id);
    }

    public function get_week_for_checkout( int $week_id ): ?stdClass {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT
        a.record_id as id, a.check_in_date as checkIn, a.check_out_date as checkOut, a.price as Price, a.record_id as weekID, a.record_id as weekId,
        a.resort as resortId, a.resort as resortID, a.availability as StockDisplay, a.type as WeekType, a.active,
        DATEDIFF(check_out_date, check_in_date) as noNights, a.active_rental_push_date as active_rental_push_date, b.Country as Country, b.Region as Region,
        b.Town as Town, b.ResortName as ResortName, b.ImagePath1 as ImagePath1, b.AlertNote as AlertNote, b.AdditionalInfo as AdditionalInfo,
        b.HTMLAlertNotes as HTMLAlertNotes, b.ResortID as ResortID, b.taxMethod as taxMethod, b.taxID as taxID, b.gpxRegionID as gpxRegionID,
        c.number_of_bedrooms as bedrooms, c.sleeps_total as sleeps, c.name as Size, a.record_id as PID, b.id as RID
    FROM wp_room a
    INNER JOIN wp_resorts b ON a.resort=b .id
    INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
    WHERE a.record_id=%d AND a.archived=0 AND a.active_rental_push_date != '2030-01-01'", $week_id );

        return $wpdb->get_row( $sql );
    }

    public function get_weeks(array $week_ids = []): array {
        global $wpdb;
        $week_ids = array_filter(array_map(fn($id) => (int)$id, $week_ids));
        if (empty($week_ids)) {
            return [];
        }
        $placeholders = gpx_db_placeholders($week_ids, '%d');
        $sql = $wpdb->prepare("SELECT
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
            WHERE a.record_id IN ({$placeholders}) AND `a`.`active` = 1 AND `a`.`archived` = 0 AND `a`.`active_rental_push_date` != '2030-01-01' AND `b`.`active` = 1",
            $week_ids);

        return $wpdb->get_results($sql);
    }

    public function get_week_data(int $WeekID): ?stdClass {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT
            a.record_id as id, a.check_in_date as checkIn, a.check_out_date as checkOut, a.price as Price,
            a.record_id as weekID, a.record_id as weekId, a.availability as StockDisplay, a.resort_confirmation_number,
            a.source_partner_id, a.type as WeekType, DATEDIFF(a.check_out_date, a.check_in_date) as noNights,
            a.active, a.source_num,

            b.Country, b.Region, b.Town, b.ResortName, b.ImagePath1, b.AlertNote, b.AdditionalInfo, b.HTMLAlertNotes,
            b.ResortID, b.gpxRegionID as gprID, c.number_of_bedrooms as bedrooms, b.sf_GPX_Resort__c,

            c.sleeps_total as sleeps, c.name as Size

            FROM wp_room a
            INNER JOIN wp_resorts b ON (a.resort = b.id)
            INNER JOIN wp_unit_type c ON (a.unit_type = c.record_id)
            WHERE a.record_id = %d", $WeekID);

        $retrieve = $wpdb->get_row($sql);
        if (!$retrieve) return null;

        if (($retrieve->source_partner_id ?? 0) > 0) {
            if ($retrieve->source_num == '1') {
                $usermeta = UserMeta::load($retrieve->source_partner_id);
                $retrieve->source_name = $usermeta->getName();
                $retrieve->source_account = $usermeta->Property_Owner__c;
            } elseif ($retrieve->source_num == '3') {
                $sql = $wpdb->prepare("SELECT name, sf_account_id FROM wp_partner WHERE user_id=%s", $retrieve->source_partner_id);
                $row = $wpdb->get_row($sql);
                $retrieve->source_name = $row->name;
                $retrieve->source_account = $row->sf_account_id;
            }
        }


        return $retrieve;
    }

    public function get_weeks_on_hold(int $user_id = null): array
    {
        if(!$user_id) return [];
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT
                h.weekType,
                h.id as holdid,
                h.release_on,
                h.released,
                a.record_id as id, a.check_in_date as checkIn, a.check_out_date as checkOut, a.price as Price, a.record_id as weekID, a.record_id as weekId, a.availability as StockDisplay, a.resort_confirmation_number as resort_confirmation_number, a.source_partner_id as source_partner_id, a.type as WeekType, a.type as AllowedWeekType, DATEDIFF(check_out_date, check_in_date) as noNights, a.active as active, a.source_num as source_num,
                b.Country as Country, b.Region as Region, b.Town as Town, b.ResortName as ResortName, b.ImagePath1 as ImagePath1, b.AlertNote as AlertNote, b.AdditionalInfo as AdditionalInfo, b.HTMLAlertNotes as HTMLAlertNotes, b.ResortID as ResortID, b.gpxRegionID as gprID,
                c.number_of_bedrooms as bedrooms, c.sleeps_total as sleeps, c.name as Size,
                a.record_id as PID, b.id as RID
                    FROM wp_gpxPreHold h
                        INNER JOIN wp_room a ON a.record_id=h.propertyID
                        INNER JOIN wp_resorts b ON a.resort=b.id
                        INNER JOIN wp_unit_type c ON a.unit_type=c.record_id
                            WHERE h.user = %d
                            AND h.released=0",
            $user_id );

        return $wpdb->get_results( $sql );
    }

    public function get_prehold_weeks( int $cid = null ): array {
        if ( null === $cid ) {
            return [];
        }

        return DB::table('wp_gpxPreHold')->select('weekId')->where('user', '=', $cid)->where('released',
            '=',
            '0' )->pluck( 'weekId' )->toArray();
    }

    public function resort_availability_calendar(AvailabilityCalendarSearch $search)
    {
        return DB::table('wp_room', 'a')
            ->selectRaw("a.record_id as id, a.check_in_date as checkIn, a.check_out_date as checkOut, a.price as Price,
                a.record_id as weekID, a.availability as StockDisplay, DATEDIFF(a.check_out_date, a.check_in_date) as noNights,
                b.Country as Country, b.Region as Region, b.Town as Town, b.ResortName as ResortName, b.ImagePath1 as ImagePath1,
                c.number_of_bedrooms as bedrooms, c.sleeps_total as sleeps, c.name as Size, a.type, a.active_rental_push_date,
                a.record_id as PID, b.id as RID")
            ->join('wp_resorts as b', 'a.resort', '=', 'b.id')
            ->join('wp_unit_type as c', 'a.unit_type', '=', 'c.record_id')
            ->whereRaw('a.check_in_date > CURRENT_DATE()')
            ->where('a.resort', '=', $search->resort)
            ->whereRaw('DATE(DATE_ADD(a.check_in_date, INTERVAL 1 WEEK)) >= ?', $search->start->format('Y-m-d'))
            ->whereDate('a.check_in_date', '<', $search->end->format('Y-m-d'))
            ->whereRaw('a.active = 1')
            ->whereRaw('a.archived = 0')
            ->whereRaw('b.active = 1')
            ->whereRaw("a.active_rental_push_date != '2030-01-01'")
            ->when($search->bedrooms !== 'Any', fn($query) => $query->where('c.number_of_bedrooms', '=', $search->bedrooms))
            ->when($search->WeekType !== 'All', fn($query) => $query
                ->when($search->isRental(), fn($query) => $query
                    ->where(fn($query) => $query
                        ->where('a.type', '=', 2)
                        ->orWhere(fn($query) => $query
                            ->where('a.type', '=', 3)
                            ->whereDate('a.check_in_date', '<=', date('Y-m-d', strtotime('+6 months')))
                        )
                    )
                )
                ->when(!$search->isRental(), fn($query) => $query
                    ->whereIn('a.type', [1, 3])
                )
            )
            ->orderBy('a.check_in_date', 'asc')
            ->orderBy('a.type', 'asc')
            ->orderBy('c.number_of_bedrooms', 'asc')
            ->get()
            ->map(function ($row) use ($search) {
                $row->WeekType = match ($row->type) {
                    '1' => 'RentalWeek',
                    '2' => 'ExchangeWeek',
                    default => $search->isRental() ? 'RentalWeek' : 'ExchangeWeek'
                };
                $row->checkIn = Carbon::parse($row->checkIn);
                $row->checkOut = Carbon::parse($row->checkOut);

                return [
                    'start' => $row->checkIn->format('Y-m-d'),
                    'end' => $row->checkIn->clone()->addWeek()->format('Y-m-d'),
                    'bedrooms' => $row->bedrooms,
                    'weektype' => $row->WeekType,
                    'title' => $row->ResortName . " - " . $row->Size,
                    'allDay' => true,
                    'url' => '/booking-path/?book=' . $row->id . '&type=' . $row->WeekType,
                ];
            })
            ->unique(fn($row) => $row['start'] . '|' . $row['end'] . '|' . $row['weektype'] . '|' . $row['bedrooms'])
            ->values()
            ->toArray();
    }

    public function getNextAvailability(int $resort, int $year = null, int $month = null): ?Week
    {
        return Week::select('wp_room.record_id', 'wp_room.check_in_date')
            ->join('wp_resorts as b', 'wp_room.resort', '=', 'b.id')
            ->join('wp_unit_type as c', 'wp_room.unit_type', '=', 'c.record_id')
            ->whereRaw('wp_room.check_in_date > CURRENT_DATE()')
            ->where('wp_room.resort', '=', $resort)
            ->whereRaw('wp_room.active = 1')
            ->whereRaw('wp_room.archived = 0')
            ->whereRaw('b.active = 1')
            ->whereRaw("wp_room.active_rental_push_date != '2030-01-01'")
            ->when($year, fn($query) => $query
                ->whereRaw("YEAR(wp_room.check_in_date) = ?", $year)
            )
            ->when($month, fn($query) => $query
                ->whereRaw("MONTH(wp_room.check_in_date) = ?", $month)
            )
            ->orderBy('wp_room.check_in_date', 'asc')
            ->take(1)
            ->first();
    }

    public function add_weeks(array $post): Collection
    {
        $weeks = new Collection();
        $count = $post['count'] ?? 1;
        $data = $this->format_week_data($post);

        for ($i = 0; $i < $count; $i++) {
            $week = new Week();
            $week->fill($data);
            $week->import_id = 0;
            $week->update_details = json_encode([
                time() => [
                    'update_by' => get_current_user_id(),
                    'details' => base64_encode(json_encode($data)),
                ],
            ]);
            $week->save();
            $weeks->add($week);
        }
        if (!empty($data['source_partner_id'])) {
            DB::table('wp_partner')->where('record_id', '=', $data['source_partner_id'])
                ->update([
                    'no_of_rooms_given' => DB::raw("no_of_rooms_given + $count"),
                    'trade_balance' => DB::raw("trade_balance + $count"),
                ]);
        }

        return $weeks;
    }

    public function update_week(Week|int $week, array $post): Week
    {
        $week = $week instanceof Week ? $week : Week::findOrFail($week);
        $data = $this->format_week_data($post);
        unset($data['create_date']);
        $updates = Arr::map($data, fn($value, $key) => [
            'old' => $week->$key,
            'new' => $value,
        ]);
        $week->fill($data);
        $details = array_filter($week->update_details);
        $details[time()] = [
            'update_by' => get_current_user_id(),
            'details' => base64_encode(json_encode($updates)),
        ];
        $week->update_details = json_encode($details);
        $week->save();

        return $week;
    }

    private function format_week_data(array $post): array
    {
        $now = Carbon::now();
        $post['check_in_date'] = Carbon::parse($post['check_in_date'])->startOfDay();
        if (empty($post['check_out_date'])) {
            $post['check_out_date'] = $post['check_in_date']->clone()->addWeek()->startOfDay();
        } else {
            $post['check_out_date'] = Carbon::parse($post['check_out_date'])->startOfDay();
        }
        if (empty($post['active_specific_date'])) {
            $post['active_specific_date'] = $post['check_in_date']->clone()->subYear()->startOfMonth();
        } else {
            $post['active_specific_date'] = Carbon::parse($post['active_specific_date'])->startOfMonth();
        }
        if (!empty($post['active_week_month'])) {
            $post['active_type'] = $post['active_type'] ?? 0;
            if ($post['active_type'] == 'weeks') {
                $post['active_specific_date'] = $post['check_in_date']->clone()->subWeeks($post['active_week_month'])->startOfMonth();
            }
            if ($post['active_type'] == 'months') {
                $post['active_specific_date'] = $post['check_in_date']->clone()->subMonths($post['active_week_month'])->startOfMonth();
            }
        }
        $post['active_rental_push_date'] = $post['check_in_date']->clone()->subMonths(6)->startOfDay();
        if (!empty($post['rental_push'])) {
            $post['active_rental_push_date'] = $post['check_in_date']->clone()->subMonths($post['rental_push'])->startOfDay();
        }
        if (!empty($post['rental_push_date'])) {
            $post['active_rental_push_date'] = Carbon::parse($post['rental_push_date'])->startOfDay();
        }
        if ($post['active_rental_push_date'] < $post['active_specific_date']) {
            $post['active_rental_push_date'] = $post['active_specific_date']->clone();
        }

        return [
            'create_date' => $now->format('Y-m-d H:i:s'),
            'last_modified_date' => $now->format('Y-m-d H:i:s'),
            'check_in_date' => $post['check_in_date']->format('Y-m-d H:i:s'),
            'check_out_date' => $post['check_out_date']->format('Y-m-d H:i:s'),
            'active_specific_date' => $post['active_specific_date']->format('Y-m-d H:i:s'),
            'active' => (bool)($post['active'] ?? false),
            'resort' => (int)$post['resort'] ?? 0,
            'unit_type' => (int)$post['unit_type_id'] ?? 0,
            'source_num' => (int)$post['source'] ?? 0,
            'source_partner_id' => (int)$post['source_partner_id'] ?? 0,
            'resort_confirmation_number' => trim($post['resort_confirmation_number'] ?? ''),
            'availability' => in_array($post['availability'] ?? null,
                [1, 2, 3]) ? (int)$post['availability'] : 1,
            'available_to_partner_id' => (int)$post['available_to_partner_id'] ?? 0,
            'type' => in_array($post['type'] ?? null, [1, 2, 3]) ? (int)$post['type'] : 1,
            'price' => is_numeric($post['price'] ?? null) ? round($post['price'], 2) : null,
            'note' => trim($post['note'] ?? '') !== '' ? trim($post['note']) : null,
            'active_week_month' => (int)$post['active_week_month'] ?? 0,
            'active_type' => in_array($post['active_type'] ?? null,
                ['weeks', 'months']) ? $post['active_type'] : 0,
            'active_rental_push_date' => $post['active_rental_push_date']->format('Y-m-d'),
            'create_by' => get_current_user_id(),
        ];
    }

    /**
     * @param int $property_id
     *
     * @return ?stdClass{
     *     id: int,
     *     checkIn: string,
     *     checkOut: string,
     *     Price: float,
     *     weekID: int,
     *     weekId: int,
     *     StockDisplay: bool,
     *     resort_confirmation_number: string,
     *     source_partner_id: int,
     *     WeekType: int,
     *     noNights: int,
     *     active: bool,
     *     source_num: int,
     *     Country: string,
     *     Region: string,
     *     Town: string,
     *     ResortName: string,
     *     ImagePath1: string,
     *     AlertNote: string,
     *     AdditionalInfo: string,
     *     HTMLAlertNotes: string,
     *     ResortID: string,
     *     gprID: int,
     *     bedrooms: int|string,
     *     sleeps: int,
     *     Size: string,
     *     PID: int,
     *     RID: int,
     * }
     */
    public function get_property(int $property_id): ?stdClass
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT
                a.record_id as id,
                a.check_in_date as checkIn,
                a.check_out_date as checkOut,
                a.price as Price,
                a.record_id as weekID,
                a.record_id as weekId,
                a.availability as StockDisplay,
                a.resort_confirmation_number,
                a.source_partner_id,
                a.type as WeekType,
                DATEDIFF(a.check_out_date, a.check_in_date) as noNights,
                a.active,
                a.source_num,
                b.Country,
                b.Region,
                b.Town,
                b.ResortName,
                b.ImagePath1,
                b.AlertNote,
                b.AdditionalInfo,
                b.HTMLAlertNotes,
                b.ResortID,
                b.gpxRegionID as gprID,
                c.number_of_bedrooms as bedrooms,
                c.sleeps_total as sleeps,
                c.name as Size,
                a.record_id as PID,
                b.id as RID
            FROM wp_room a
            INNER JOIN wp_resorts b ON (a.resort = b.id)
            INNER JOIN wp_unit_type c ON (a.unit_type = c.record_id)
            WHERE a.record_id = %d",
            $property_id);
        $prop = $wpdb->get_row($sql, OBJECT);
        if (!$prop) return $prop;

        $prop->Currency = match (true) {
            !empty($prop->Currency) => $prop->Currency,
            !empty($prop->WeekPrice) && preg_match("/^\S{3}\s/i", $prop->WeekPrice) => mb_substr($prop->WeekPrice, 0, 3),
            default => 'USD'
        };


        return $prop;
    }

    public function isRoomBooked(int $weekID): bool {
        return Transaction::forWeek($weekID)->cancelled(false)->exists();
    }
}
