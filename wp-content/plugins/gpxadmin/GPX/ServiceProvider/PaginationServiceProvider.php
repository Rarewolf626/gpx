<?php

namespace GPX\ServiceProvider;

use Illuminate\View\FileViewFinder;
use Illuminate\Pagination\Paginator;
use Illuminate\Filesystem\Filesystem;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class PaginationServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

    public function provides(string $id): bool {
        return false;
    }

    public function register(): void {
        // TODO: Implement register() method.
    }

    public function boot(): void {
        Paginator::queryStringResolver(fn () => gpx_request()->query());
        Paginator::currentPathResolver(fn() => gpx_request()->fullUrlWithoutQuery('paged'));
        Paginator::currentPageResolver(fn() => (int)gpx_request('paged', 1));
        Paginator::viewFactoryResolver(fn() => $this->getContainer()->get('view'));
        Paginator::useBootstrapThree();

//        $finder = $this->getContainer()->get('view');
//        dd($finder);
//
//        $finder->addNamespace('pagination', ABSPATH . 'vendor/illuminate/pagination/resources/views');
    }
}
