<?php

namespace GPX\Model\Checkout;

/**
 * @property-read bool $has_guest
 * @property-read int $owner
 * @property-read int $adults
 * @property-read int $children
 * @property-read ?bool $fee
 * @property-read ?string $email
 * @property-read ?string $first_name
 * @property-read ?string $last_name
 * @property-read ?string $phone
 * @property-read ?string $special_request
 */
class Guest implements \JsonSerializable, \ArrayAccess {
    private array $guest = [
        'has_guest' => false,
        'owner' => null,
        'adults' => 1,
        'children' => 0,
        'email' => null,
        'fee' => false,
        'first_name' => null,
        'last_name' => null,
        'phone' => null,
        'special_request' => null,
    ];

    public function __construct(array $guest = [], int $cid = null) {
        $this->guest = array_replace($this->guest, array_intersect_key($guest, $this->guest));
        if (!$this->guest['owner'] && $cid) {
            $this->guest['owner'] = $cid;
        }
    }

    public function __get(string $name) {
        return $this->offsetGet($name);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->guest[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        if (!array_key_exists($offset, $this->guest)) {
            throw new \InvalidArgumentException('Invalid guest property');
        }

        return $this->guest[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        throw new \InvalidArgumentException('Guest is read-only');
    }

    public function offsetUnset(mixed $offset): void {
        throw new \InvalidArgumentException('Guest is read-only');
    }

    public function toArray(): array {
        return $this->guest;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function getName(): string {
        return trim($this->guest['first_name'] . ' ' . $this->guest['last_name']);
    }

    public function hasGuest(): bool {
        return $this->guest['has_guest'] ?? false;
    }
}
