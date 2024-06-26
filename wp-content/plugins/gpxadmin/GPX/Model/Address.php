<?php

namespace GPX\Model;

use Illuminate\Support\Arr;

class Address implements \JsonSerializable, \ArrayAccess, Addressable {
    private ?string $name;
    private ?string $address1;
    private ?string $address2;
    private ?string $town;
    private ?string $region;
    private ?string $state;
    private ?string $country;
    private ?string $post_code;
    private ?string $phone;
    private ?string $fax;
    private ?string $email;

    public function __construct( array $address ) {
        $this->name = $address['Name'] ?? null;
        $this->address1 = $address['Address1'] ?? null;
        $this->address2 = $address['Address2'] ?? null;
        $this->town = $address['Town'] ?? null;
        $this->region = $address['Region'] ?? null;
        $this->state = $address['State'] ?? null;
        $this->country = $address['Country'] ?? null;
        $this->post_code = $address['PostCode'] ?? null;
        $this->phone = $address['Phone'] ?? null;
        $this->fax = $address['Fax'] ?? null;
        $this->email = $address['Email'] ?? null;
    }

    public static function create( array $address ): static {
        return new static( $address );
    }

    public function __get( string $name ) {
        return $this->offsetGet( $name );
    }

    public function isEmpty( array $fields = [] ): bool {
        $data = $fields ? $this->only( $fields ) : $this->toArray();

        return count( array_filter( $data ) ) === 0;
    }

    public function only( array $fields = [] ): array {
        if(empty($fields)) return $this->toArray();
        return Arr::only( $this->toArray(), $fields );
    }

    public function toArray(): array {
        return [
            'Name' => $this->name,
            'Address1' => $this->address1,
            'Address2' => $this->address2,
            'Town' => $this->town,
            'Region' => $this->region,
            'State' => $this->state,
            'Country' => $this->country,
            'PostCode' => $this->post_code,
            'Phone' => $this->phone,
            'Fax' => $this->fax,
            'Email' => $this->email,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function offsetExists( mixed $offset ): bool {
        return property_exists( $this, $offset );
    }

    public function offsetGet( mixed $offset ): ?string {
        if ( ! property_exists( $this, $offset ) ) {
            throw new \InvalidArgumentException( "Property $offset does not exist" );
        }

        return $this->$offset;
    }

    public function offsetSet( mixed $offset, mixed $value ): void {
        throw new \InvalidArgumentException( 'Cannot set values on Address object' );
    }

    public function offsetUnset( mixed $offset ): void {
        throw new \InvalidArgumentException( 'Cannot set values on Address object' );
    }

    public function toAddress(): Address {
        return $this;
    }
}
