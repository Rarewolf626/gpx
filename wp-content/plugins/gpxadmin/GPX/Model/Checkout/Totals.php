<?php

namespace GPX\Model\Checkout;

/**
 * @property-read float $price
 * @property-read float $special
 * @property-read float $late
 * @property-read float $third_party
 * @property-read float $flex
 * @property-read float $upgrade
 * @property-read float $guest
 * @property-read float $extension
 * @property-read float $discount
 * @property-read float $credit
 * @property-read float $occredit
 * @property-read float $coupon
 * @property-read float $tax
 * @property-read float $total
 */
class Totals implements \JsonSerializable, \ArrayAccess {
    private array $totals = [
        'price' => 0.00,
        'special' => 0.00,
        'late' => 0.00,
        'third_party' => 0.00,
        'flex' => 0.00,
        'upgrade' => 0.00,
        'guest' => 0.00,
        'extension' => 0.00,
        'discount' => 0.00,
        'credit' => 0.00,
        'occredit' => 0.00,
        'coupon' => 0.00,
        'tax' => 0.00,
        'total' => 0.00,
    ];

    public function __construct( array $totals = [] ) {
        $this->totals = array_replace( $this->totals, array_intersect_key( array_map( 'floatval', $totals ), $this->totals ) );
    }

    public function __get( string $name ) {
        return $this->offsetGet( $name );
    }

    public function toArray(): array {
        return $this->totals;
    }

    public function offsetExists( mixed $offset ): bool {
        return isset( $this->totals[ $offset ] );
    }

    public function offsetGet( mixed $offset ): float {
        if ( ! array_key_exists( $offset, $this->totals ) ) {
            throw new \InvalidArgumentException( 'Invalid total' );
        }

        return $this->totals[ $offset ];
    }

    public function offsetSet( mixed $offset, mixed $value ): void {
        throw new \InvalidArgumentException( 'Totals are read-only' );
    }

    public function offsetUnset( mixed $offset ): void {
        throw new \InvalidArgumentException( 'Totals are read-only' );
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
