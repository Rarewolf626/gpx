<?php 

class Salesforce
{
    public $dir;
    public $sbusername;
    public $sbpassword;
    public $password;
    public $username;
    public $organizationid;
    public $client_id;
    public $client_secret;
    public $url;
    public $uri;
    public $scope;

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
        $this->sbpassword = $SBPASSWORD;

        $this->username = $USERNAME;
        $this->password = $PASSWORD;

        $this->organizationid = $LOGINSCOPEHEADER;
        $this->scope = '/gpxprod.wsdl.xml';

        if (!isset($_SERVER['SERVER_NAME']) OR (strpos($_SERVER['SERVER_NAME'], "gpxvacations") === false) )
        {
            $this->username = $SBUSERNAME;
            $this->password = $SBPASSWORD;
            $this->scope = '/partner.wsdl.xml';
        }
    }



    /*
     * singleton
     */
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
            $session = json_encode($sessionObj);
            
            $wpdb->insert('wp_sf_login', array('sessionVar'=>$session, 'expires'=>date('Y-m-d H:i:s', strtotime($dt.' + 2 hours'))));
        }

        return $sessionObj;
    }







    function setLoginScopeHeader()
    {
        //require_once ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            $header = new LoginScopeHeader($ORGANIZATION);         // @phpstan-ignore-line
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
            $header = new LoginScopeHeader($ORGANIZATION);       // @phpstan-ignore-line
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
        $result = [];
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);
            $session = $this->sessionLogin();
            $mySforceConnection->setEndpoint($session->serverUrl);
            $mySforceConnection->setSessionHeader($session->sessionId);
            $response = $mySforceConnection->query($query);
            
            $queryResult = new QueryResult($response);       // @phpstan-ignore-line
            for ($queryResult->rewind(); $queryResult->pointer < $queryResult->size; $queryResult->next()) {
                $result[] = $queryResult->current();
            }
            return $result;
        
        } catch (Exception $e) {
            print_r($mySforceConnection->getLastRequest());
            echo $e->faultstring;
        }

    }





    function gpxUpsert($object, $data, $sb = '')
    {
        global $wpdb;

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

            
            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->upsert($object, $data);
            
            if(isset($_REQUEST['debug']))
            {
                $wpdb->insert('wp_sf_calls', array('func'=>$object, 'data'=>json_encode($data)));
            }

            return $createResponse;

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
            
            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->create($data);
            if(isset($_REQUEST['debug']))
            {
                $wpdb->insert('wp_sf_calls', array('func'=>'create', 'data'=>json_encode($data)));
            }
         
            return $createResponse;

        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            return $failure;
        }
        
        
        
        
    }




    function gpxLogout()
    {
        
    }
    




    function gpxTransactions($data)
    {
        
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);

            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->upsert('GPXTransaction__c', $data);
            
            if(isset($_REQUEST['debug']))
            {
                $wpdb->insert('wp_sf_calls', array('func'=>'GPXTransaction__c', 'data'=>json_encode($data)));
            }
            
            return $createResponse;

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

            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
                        
            $mySforceConnection->setSessionHeader($session->sessionId);

            $createResponse = $mySforceConnection->create($data);

            if(isset($_REQUEST['debug']))
            {
                $wpdb->insert('wp_sf_calls', array('func'=>'custom request', 'data'=>json_encode($data)));
            }
            if(isset($_REQUEST['cr_debug']))
            {
                echo '<pre>'.print_r($createResponse, true).'</pre>';
            }
            $return = [
              'response'=>$createResponse,
            ];
            if(isset($sessionID) && !empty($sessionID))
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






    function depositDelete($id)
    {
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);

            
            $session = $this->sessionLogin();

            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $delete = $mySforceConnection->delete($id);
            
            return $delete;
            
        } catch (Exception $e) {
            print_r($mySforceConnection->getLastRequest());
            echo $e->faultstring;
        }




    }



    /**
     *  gpxLoginTesting
     *
     *  tests login session to SF
     *
     */


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



    /**
     *  gpxWeek
     *
     *  inserts week into SF
     *
     */

    function gpxWeek($data)
    {
        
        global $wpdb;
        //include ($this->dir.'/models/salesforceUserAuth.php');
        try {
            $mySforceConnection = new SforcePartnerClient();
            $mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.$this->scope);

            $session = $this->sessionLogin($username, $password);
            
            $mySforceConnection->setEndpoint($session->serverUrl);
            
            $mySforceConnection->setSessionHeader($session->sessionId);
            
            $createResponse = $mySforceConnection->upsert('GPXWeek__c', $data);
            
            if(isset($_REQUEST['debug']))
            {
                $wpdb->insert('wp_sf_calls', array('func'=>'GPX Week', 'data'=>json_encode($data)));
            }
            return $createResponse;

        } catch (Exception $e) {
            $action = $mySforceConnection->getLastRequest();
            $failure = $e->faultstring;
            return $failure;
        }

        
    }


    /**
     *  emailException
     *
     *  emails error exceptions
     *
     */


    function emailException($type, $data, $failure, $action)
    {
        // SOAP_CLIENT_BASEDIR - folder that contains the PHP Toolkit and your WSDL

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