<?php

namespace GPX\ServiceProvider;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use League\Container\ServiceProvider\AbstractServiceProvider;

class HttpServiceProvider extends AbstractServiceProvider
{

    public function provides(string $id): bool
    {
        return in_array($id, [
            'request',
            Request::class,
            SymfonyRequest::class,
        ]);
    }

    public function register(): void
    {

        $this->getContainer()->addShared(
            Request::class,
            fn() => Request::capture()
        );
        $this->getContainer()->add('request', fn() => $this->getContainer()->get(Request::class));
        $this->getContainer()->add('Symfony\Component\HttpFoundation\Request', fn() => $this->getContainer()->get(Request::class));
    }
}
