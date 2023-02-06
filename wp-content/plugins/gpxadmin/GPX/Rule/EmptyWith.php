<?php

namespace GPX\Rule;

use Countable;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Contracts\Validation\DataAwareRule;

class EmptyWith implements Rule, DataAwareRule {
    protected array $data = [];
    protected array $fields = [];

    /**
     * @param string|string[] $fields
     */
    public function __construct( $fields = [] ) {
        if ( is_string( $fields ) ) {
            $this->fields = explode( ',', $fields );
        } elseif ( is_array( $fields ) ) {
            $this->fields = $fields;
        } else {
            throw new \InvalidArgumentException( 'Fields must be array or string' );
        }
    }

    public function setData( $data ) {
        $this->data = $data;

        return $this;
    }

    public function passes( $attribute, $value ): bool {
        if ( ! $this->fields || $this->isEmpty( $value ) ) {
            return true;
        }

        return collect( Arr::only( $this->data, $this->fields ) )
            ->filter( function ( $value ) {
                return ! $this->isEmpty( $value );
            } )
            ->isEmpty();
    }

    public function message() {
        return sprintf(':attribute must be empty if %s is provided.', implode(', ', $this->fields));
    }

    public function isEmpty( $value ): bool {
        if ( is_null( $value ) ) {
            return true;
        } elseif ( is_string( $value ) && trim( $value ) === '' ) {
            return true;
        } elseif ( ( is_array( $value ) || $value instanceof Countable ) && count( $value ) < 1 ) {
            return true;
        } elseif ( $value instanceof File ) {
            return (string) $value->getPath() === '';
        }

        return false;
    }
}
