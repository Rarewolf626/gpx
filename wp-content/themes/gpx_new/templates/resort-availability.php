<?php

$output = '        <ul class="w-list-view dgt-container" id="results-content">';
foreach($resorts as $resort)
{
$output .= '<li class="w-item-view filtered" id="rl'.$resort->id.'" data-subregions=\'["'.$resort['resort']->gpxRegionID.'"]\'>';
$output .= '<ul id="gpx-listing-result" class="w-list-result" >';
    foreach($resort['props'] as $prop)
    {
        $date = date('m/d/Y', strtotime($prop->checkIn));
        $prop->sortDate = $date;
    }
    ksort($propDates);
    ksort($resort['props']);
//     usort($resort['props'], function($a, $b)
//     {
//         return strcmp($a->sortDate, $b->sortDate);    
//     });
    $rt = 0;
    foreach($resort['props'] as $pk=>$prop)
    {
        $rt++;
        $prop->Price = number_format($propPrice[$pk], 0);
        $prop->WeekPrice = $prop->Price;
        $prop->WeekType = $propType[$pk];
        $cmpSP = '';
        $cmpP = '';
        if(!empty($prop->specialPrice))
        {
            $cmpSP = preg_replace("/[^0-9\.]/", "",$prop->specialPrice);
            $cmpP = preg_replace("/[^0-9\.]/", "",$prop->Price);
        }        
$output .= '<li id="prop'.str_replace(" ", "", $prop->WeekType).$prop->weekId.'" class="item-result'; 
               if(!empty($prop->specialPrice) && ($cmpSP - $cmpP != 0))
                   $output .= ' active';
$output .= '"';
$output .= 'data-resorttype=\'["'.$prop->WeekType.'"';
if(!empty($prop->AllInclusive))
    $output .= ' ,"'.$prop->AllInclusive.'"';
$output .= ']\'';
$output .= '>';
$output .= '<div class="w-cnt-result">';
$output .= '<div class="result-head">';
               $pricesplit = explode(" ", $prop->WeekPrice);
               $thisPrice = $prop->WeekPrice;
               
               if(empty($prop->specialPrice) || ($cmpSP - $cmpP == 0))
                   $output .= '<p>$<strong>'.$prop->WeekPrice.'</strong></p>';
               else
               {
                   if(isset($prop->specialicon) && isset($prop->specialdesc) && !empty($prop->speciaicon))
                   {
                       $output .= '<p class="mach">$<strong>'.$prop->WeekPrice.'</strong></p>';
                   }
                   echo '';
                   if($prop->specialPrice - $prop->Price != 0)
                   {
                       $output .= '<p class="now">';
                       if(isset($prop->specialicon) && isset($prop->specialdesc) && !empty($prop->speciaicon))
                       {
                           $output .= 'Now ';
                       }
                       $output .= '<strong>$'.number_format($prop->specialPrice, 0).'</strong></p>';
                       $thisPrice = number_format($prop->specialPrice, 0);
                   }
               }
               if(isset($prop->specialicon) && isset($prop->specialdesc))
               {
$output .= '<a href="#" class="special-link" aria-label="promo info"><i class="fa '.$prop->specialicon.'"></i></a>';
$output .= '<div class="modal dgt-modal modal-special">';
$output .= '<div class="close-modal"><i class="icon-close"></i></div>';
$output .= '<div class="w-modal">';
$output .= '<p>'.$prop->specialdesc.'</p>';
$output .= '</div>';
$output .= '</div>';
               } 
$output .= '<ul class="status">';
$output .= '<li>';
$output .= '<div class="status-'.str_replace(" ", "", $prop->WeekType).'"></div>';
$output .= '</li>';
$output .= '</ul>';
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
}
$output .= '</ul>';
if(isset($limitCount) && $limitCount < 10000)
{
    $nextCnt = $totalCnt + 10;
    $output .= '<div style="text-align: center; margin-bottom: 20px;"><a href="#" class="dgt-btn show-more-btn" data-next="'.$nextCnt.'">Show All</a></div>';
}
?>