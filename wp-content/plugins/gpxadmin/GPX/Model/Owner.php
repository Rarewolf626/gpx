<?php



namespace GPX\Model;



class Owner
{

    /*
     *  don't really insert if in debug, instead dump inserts to the stdout
     */
    private bool $debug = true;


    /**
     * @return array|void
     */
    public function get_new_owners_sf() {


        $sf = \Salesforce::getInstance();

        $sfquery = "SELECT CreatedDate,Name,SPI_First_Name__c,SPI_Last_Name__c,
                     Total_Active_Contracts__c,
                      SPI_First_Name2__c,SPI_Last_Name2__c,SPI_Email__c,
                      SPI_Home_Phone__c,SPI_Work_Phone__c,SPI_Street__c,
                      SPI_City__c,SPI_State__c,SPI_Zip_Code__c,
                      SPI_Country__c,SpiOwnerId__c,Property_Owner__c,
                      Legacy_Preferred_Program_Member__c,GPX_Member_VEST__c

                FROM GPR_Owner_ID__c
                WHERE
                    GPX_Member_VEST__c = null
                    AND HOA_Developer__c = false
                    AND Id NOT IN (SELECT GPR_Owner_ID__c FROM Ownership_Interval__c WHERE Resort_ID_v2__c='GPVC')
                ORDER BY CreatedDate DESC
                LIMIT 12
                ";

     return $sf->query($sfquery);

    }


    /**
     * @param $ownerid
     * @return array|void
     */
    public function get_owner_intervals_sf($ownerid) {

        $sf = \Salesforce::getInstance();

        $query2 = "SELECT  Owner_ID__c,GPR_Resort__c,Contract_ID__c,UnitWeek__c,
                           Contract_Status__c,Delinquent__c,Days_Past_Due__c,
                           Total_Amount_Past_Due__c,Room_Type__c,ROID_Key_Full__c,
                           Resort_ID_v2__c
                    FROM Ownership_Interval__c
                    WHERE Resort_ID_v2__c != 'GPVC' AND
                        Owner_ID__c ='".$ownerid."'";

        return $sf->query($query2);

    }


    /**
     * @param $ownerObj
     * @return void
     */
    public function add_new_owner_sf($ownerObj)
    {

        global $wpdb;

        if (!$this->debug) $wpdb->update('import_owner_no_vest', array('imported' => '5'), array('id' => $ownerObj->Name));

        // check if the user already exists
        // check the wp_user_meta for the 'Name'/GPX_Member_VEST__c to see if the owner has been added
        $user = get_users(array(
            'meta_key' => 'GPX_Member_VEST__c',
            'meta_value' => $ownerObj->Name));

        // if there is no email, make one up
        if(empty($ownerObj->SPI_Email__c))   $ownerObj->SPI_Email__c = 'gpr'.$ownerObj->Name.'@NOT_A_VALID_EMAIL.com';


        if (empty($user)) {
            // user not found
            // insert as new user
            $this->insert_new_owner($ownerObj);
            $this->insert_new_intervals($ownerObj->fields->Name);

        } else {
            // user found
            // force to use this user
            $this->update_existing_owner($ownerObj);
        }

    }

    /**
     * @param $ownerObj
     * @return void
     */
    private function insert_new_owner ($ownerObj){

        $user_login = wp_slash( $ownerObj->SPI_Email__c );
        $user_email = wp_slash( $ownerObj->SPI_Email__c );
        $user_pass = wp_generate_password();

        $userdata = [
            'user_login' => $user_login,
            'user_email' => $user_email,
            'user_pass'  => $user_pass,
        ];


        if (!$this->debug)  {
            // insert into WP_User
            $user_id = wp_insert_user($userdata);
            // insert into user_meta

            // set GPX_Member_VEST__c in SF

        } else {
            echo "Add WP User<br>"; print_r($userdata);
        }
    }


    /**
     * @param $ownerObj
     * @return void
     */
    private function update_existing_owner($ownerObj) {


    }


    /**
     * @param $ownerid  Name
     * @return void
     */
    public function add_new_intervals($ownerid) {

        global $wpdb;
        $sf = \Salesforce::getInstance();

        $intervals = $this->get_owner_intervals_sf($ownerid);

        // loop through the intervals and add them to WP
        foreach($intervals as $interval) {

            //insert interval
            $r2 = $interval->fields;

            $data = [
                'userID'                    =>$user_id,
                'ownerID'                   =>$r2->Owner_ID__c,
                'resortID'                  =>substr($r2->GPR_Resort__c, 0, 15),
                'contractID'                =>$r2->Contract_ID__c,
                'unitweek'                  =>$r2->UnitWeek__c,
                'Contract_Status__c'        =>$r2->Contract_Status__c,
                'Delinquent__c'             =>$r2->Delinquent__c,
                'Days_past_due__c'          =>$r2->Days_Past_Due__c,
                'Total_Amount_Past_Due__c'  =>$r2->Total_Amount_Past_Due__c,
                'Room_type__c'              =>$r2->Room_Type__c,
                'Year_Last_Banked__c'       =>$r2->Year_Last_Banked__c,
                'RIOD_Key_Full'             =>$r2->ROID_Key_Full__c,
            ];

            // does the interval exist?
            $sql = $wpdb->prepare("SELECT id FROM wp_owner_interval WHERE RIOD_Key_Full=%s", $r2->ROID_Key_Full__c);
            $row = $wpdb->get_row($sql);
            if(empty($row))
            {
                // doesn't exist - insert
                if (!$this->debug) {
                    $wpdb->insert('wp_owner_interval', $data);
                } else {
                    echo "insert<br>";print_r($data);
                }
            }
            else
            {
                // exists - update
                if (!$this->debug) {
                    $wpdb->update('wp_owner_interval', $data, array('RIOD_Key_Full'=>$r2->ROID_Key_Full__c));
                } else {
                    echo "update<br>";print_r($data);
                }
            }

        }  // foreach


    }




}
