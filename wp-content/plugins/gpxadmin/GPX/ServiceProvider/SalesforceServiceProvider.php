<?php

namespace GPX\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use GPX\Api\Salesforce\Salesforce as GPXSalesforce;
use Salesforce;

class SalesforceServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            'salesforce',
            Salesforce::class,
            GPXSalesforce::class,
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            Salesforce::class,
            function () {
                return Salesforce::getInstance();
            }
        );
        $this->getContainer()->addShared(
            GPXSalesforce::class,
            function () {
                $sandbox = defined( 'GPX_SALESFORCE_SANDBOX' ) && GPX_SALESFORCE_SANDBOX;

                return new GPXSalesforce( GPX_SALESFORCE_USERNAME,
                                          GPX_SALESFORCE_PASSWORD,
                                          GPX_SALESFORCE_LOGINSCOPEHEADER,
                                          $sandbox );
            }
        );
        $this->getContainer()->add( 'salesforce', fn() => $this->getContainer()->get( GPXSalesforce::class ) );
    }
}
