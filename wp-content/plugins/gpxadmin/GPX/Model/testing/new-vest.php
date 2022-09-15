<?php

require('../../../../../../wp-load.php');
unset($session);

use GPX\Model\Owner;
use GPX\Repository\OwnerRepository;
//use Salesforce;

$OwnerObj = new Owner();

$new_owners = $OwnerObj->get_new_owners_sf();


echo "<PRE>";

print_r($new_owners);

echo "<HR />";


foreach ($new_owners as $new_owner) {

    $new_interval = $OwnerObj->get_owner_intervals_sf($new_owner->fields->Name);
    print_r($new_interval);
}

echo "</PRE>";
