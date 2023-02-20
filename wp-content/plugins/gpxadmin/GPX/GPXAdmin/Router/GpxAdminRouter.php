<?php

namespace GPX\GPXAdmin\Router;

use Spatie\Url\Url;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use GPX\Exception\NoMatchingRouteException;
use Illuminate\Contracts\Container\Container;

class GpxAdminRouter {

    protected Collection $routes;

    public function __construct( protected Container $container ) {
        $this->routes = new Collection();
    }

    public function add( string $page, $callback, array $params = [], string $method = null ): self {
        $this->routes->add( AdminRoute::create( $page, $callback, $params, $method ) );

        return $this;
    }

    public function url( string $page, array $params = [] ): string {
        $url = Url::fromString( admin_url( 'admin.php' ) );

        return $url->withQueryParameters( array_merge( $params, [
            'page'   => 'gpx-admin-page',
            'gpx-pg' => $page,
        ] ) )->__toString();
    }

    public function dispatch( Request $request = null ): void {
        $request = $request ?? gpx_request();
        $route   = $this->match( $request );
        if ( ! $route ) {
            throw ( new NoMatchingRouteException() )->setRequest( $request );
        }
        $args     = Arr::only( $request->input(), $route->params );
        $callable = $route->callable;
        if ( is_string( $callable ) && Str::match( '/[^@]+@[^@]+/i', $callable ) ) {
            $callable = explode( '@', $callable, 1 );
        }
        if ( is_array( $callable ) && count( $callable ) === 2 ) {
            $class    = $this->container->make( $callable[0] );
            $callable = [ $class, $callable[1] ];
        }
        $response = $this->container->call( $callable, $args );
        if ( $response instanceof \Symfony\Component\HttpFoundation\Response ) {
            gpx_send_response( $response );
        }
        if ( is_string( $response ) || is_numeric( $response ) ) {
            gpx_send_response( gpx_response( $response ) );
        }
        if ( is_array( $response ) ) {
            wp_send_json( $response );
        }
    }

    public function match( Request $request ): ?AdminRoute {
        if ( $request->query->get( 'page' ) !== 'gpx-admin-page' ) {
            return null;
        }
        if ( ! $request->query->has( 'gpx-pg' ) ) {
            return null;
        }

        return $this->routes->first( function ( AdminRoute $route ) use ( $request ) {
            return $route->isPage( $request->query->get( 'gpx-pg' ) ) && $route->methodMatches( $request->getMethod() );
        } );
    }
}
