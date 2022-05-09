<?php

namespace GPX\Database;

use ReflectionObject;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\Mysqli\Connection;
use wpdb;

class WpdbDriver extends AbstractMySQLDriver {

    public function connect( array $params, $username = null, $password = null, array $driverOptions = [] ) {

        return new Connection( $this->getMysqli(  ) );
    }

    public function getMysqli(  ) {
        global $wpdb;
        $r = new ReflectionObject( $wpdb );
        $p = $r->getProperty( 'dbh' );
        $p->setAccessible( true ); // <--- you set the property to public before you read the value

        return $p->getValue( $wpdb );
    }

    public function getName() {
        return 'wp-mysqli';
    }
}
