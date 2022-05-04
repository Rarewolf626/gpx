<?php

namespace GPX\ServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use League\Container\ServiceProvider\AbstractServiceProvider;

class HttpServiceProvider extends AbstractServiceProvider {

    public function provides( string $id ): bool {
        return in_array($id, [
            'request',
            Request::class
        ]);
    }

    public function register(): void {

        $this->getContainer()->addShared(Request::class, function(){
            return Request::createFromGlobals();
        });
        $this->getContainer()->add('request', Request::class);
    }
}
