<?php

namespace GPX\Container;

use Illuminate\Container\Container;
use Psr\Container\ContainerInterface;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class LaravelContainer extends Container implements ApplicationContract {
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Class Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct( ContainerInterface $container ) {
        $this->container = $container;
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     *
     * @return mixed
     */
    public function make( $abstract, array $parameters = [] ) {
        if ( ! $this->bound( $abstract ) && !$parameters ) {
            return $this->container->get( $abstract );
        }

        return parent::resolve( $abstract, $parameters );
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     *
     * @return mixed
     */
    public function get( $id ) {
        return $this->make( $id );
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has( $id ): bool {
        if ( $this->bound( $id ) ) {
            return true;
        }

        return $this->container->has( $id );
    }

    /**
     * Determine if the application is in maintenance mode.
     *
     * @return bool
     */
    public function isDownForMaintenance(): bool {
        return false;
    }

    public function version() {
        return GPXADMIN_VERSION;
    }

    public function basePath( $path = '' ) {
        return ABSPATH . $path;
    }

    public function bootstrapPath( $path = '' ) {
        return ABSPATH . $path;
    }

    public function configPath( $path = '' ) {
        return ABSPATH . $path;
    }

    public function databasePath( $path = '' ) {
        return ABSPATH . $path;
    }

    public function resourcePath( $path = '' ) {
        return ABSPATH . $path;
    }

    public function storagePath( $path = '' ) {
        return ABSPATH . $path;
    }

    public function environment( ...$environments ) {
        $value = 'production';
        if ( is_array( $environments ) ) {
            return in_array( $value, $environments );
        }
        if ( is_string( $environments ) ) {
            return $value === $environments;
        }

        return $value;
    }

    public function runningInConsole() {
        return false;
    }

    public function runningUnitTests() {
        return false;
    }

    public function maintenanceMode() {
        return false;
    }

    public function registerConfiguredProviders() {
        // TODO: Implement registerConfiguredProviders() method.
    }

    public function register( $provider, $force = false ) {
        $this->container->addServiceProvider( $provider );
        return $provider;
    }

    public function registerDeferredProvider( $provider, $service = null ) {
        $this->container->addServiceProvider( $provider );
    }

    public function resolveProvider( $provider ) {
        return $provider;
    }

    public function boot() {
        // TODO: Implement boot() method.
    }

    public function booting( $callback ) {
        // TODO: Implement booting() method.
    }

    public function booted( $callback ) {
        // TODO: Implement booted() method.
    }

    public function bootstrapWith( array $bootstrappers ) {
        // TODO: Implement bootstrapWith() method.
    }

    public function getLocale() {
        return 'en-US';
    }

    public function getNamespace() {
        return 'GPX\\';
    }

    public function getProviders( $provider ) {
        return [];
    }

    public function hasBeenBootstrapped() {
        return true;
    }

    public function loadDeferredProviders() {
        // TODO: Implement loadDeferredProviders() method.
    }

    public function setLocale( $locale ) {
        // TODO: Implement setLocale() method.
    }

    public function shouldSkipMiddleware() {
        return false;
    }

    public function terminating( $callback ) {
        return $this;
    }

    public function terminate() {
        // TODO: Implement terminate() method.
    }
}
