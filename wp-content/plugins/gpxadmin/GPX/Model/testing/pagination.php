<?php
require('../../../../../../wp-load.php');


echo <<<STYLE
<style>
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
</style>
STYLE;


use GPX\Model\Pagination;

$link = basename($_SERVER['PHP_SELF']);
$total = 100;
$limit = 10;

$page = (isset($_GET['p'])) ? intval($_GET['p']) : 1;

$pagination = new Pagination($page,$total,$limit);


echo "<PRE>";
print_r($pagination);



echo "</PRE>";


echo $pagination->display_html($link);
