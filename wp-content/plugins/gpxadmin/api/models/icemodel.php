<?php
class IceModel
{
    
    protected $cred;
    protected $data;

    public function iceretrieveJWT($cred, $jwt){

        error_log( "ATTEMPTING JWT RETRIEVE");

        global $wpdb;
        extract($cred);

        $dailyKey = $this->getDailyApiKey($AppId, $AppKey, $host);
        $ssotoken = $this->getAuthorization($AppId, $AppKey, $host, $dailyKey);

        error_log("Daily Key: " . print_r($dailyKey, true));
        error_log("SSO Token: " . print_r($ssotoken, true));

        $url = "https://partneraccesspoint-api-prod-westus.azurewebsites.net/redirect/jwt_sso_in";

        //$token = "080042cad6356ad5dc0a720c18b53b8e53d4c274"; // Get your token from a cookie or database
        //$post = array('some_trigger'=>'...','some_values'=>'...'); // Array of data with a trigger

        header('Content-Type: application/json'); // Specify the type of data
        $ch = curl_init($url); // Initialise cURL
        
        $post = json_encode($jwt); // Encode the data array into a JSON string
        
        $authorization = "Authorization: Bearer ".$ssotoken; // Prepare the authorisation token
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        
        $result = curl_exec($ch); // Execute the cURL statement

        error_log("The Result");
        error_log( print_r($result , true) );
        
        curl_close($ch); // Close the cURL connection
        
        //return json_decode($result); // Return the received data
        return $result;
    } 
    
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

    /*
    //JWT Functions from Documentation
    public function requestJwtSso($token, $post){

        header('Content-Type: application/json'); // Specify the type of data
        $ch = curl_init('https://APPURL.com/api/json.php'); // Initialise cURL
        $post = json_encode($post); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        return json_decode($result); // Return the received data         
    }

    public function attemptJWTConnection(){
        $token = "080042cad6356ad5dc0a720c18b53b8e53d4c274"; // Get your token from a cookie or database
        $post = array('some_trigger'=>'...','some_values'=>'...'); // Array of data with a trigger
        $request = $this->requestJwtSso($token,$post); // Send or retrieve data
    }
    */
}
