<?php

namespace GPX\Model;

use stdClass;
use Illuminate\Support\Str;
use GPX\Repository\ResortRepository;

class ResortMeta {
    private string $resort;
    private stdClass $data;

    public function __construct( string $resort_id, \stdClass $meta = null ) {
        $this->resort = $resort_id;
        $this->data = $meta ?? new \stdClass();
    }

    public static function load( string $resort_id, array $fields = [], bool $booking = false ): ResortMeta {
        return new ResortMeta( $resort_id, ResortRepository::instance()->get_resort_meta( $resort_id, $fields, $booking ) );
    }

    public function __get( $name ) {
        if ( $name === 'resort' ) {
            return $this->resort;
        }
        $method = 'get' . Str::studly( $name );
        if ( method_exists( $this, $method ) ) {
            return $this->$method();
        }

        return $this->data->$name ?? null;
    }

    public function __set( $name, $value ) {
        if ( $name === 'resort' ) {
            throw new \InvalidArgumentException( 'Cannot set resort id' );
        }
        $method = 'set' . Str::studly( $name );
        if ( method_exists( $this, $method ) ) {
            return $this->$method( $value );
        }
        $this->data->$name = $value;
    }

    public function __isset( $name ): bool {
        if ( $name === 'resort' ) {
            return true;
        }

        return isset( $this->data->$name );
    }

    public function __unset( $name ) {
        if ( $name === 'resort' ) {
            throw new \InvalidArgumentException( 'Cannot unset resort id' );
        }
        unset( $this->data->$name );
    }

    public function __call( $method, $arguments ) {
        return call_user_func_array( [ $this->data, $method ], $arguments );
    }
}
