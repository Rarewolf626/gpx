<?php
/**
 * @var array[] $resorts
 * @var float[] $propPrice
 * @var string[] $propType
 * @var array $propDates
 * @var int $resortid
 * @var int $totalCnt
 */

$outcnt = 0;

$total = array_sum(array_map(fn($resort) => count($resort['props']), $resorts));
$output = '<ul class="w-list-view dgt-container" id="results-content" data-count="'.esc_attr((int)$total).'">';
foreach($resorts as $resort)
{

$output .= '<li class="w-item-view filtered" id="rl'.$resort['resort']->id.'" data-subregions=\'["'.$resort['resort']->gpxRegionID.'"]\'>';
$output .= '<ul id="gpx-listing-result" class="w-list-result" >';
    foreach($resort['props'] as $prop)
    {
        $date = date('m/d/Y', strtotime($prop->checkIn));
        $prop->sortDate = $date;
    }
    if(isset($propDates)) ksort($propDates);
    ksort($resort['props']);
    $rt = 0;
    foreach($resort['props'] as $pk=>$prop)
    {
    	$outcnt++;
        $rt++;
        $prop->Price = number_format($propPrice[$pk], 0);
        $prop->WeekPrice = $prop->Price;
        $prop->WeekType = $propType[$pk];
        $cmpSP = '';
        $cmpP = '';
        $finalPrice = $prop->WeekPrice;
        $highlight = false;
        $hasSpecial = false;
        $showSlash = false;
        if(!empty($prop->specialPrice))
        {
            $cmpSP = preg_replace("/[^0-9\.]/", "",$prop->specialPrice);
            $cmpP = preg_replace("/[^0-9\.]/", "",$prop->Price);
        }
        if (!empty($prop->specialPrice) && ($cmpSP - $cmpP != 0)) {
            $finalPrice = $prop->specialPrice;
            $hasSpecial = true;
            $highlight = true;
            if(isset($prop->specialicon) && isset($prop->specialdesc) && !empty($prop->speciaicon)){
                $showSlash = true;
            }
        }
$output .= '<li id="prop'.str_replace(" ", "", $prop->WeekType).$prop->weekId.'" class="item-result';
if($highlight) $output .= ' active';
$output .= '"';
$output .= 'data-resorttype=\'["'.$prop->WeekType.'"';
if(!empty($prop->AllInclusive)) $output .= ' ,"'.$prop->AllInclusive.'"';
$output .= ']\'';
$output .= '>';
$output .= '<div class="w-cnt-result">';
$output .= '<div class="result-header ' . ($showSlash ? 'result-header--highlight' : '') . '">';
   $pricesplit = explode(" ", $prop->WeekPrice);
   $thisPrice = $prop->WeekPrice;
    $output .= '<div class="result-header-details">';
        $output .= '<div class="result-header-pricing">';
            $output .= '<div class="result-header-price '.($showSlash ? 'result-header-price--strike' : '').'">';
                $output .= gpx_currency(gpx_parse_number($showSlash ? $prop->WeekPrice : $finalPrice), true);
            $output .= '</div>';
            if($showSlash){
                $output .= '<div class="result-header-price result-header-price--now">';
                    $output .= '<span>Now</span> <strong>'. gpx_currency(gpx_parse_number($finalPrice), true) . '</strong>';
                $output .= '</div>';
            }
            if(isset($prop->specialicon) && isset($prop->specialdesc)){
                $dialogID = bin2hex(random_bytes(8));
                $output .= '<a href="#dialog-special-'.$dialogID.'" class="special-link" aria-label="promo info"><i class="fa '.$prop->specialicon.'"></i></a>';
                $output .= '<dialog id="dialog-special-'.$dialogID.'" class="modal-special">';
                $output .= '<div class="w-modal">';
                $output .= '<p>'.$prop->specialdesc.'</p>';
                $output .= '</div>';
                $output .= '</dialog>';
            }
        $output .= '</div>';
        if($resort['resort']->ResortFeeSettings['enabled'] ?? false){
                $output .= '<div class="result-header-fees">';
                        $output .= gpx_currency($finalPrice + $resort['resort']->ResortFeeSettings['total'], true) . ' including resort fees';
                $output .= '</div>';
        }
    $output .= '</div>';
    $output .= '<div class="result-header-status">';
        if($prop->WeekType === 'Exchange Week'){
            $output .= '<div class="status-icon status-icon--ExchangeWeek"></div>';
        } else {
            $output .= '<div class="status-icon status-icon--RentalWeek" ></div>';
        }
    $output .= '</div>';
$output .= '</div>';
$output .= '<div class="cnt">';
$weekType = 'Rental Week';
if($prop->WeekType == 'ExchangeWeek')
{
    $weekType = 'Exchange Week';
}
$output .= '<p><strong>'.$weekType.'</strong>';

if($prop->prop_count < 6)
{
    $output .= '<span class="count-'.str_replace(" ", "", $prop->WeekType).'"> Only '.$prop->prop_count.' remaining </span>';
}
$output .= '</p>';
$output .= '<p>Check-In '.date('m/d/Y', strtotime($prop->checkIn)).'</p>';
$output .= '<p>'.$prop->noNights.' Nights</p>';
$output .= '<p>Size '.$prop->Size.'</p>';
$output .= '</div>';
$output .= '<div class="list-button">';

//Changed from limiting # of holds to just hiding the Hold button for SoCal weeks between Memorial day and Labor day.
//set an empty hold class
$holdClass = '';
//is this in the summer?
$checkIN = strtotime($prop->checkIn);
$thisYear = date('Y', $checkIN);
$memorialDay = strtotime(" last monday of may $thisYear");
$laborDay = strtotime("first monday of september $thisYear");
if(($memorialDay <= strtotime($prop->checkIn) AND strtotime($prop->checkIn) <= $laborDay)) //the date in the range is between memorial day and labor day
{
    //check to see if this gpxRegionID is a restricted one.
    if(isset($restrictIDs) && in_array($prop->gpxRegionID, $restrictIDs))
    {
        //we don't want to show the hold button
        $holdClass = 'hold-hide';
    }
}
//Chris- We want to disable consumer use until the hold plus one project is done.  Need to keep this simple.
//Thanks
//Jeff
// $holdClass = 'hold-hide';
$output .= '<a href="" class="dgt-btn hold-btn '.$holdClass.'" data-type="'.str_replace(" ", "", $prop->WeekType).'" data-wid="'.$prop->weekId.'" data-pid="'.$prop->PID.'" data-cid="';
if(isset($cid)) $output .= $cid;
$output .= '">Hold<i class="fa fa-refresh fa-spin fa-fw" style="display: none;"></i></a>';
$output .= '<a href="/booking-path/?book='.$prop->PID.'&type='.str_replace(" ", "", $prop->WeekType).'" class="dgt-btn active book-btn '.$holdClass.'" data-type="'.str_replace(" ", "", $prop->WeekType).'" data-propertiesID="'.$prop->PID.'" data-wid="'.$prop->weekId.'" data-pid="'.$prop->PID.'" data-cid="';
if(isset($cid)) $output .= $cid;
$output .= '"';
//add all the extra options for filtering
if(!isset($minPrice) || $thisPrice < $minPrice)
{
    $minPrice = $thisPrice;
}
if(!isset($maxPrice) || $thisPrice > $maxPrice)
{
    $maxPrice = $thisPrice;
}
if(!isset($minDate) || $checkIN < $minDate)
{
    $minDate = $checkIN;
}
if(!isset($maxDate) || $checkIN < $maxDate)
{
    $maxDate = $checkIN;
}
$output .= ' data-minprice="'.$minPrice.'" ';
$output .= ' data-maxprice="'.$maxPrice.'" ';
$output .= ' data-mindate="'.$minDate.'" ';
$output .= ' data-maxdate="'.$maxDate.'" ';
$output .= ' data-runcnt="'.$rt.'" ';
$output .= '>Book</a>';
$output .= '</div>';
$output .= '</div>';
$output .= '</li>';
    }
$output .= '</ul>';
$output .= '</li>';
$output.='<div id="res_count_'.$resortid.'" data-res-count="'.number_format($outcnt).'"></div>';
}

$output .= '</ul>';
if(isset($limitCount) && $limitCount < 10000)
{
    $nextCnt = $totalCnt + 10;
    $output .= '<div style="text-align: center; margin-bottom: 20px;"><a href="#" class="dgt-btn show-more-btn" data-next="'.$nextCnt.'">Show All</a></div>';
}
?>
