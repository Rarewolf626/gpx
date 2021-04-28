<?php
/*
 Plugin Name: Suppress Notices
 Description: Just a little plugin to suppress notices while in beta.
 Author: Chris Goering
 Version: 1.0
 Author URI: http://4eightyeast.com
 */
error_reporting(E_ALL &  ~( E_NOTICE | E_USER_NOTICE | E_STRICT | 
E_DEPRECATED | E_USER_DEPRECATED | E_WARNING | E_CORE_WARNING | 
E_USER_WARNING | E_COMPILE_WARNING | E_PARSE )); 
error_reporting(E_ALL ^ E_NOTICE);