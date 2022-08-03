<?php
require('../../../../../../wp-load.php');

use GPX\Repository\WeekRepository;
use GPX\Model\Week;

//$week_repo = WeekRepository::where('record_id','=','47347575')->first();

echo "<pre>";
echo "start";
//print_r($week_repo);
//print_r( $week_repo->get_details());
$weekObj = Week::get_week(47347575);

print_r($weekObj);

echo "end";
echo "</pre>";
