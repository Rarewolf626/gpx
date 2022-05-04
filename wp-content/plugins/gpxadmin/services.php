<?php

use League\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @param ?string $key
 * @param array $args
 *
 * @return Container|array|mixed|object
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function gpx( string $key = null, array $args = [] ) {
    static $container;
    if ( ! $container ) {
        $container = new League\Container\Container();
        $container->delegate(
            new League\Container\ReflectionContainer()
        );
        // Add any service providers here
        $container->addServiceProvider( new GPX\ServiceProvider\HttpServiceProvider() );
    }
    if ( null === $key ) {
        return $container;
    }

    return $container->get( $key, $args );
}

/**
 * @param ?string $key
 * @param mixed   $default
 *
 * @return mixed|Request
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function gpx_request( $key = null, $default = null ) {
    /** @var Request $request */
    $request = gpx( Request::class );
    if ( is_null( $key ) ) {
        return $request;
    }

    return $request->get( $key, $default );
}

function gpx_response( ?string $content = '', int $status = 200, array $headers = [], bool $send = true ) {
    $response = new Response( $content, $status, $headers );
    if ( $send ) {
        gpx_send_response( $response );
    }

    return $response;
}

function gpx_redirect(string $url, int $status = 302, array $headers = [])
{
    return gpx_send_response(new RedirectResponse($url, $status, $headers));
}

function gpx_send_response( Response $response, bool $exit = true ) {
    $response->prepare( gpx_request() );
    $response->send();
    if ( $exit ) {
        exit;
    }

    return $response;
}
