<?php

class Shiftfour
{
    
    public function __construct($uri, $dir)
    {
        $this->uri = plugins_url('', __FILE__).'/api';
        $this->dir = str_replace("functions/", "", trailingslashit( dirname(__FILE__) ));
        
        define('SHIFT4_URL', 'https://utg.shift4api.net/');
        define('I4GO_URL', 'https://access.i4go.com/');
        
        $this->auth_token = '0C5AAB46-AA53-AB36-6053785657A00AF0';
        $this->client_guid = '38471D57-EEF4-FA74-43E8BE7F13B82F38';
        $this->access_token = 'BF1CFECB-9B28-4705-8CEC-E14F08E7962B';
        
    }
    
    public function shift_auth()
    {
        require_once $this->dir.'/models/shiftfourmodel.php';
        $shiftfour = new ShiftfourModel();
        
        $action = 'POST';
        $url = SHIFT4_URL.'api/rest/v1/credentials/accesstoken';
        
        $data = [
            'dateTime'=>date('c'),
            'credential'=>[
                'authToken' => $this->auth_token,
                'clientGuid' => $this->client_guid,
            ],
        ];
        
        $response = $shiftfour->shiftretrieve($action, $url, $data);
        
        $responseData = json_decode($response);
        
        $token = $responseData->credential->accessToken;
        
        return $token;
    }
    
    public function i_four_go_auth()
    {
        global $wpdb;
        
        require_once $this->dir.'/models/shiftfourmodel.php';
        $shiftfour = new ShiftfourModel();
        
        $action = 'DIRECTPOST';
        $url = I4GO_URL;
        
        $access_token = $this->access_token;
        
        $data = [
            'fuseaction' => 'account.authorizeClient',
            'i4go_clientip' => '68.102.136.109',
            'i4go_accesstoken' => $access_token,
        ];
        
        $response['i4go'] = $shiftfour->shiftretrieve($action, $url, $data, $access_token);
        $decoded = json_decode($response['i4go']);
        //store the details in the server
        //who is this?
        $sql = "SELECT user FROM wp_cart WHERE cartID='".$_REQUEST['cartID']."'";
        $user = $wpdb->get_row($sql);
        
        if(empty($user))
        {
            $cid = get_current_user_id();
            
            if(isset($_COOKIE['switchuser']))
            {
                $cid = $_COOKIE['switchuser'];
            }
            $_REQUEST['cartID'] = '00';
            
        }
        else
        {
            $cid = $user->user;
        }
        $insert = [
            'cartID' => $_REQUEST['cartID'],
            'userID' => $cid,
            'i4go_accessblock' => $decoded->i4go_accessblock,
        ];
        $wpdb->insert('wp_payments', $insert);
        
        $response['paymentID'] = $wpdb->insert_id.$cid;
        $response['paymentID'] = $wpdb->insert_id;
        
        $wpdb->update('wp_payments', array('invoice_id'=>$response['paymentID']), array('id'=>$wpdb->insert_id));
        
        return $response;
    }
    
    public function shift_sale($token, $amt, $tax, $invoice, $cr, $type = ['Booking'])
    {
        require_once $this->dir.'/models/shiftfourmodel.php';
        $shiftfour = new ShiftfourModel();
        
        $action = 'POST';
        $url = SHIFT4_URL.'api/rest/v1/transactions/sale';
        
        $access_token = $this->access_token;
        
        $tokenobj = (object) [
            'value'=>$token,
        ];
        
        $purchaseCard = (object) [
            'customerReference'=>$cr,
            'destinationPostalCode'=>'92008',
            'productDescriptors'=>$type
        ];
        
        $amount = (object) [
            'tax'=>$tax,
            'total'=>$amt,
        ];
        
        $transaction = (object) [
            'invoice'=>$invoice,
            'purchaseCard'=>$purchaseCard,
        ];
        
        $card = (object) [
            'present'=>'N',
            'token'=> $tokenobj
        ];
        
        $clerk = (object) [
            'numericId'=>get_current_user_id(),
        ];
        
        $data = [
            'dateTime'=>date('c'),
            'amount'=>$amount,
            'clerk'=>$clerk,
            'transaction'=>$transaction,
            //             'apiOptions' => [
            //                 "ALLOWPARTIALAUTH"
            //             ],
            'card'=>$card,
        ];
        
        $response = $shiftfour->shiftretrieve($action, $url, $data, $access_token);
        
        return $response;
    }
    
