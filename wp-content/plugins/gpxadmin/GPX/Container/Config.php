<?php

namespace GPX\Container;

use Illuminate\Contracts\Config\Repository as ContainerContract;

class Config implements ContainerContract {

    public function has( $key ) {
        return false;
    }

    public function get( $key, $default = null ) {
        return $default;
    }

    public function all() {
        return [];
    }

    public function set( $key, $value = null ) {
        // TODO: Implement set() method.
    }

    public function prepend( $key, $value ) {
        // TODO: Implement prepend() method.
    }

    public function push( $key, $value ) {
        // TODO: Implement push() method.
    }
}
