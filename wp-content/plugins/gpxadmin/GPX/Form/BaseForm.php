<?php

namespace GPX\Form;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-consistent-constructor
 */
abstract class BaseForm {
    protected Request $request;
    protected Validator $validator;
    protected bool $filter_create_missing = true;

    public function __construct( Validator $validator, Request $request ) {
        $this->validator = $validator;
        $this->request = $request;
    }

    public static function instance( Request $request = null ) {
        $request = $request ?? gpx_request();
        $validator = gpx( Validator::class );

        return new static( $validator, $request );
    }

    public function request(): Request {
        return $this->request;
    }

    public function default(): array {
        return [];
    }

    public function rules(): array {
        return [];
    }

    public function messages(): array {
        return [];
    }

    public function attributes(): array {
        return [];
    }

    public function filters(): array {
        return [];
    }

    public function data( array $data = null, bool $clean = true ): array {
        if ( $data === null ) {
            $data = $this->request->input();
        }
        if ( ! $clean ) return $data;

        return $this->cleanStrings( $data );
    }

    public function validator( array $data = null ) {
        return $this->validator->make( $this->data( $data ), $this->rules(), $this->messages(), $this->attributes() );
    }

    public function validate( array $data = null, bool $send = true ) {
        $validator = $this->validator( $data );
        try {
            $validated = $validator->validate();

            return $this->filter( $validated );
        } catch ( ValidationException $e ) {
            if ( $send ) {
                wp_send_json(
                    [
                        'success' => false,
                        'message' => 'Submitted data was invalid.',
                        'errors' => $e->errors(),
                    ]
                    , 422
                );
            }
            throw $e;
        }
    }

    public function filter( array $data = null ): array {
        $data = array_replace( $this->default(), $this->data( $data ) );
        $filters = $this->filters();
        if ( ! $filters ) {
            return $data;
        }
        foreach ( $filters as $field => $filter ) {
            $value = Arr::get( $data, $field );

            if ( is_array( $filter ) && array_key_exists( 'filter', $filter ) ) {
                $value = filter_var( $value, $filter['filter'], Arr::except( $filter, 'filter' ) );
            } elseif ( is_callable( $filter ) ) {
                $value = filter_var( $value, FILTER_CALLBACK, [ 'options' => $filter ] );
            } elseif ( $filter ) {
                $value = filter_var( $value, $filter );
            }
            if ( is_string( $value ) && $value === '' ) {
                $value = null;
            }
            Arr::set( $data, $field, $value );
        }

        return $data;
    }

    public function cleanStrings( array $data ): array {
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $data[ $key ] = $this->cleanStrings( $value );
            } elseif ( is_string( $value ) ) {
                $data[ $key ] = trim( $value );
                if ( $data[ $key ] === '' ) $data[ $key ] = null;
            }
        }

        return $data;
    }
}
