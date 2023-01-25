<?php

namespace GPX\ServiceProvider;

use GPX\Api\GoogleMap;
use League\Container\ServiceProvider\AbstractServiceProvider;

class GeocodeServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            GoogleMap::class,
            'geocode',
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(
            GoogleMap::class, function () {
            return new GoogleMap(GPX_GOOGLE_MAPS_GEOCODE_API_KEY);
        } );

        $this->getContainer()->add( 'geocode', function () {
            return $this->getContainer()->get( GoogleMap::class );
        } );
    }
}
