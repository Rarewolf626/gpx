<?php

namespace GPX\Api\Salesforce\Resource;

class Resorts extends AbstractResource
{

    /**
     *  incomplete...
     *
     */
    public function get_resort(  ) {

        $sfquery = "  ";

        return $this->sf->query($sfquery);


    }

}
