<?php
/*--------------------------------------------------------\
 |  Assign your USER ID & PASSWORD with TOKEN
 |  This file is included in each of the SAMPLES
 \--------------------------------------------------------*/

//sandbox
$SBUSERNAME = "chris@4eightyeast.com.gpxdev";
$sfpw = '6XAF0Iw0TmSX';
$sftoken = 'vJ6jeru6qjOD6R3DyUk5alN7w';
$SBPASSWORD = $sfpw.$sftoken;
$CLIENTID = '3MVG9dCCPs.KiE4RJOybDM8wUD.Me3GLEEuKenEkADM_HMPomP8iodKy5VHjZM3RguThzwUAWbhTABO_kon8q';
$CLIENTSECRET = '7885C8AD33EB01636E39C5231D765CEA18B53C908BF81C14C27281FFEF92AAB6';
$URL = 'https://grandpacificresorts--GPXDev.cs78.my.salesforce.com/services';
//production
$USERNAME = "chris@4eightyeast.com";
$sfpw = '6XAF0Iw0TmSX';
$sftoken = 'gWJnr86WsXNhXulXhFVymKyc';
$PASSWORD = $sfpw.$sftoken;
/*--------------------------------------------------------\
 |  Create a Lead using the salesforce account
 |  Get the LEADID and modify it in following file
 |  userAuth.php in samples directory
 \--------------------------------------------------------*/

//Used for sample convertLead from file convertLead.php
$convertLEADID = '00Q5000000DO0gJEAT';

//Used for sample fieldsToNull from file fieldsToNull.php
$LEADID = '00Q5000000DO0gs';

//Used for sample loginScopeHeader from file loginScopeHeader.php
$LOGINSCOPEHEADER = '00D40000000MzoY';

//Used in sample processSubmitRequest from file processSubmitRequest.php
$OBJECTID1 = '01I40000000MMga';
$OBJECTID2 = '01Ic00000005Yxv';
$NEXTOBJECTID = '00530000000tH4t';

//Used for sending email from file sendEmail.php
$EMAILID = 'email1@test.com';

//Used for updating object from file update.php
$UPDATEOBJECTID1 = '00Q5000000K0KjM';
$UPDATEOBJECTID2 = '00Q5000000K1sL1';

//Used in callOptions
$YOURCLIENTID = 'YourClientId';
$NAMESPACE = 'aNamespace';

//Used for emailHeader from file emailHeader.php
$EMAILIDFORHEADER = 'email1@test.com';

$URL = 'https://grandpacificresorts--GPXDev.cs78.my.salesforce.com/services';

/*--------------------------------------------------------\
 |
 |  For Enterprise Samples
 |
 \--------------------------------------------------------*/
//Need to login on account then create the lead
//Assign that id here and check the sample
$eLEADID = "00Q5000000DO0gJEAT";
?>