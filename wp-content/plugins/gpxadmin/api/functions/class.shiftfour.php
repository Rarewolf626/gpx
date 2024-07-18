<?php

use GPX\ShiftFour\PaymentResponse;
use GPX\Exception\ShiftFour\InvalidJsonResponse;

class Shiftfour {

    public $uri;
    public $dir;
    public $auth_token;
    public $client_guid;
    public $access_token;

    public function __construct( $uri = null, $dir = null ) {
        $this->uri = plugins_url( '', __FILE__ ) . '/api';
        $this->dir = str_replace( "functions/", "", trailingslashit( dirname( __FILE__ ) ) );

        $this->auth_token = SHIFT4_AUTH_TOKEN;
        $this->client_guid = SHIFT4_CLIENT_GUID;
        $this->access_token = SHIFT4_ACCESS_TOKEN;
        $this->api = new ShiftfourModel();
    }

    public function shift_auth() {
        $response = $this->api->shiftretrieve(
            'POST',
            SHIFT4_URL . 'api/rest/v1/credentials/accesstoken', [
            'dateTime' => date( 'c' ),
            'credential' => [
                'authToken' => $this->auth_token,
                'clientGuid' => $this->client_guid,
            ],
        ] );
        $responseData = json_decode( $response );

        return $responseData->credential->accessToken;
    }

    public function i_four_go_auth() {

        $start = microtime( true );
        $response = $this->api->shiftretrieve( 'DIRECTPOST', I4GO_URL, [
            'fuseaction' => 'account.authorizeClient',
            'i4go_clientip' => $_SERVER['REMOTE_ADDR'] ?? '68.102.136.109',
            'i4go_accesstoken' => $this->access_token,
        ], $this->access_token );
        $duration = microtime( true ) - $start;
        try {
            $result = json_decode( trim( $response ), true, 512, JSON_THROW_ON_ERROR );
        } catch ( JsonException $e ) {
            throw ( new InvalidJsonResponse( 'Api returned an invalid json response', $e->getCode(), $e ) )
                ->setResponse( $response )
                ->setDuration( $duration );
        }

        return $result;
    }

    public function shift_sale( $token, $amt, $tax, $invoice, $cr, $type = [ 'Booking' ] ): PaymentResponse {

        $start = microtime( true );
        $response = $this->api->shiftretrieve(
            'POST',
            SHIFT4_URL . 'api/rest/v1/transactions/sale',
            [
                'dateTime' => date( 'c' ),
                'amount' => [
                    'tax' => min( $amt, $tax ),
                    'total' => $amt,
                ],
                'clerk' => [
                    'numericId' => get_current_user_id(),
                ],
                'transaction' => [
                    'invoice' => $invoice,
                    'purchaseCard' => [
                        'customerReference' => $cr,
                        'destinationPostalCode' => '92008',
                        'productDescriptors' => $type,
                    ],
                ],
                'card' => [
                    'present' => 'N',
                    'token' => [
                        'value' => $token,
                    ],
                ],
            ],
            $this->access_token
        );
        $duration = microtime( true ) - $start;
        try {
            $result = json_decode( trim( $response ), true, 512, JSON_THROW_ON_ERROR );
        } catch ( JsonException $e ) {
            throw ( new InvalidJsonResponse( 'Api returned an invalid json response', $e->getCode(), $e ) )
                ->setResponse( $response )
                ->setDuration( $duration );
        }

        return new PaymentResponse( $result, $duration );
    }

