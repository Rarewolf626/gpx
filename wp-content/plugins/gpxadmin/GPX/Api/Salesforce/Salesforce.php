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
    private string $environment;
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
            if(isset($mySforceConnection)){
                print_r( $mySforceConnection->getLastRequest() );
            }

            echo $e->faultstring;
        }
    }

    function gpxUpsert( $object, $data, $sb = '' ) {
        global $wpdb;

       $data[0]->fields = $this->data_cleanse( $data[0]->fields );

        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->upsert( $object, $data );
            $wpdb->insert( 'wp_sf_calls', [ 'func' => $object, 'data' => json_encode( $data ), 'response' => json_encode($createResponse) ] );
            return $createResponse;
        } catch ( Exception $e ) {
            $failure = $e->faultstring;
            $wpdb->insert( 'wp_sf_calls', [ 'func' => $object, 'data' => json_encode( $data ), 'response' => json_encode($e->faultstring) ] );
            return $failure;
        }
    }

    function gpxCreate( $data ) {
        global $wpdb;
        $data[0]->fields = $this->data_cleanse( $data[0]->fields );
        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->create( $data );
            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'create', 'data' => json_encode( $data ), 'response' => json_encode($createResponse) ] );
            return $createResponse;
        } catch ( Exception $e ) {
            $failure = $e->faultstring;
            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'create', 'data' => json_encode( $data ), 'response' => json_encode($e->faultstring) ] );
            return $failure;
        }
    }

    function gpxTransactions( $data ) {
        global $wpdb;
        $data[0]->fields = $this->data_cleanse( $data[0]->fields );
        try {
            $mySforceConnection = $this->connection();
            $createResponse = $mySforceConnection->upsert( 'GPXTransaction__c', $data );

            $wpdb->insert( 'wp_sf_calls', [ 'func' => 'GPXTransaction__c', 'data' => json_encode( $data ) ] );

            return $createResponse;
        } catch ( Exception $e ) {
            return $e->faultstring;
        }
    }
    public function data_cleanse(array $data ): array {
        $cleaned = [];
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $cleaned[ $key ] = $this->data_cleanse( $value );
            } else {
                $cleaned[ $key ] = $this->char_replace( $value );
            }
        }
        return $cleaned;
    }

    public function char_replace(string $value): string {
        $replacements = [
            "&" => "and",
        ];
        return str_replace( array_keys( $replacements ), array_values( $replacements ), $value );
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
