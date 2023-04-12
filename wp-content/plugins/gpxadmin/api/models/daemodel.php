<?php 
/*
  *
  *  @deprecated
  */
class DaeModel
{
    
    protected $cred;
    protected $data;
    
    public function daeretrieve($cred, $data)
    {
        return array();
        /*
         *  TODO
         *  This function returns an empty array and none
         *  of this code below is NEVER used and will NEVER run.
         *
         *  Need to delete this file and references.
         *
         */
        global $wpdb;
       extract($cred);
       extract($data);
       $DAEMemberNo = str_replace("U", "", $DAEMemberNo);
       
       //these calls need to have the client DAEMemberNo instead of the other one
       $clientMemberNo = array('DAEReIssueConfirmation', 'DAEGetWeeksOnHold', 'DAEHoldWeek', 'DAECompleteBooking', 'DAEGetMemberOwnership', 'DAEGetMemberDetails', 'DAEGetMemberCredits', 'DAECreateWillBank', 'DAEGetMemberHistory', 'DAEGetAccountDetails', 'DAEGetWeekDetails');
       
       $db['function'] = $data['functionName'];
       
       $xmlBody = '
       <'.$functionName.' xmlns="DAE">
         <AuthID>'.$AuthID.'</AuthID>';
         if(isset($externalObject))
             $xmlBody .= "<".$externalObject.">";
         if(isset($ExtMemberNo))
             $xmlBody .= '<ExtMemberNo>'.$DAEMemberNo.'</ExtMemberNo>';
         else
             if(isset($inputMembers['DAEMemberNo']) && !in_array($functionName, $clientMemberNo))
                 $xmlBody .= '<DAEMemberNo>'.$DAEMemberNo.'</DAEMemberNo>';
        
         if(isset($inputMembers))
           foreach($inputMembers as $key=>$value)
           {
               $arv = '';
               if(is_array($value))
               {
                   foreach($value as $k=>$v)
                       $arv .= '<'.$k.'>'.$v.'</'.$k.'>'; 
                   $value = $arv;
               }
               $xmlBody .= '<'.$key.'>'.$value.'</'.$key.'>';
           }
         if(isset($externalObject))
             $xmlBody .= "</".$externalObject.">";
       $xmlBody .= '</'.$functionName.'>';
       $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
    <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
        <soap12:Body>
'.$xmlBody.'
        </soap12:Body>
    </soap12:Envelope>';
               if(isset($_REQUEST['output_dae']))
               {
                   echo '<pre>'.print_r($xmlBody, true).'</pre>';
               }
       $dbxml = $this->delete_all_between('<CardNo>', '</CardNo>', $xmlBody);
       $dbxml = $this->delete_all_between('<CCV>', '</CCV>', $dbxml);
       $db['xml'] = $dbxml;
//        if(get_current_user_id() == 5)
//        echo '<pre>'.print_r($xml_post_string, true).'</pre>';
       $headerpost = str_replace("?wsdl", "", $action); 
       $headers = array(
           "POST ".$headerpost." HTTP/1.1",
           "Content-type: text/xml;charset=\"utf-8\"",
           "Host: ".$host,
           "Content-length: ".strlen($xml_post_string),
       ); //SOAPAction: your op URL
       //live
       $url = "https://".$host.$action;
       //testing
       if($mode == 'testing')
       {
           $url = "https://".$host.$action;
           if($functionName == 'DAEPayAndCompleteBooking')
               $url = "https://".$host.$action;
               
       }
           
//        echo '<pre>'.print_r($url, true).'</pre>';
       // PHP cURL  for https connection with auth
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_ENCODING,  '');
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_USERPWD, $AuthID.":"); // username and password - declared at the top of the doc
       curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
       curl_setopt($ch, CURLOPT_TIMEOUT, 240);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       
       // converting
       $mtstart = $this->microtime_float();

       $response = curl_exec($ch);

       $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       $db['status'] = $httpcode;
       
       $mtend = $this->microtime_float();
       $seconds = $mtend - $mtstart;
       $db['returntime'] = $seconds;
       if(isset($_REQUEST['output_dae']))
       {
            echo '<pre>'.print_r($response, true).'</pre>';
       }
       curl_close($ch);
       
               
       // converting
       $response1 = str_replace("<soap:Body>","",$response);
       $response2 = str_replace("</soap:Body>","",$response1);
       // convertingc to XML
       $parser = @simplexml_load_string($response2);
       $response = $functionName.'Response';
       $result = $functionName.'Result';
       if($return == 'BookingReceipt' || $return == 'ResortProfile' || $return == 'MemberDetails' || $return == 'UpgradeFeeList' || $return == 'WeekDetails' || $functionName == 'DAEGetAccountDetails' || $return == 'TransactionHistory' || $functionName == 'DAEHoldWeek' || $functionName == 'DAEGetWeeksOnHold' || isset($parser->DAECompleteBookingResult) || (isset($parser->$response->$result->ReturnCode) && $parser->$response->$result->ReturnCode != 0))
       { 
           if(isset($parser->$response->$result))
               $datas = $parser->$response->$result;
       }
       elseif($return == 'GeneralResultInteger')
       {
           if(isset($parser->$response->$result->ResponseInteger))
               $datas = $parser->$response->$result->ResponseInteger;
       }
       elseif($return == 'ResortList')
       {
           $datas = $parser->$response->$result->ListItems->ResortItem;
       }
       elseif($return == 'TransactionHistory')
       {
           if(isset($parser))
               $datas = $parser;
       }
       else
       {
           if(isset($parser->$response->$result->ListItems->$return))
              $datas = $parser->$response->$result->ListItems->$return;
       }
       $output = array();
       if(isset($datas) && !empty($datas))
           foreach($datas as $d)
           {
               $output[] = $d;
           }
       $wpdb->insert('wp_daeCalls', $db);
       return $output;
    }
    
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }  
    function delete_all_between($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning)+strlen($beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }
    
        $textToDelete = substr($string, $beginningPos, $endPos  - $beginningPos);
    
        return str_replace($textToDelete, '', $string);
    }
}

?>