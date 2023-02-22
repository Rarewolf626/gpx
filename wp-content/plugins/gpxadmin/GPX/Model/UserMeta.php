<?php

namespace GPX\Model;

use stdClass;
use Illuminate\Support\Str;

/**
 * @property-read int $id
 * @property ?string  $Mobile
 * @property ?string  $DayPhone
 * @property ?string  $FirstName1
 * @property ?string  $LastName1
 * @property ?int     $DAEMemberNo
 * @property ?string  $GP_Preferred
 */
class UserMeta {
    private int $id;
    private stdClass $data;

    public function __construct( int $id, \stdClass $usermeta = null ) {
        $this->id   = $id;
        $this->data = $usermeta ?? new \stdClass();
    }

    public static function load( int $cid ): UserMeta {
        $data = gpx_get_usermeta( $cid );

        return new static( $cid, $data );
    }

    public function getDayPhone(): string {
        $phone = $this->data->DayPhone;

        return $phone && ! is_object( unserialize($phone) ) ? $phone : '';
    }

    public function getMobile(): string {
        return $this->data->Mobile1 ?? $this->data->Mobile ?? '';
    }

    public function __get( $name ) {
        if ( $name === 'id' ) {
            return $this->id;
        }
        $method = 'get' . Str::studly( $name );
        if ( method_exists( $this, $method ) ) {
            return $this->$method();
        }

        return $this->data->$name;
    }

    public function __set( $name, $value ) {
        if ( $name === 'id' ) {
            throw new \InvalidArgumentException( 'Cannot set user id' );
        }
        $method = 'set' . Str::studly( $name );
        if ( method_exists( $this, $method ) ) {
            return $this->$method( $value );
        }
        $this->data->$name = $value;
    }

    public function __isset( $name ): bool {
        if ( $name === 'id' ) {
            return true;
        }

        return isset( $this->data->$name );
    }

    public function __unset( $name ) {
        if ( $name === 'id' ) {
            throw new \InvalidArgumentException( 'Cannot unset user id' );
        }
        unset( $this->data->$name );
    }

    public function __call( $method, $arguments ) {
        return call_user_func_array( [ $this->data, $method ], $arguments );
    }
}
