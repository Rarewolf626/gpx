<?php

namespace GPX\ServiceProvider;

use GPX\GPXAdmin\Router\GpxAdminRouter;
use Illuminate\Contracts\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;

class AdminRouterServiceProvider extends AbstractServiceProvider {
    public function provides( string $id ): bool {
        return in_array( $id, [
            GpxAdminRouter::class,
        ] );
    }

    public function register(): void {
        $this->getContainer()->addShared(GpxAdminRouter::class, function () {
            $router = new GpxAdminRouter($this->container->get(Container::class));
            require_once GPXADMIN_DIR . 'routes/gpxadmin.php';
            return $router;
        });
    }
}
