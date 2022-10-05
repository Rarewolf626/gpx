<?php

use GPX\CLI\Salesforce\Owner\ImportOwnersFromSalesforceCommand;

$root = realpath( $_SERVER["DOCUMENT_ROOT"] );
require( "$root/wp-load.php" );

echo PHP_EOL.'DEPRECATED' . PHP_EOL.PHP_EOL;
echo 'Instead run php console sf:owner:import from the webroot.' . PHP_EOL;
echo ImportOwnersFromSalesforceCommand::class . PHP_EOL;
exit(2);
