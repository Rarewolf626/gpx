<?php

namespace GPX\Model\Checkout;

use Illuminate\Support\Carbon;

/**
 * @property-read ?string $type
 * @property-read ?int $deposit
 * @property-read ?int $credit
 * @property-read ?bool $fee
 * @property-read ?bool $waive_tp_fee
 * @property-read ?string $date
 * @property-read ?string $reservation
 * @property-read ?string $unit_type
 */
class Exchange implements \JsonSerializable, \ArrayAccess {
    private array $exchange = [
        'type' => null,
        'deposit' => null,
        'credit' => null,
        'fee' => null,
        'waive_tp_fee' => false,
        'date' => null,
        'reservation' => null,
        'unit_type' => null,
    ];

    public function __construct(array $exchange = []) {
        $type = $exchange['type'] ?? null;
        if ($type !== 'credit') {
            $exchange['credit'] = null;
        }
        if ($type !== 'deposit') {
            $exchange['deposit'] = null;
        }
        if (array_key_exists('waive_late_fee', $exchange)) {
            $exchange['fee'] = !$exchange['waive_late_fee'];
            unset($exchange['waive_late_fee']);
        }
        $this->exchange = array_replace($this->exchange, array_intersect_key($exchange, $this->exchange));
    }

    public function isDeposit(): bool {
        return $this->exchange['type'] === 'deposit';
    }

    public function isCredit(): bool {
        return $this->exchange['type'] === 'credit';
    }

    public function getDate(): ?Carbon {
        if (null === $this->exchange['date']) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $this->exchange['date'])->startOfDay();
    }

    public function __get(string $name) {
        return $this->offsetGet($name);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->exchange[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        if (!array_key_exists($offset, $this->exchange)) {
            throw new \InvalidArgumentException('Invalid exchange property');
        }

        return $this->exchange[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        throw new \InvalidArgumentException('Exchange is read-only');
    }

    public function offsetUnset(mixed $offset): void {
        throw new \InvalidArgumentException('Exchange is read-only');
    }

    public function toArray(): array {
        return $this->exchange;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
