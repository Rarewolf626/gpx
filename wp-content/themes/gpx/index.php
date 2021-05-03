<?php
/**
 * Author: marioccarloss
 * Date: 03/09/16
**/
global $formheader;
$formheader='default';
get_header(); 
 
if(is_homepage()) {
  get_template_part( 'pages/page-home'); 	
} else {
  get_template_part( 'content-page');
}
 
get_footer(); 
