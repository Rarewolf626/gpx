<?php

namespace GPX\Model\Reports;
use GPX\Model\Reports\Filter;

class MasterAvailability
{

    public Filter $filter;

    // store err message
    public $error;

    protected $data;

    /**
     *
     */
    public function __construct() {

        // initialize dates
        $this->filter = new Filter();
        $this->filter->start_date =  date('Y-m-d');
        $this->filter->end_date = date('Y-m-d',strtotime($this->filter->start_date.'+ 12 months'));
    }

    /**
     * @param $start
     * @param $end
     * @return void
     */
    public function set_dates ($start = NULL, $end = NULL) {

        // @todo implement checkdate()
        // if the start_date is not set, then start today
        $this->start_date = (isset($start)) ? $start : date('Y-m-d');

        // if the end date is not set, use today + 1 year
        $this->end_date = (isset($end)) ? $end : date('Y-m-d',strtotime($this->start_date.'+ 12 months'));

        // if the range is greater than 1 year limit the range to 1 year only
        $origin = date_create($this->start_date);
        $target = date_create($this->end_date);
        $interval = date_diff($origin, $target);
        if ( intval($interval->format('%a')) > 366) {
            $this->end_date = date('Y-m-d', strtotime($this->start_date.'+ 12 months'));
        }

    }


    /**
     * @return mixed
     */
    public function run() {

        // get the base inventory data
        $this->basequery();

        // for each row, process the rest of the data
        foreach ($this->data as $key => $row) {

            // first pass - set all status to available
            $this->data[$key]->status = 'Available';

            // modify for held weeks
            $weeksheld = $this->weekheld($row->record_id);
            if ($weeksheld) {
                $this->data[$key]->held_for = $weeksheld[0]->user;
                $this->data[$key]->release_on = $weeksheld[0]->release_on;
                $this->data[$key]->status = 'Held';
            } else {
                $this->data[$key]->held_for = null;
                $this->data[$key]->release_on = null;
            }

            // modify for booked weeks
            $booked = $this->booked($row->record_id);
            if ($booked) {
                $this->data[$key]->status = 'Booked';
            }
        }
        return $this->data;
    }



    /**
     * @return void
     */
    private function basequery() {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT
                                        i.record_id,
                                        r.ResortName as 'ResortName',
                                        IF (i.active = 1, 'Yes', 'No')  active,
                                        CAST(i.check_in_date AS DATE) check_in_date,
                                        r.Town  city,
                                        r.Region  state,
                                        r.Country country,
                                        i.Price ,
                                        u.name  'UnitType',
                                        i.type 'type',
                                        IF (i.type = 1, 'Exchange', IF (i.type = 2, 'Rental', 'Both')) AS type,
                                        IF (i.source_num = 1, 'Owner', IF (i.source_num = 2, 'GPR', 'Trade Partner')) AS Source,
                                        pa.name 'SourcePartnerName'
                                        FROM wp_room i
                                        JOIN wp_resorts r ON (i.resort = r.id)
                                        JOIN wp_unit_type u ON (i.unit_type = u.record_id)
                                        LEFT JOIN wp_partner pa ON (pa.user_id = i.source_partner_id)

                                        WHERE
                                            i.check_in_date BETWEEN '%s' and '%s'", $this->filter->start_date, $this->filter->end_date );
        $this->data = $wpdb->get_results($sql);
        $this->error =  $wpdb->last_error;
    }

    /**
     * @return void
     */
    private function weekheld ($weekId) {
       global $wpdb;

       $sql = $wpdb->prepare ("SELECT
                                        propertyID,
                                        weekId,
                                        `user`,
                                        weekType,
                                        released,
                                        release_on
                                    FROM  wp_gpxPreHold h
                                    WHERE
                                        released = 0 AND
                                        h.weekId = %d", $weekId);

        return   $wpdb->get_results($sql);

    }

    /**
     * @param $weekId
     * @return array|object|\stdClass[]|null
     */
    private function booked ($weekId) {
        global $wpdb;

        $sql = $wpdb->prepare ( "SELECT
                                                 userID,
                                                 weekId,
                                                 paymentGatewayID,
                                                 `cancelled`
                                        FROM wp_gpxTransactions t
                                        WHERE
                                            t.`cancelled` != 1 AND
                                            t.transactionType = 'booking' AND
                                            t.weekId = %d", $weekId);
        return $wpdb->get_results($sql);

    }


}
