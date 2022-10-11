<?php

namespace GPX\Api\Salesforce;

use Exception;
use QueryResult;
use SforcePartnerClient;
use GPX\Api\Salesforce\Resource\Owners;
use GPX\Api\Salesforce\Resource\Resorts;
use GPX\Api\Salesforce\Resource\Intervals;
use GPX\Api\Salesforce\Resource\Transactions;
use GPX\Api\Salesforce\Resource\AbstractResource;

/**
 * @property-read Owners       $owner
 * @property-read Transactions $transaction
 * @property-read Resorts      $resort
 * @property-read Intervals    $interval
 */
class Salesforce {
    private string $password;
    private string $username;
    private string $organizationid;
    private string $scope;
    private string $environment = 'production';
    private $connection;
    private static $instance = null;
    protected array $resources = [];
    protected array $mapping = [
        'owner'       => Owners::class,
        'interval'    => Intervals::class,
        'resort'      => Resorts::class,
        'transaction' => Transactions::class,
    ];

    public function __construct( string $username, string $password, string $organizationid, bool $sandbox = false ) {
        $this->resources      = [];
        $this->username       = $username;
        $this->password       = $password;
        $this->organizationid = $organizationid;
        $this->scope          = $sandbox ? '/partner.wsdl.xml' : '/gpxprod.wsdl.xml';
        $this->environment    = $sandbox ? 'sandbox' : 'production';
    }

    public static function getInstance(): Salesforce {
        if ( self::$instance == null ) {
            self::$instance = gpx( Salesforce::class );
        }

        return self::$instance;
    }


    public function connection() {
        global $wpdb;

        if ( $this->connection ) {
            return $this->connection;
        }

        $mySforceConnection = new SforcePartnerClient();
        $mySoapClient       = $mySforceConnection->createConnection( SOAP_CLIENT_BASEDIR . $this->scope );

        //is this session valid?
        $dt      = date( 'Y-m-d H:i:s' );
        $sql     = $wpdb->prepare( "SELECT sessionVar from wp_sf_login WHERE expires > %s AND environment = %s ORDER BY expires DESC LIMIT 1",
                                   $dt,
                                   $this->environment );
        $session = $wpdb->get_var( $sql );

        if ( ! empty( $session ) ) {
            $sessionObj = json_decode( $session );
            $mySforceConnection->setEndpoint( $sessionObj->serverUrl );
            $mySforceConnection->setSessionHeader( $sessionObj->sessionId );
            $tsCheck = $mySforceConnection->getServerTimestamp();
        }

        if ( empty( $session ) || empty( $tsCheck ) ) {
            $dt = date( 'Y-m-d H:i:s', strtotime( $dt . " -5 minutes" ) );

            $sessionObj = $mySforceConnection->login( $this->username, $this->password );
            $mySforceConnection->setEndpoint( $sessionObj->serverUrl );
            $mySforceConnection->setSessionHeader( $sessionObj->sessionId );
            $session = json_encode( $sessionObj );

            $wpdb->insert( 'wp_sf_login', [
                'sessionVar'  => $session,
                'environment' => $this->environment,
                'expires'     => date( 'Y-m-d H:i:s', strtotime( $dt . ' + 2 hours' ) ),
            ] );
        }

        $this->connection = $mySforceConnection;

        return $mySforceConnection;
    }


    function query( $query ) {
        $result = [];
        try {
            $mySforceConnection = $this->connection();
            $response = $mySforceConnection->query( $query );

            $queryResult = new QueryResult( $response );       // @phpstan-ignore-line
            for ( $queryResult->rewind(); $queryResult->pointer < $queryResult->size; $queryResult->next() ) {
                $result[] = $queryResult->current();
            }

            return $result;
        } catch ( Exception $e ) {
            print_r( $mySforceConnection->getLastRequest() );
            echo $e->faultstring;
        }
    }


    function gpxUpsert( $object, $data, $sb = '' ) {
        global $wpdb;

        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->upsert( $object, $data );
            $wpdb->insert( 'wp_sf_calls', [ 'func' => $object, 'data' => json_encode( $data ) ] );

            return $createResponse;
        } catch ( Exception $e ) {
            $action  = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;

            return $failure;
        }
    }


    function gpxCreate( $data ) {
        global $wpdb;

        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->create( $data );
            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'create', 'data' => json_encode( $data ) ] );

            return $createResponse;
        } catch ( Exception $e ) {
            $action  = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;

            return $failure;
        }
    }

    function gpxTransactions( $data ) {
        global $wpdb;

        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->upsert( 'GPXTransaction__c', $data );

            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'GPXTransaction__c', 'data' => json_encode( $data ) ] );

            return $createResponse;
        } catch ( Exception $e ) {
            $action  = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            echo '<pre>' . print_r( $failure, true ) . '</pre>';

            return $failure;
        }
    }


    function gpxCustomRequestMatch( $data ) {
        global $wpdb;

        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->create( $data );
            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'custom request', 'data' => json_encode( $data ) ] );


            $return = [
                'response' => $createResponse,
            ];

            return $return;
        } catch ( Exception $e ) {
            $action  = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;

            return $failure;
        }
    }


    function depositDelete( $id ) {
        try {
            $mySforceConnection = $this->connection();
            $delete = $mySforceConnection->delete( $id );

            return $delete;
        } catch ( Exception $e ) {
            print_r( $mySforceConnection->getLastRequest() );
            echo $e->faultstring;
        }
    }


    function gpxWeek( $data ) {
        global $wpdb;

        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->upsert( 'GPXWeek__c', $data );
            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'GPX Week', 'data' => json_encode( $data ) ] );

            return $createResponse;
        } catch ( Exception $e ) {
            $action  = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;

            return $failure;
        }
    }

    public function esc( string $value, bool $quote = true ): string {
        $replacements = [
            "\n" => "\\\n",
            "\r" => "\\\r",
            "\t" => "\\\t",
            "\b" => "\\\b",
            "\f" => "\\\f",
            "\"" => "\\\"",
            "'"  => "\\'",
            "\\" => "\\\\",
        ];
        $value        = str_replace( array_keys( $replacements ), array_values( $replacements ), $value );
        if ( ! $quote ) {
            return $value;
        }

        return "'" . $value . "'";
    }

    public function esc_like( string $value, bool $quote = true ): string {
        $replacements = [
            "_" => "\\_",
            "%" => "\\%",
        ];
        $value        = str_replace( array_keys( $replacements ),
                                     array_values( $replacements ),
                                     $this->esc( $value, false ) );
        if ( ! $quote ) {
            return $value;
        }

        return "'" . $value . "'";
    }

    public function resource( string $name ): AbstractResource {
        if ( ! array_key_exists( $name, $this->mapping ) ) {
            throw new \InvalidArgumentException( 'Not a valid resource' );
        }
        if ( ! array_key_exists( $name, $this->resources ) ) {
            $classname                = $this->mapping[ $name ];
            $this->resources[ $name ] = new $classname( $this );
        }

        return $this->resources[ $name ];
    }

    public function __get( string $name ) {
        return $this->resource( $name );
    }
}
