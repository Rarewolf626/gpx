<?php 

class RestSalesforce
{
    
    protected $curl;
    protected $response;

    public function __construct() 
    {
        $this->dir = str_replace("functions/", "", trailingslashit( dirname(__FILE__) ));
        
        require_once ($this->dir.'/models/salesforceUserAuth.php');
        
        $this->sbusername = $SBUSERNAME;
        $this->username = $USERNAME;
        $this->sbpassword = $SBPASSWORD;
        $this->password = $PASSWORD;
        $this->organizationid = $LOGINSCOPEHEADER;
        $this->client_id = $CLIENTID;
        $this->client_secret = $CLIENTSECRET;             
        $this->url = $URL;

        curl_close($curl);
    }

    public function getAuthTocken() 
    {
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url."/oauth2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('username' => $this->sbusername,'password' => $this->sbpassword,'grant_type' => 'password','client_id' => $this->client_id,'client_secret' => $this->client_secret)
        ));
        $response = json_decode(curl_exec($curl));
       
        return $response;

    }

    public function query($q) 
    {
          return $this->url."/data/v48.0/query?q=".urlencode($q);
    }

    public function httpGet($parameter = null)
    {
        
       
        $oauth = $this->getAuthTocken();
        echo '<pre>'.print_r($oauth, true).'</pre>';
        $header = [
            'Authorization: Bearer '.$oauth->access_token   
        ];
        
//         $ch = curl_init();
//         curl_setopt_array($ch, array(
//         CURLOPT_URL => $this->query($parameter),
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => "",
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 0,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => "GET",
//         CURLOPT_HTTPHEADER => $header,
//         ));

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->query($parameter));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        $response = curl_exec($ch);
        
//         $response = curl_exec($curl);
        echo '<pre>'.print_r($response, true).'</pre>';
        
        curl_close($ch);
        
        return json_decode($response);

    }

    public function httpPost($params, $object)
    {
        $postData = '';

        $oauth = $this->getAuthTocken();
        
        $header = [
            'Authorization: Bearer '.$oauth->access_token,
            "Content-Type: application/json",
        ];
                
        foreach($params as $k => $v) 
        { 
            $postData .= $k . '='.$v.'&'; 
        }
        
            $postData = rtrim($postData, '&');
            $ch = curl_init();  
            curl_setopt($ch,CURLOPT_URL,$this->url.'/data/v48.0/sobjects/'.$object);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');    
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);    

            $output=curl_exec($ch);
            curl_close($ch);
            return $output;
    }


}