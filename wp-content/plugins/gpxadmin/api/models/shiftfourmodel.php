<?php
class ShiftfourModel
{
    
    
    public function shiftretrieve($action, $url, $data, $token='')
    {
        global $wpdb;
        
        if($action == 'GET')
        {
            $url .= '?'.http_build_query($data);
        }
        
        
        
        $headers = array(
            'InterfaceVersion: 2.0',
            'InterfaceName: VEST',
            'CompanyName: ResorTime',
        );
        //is the access
        if(!empty($token))
        {
            $headers[] = 'AccessToken: '.$token;
        }
        //                 echo '<pre>'.print_r($url, true).'</pre>';
        $ch = curl_init($url);
        //                 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
        
        if($action == 'GET' || $action == 'DELETE')
        {
            if(strpos($url, 'transactions/invoice'))
            {
                $headers[] = 'Invoice: '.$data['invoice'];
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        //                 echo '<pre>'.print_r($headers, true).'</pre>';
        //                 echo '<pre>'.print_r($data, true).'</pre>';
        if($action == 'POST')
        {
            //                     $headers[] = 'Content-Type: application/json';
            //                     $headers[] = 'Content-Length='.strlen(json_encode($data));
            //                     $headers[] = 'Accept=*/*';
            //                     $headers[] = 'Host='.$url;
            //                     $headers[] = 'cache-control: no-cache';
            //                     $headers[] = 'no-store';
            //                     $headers[] = 'must-revalidate';
            //                     $headers[] = 'max-age=0';
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $postData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        
        if($action == 'DIRECTPOST')
        {
            //                     $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            //                     $headers[] = 'cache-control: no-cache';
            //                     $headers[] = 'no-store';
            //                     $headers[] = 'must-revalidate';
            //                     $headers[] = 'max-age=0';
            
            
            $postData = http_build_query($data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $header_data= curl_getinfo($ch);
        //                 echo '<pre>'.print_r($postData, true).'</pre>';
        //                 echo '<pre>'.print_r($url, true).'</pre>';
        //                 echo '<pre>'.print_r($response, true).'</pre>';
        $wpdb->insert('wp_shift4', array('header'=>json_encode($headers), 'post'=>$postData, 'response'=>$response, 'headerInfo'=>json_encode($header_data)));
        
        return $response;
    }
    
    
}