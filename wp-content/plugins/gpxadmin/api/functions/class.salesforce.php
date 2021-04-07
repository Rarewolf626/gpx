<?php 

class Salesforce
{

    private static $instance = null;
    
    public function __construct($uri='', $dir='')
    {
        $this->uri = plugins_url('', __FILE__).'/api';
        $this->dir = str_replace("functions/", "", trailingslashit( dirname(__FILE__) ));
        
        
        define("SOAP_CLIENT_BASEDIR", $this->dir."/lib/salesforce/soapclient");
        require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');
        require_once (SOAP_CLIENT_BASEDIR.'/SforceHeaderOptions.php');
        require_once ($this->dir.'/models/salesforceUserAuth.php');
        
        $this->sbusername = $SBUSERNAME;
        $this->username = $USERNAME;
        $this->sbpassword = $SBPASSWORD;
        $this->password = $PASSWORD;
        $this->organizationid = $LOGINSCOPEHEADER;
        $this->scope = '/gpxprod.wsdl.xml';
        if (strpos($_SERVER['SERVER_NAME'], "my-gpx") !== false)
        {
//             if(get_current_user_id() == 5)
//             {
//                 echo '<pre>'.print_r("my-gpx", true).'</pre>';
//             }
            $this->username = $SBUSERNAME;
            $this->password = $SBPASSWORD;
            $this->scope = '/partner.wsdl.xml';
        }
    }
 
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new Salesforce();
        }
        
        return self::$instance;
    }
    
    function sessionLogin($un='', $pw='')
    {
        global $wpdb;
        
        $mySforceConnection = new SforcePartnerClient();
//         $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
        $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
        
        //is this session valid?
        $dt = date('Y-m-d H:i:s');
        $sql = "SELECT sessionVar from wp_sf_login WHERE expires > '".$dt."'";
        $session = $wpdb->get_var($sql);
        
        if(!empty($session))
        {
            $sessionObj = json_decode($session);
            
            $mySforceConnection->setEndpoint($sessionObj->serverUrl);
            
            $mySforceConnection->setSessionHeader($sessionObj->sessionId);
            
            $tsCheck = $mySforceConnection->getServerTimestamp();
        }
        
        if(empty($session) || empty($tsCheck))
        {
            $dt = date('Y-m-d H:i:s', strtotime($dt." -5 minutes"));
            
            $sessionObj = $mySforceConnection->login($this->username, $this->password);
            if(get_current_user_id() == 5)
            {
                echo '<pre>'.print_r($this->username, true).'</pre>';
                echo '<pre>'.print_r($this->password, true).'</pre>';
            }
            $session = json_encode($sessionObj);
            
            $wpdb->insert('wp_sf_login', array('sessionVar'=>$session, 'expires'=>date('Y-m-d H:i:s', strtotime($dt.' + 2 hours'))));
        }
//         if(get_current_user_id() == 5)
//         {
//             echo '<pre>'.print_r($session, true).'</pre>';
//         }
        return $sessionObj;
    }
    
    function setLoginScopeHeader()
    {
        //require_once ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            $header = new LoginScopeHeader($ORGANIZATION);
            $mySforceConnection->setLoginScopeHeader($header);
            
            
            $mylogin = $mySforceConnection->login($this->username, $this->password);
        
            print_r($mylogin);
            print_r($mySforceConnection->getServerTimestamp());
        
        } catch (Exception $e) {
            echo $mySforceConnection->getLastRequest();
            echo $e->faultstring;
        }        
    }
 
    function setSBLoginScopeHeader()
    {
        //require_once ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            $header = new LoginScopeHeader($ORGANIZATION);
            $mySforceConnection->setLoginScopeHeader($header);
            
            
//             $mylogin = $mySforceConnection->login($this->sbusername, $this->sbpassword);
        
            
            
            print_r($mylogin);
            print_r($mySforceConnection->getServerTimestamp());
        
        } catch (Exception $e) {
            echo $mySforceConnection->getLastRequest();
            echo $e->faultstring;
        }        
    }
    function search($find, $returns)
    {
        global $wpdb;
        
        foreach($returns as $key=>$value)
        {
            $return = $key." (";
            $return .= implode(", ", $value);
            $return .= ")";
            $returning[] = $return;
        }
        $search = 'FIND {'.$find['value'].'} IN '.$find['feild'];
        $search .= 'RETURNING ';
        $search .= implode(", ", $returning);
        
        
        
        
    }
    function query($query)
    {
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        
        try {
            $mySforceConnection = new SforcePartnerClient();
//             $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            //is this session valid?
            
//             $mylogin = $mySforceConnection->login($this->sbusername, $this->sbpassword);
            
            //$mySforceConnection->setEndpoint($mylogin->serverUrl);
//             $mySforceConnection->setSessionHeader($mylogin->sessionId);
            
            $session = $this->sessionLogin();
            
//             echo '<pre>'.print_r($session, true).'</pre>';
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $response = $mySforceConnection->query($query);
            $wpdb->insert('wp_sf_calls', array('func'=>'query', 'data'=>$query));
            $queryResult = new QueryResult($response);
            for ($queryResult->rewind(); $queryResult->pointer < $queryResult->size; $queryResult->next()) {
                $result[] = $queryResult->current();
            }
            return $result;
        
        } catch (Exception $e) {
            print_r($mySforceConnection->getLastRequest());
            echo $e->faultstring;
        }
             
       /*
        try {
            $mySforceConnection = new SforceEnterpriseClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            $header = new LoginScopeHeader($this->organizationid);
            $mySforceConnection->setLoginScopeHeader($header);
            $mylogin = $mySforceConnection->login($this->username, $this->password);
             
            
           
            // Define constants for the web service. We'll use these later
            //http://salesforce.stackexchange.com/questions/27806/element-item-is-invalid-can-anyone-explain-this-error
            $parsedURL = parse_url($mySforceConnection->getLocation());
            define ("_SFDC_SERVER_", substr($parsedURL['host'],0,strpos($parsedURL['host'], '.')));
            define ("_WS_NAME_", SOAP_CLIENT_BASEDIR.'/wsdl.jsp');
            define ("_WS_WSDL_", _WS_NAME_ . '.xml');
            define ("_WS_ENDPOINT_", 'https://' . _SFDC_SERVER_ . '.salesforce.com/services/wsdl/class/' . _WS_NAME_);
            define ("_WS_NAMESPACE_", 'http://soap.sforce.com/schemas/class/' . _WS_NAME_);
            
            $client = new SoapClient(_WS_WSDL_);
            $sforce_header = new SoapHeader(_WS_NAMESPACE_, "SessionHeader", array("sessionId" => $mySforceConnection->getSessionId()));
            $client->__setSoapHeaders(array($sforce_header));
            
            $method = $client->__getFunctions();
            echo _SFDC_SERVER_."<br>";
            echo _WS_NAME_."<br>";
            echo _WS_WSDL_."<br>";
            echo _WS_ENDPOINT_."<br>";
            echo _WS_NAMESPACE_."<br>";
        
            $response = $mylogin->query(($query));
        
            foreach ($response->records as $record) {
                echo '<pre>'.print_r($record, true).'</pre>';
            }
        } catch (Exception $e) {
            echo $e->faultstring;
        } */       
    }
    function gpxUpsert($object, $data, $sb = '')
    {
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            
            $username = $this->username;
            $password = $this->password;
            
            
            
            if(!empty($sb))
            {
                $username = $this->sbusername;
                $password = $this->sbpassword;
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            }
            else
            {
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            }
            
//             $mylogin = $mySforceConnection->login($username, $password);
            
//             //$mySforceConnection->setEndpoint($mylogin->serverUrl);
//             $mySforceConnection->setSessionHeader($mylogin->sessionId);
            
            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->upsert($object, $data);
            $wpdb->insert('wp_sf_calls', array('func'=>$object, 'data'=>json_encode($data)));
            
//             $mySforceConnection->logout();
            
            return $createResponse;
            //               $ids = array();
            //               foreach ($createResponse as $createResult) {
            //                 array_push($ids, $createResult->id);
            //               }
            //               $deleteResult = $mySforceConnection->delete($ids);
            
            //               $deleteResult = $mySforceConnection->undelete($ids);
        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            return $failure;
        }
        
        
        
        
    }
    
    function gpxCreate($data, $sb = '')
    {
        
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            
            $username = $this->username;
            $password = $this->password;
            
            
            
            if(!empty($sb))
            {
                $username = $this->sbusername;
                $password = $this->sbpassword;
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            }
            else
            {
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            }
            
//             $mylogin = $mySforceConnection->login($username, $password);
            
//             //$mySforceConnection->setEndpoint($mylogin->serverUrl);
//             $mySforceConnection->setSessionHeader($mylogin->sessionId);
 
            
            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->create($data);
            $wpdb->insert('wp_sf_calls', array('func'=>'create', 'data'=>json_encode($data)));
         
            return $createResponse;
            //               $ids = array();
            //               foreach ($createResponse as $createResult) {
            //                 array_push($ids, $createResult->id);
            //               }
            //               $deleteResult = $mySforceConnection->delete($ids);
            
            //               $deleteResult = $mySforceConnection->undelete($ids);
        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            return $failure;
        }
        
        
        
        
    }
    
    function gpxLogout()
    {
        
//         $mySforceConnection = new SforcePartnerClient();
        
//         $username = $this->username;
//         $password = $this->password;
        
        
        
//         if(!empty($sb))
//         {
//             $username = $this->sbusername;
//             $password = $this->sbpassword;
//             $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
//         }
//         else
//         {
//             $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/gpxprod.wsdl.xml');
//         }
        
// //         $mylogin = $mySforceConnection->login($username, $password);
        
// //         //$mySforceConnection->setEndpoint($mylogin->serverUrl);
// //         $mySforceConnection->setSessionHeader($mylogin->sessionId);
//         $session = $this->sessionLogin($username, $password);
        
//         $mySforceConnection->setEndpoint($session->serverUrl)
        
//         $mySforceConnection->setSessionHeader($session->sessionId);
        
//         $lo = $mySforceConnection->logout();
        
//         return $lo;
    }
    
    
    function gpxTransactions($data)
    {
        
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            
//             $mylogin = $mySforceConnection->login($this->username, $this->password);
            
//             //$mySforceConnection->setEndpoint($mylogin->serverUrl);
//             $mySforceConnection->setSessionHeader($mylogin->sessionId);

            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->upsert('GPXTransaction__c', $data);
            
            $wpdb->insert('wp_sf_calls', array('func'=>'GPXTransaction__c', 'data'=>json_encode($data)));
//             $mySforceConnection->logout();
            
            return $createResponse;
            //               $ids = array();
            //               foreach ($createResponse as $createResult) {
            //                 array_push($ids, $createResult->id);
            //               }
            //               $deleteResult = $mySforceConnection->delete($ids);
            
            //               $deleteResult = $mySforceConnection->undelete($ids);
        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            echo '<pre>'.print_r($failure, true).'</pre>';
            return $failure;
        }
        
        
        
        
    }
    
    function gpxCustomRequestMatch($data, $sfLoginSet='', $sb='')
    {
        
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            
            if(!empty($sb))
            {
                $username = $this->sbusername;
                $password = $this->sbpassword;
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            }
            else
            {
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            }

//             if(!empty($sfLoginSet))
//             {
//                 $sessionID = $sfLoginSet;
//             }
//             else 
//             {
//                 $mylogin = $mySforceConnection->login($username, $password);
//                 $sessionID = $mylogin->sessionId;
//             }
            

            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
                        
            $mySforceConnection->setSessionHeader($session->sessionId);

            //$mySforceConnection->setEndpoint($mylogin->serverUrl);
//             $mySforceConnection->setSessionHeader($session);
            $createResponse = $mySforceConnection->create($data);
            $wpdb->insert('wp_sf_calls', array('func'=>'custom request', 'data'=>json_encode($data)));
            
            $return = [
              'response'=>$createResponse,
            ];
            if(isset($sessionID) && !empty($sessionID))
            {
                $return['sessionId'] = $mylogin->sessionId;
            }
//             $mySforceConnection->logout();
            return $return;
            //               $ids = array();
            //               foreach ($createResponse as $createResult) {
            //                 array_push($ids, $createResult->id);
            //               }
            //               $deleteResult = $mySforceConnection->delete($ids);
            
            //               $deleteResult = $mySforceConnection->undelete($ids);
        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            return $failure;
        }
        
        
        
        
    }
    
    function depositDelete($id)
    {
        try {
            $mySforceConnection = new SforcePartnerClient();
            //             $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            //is this session valid?
            
            //             $mylogin = $mySforceConnection->login($this->sbusername, $this->sbpassword);
            
            //$mySforceConnection->setEndpoint($mylogin->serverUrl);
            //             $mySforceConnection->setSessionHeader($mylogin->sessionId);
            
            $session = $this->sessionLogin();
            
            //             echo '<pre>'.print_r($session, true).'</pre>';
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $delete = $mySforceConnection->delete($id);
            
            return $delete;
            
        } catch (Exception $e) {
            print_r($mySforceConnection->getLastRequest());
            echo $e->faultstring;
        }
    }
    
    function gpxLoginTesting($data, $sfLoginSet='', $sb='')
    {
        try {
            $mySforceConnection = new SforcePartnerClient();
            
            if(!empty($sb))
            {
                $username = $this->sbusername;
                $password = $this->sbpassword;
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
            }
            else
            {
                $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            }
            
//             if(!empty($sfLoginSet))
//             {
//                 $sessionID = $sfLoginSet;
//             }
//             else
//             {
//                 $mylogin = $mySforceConnection->login($username, $password);
//                 echo '<pre>'.print_r($createResponse, true).'</pre>';
//                 $sessionID = $mylogin->sessionId;
//             }

            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->create($data);
            echo '<pre>'.print_r($createResponse, true).'</pre>';
            $return = [
                'response'=>$createResponse,
            ];
            if(isset($mylogin->sessionId) && !empty($mylogin->sessionId))
            {
                $return['sessionId'] = $mylogin->sessionId;
            }
            return $return;
            
        } catch (Exception $e) {
                $action = $mySforceConnection->getLastRequest();
                $failure = $e->faultstring;
                return $failure;
            }
    }
    
    function gpxWeek($data)
    {
        
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            
//             $mylogin = $mySforceConnection->login($this->username, $this->password);
            
//             //$mySforceConnection->setEndpoint($mylogin->serverUrl);
//             $mySforceConnection->setSessionHeader($mylogin->sessionId);
            
            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->upsert('GPXWeek__c', $data);
            $wpdb->insert('wp_sf_calls', array('func'=>'GPX Week', 'data'=>json_encode($data)));
            return $createResponse;
            //               $ids = array();
            //               foreach ($createResponse as $createResult) {
            //                 array_push($ids, $createResult->id);
            //               }
            //               $deleteResult = $mySforceConnection->delete($ids);
            
            //               $deleteResult = $mySforceConnection->undelete($ids);
        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            return $failure;
        }
        
        
        
        
    }
    
    function emailException($type, $data, $failure, $action)
    {
        // SOAP_CLIENT_BASEDIR - folder that contains the PHP Toolkit and your WSDL
        // $this->username - variable that contains your Salesforce.com username (must be in the form of an email)
        // $this->password - variable that contains your Salesforce.com password
        
        $html = '<p><strong>'.$type.'</strong></p>';
        $html .= '<p>Transaction Time: '.date('m/d/Y h:i A').'</p>';
        $html .= '<p>Dataset:</p>';
        $html .= '<ul>';
        foreach($data as $dataKey=>$dataValue)
        {
            $html .= '<li>'.$dataKey.': '.$dataValue.'</li>';
        }
        $html .= '</ul>';
        $html .= '<p>Action: '.$action.'</p>';
        $html .= '<p>Failure: '.$failure.'</p>';
        
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            
            $mylogin = $mySforceConnection->login($this->username, $this->password);
            
            $singleEmail1 = new SingleEmailMessage();
            $singleEmail1->setToAddresses(array($eEMAILID));
            $singleEmail1->setHtmlBody($html);
            $singleEmail1->setSubject("API Exception");
            $singleEmail1->setSaveAsActivity(true);
            $singleEmail1->setEmailPriority(EMAIL_PRIORITY_LOW);
            
            $emailResponse = $mySforceConnection->sendSingleEmail(array($singleEmail1));
            
        } catch (Exception $e) {
            echo $mySforceConnection->getLastRequest();
            echo $e->faultstring;
        }
    }
}