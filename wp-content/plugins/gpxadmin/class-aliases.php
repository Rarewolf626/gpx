<?php
/**
 * This file is only used to define class alias's to prevent warnings from phpstan.
 * It is not run as part of the application.
 */
require_once __DIR__.'/api/lib/salesforce/soapclient/SObject.php';
class_alias(Illuminate\Database\Capsule\Manager::class, 'DB');
