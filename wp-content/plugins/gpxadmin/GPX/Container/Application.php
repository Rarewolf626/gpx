<?php

namespace GPX\Container;

use Closure;
use Illuminate\Contracts\Container\Container;

class Application implements \Illuminate\Contracts\Foundation\Application
{
    public function __construct(protected Container $container) {}

    public function version() {
        // TODO: Implement version() method.
    }

    public function basePath( $path = '' ) {
        // TODO: Implement basePath() method.
    }

    public function bootstrapPath( $path = '' ) {
        // TODO: Implement bootstrapPath() method.
    }

    public function configPath( $path = '' ) {
        // TODO: Implement configPath() method.
    }

    public function databasePath( $path = '' ) {
        // TODO: Implement databasePath() method.
    }

    public function resourcePath( $path = '' ) {
        // TODO: Implement resourcePath() method.
    }

    public function storagePath( $path = '' ) {
        // TODO: Implement storagePath() method.
    }

    public function environment( ...$environments ) {
        // TODO: Implement environment() method.
    }

    public function runningInConsole() {
        // TODO: Implement runningInConsole() method.
    }

    public function runningUnitTests() {
        // TODO: Implement runningUnitTests() method.
    }

    public function maintenanceMode() {
        // TODO: Implement maintenanceMode() method.
    }

    public function isDownForMaintenance() {
        // TODO: Implement isDownForMaintenance() method.
    }

    public function registerConfiguredProviders() {
        // TODO: Implement registerConfiguredProviders() method.
    }

    public function register( $provider, $force = false ) {
        // TODO: Implement register() method.
    }

    public function registerDeferredProvider( $provider, $service = null ) {
        // TODO: Implement registerDeferredProvider() method.
    }

    public function resolveProvider( $provider ) {
        // TODO: Implement resolveProvider() method.
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
        // TODO: Implement getLocale() method.
    }

    public function getNamespace() {
        return 'GPX\\';
    }

    public function getProviders( $provider ) {
        // TODO: Implement getProviders() method.
    }

    public function hasBeenBootstrapped() {
        // TODO: Implement hasBeenBootstrapped() method.
    }

    public function loadDeferredProviders() {
        // TODO: Implement loadDeferredProviders() method.
    }

    public function setLocale( $locale ) {
        // TODO: Implement setLocale() method.
    }

    public function shouldSkipMiddleware() {
        // TODO: Implement shouldSkipMiddleware() method.
    }

    public function terminating( $callback ) {
        // TODO: Implement terminating() method.
    }

    public function terminate() {
        // TODO: Implement terminate() method.
    }

    public function bound( $abstract ) {
        // TODO: Implement bound() method.
    }

    public function alias( $abstract, $alias ) {
        // TODO: Implement alias() method.
    }

    public function tag( $abstracts, $tags ) {
        // TODO: Implement tag() method.
    }

    public function tagged( $tag ) {
        // TODO: Implement tagged() method.
    }

    public function bind( $abstract, $concrete = null, $shared = false ) {
        // TODO: Implement bind() method.
    }

    public function bindIf( $abstract, $concrete = null, $shared = false ) {
        // TODO: Implement bindIf() method.
    }

    public function singleton( $abstract, $concrete = null ) {
        // TODO: Implement singleton() method.
    }

    public function singletonIf( $abstract, $concrete = null ) {
        // TODO: Implement singletonIf() method.
    }

    public function scoped( $abstract, $concrete = null ) {
        // TODO: Implement scoped() method.
    }

    public function scopedIf( $abstract, $concrete = null ) {
        // TODO: Implement scopedIf() method.
    }

    public function extend( $abstract, Closure $closure ) {
        // TODO: Implement extend() method.
    }

    public function instance( $abstract, $instance ) {
        // TODO: Implement instance() method.
    }

    public function addContextualBinding( $concrete, $abstract, $implementation ) {
        // TODO: Implement addContextualBinding() method.
    }

    public function when( $concrete ) {
        // TODO: Implement when() method.
    }

    public function factory( $abstract ) {
        // TODO: Implement factory() method.
    }

    public function flush() {
        // TODO: Implement flush() method.
    }

    public function make( $abstract, array $parameters = [] ) {
        // TODO: Implement make() method.
    }

    public function call( $callback, array $parameters = [], $defaultMethod = null ) {
        // TODO: Implement call() method.
    }

    public function resolved( $abstract ) {
        // TODO: Implement resolved() method.
    }

    public function beforeResolving( $abstract, Closure $callback = null ) {
        // TODO: Implement beforeResolving() method.
    }

    public function resolving( $abstract, Closure $callback = null ) {
        // TODO: Implement resolving() method.
    }

    public function afterResolving( $abstract, Closure $callback = null ) {
        // TODO: Implement afterResolving() method.
    }

    public function get( string $id ) {
        // TODO: Implement get() method.
    }

    public function has( string $id ) {
        // TODO: Implement has() method.
    }
}