    public function shift_refund($invoiceID, $amt='')
    {
        global $wpdb;
        
        require_once $this->dir.'/models/shiftfourmodel.php';
        $shiftfour = new ShiftfourModel();
        
        $action = 'GET';
        $url = SHIFT4_URL.'api/rest/v1/transactions/invoice';
        
        $sql = "SELECT p.*, t.transactionData FROM wp_payments p
                INNER JOIN wp_gpxTransactions t on p.id=t.paymentGatewayID
                WHERE t.id='".$invoiceID."'";
        $row = $wpdb->get_row($sql);
        
        if(!empty($row))
        {
            $tdata = json_decode($row->transactionData);
            if(empty($amt))
            {
                $amt = $tdata->Paid;
            }
            $invoiceID = $row->id;
            
            $object = json_decode($row->i4go_object, true);
            $access_token = $row->i4go_accessblock;
            //         $data['invoice'] = $object['invoice'];
            $data['invoice'] = $invoiceID;
            //get the invoice information
            $rawInvoice = $shiftfour->shiftretrieve($action, $url, $data, $this->access_token);
            
            $invoice = json_decode($rawInvoice, true);
            //has this been batched?  If an error is returned then this has been batched
            if( array_key_exists('error', $invoice['result'][0]) || (isset($invoice['result'][0]['amount']['total']) && $amt != $invoice['result'][0]['amount']['total']))
            {
                $amount = [
                    'total'=>$amt,
                ];
                
                $clerk = (object) [
                    'numericID' => get_current_user_id(),
                ];
                
                $purchaseCard = (object) [
                    'customerReference'=>$row->userID,
                    'destinationPostalCode'=>'92008',
                    'productDescriptors'=>['Booking']
                ];
                
                $card = (object) [
                    'present'=>'N',
                    'token'=> [
                        'value' => $row->i4go_uniqueid
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
                
                $wpdb->insert('wp_payments', $insert);
                
                $invoiceObj = (object) [
                    'invoice' => $wpdb->insert_id,
                ];
                
                $data = [
                    'dateTime'=>date('c'),
                    'amount'=>$amount,
                    'clerk'=>$clerk,
                    'transaction'=> $invoiceObj,
                    'card'=>$card,
                    
                ];
                
                $action = "POST";
                $url = SHIFT4_URL.'api/rest/v1/transactions/refund';
                
                $responseJSON = $shiftfour->shiftretrieve($action, $url, $data, $this->access_token);
                $response = json_decode($responseJSON, true);
                $update = [
                    'i4go_responsetext' => json_encode($response['result'][0]['transaction']),
                    'i4go_cardtype' => $response['result'][0]['card']['type'],
                    'i4go_object' => json_encode($response),
                    'i4go_streetaddress' => $response['result'][0]['customer']['addressLine1'],
                    'i4go_postalcode' => $response['result'][0]['customer']['postalCode'],
                    'i4go_cardholdername' => $response['result'][0]['customer']['firstName'].' '.$response['result'][0]['customer']['lastName'],
                ];
                
                $wpdb->update('wp_payments', $update, array('id'=>$row->id));
                
            }
            else
            {
                $action = "DELETE";
                $response = $shiftfour->shiftretrieve($action, $url, $data, $this->access_token);
            }
            //when cancelled add the user that cancelled and the date
            $cancelled = [
                'datetime' => date('Y-m-d H:i:s'),
                'user' => get_current_user_id(),
            ];
            //         $wpdb->update('wp_gpxTransactions', array('cancelled'=>json_encode($cancelled)), array('id'=>$invoiceID));
            $total = $tdata->Paid;
        }
        else 
        {
            $total = '0';
            $response = "Transaction Not Found";
        }
        $output = [
            'shiftfour' => $response,
            'total' => $total,
        ];
        
        
        return $output;
    }
    
    public function shift_invioce($invoiceID)
    {
        require_once $this->dir.'/models/shiftfourmodel.php';
        $shiftfour = new ShiftfourModel();
        
        $action = 'GET';
        $url = SHIFT4_URL.'api/rest/v1/transactions/invoice';
        $data['invoice'] = $invoiceID;
        if(empty($data['invoice']))
        {
            $data['invoice'] = $row->id;
        }
        $data['invoice'] = sprintf("%010s", $data['invoice']);
        $invoice = $shiftfour->shiftretrieve($action, $url, $data, $this->access_token);
        
        return $invoice;
    }
    
}