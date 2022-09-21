<?php

namespace GPX\Model\Salesforce;

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
