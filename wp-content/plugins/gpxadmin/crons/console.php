<?php

 $application->add(gpx(\GPX\CLI\Salesforce\Owner\ImportOwnersFromSalesforceCommand::class));
 $application->add(gpx(\GPX\CLI\Salesforce\Owner\UpdateOwnersFromSalesforceCommand::class));
 $application->add(gpx(\GPX\CLI\Salesforce\Owner\GenerateOwnerUpdatesInSalesforceCommand::class));
 $application->add(gpx(\GPX\CLI\Salesforce\Resort\FixResortIdsCommand::class));
 $application->add(gpx(\GPX\CLI\Week\ActivateWeeksCommand::class));
