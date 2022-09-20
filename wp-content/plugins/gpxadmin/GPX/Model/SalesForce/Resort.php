<?php

namespace GPX\Model\SalesForce;

class Resort
{

    private bool $debug = true;


    /**
     *  incomplete...
     *
     */
    public function get_resort_sf(  ) {
        $sf = \Salesforce::getInstance();

        $sfquery = "  ";

        return $sf->query($sfquery);


    }

}
