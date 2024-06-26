<?php

namespace GPX\DataObject\Transaction;

use Money\Money;
use GPX\Model\OwnerCreditCoupon;

class RefundResult implements \JsonSerializable{
    public Money $credit;
    public Money $refund;
    public Money $previous;
    public bool $success = true;
    public string $message = '';
    public RefundRequest $requested;
    public ?OwnerCreditCoupon $coupon = null;

    public function __construct() {
        $this->credit = Money::USD(0);
        $this->refund = Money::USD(0);
        $this->previous = Money::USD(0);
        $this->requested = new RefundRequest();
    }

    public function setRequested(RefundRequest $requested): static {
        $this->requested = $requested;
        return $this;
    }

    public function setResult(bool $success = true, string $message = ''): static {
        $this->success = $success;
        $this->message = $message;
        return $this;
    }

    public function total(): float {
        return $this->previous->add($this->refund)->add($this->credit)->getAmount() / 100;
    }

    public function setCoupon(OwnerCreditCoupon $coupon = null): static {
        $this->coupon = $coupon;
        return $this;

    }

    public function hasCardRefund(): bool {
        return $this->refund->isPositive();
    }

    public function card(): float {
        return $this->refund->getAmount() / 100;
    }

    public function hasCreditRefund(): bool {
        return $this->credit->isPositive();
    }

    public function credit(): float {
        return $this->credit->getAmount() / 100;
    }

    public function toArray(): array {
        return [
            'success' => $this->success,
            'credit' => $this->credit(),
            'refund' => $this->card(),
            'coupon' => $this->coupon?->id,
            'message' => $this->message,
            'requested' => $this->requested->toArray(),
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
