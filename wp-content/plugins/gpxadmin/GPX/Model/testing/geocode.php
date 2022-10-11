<?php


require('../../../../../../wp-load.php');

Use GPX\Api\GoogleMap;



$location = GoogleMap::instance()->geocode('3312 smokey ct, antioch, ca 94531 USA');

if ($location ) {
    echo 'location : '. $location->lat.','.$location->lng;
    echo '<hr />';
    print_r($location);
} else {
    echo "location error!";

}
