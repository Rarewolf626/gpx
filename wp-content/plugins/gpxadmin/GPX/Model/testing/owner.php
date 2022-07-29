<?php

require('../../../../../../wp-load.php');

use GPX\Repository\OwnerRepository;


echo "start";
echo "<pre>";


$data = OwnerRepository::get_email(28372);  //16608
print_r($data);


echo "</pre>";
echo "end";
