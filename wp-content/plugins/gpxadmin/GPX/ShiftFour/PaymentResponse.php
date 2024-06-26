<?php

namespace GPX\ShiftFour;

use ArrayAccess;
use JsonSerializable;
use Illuminate\Support\Carbon;

class PaymentResponse implements ArrayAccess, JsonSerializable {

    private array $response;
    private int|float $duration;

    public function __construct( array $response = [], int|float $duration = 0 ) {
        $this->response = $response['result'][0] ?? $response;
        $this->duration = abs($duration);
    }

    public function response(): array {
        return $this->response;
    }

    public function duration(): int|float {
        return $this->duration;
    }

    public function isError(): bool {
        return isset( $this->response['error'] ) || empty( $this->response );
    }

    public function __get( string $name ) {
        if ( $name === 'duration' ) return $this->duration();

        return $this->offsetGet( $name );
    }

    public function offsetExists( mixed $offset ): bool {
        return isset( $this->response[ $offset ] );
    }

    public function offsetGet( mixed $offset ) {
        if ( ! isset( $this->response[ $offset ] ) ) return null;
        if ( $offset === 'error' ) return $this->error();
        if ( $offset === 'transaction' ) return $this->transaction();

        return $this->response[ $offset ];
    }

    public function offsetSet( mixed $offset, mixed $value ) {
        throw new \InvalidArgumentException( 'Response is read-only' );
    }

    public function offsetUnset( mixed $offset ) {
        throw new \InvalidArgumentException( 'Response is read-only' );
    }

    public function toArray(): array {
        return $this->response;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function error(): ?PaymentError {
        if ( ! $this->isError() ) return null;

        return new PaymentError( $this->response['error'] );
    }

    public function transaction(): ?Transaction {
        if ( $this->isError() ) return null;

        return new Transaction( $this->response['transaction'] );
    }

    public function date(): ?Carbon {
        $date = $this->offsetGet( 'dateTime' );

        return $date ? Carbon::parse( $date ) : null;
    }
}
