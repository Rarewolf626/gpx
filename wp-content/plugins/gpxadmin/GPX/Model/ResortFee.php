<?php

namespace GPX\Model;
class ResortFee
{
    private string $start;
    private string $end;

    public $start_date;
    public $end_date;

    public float $fee;

    private array $fee_list;


    /**
     * construct the date and turn into usable data
     */
    public function __construct($data)
    {

        $this->parse_data($data);
        $this->makedate();
    }

    /**
     * input the data sting and split into dates / fees
     */
    private function parse_data($data)
    {
        $jdata = json_decode($data);

        foreach ($jdata as $key => $times) {

            $this->fee_list = $times;
            $dates = explode('_', $key, 2);
            $this->start = $dates[0];
            $this->end = $dates[1];

            $this->fee = ($times[sizeof($times) - 1]);
        }
    }

    /**
     * turn the time stamps into actual dates
     */
    private function makedate()
    {

        date_default_timezone_set('America/Los_Angeles');
        // March 10, 2001  date("F j, Y")
        $this->start_date = date("F j, Y", $this->start);
        $this->end_date = date("F j, Y", $this->end);
    }

    /**
     * get the resort times in the database format
     */
    public function get()
    {
        $output = array();

        $output [$this->start . '_' . $this->end] = $this->fee_list;

        return json_encode($output);

    }

    public function savelocation ($location) {


    }


}
