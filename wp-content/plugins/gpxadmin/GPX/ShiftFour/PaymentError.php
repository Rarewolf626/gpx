<?php

namespace GPX\ShiftFour;

class PaymentError implements \ArrayAccess, \JsonSerializable {

    public function __construct(private array $error = [] ) {}


    public function offsetExists( mixed $offset ) {
        return isset( $this->error[ $offset ] );
    }

    public function __get( string $name ) {
        return $this->offsetGet( $name );
    }

    public function offsetGet( mixed $offset ) {
        if ( ! isset( $this->error[ $offset ] ) ) return null;

        return $this->error[ $offset ];
    }

    public function offsetSet( mixed $offset, mixed $value ) {
        throw new \InvalidArgumentException( 'Error is read-only' );
    }

    public function offsetUnset( mixed $offset ) {
        throw new \InvalidArgumentException( 'Error is read-only' );
    }

    public function toArray(  ): array {
        return $this->error;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function code(): int {
        return $this->offsetGet( 'primaryCode' );
    }

    public function type(): ?string {
        return $this->offsetGet( 'shortText' );
    }

    public function message(): ?string {
        return $this->offsetGet( 'longText' );
    }

    public function isTimeout(  ): bool {
        return $this->code() === 9961;
    }

    public function isInvoiceNotFound(  ): bool {
        return $this->code() === 9815;
    }
}
