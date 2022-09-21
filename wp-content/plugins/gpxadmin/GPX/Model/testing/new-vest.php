<?php

require('../../../../../../wp-load.php');
unset($session);

use GPX\Model\Owner;
use GPX\Repository\OwnerRepository;
use GPX\Model\Pagination;
//use Salesforce;

$OwnerObj = new Owner();

echo <<<STYLE
<style>
body{ font-family: "arial", sans-serif;}
ul.pagination {
    display: inline-block;
    padding: 0;
    margin: 0;
}

ul.pagination li {display: inline;}

ul.pagination li a {
    color: black;
    float: left;
    padding: 8px 16px;
    text-decoration: none;
    transition: background-color .3s;
}

ul.pagination li a.active {
    background-color: #4CAF50;
    color: white;
}

ul.pagination li a:hover:not(.active) {background-color: #ddd;}



table{
  border-collapse: collapse;
}
table.main, table.main th, table.main td{
    border: 2px solid #666;
}
table.main th, table.main td {
    padding: 5px;
    vertical-align: top;
}
table.contract{
width: 100%;
}

table.contract th{
background-color: #eee;
color: #333;
}
table.contract, table.contract th, table.contract td{
        padding: 5px;
        border-color: #666;
}
 table.contract tr th{
  border-top: 0;
}
table.contract  tr td:first-child,
 table.contract  tr th:first-child{
  border-left: 0;
}
table.contract  tr:last-child td{
  border-bottom: 0;
}
table.contract  tr td:last-child,
 table.contract  tr   th:last-child {
  border-right: 0;
}

.green {
color: green;
}
.red {
color: indianred;
}
.bold {
font-weight: bold;
}
.warning{
background-color: #ffeb3b;
    border: 1px solid #ccc !important;
padding:0 12px;
margin:  12px 0 ;
}
</style>
STYLE;


$link = basename($_SERVER['PHP_SELF']);
$limit = 12;

// if in url trust it, so we don't have to keep calling..
$total = (isset($_GET['total'])) ? intval($_GET['total']) : $OwnerObj->get_new_owner_total_sf();
$page = (isset($_GET['p'])) ? intval($_GET['p']) : 1;


// calc the offset
$offset = ( $page -1 ) * $limit;
// don't let $offset larger than 2000  - SOQL LIMIT
if ($offset > 2000)  $offset = 2000;

// display the page before...
if (( $page ) * $limit > 2000)   display_soql_2000_offset_limit_message();

$new_owners = $OwnerObj->get_new_owners_sf($limit, $offset);

$pagination = new Pagination($page,$total,$limit);


echo <<<TABLESTART

<table class="main">
<tr>

<th>SF ID</th><th>SPI ID</th><th>Name(s)</th><th>Email</th><th>Phone</th><th>Address</th><th>Contracts</th>


</tr>
TABLESTART;

    foreach ($new_owners as $new_owner) {
        echo '<tr>';



        echo "<td>".$new_owner->Property_Owner__c."</td>";
        echo "<td>".intval($new_owner->Name)."</td>";
        // name(S)
        echo "<td>".$new_owner->SPI_First_Name__c." ".$new_owner->SPI_Last_Name__c;
        $secondname = (isset($new_owner->SPI_First_Name2__c)) ? "<br/>".$new_owner->SPI_First_Name2__c." ".$new_owner->SPI_Last_Name2__c : '';
        echo $secondname.'</td>';
       // email
        echo "<td>".$new_owner->SPI_Email__c."</td>";

        // phones
       echo '<td>';
       echo  (isset($new_owner->SPI_Home_Phone__c)) ? "<span class='bold'>H:</span> ".$new_owner->SPI_Home_Phone__c : '';
       echo (isset($new_owner->SPI_Home_Phone__c) && isset($new_owner->SPI_Work_Phone__c)) ? '<br />' :'';
       echo  (isset($new_owner->SPI_Work_Phone__c)) ? "<span class='bold'>W:</span> ".$new_owner->SPI_Work_Phone__c : '';
       echo  '</td>';

       // address
        echo '<td>';
        echo $new_owner->SPI_Street__c.'<br/>';
        echo $new_owner->SPI_City__c.' '.$new_owner->SPI_State__c.' '.$new_owner->SPI_Zip_Code__c.' '.$new_owner->SPI_Country__c;
        echo  '</td>';

        // contracts
        echo '<td style="padding:0">';

        $new_interval = $OwnerObj->get_owner_intervals_sf($new_owner->Name);

        echo print_contracts($new_interval);


        echo  '</td>';


        // end row
        echo '</tr>';
    }



echo <<<TABLEEND
</table>
TABLEEND;

$data = $pagination->get_data();

echo $pagination->display_html($link);

echo "<hr>";
echo "Records: ";
echo ( ($data['current_page'] - 1) * $limit)+1;
echo " to ";
echo count($new_owners) * $data['current_page'];

echo " of ". $total;

echo " | Total Pages: ";
$data = $pagination->get_data();
echo $data['max_pages'];

/**
 * @param $i
 * @return string
 */
    function print_contracts($i) {
       $html = '<table class="contract">';
        $html .= '<tr><th>Contract Id</th><th>Resort Id</th><th>Delinquent</th><th>Room Type</th></tr>';
        foreach ($i as $j) {
            $html.= '<tr>';
            $html .= '<td>'.$j->Contract_ID__c.'</td>';
            $html .= '<td>'.$j->GPR_Resort__c.' ('.$j->Resort_ID_v2__c.')</td>';
            //  Delinquent
            if ($j->Delinquent__c == 'Yes') {
                $html .= "<td class='red'>";
                $html .= 'Days: '. intval($j->Days_Past_Due__c). '<br />';
                $html .=  '$'. number_format($j->Total_Amount_Past_Due__c,2);
            }   else {
                $html .= "<td class='green'>";
                $html .=  $j->Delinquent__c;
            }
            $html .= '</td>';
            $html .= '<td>'.$j->Room_Type__c.'</td>';
            $html.= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }



/**
 *
 */
function display_soql_2000_offset_limit_message(){

    echo <<<MESSAGE

<div class="warning">

<h1>Warning: SalesForce SOQL limit</h1>

Offset can not be greater than 2000.
<p>when querying SF directly with SOSQL can not pull records past 2000 for this data.</p>
</div>

MESSAGE;


}
