<?php

namespace GPX\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use GPX\Api\Salesforce\Salesforce;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class SalesforceServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            'salesforce',
            Salesforce::class,
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            Salesforce::class,
            function () {
                $sandbox = defined( 'GPX_SALESFORCE_SANDBOX' ) && GPX_SALESFORCE_SANDBOX;
                return new Salesforce( GPX_SALESFORCE_USERNAME,
                                       GPX_SALESFORCE_PASSWORD,
                                       GPX_SALESFORCE_LOGINSCOPEHEADER,
                                       $sandbox );
            }
        );
        $this->getContainer()->add( 'salesforce', fn() => $this->getContainer()->get( Salesforce::class ) );
    }
}
