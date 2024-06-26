<?php

namespace GPX\Model\Checkout;

use Illuminate\Support\Carbon;

/**
 * @property-read ?int $id
 * @property-read ?string $checkin
 * @property-read bool $fee
 * @property-read ?string $reservation_number
 * @property-read ?string $unit_type
 * @property-read ?string $coupon
 * @property-read bool $waive_tp_fee
 */
class Deposit implements \JsonSerializable, \ArrayAccess {
    private array $deposit = [
        'id' => null,
        'checkin' => null,
        'fee' => false,
        'waive_tp_fee' => false,
        'reservation_number' => null,
        'unit_type' => null,
        'coupon' => null,
    ];

    public function __construct(array $deposit = []) {
        if (array_key_exists('waive_late_fee', $deposit)) {
            $deposit['fee'] = !$deposit['waive_late_fee'];
            unset($deposit['waive_late_fee']);
        }
        $this->deposit = array_replace($this->deposit, array_intersect_key($deposit, $this->deposit));
    }

    public function getCheckinDate(): ?Carbon {
        return $this->deposit['checkin'] ? Carbon::parse($this->deposit['checkin']) : null;
    }

    public function __get(string $name) {
        return $this->offsetGet($name);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->deposit[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        if (!array_key_exists($offset, $this->deposit)) {
            throw new \InvalidArgumentException('Invalid deposit property');
        }

        return $this->deposit[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        throw new \InvalidArgumentException('Deposit is read-only');
    }

    public function offsetUnset(mixed $offset): void {
        throw new \InvalidArgumentException('Deposit is read-only');
    }

    public function toArray(): array {
        return $this->deposit;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
