<?php

require('../../../../../../wp-load.php');

use GPX\Model\Reports\MasterAvailability;

echo "start";
echo "<pre>";

$ma = new MasterAvailability();

echo "....loaded <br />";


$ma->filter->dates('2022-08-01','2023-08-01');

echo "START: ".$ma->filter->start_date. "<br />";
echo "END: ".$ma->filter->end_date. "<br />";

$data = $ma->run();
echo "Number of Records: ".sizeof($data). "<br />";


print_r($data);
print_r($ma->error);


echo "</pre>";
echo "end";
