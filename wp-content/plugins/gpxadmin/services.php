<?php

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use League\Container\Container;
use GPX\Container\LaravelContainer;
use Illuminate\Http\RedirectResponse;

/**
 * @param ?string $key
 * @param array   $args
 *
 * @return Container|array|mixed|object
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function gpx( string $key = null, array $args = [] ) {
    static $container;
    if ( ! $container ) {
        global $wpdb;
        $container         = new League\Container\Container();
        $laravel_container = new LaravelContainer( $container );
        $container->delegate(
            new League\Container\ReflectionContainer()
        );
        $container->add( 'League\Container\Container', $container );
        $container->add( 'Psr\Container\ContainerInterface', $container );
        $container->add( 'Illuminate\Container\Container', $laravel_container );
        $container->add( 'Illuminate\Contracts\Container\Container', $laravel_container );
        $container->add( wpdb::class, $wpdb );

        // Add any service providers here
        $container->addServiceProvider( new GPX\ServiceProvider\HttpServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\EventServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\DatabaseServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\ValidationServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\TranslationServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\LoggerServiceProvider() );
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

function gpx_redirect( string $url, int $status = 302, array $headers = [] ) {
    return gpx_send_response( new RedirectResponse( $url, $status, $headers ) );
}

function gpx_send_response( BaseResponse $response, bool $exit = true ) {
    $response->prepare( gpx_request() );
    $response->send();
    if ( $exit ) {
        exit;
    }

    return $response;
}

function gpx_db(): Connection {
    return gpx( Connection::class );
}

/**
 * @param string|object $event
 * @param mixed         $payload
 * @param bool          $halt
 *
 * @return array|\Illuminate\Events\Dispatcher|null
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function gpx_event( $event = null, $payload = [], bool $halt = false ) {
    /** @var Illuminate\Events\Dispatcher $dispatcher */
    $dispatcher = gpx( 'Illuminate\Events\Dispatcher' );
    if ( null === $event ) {
        return $dispatcher;
    }

    return $dispatcher->dispatch( $event, $payload, $halt );
}

function gpx_logger(string $logger = 'logger'): LoggerInterface {
    if($logger == 'output') return gpx( 'logger.output' );
    return gpx( 'logger' );
}

/**
 * @return Illuminate\Contracts\Validation\Factory
 */
function gpx_validator(): \Illuminate\Contracts\Validation\Factory {
    /** @var Illuminate\Contracts\Validation\Factory $dispatcher */
    return gpx( 'Illuminate\Contracts\Validation\Factory' );
}

function gpx_db_placeholders( array $data = [], string $placeholder = '%s' ): string {
    return implode( ',', array_fill( 0, count( $data ), $placeholder ) );
}

/**
 * escape a string to be used as a mysql table or column name
 *
 * @param string $value
 *
 * @return string
 */
function gpx_esc_table( string $value ): string {
    if ( Str::contains( $value, '.' ) ) {
        // this is table.column or schema.table.column or schema.table
        // each section needs to be escaped separately
        return implode( '.', array_map( 'gpx_esc_table', explode( '.', $value ) ) );
    }

    return '`' . preg_replace( "/[^a-z0-9\\-_\$]/i", '', $value ) . '`';
}

function gpx_esc_orderby( $value ): string {
    if ( ! in_array( mb_strtolower( $value ), [ 'asc', 'desc' ] ) ) {
        return 'ASC';
    }

    return mb_strtoupper( $value );
}

function gpx_esc_like( $value ): string {
    global $wpdb;

    return $wpdb->esc_like( $value );
}
