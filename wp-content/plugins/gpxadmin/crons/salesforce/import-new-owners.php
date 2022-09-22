<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require("$root/wp-load.php");

use GPX\Model\Owner;
use GPX\Repository\OwnerRepository;

// number of owners to call on each run
$limit = 3;


$OwnerObj = new Owner();



$new_owners = $OwnerObj->get_new_owners_sf($limit);


foreach ($new_owners as $new_owner) {

    /*
    $new_owner->Property_Owner__c
    intval($new_owner->Name)
    $new_owner->SPI_First_Name__c
    $new_owner->SPI_Last_Name__c
    $new_owner->SPI_First_Name2__c
    $new_owner->SPI_First_Name2__c
    $new_owner->SPI_Last_Name2__c
    $new_owner->SPI_Email__c
    $new_owner->SPI_Home_Phone__c
    $new_owner->SPI_Home_Phone__c
    $new_owner->SPI_Home_Phone__c
    $new_owner->SPI_Work_Phone__c
    $new_owner->SPI_Work_Phone__c
    $new_owner->SPI_Work_Phone__c
    $new_owner->SPI_Street__c
    $new_owner->SPI_City__c
    $new_owner->SPI_State__c
    $new_owner->SPI_Zip_Code__c
    $new_owner->SPI_Country__c
*/

    $new_intervals = $OwnerObj->get_owner_intervals_sf($new_owner->Name);

    var_dump($new_owner);
    var_dump($new_intervals);



    foreach ($new_intervals  as $interval) {


/*
         $j->GPR_Resort__c
         $j->Resort_ID_v2__c
         $j->Contract_ID__c;
         $j->Delinquent__c;
         intval($j->Days_Past_Due__c);
         number_format($j->Total_Amount_Past_Due__c,2);
         $j->Room_Type__c
*/


    }

}
