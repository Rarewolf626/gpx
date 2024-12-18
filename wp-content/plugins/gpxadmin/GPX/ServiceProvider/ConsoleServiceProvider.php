<?php

namespace GPX\ServiceProvider;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ConsoleServiceProvider extends AbstractServiceProvider {

    public function provides( string $id ): bool {
        return in_array( $id, [
            Application::class,
            ConsoleOutput::class,
        ] );
    }

    public function register(): void {
        $this->getContainer()->add( Application::class, function () {
            $application = new Application('GPX Vacations');
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\CustomSalesforceCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Owner\ImportOwnersFromSalesforceCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Owner\UpdateOwnersFromSalesforceCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Owner\GenerateOwnerUpdatesInSalesforceCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Resort\FixResortIdsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Resort\PushMissingResortsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Transaction\PushMissingTransactionsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Transaction\PopulateSalesforceIdCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Transaction\CancelDeniedTransactionsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Salesforce\Transaction\CheckDepositsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\CustomRequests\DisableExpiredCustomRequestsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\CustomRequests\CheckCustomRequestsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\CustomRequests\FixCustomRequestEscapingCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Week\ActivateWeeksCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Hold\ReleaseHoldsCommand::class));
            $application->add($this->getContainer()->get(\GPX\CLI\Coupon\DeactivateExpiredCouponsCommand::class));
            $application->add(gpx(\GPX\CLI\Search\PopulateSearchColumnsCommand::class));
            $application->add(gpx(\GPX\CLI\Resort\MigrateResortMetaToResort::class));
            $application->add(gpx(\GPX\CLI\Resort\FixResortMetaDescriptions::class));
            //$application->add(gpx(\GPX\CLI\Resort\TripAdvisorLookupCommand::class));
            $application->add(gpx(\GPX\CLI\Cache\ClearViewCacheCommand::class));

            return $application;
        } );
        $this->getContainer()->addShared( ConsoleOutput::class, function () {
            $output = new ConsoleOutput();
            $style = new OutputFormatterStyle('blue', null, array('bold'));
            $output->getFormatter()->setStyle('header', $style);

            return $output;
        } );
    }
}