    public function shift_refund( $invoiceID, $amt = '' ) {
        global $wpdb;

        $action = 'GET';
        $url = SHIFT4_URL . 'api/rest/v1/transactions/invoice';

        $sql = $wpdb->prepare( "SELECT p.*, t.transactionData, t.cancelledData FROM wp_payments p
                INNER JOIN wp_gpxTransactions t on p.id=t.paymentGatewayID
                WHERE t.id=%s", $invoiceID );
        $row = $wpdb->get_row( $sql );

        if ( ! empty( $row ) ) {
            $tdata = json_decode( $row->transactionData );
            if ( empty( $amt ) ) {
                $amt = $tdata->Paid;
            }

            //never ever over refund!
            //look for additional refunds
            if ( ! empty( $row->cancelledData ) ) {
                //get the cancelled data
                $cdata = json_decode( $row->cancelledData );
                $cancelledAmounts = [];
                foreach ( $cdata as $c ) {
                    $cancelledAmounts[] = $c->amount;
                }

                //add the amounts together
                $cancelledAmount = array_sum( $cancelledAmounts );

                //get the amount paid
                $paid = $tdata->Paid;
                //calculate the difference -- this is the amount that can be cancelled without over refunding
                $difference = $paid - $cancelledAmount;
                //the amount cannot be greater than the difference
                if ( $amt > $difference ) {
                    //only refund the difference
                    $amt = $difference;
                }
                //don't do anything if the amount is less than $1
                if ( ( strpos( $amt, '-' ) !== false ) || $amt <= '0' ) {

                    $output = [
                        'shiftfour' => 'Refund exceeds amount available!',
                        'error' => true,
                        'total' => 0,
                    ];

                    return $output;
                }
            }

            $invoiceID = $row->id;

            $object = json_decode( $row->i4go_object, true );
            $access_token = $row->i4go_accessblock;
            //         $data['invoice'] = $object['invoice'];
            $data['invoice'] = $invoiceID;
            //get the invoice information
            $rawInvoice = $this->api->shiftretrieve( $action, $url, $data, $this->access_token );

            $invoice = json_decode( $rawInvoice, true );
            //has this been batched?  If an error is returned then this has been batched
            if ( array_key_exists( 'error', $invoice['result'][0] ) || ( isset( $invoice['result'][0]['amount']['total'] ) && $amt != $invoice['result'][0]['amount']['total'] ) ) {
                $amount = [
                    'total' => $amt,
                ];

                $clerk = (object) [
                    'numericID' => get_current_user_id(),
                ];

                $purchaseCard = (object) [
                    'customerReference' => $row->userID,
                    'destinationPostalCode' => '92008',
                    'productDescriptors' => [ 'Booking' ],
                ];

                $card = (object) [
                    'present' => 'N',
                    'token' => [
                        'value' => $row->i4go_uniqueid,
                    ],
                    'purchaseCard' => $purchaseCard,

                ];


                //we need to create a new invoice
                $insert = [
                    'cartID' => $row->cartID,
                    'transactionID' => $row->transactionID,
                    'userID' => $row->userID,
                    'i4go_accessblock' => $row->i4go_accessblock,
                    'i4go_uniqueid' => $row->i4go_uniqueid,
                ];

                $wpdb->insert( 'wp_payments', $insert );

                $invoiceObj = (object) [
                    'invoice' => $wpdb->insert_id,
                ];

                $data = [
                    'dateTime' => date( 'c' ),
                    'amount' => $amount,
                    'clerk' => $clerk,
                    'transaction' => $invoiceObj,
                    'card' => $card,

                ];

                $action = "POST";
                $url = SHIFT4_URL . 'api/rest/v1/transactions/refund';

                $responseJSON = $this->api->shiftretrieve( $action, $url, $data, $this->access_token );
                $response = json_decode( $responseJSON, true );
                $update = [
                    'i4go_responsetext' => json_encode( $response['result'][0]['transaction'] ),
                    'i4go_cardtype' => $response['result'][0]['card']['type'],
                    'i4go_object' => json_encode( $response ),
                    'i4go_streetaddress' => $response['result'][0]['customer']['addressLine1'],
                    'i4go_postalcode' => $response['result'][0]['customer']['postalCode'],
                    'i4go_cardholdername' => $response['result'][0]['customer']['firstName'] . ' ' . $response['result'][0]['customer']['lastName'],
                ];

                $wpdb->update( 'wp_payments', $update, [ 'id' => $row->id ] );

            } else {
                $action = "DELETE";
                $response = $this->api->shiftretrieve( $action, $url, $data, $this->access_token );
            }
            //when cancelled add the user that cancelled and the date
            $cancelled = [
                'datetime' => date( 'Y-m-d H:i:s' ),
                'user' => get_current_user_id(),
            ];
            $total = $tdata->Paid;
        } else {
            $total = '0';
            $error = true;
            $response = "Transaction Not Found";
        }
        $output = [
            'shiftfour' => $response,
            'total' => $total,
        ];

        if ( $error ) {
            $output['error'] = true;
        }

        return $output;
    }

    public function shift_invoice( $invoiceID ): PaymentResponse {
        $start = microtime( true );
        $response = $this->api->shiftretrieve( 'GET', SHIFT4_URL . 'api/rest/v1/transactions/invoice', [
            'invoice' => sprintf( "%010s", $invoiceID ),
        ], $this->access_token );
        $duration = microtime( true ) - $start;
        try {
            $result = json_decode( trim( $response ), true, 512, JSON_THROW_ON_ERROR );
        } catch ( JsonException $e ) {
            throw ( new InvalidJsonResponse( 'Api returned an invalid json response', $e->getCode(), $e ) )
                ->setResponse( $response )
                ->setDuration( $duration );
        }

        return new PaymentResponse( $result, $duration );
    }

}
