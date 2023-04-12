<?php
class IceModel
{
    
    protected $cred;
    protected $data;
    
    public function iceretrieve($cred, $data)
    {
        global $wpdb;
        extract($cred);
        extract($data);
        
        
        
        if($function == 'dailyapikey')
            $inputMembers = array('app_id' => $AppId,
                'app_key' => $AppKey,
                'dev_identifier' => "GPX",
            );
            
            $url = $host.$function;
            
            if($action == 'GET')
                $url .= '?'.http_build_query($inputMembers);
                
                $dailyKey = $this->getDailyApiKey($AppId, $AppKey, $host);
                
                $headers = array(
                    
                );
                if(get_current_user_id() == 5)
                {
                    //            echo '<pre>'.print_r($url, true).'</pre>';
                }
                
                $ch = curl_init($url);
                
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
                
                if($action == 'POST')
                {
                    $postData = json_encode($inputMembers);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'DailyApiKey: '.$dailyKey,
                        'Content-Length: ' . strlen($postData)));
                    
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                }
                else
                {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'DailyApiKey: '.$dailyKey,
                    ));
                }
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                if(get_current_user_id() == 5)
                {
                    //                       echo '<pre>'.print_r($inputMembers, true).'</pre>';
                    //                        echo '<pre>'.print_r($response, true).'</pre>';
                }
                
                return $response;
    }
    
    public function getDailyApiKey($AppId, $AppKey, $host)
    {
        $inputMembers = array('app_id' => $AppId,
            'app_key' => $AppKey,
            'dev_identifier' => "GPX",
        );
        
        $postData = json_encode($inputMembers);
        
        $ch = curl_init($host."dailyapikey");
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData))
            );
        
        $response = json_decode(curl_exec($ch));
        
        return $response->daily_api_key;
    }
    public function getAuthorization($AppId, $AppKey, $host, $dailyKey)
    {
        $inputMembers = array(
            'code' => $dailyKey,
            'app_id' => $AppId,
            'app_key' => $AppKey,
            'dev_identifier' => "GPX",
        );
        
        $postData = json_encode($inputMembers);
        
        $ch = curl_init($host."token");
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData))
            );
        
        $response = json_decode(curl_exec($ch));
        //         echo '<pre>'.print_r($response, true).'</pre>';
        return $response->token;
    }
    
}
