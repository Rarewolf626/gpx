<?php

namespace GPX\Model\Reports;

use Illuminate\Support\Carbon;

class Filter {

    public $resort = null;
    public $active = null;

    public string $start_date;
    public string $end_date;

    public $check_in = null;
    public $state = null;
    public $available = null;
    public $city = null;
    public $country = null;
    public $price = null;
    public $source = null;
    public $type = null;
    public $partnerid = null;
    public $release_on = null;

    public function __construct() {
        $this->start_date = date('Y-m-d');
        $this->end_date = Carbon::now()->addYear()->format('Y-m-d');
    }

    public function dates( ?string $start = null, ?string $end = null ): self {
        if ( $start ) {
            $start = Carbon::parse( $start );
            if ( ! $start->isValid() ) {
                $start = Carbon::now();
            }
        } else {
            $start = Carbon::now();
        }

        if ( $end ) {
            $end = Carbon::parse( $end );
            if ( ! $end->isValid() ) {
                $end = $start->clone()->addYear();
            }
        } else {
            $end = $start->clone()->addYear();
        }

        $this->start_date = $start->format( 'Y-m-d' );
        $this->end_date   = $end->format( 'Y-m-d' );

        return $this;
    }
}
