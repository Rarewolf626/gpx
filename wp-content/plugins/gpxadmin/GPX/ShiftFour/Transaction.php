<?php

namespace GPX\ShiftFour;

use ArrayAccess;
use JsonSerializable;

class Transaction implements ArrayAccess, JsonSerializable {

    public function __construct(private array $data = []) {}

    public function data(  ): array {
        return $this->data;
    }

    public function toArray(  ): array {
        return $this->data();
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function offsetExists( mixed $offset ) {
        return isset( $this->data[ $offset ] );
    }

    public function __get( string $name ) {
        return $this->offsetGet( $name );
    }

    public function offsetGet( mixed $offset ) {
        if ( ! isset( $this->data[ $offset ] ) ) return null;

        return $this->data[ $offset ];
    }

    public function offsetSet( mixed $offset, mixed $value ) {
        throw new \InvalidArgumentException( 'Error is read-only' );
    }

    public function offsetUnset( mixed $offset ) {
        throw new \InvalidArgumentException( 'Error is read-only' );
    }

    public function code(): string {
        return $this->offsetGet( 'responseCode' );
    }

    public function invoice(  ): int {
        (int) ltrim($this->offsetGet( 'invoice' ), '0');
    }

    public function isSuccessful(  ): bool {
        return in_array($this->code(), ['0', '105', '106', 'A', 'a']);
    }
}
