<?php

namespace GPX\Api\Salesforce\Resource;

use GPX\Api\Salesforce\Salesforce;

abstract class AbstractResource {
    protected Salesforce $sf;

    public function __construct( Salesforce $sf ) {
        $this->sf = $sf;
    }

    public static function instance() {
        return gpx( static::class );
    }
}
