<?php

namespace GPX\ServiceProvider;

use Illuminate\Http\Request;
use League\Container\ServiceProvider\AbstractServiceProvider;

class HttpServiceProvider extends AbstractServiceProvider {

    public function provides( string $id ): bool {
        return in_array($id, [
            'request',
            Request::class,
            'Symfony\Component\HttpFoundation\Request',
        ]);
    }

    public function register(): void {

        $this->getContainer()->addShared(
            Request::class,
            function () {
//                $trusted = ['*'];
//                Request::setTrustedProxies(
//                    $trusted,
//                    Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO
//                );

                return Request::capture();
            }
        );
        $this->getContainer()->add('request', fn() => $this->getContainer()->get(Request::class));
        $this->getContainer()->add('Symfony\Component\HttpFoundation\Request', fn() => $this->getContainer()->get(Request::class));
    }
}
