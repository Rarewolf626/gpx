<?php

$application->add(gpx(\GPX\CLI\Salesforce\CustomSalesforceCommand::class));
$application->add(gpx(\GPX\CLI\Salesforce\Owner\ImportOwnersFromSalesforceCommand::class));
$application->add(gpx(\GPX\CLI\Salesforce\Owner\UpdateOwnersFromSalesforceCommand::class));
$application->add(gpx(\GPX\CLI\Salesforce\Owner\GenerateOwnerUpdatesInSalesforceCommand::class));
$application->add(gpx(\GPX\CLI\Salesforce\Resort\FixResortIdsCommand::class));
$application->add(gpx(\GPX\CLI\CustomRequests\CheckCustomRequestsCommand::class));
$application->add(gpx(\GPX\CLI\CustomRequests\DisableExpiredCustomRequestsCommand::class));
$application->add(gpx(\GPX\CLI\CustomRequests\ReleaseHeldRequestsCommand::class));
$application->add(gpx(\GPX\CLI\CustomRequests\SendSixtyDayRequestNotificationsCommand::class));
