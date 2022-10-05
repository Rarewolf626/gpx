<?php
require('../../../../../../wp-load.php');

use GPX\Repository\WeekRepository;
use GPX\Model\Week;

echo "<pre>";
echo "start";
//print_r($week_repo);
//print_r( $week_repo->get_details());
$week = WeekRepository::instance()->get_week(47347575);
print_r($week->update_details);
$weekObj = Week::with('unit')->find(47347575);

print_r($weekObj);

echo "end";
echo "</pre>";
