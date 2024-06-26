<?php

namespace GPX\Model\Checkout\Item;

use GPX\Model\Week;
use GPX\Model\Credit;
use GPX\Model\Interval;
use GPX\Model\Transaction;
use GPX\Model\Checkout\Guest;
use GPX\Model\Checkout\Totals;
use GPX\Model\Checkout\Deposit;
use GPX\Model\Checkout\Exchange;
use GPX\Model\DepositOnExchange;
use Illuminate\Support\Collection;

interface CartItem {
    public function getWeekId(): ?int;
    public function setWeek( ?int $week_id ): static;
    public function clearWeek(): static;
    public function getWeek(): ?Week;
    public function getType(): ?string;
    public function setGuestInfo( array|Guest $guest ): static;
    public function getGuestInfo(): Guest;
    public function setExchangeInfo( array|Exchange $exchange ): static;
    public function getExchangeInfo(): Exchange;
    public function getInterval(): ?Interval;
    public function getCredit(): ?Credit;
    public function getExchangeCredit(): ?Credit;
    public function getExchangeDeposit(): ?DepositOnExchange;
    public function getDeposit(): ?Deposit;
    public function getOwnership(): ?Interval;
    public function getTransaction(): ?Transaction;
    public function getTransactionID(): ?int;
    public function getExtensionDate(): ?string;
    public function calculateLateFee( string|\DateTimeInterface $checkin = null ): float;
    public function getLateFee(): float;
    public function getUpgradeFee(): float;
    public function calculateUpgradeFee( string $unit_type = null ): float;
    public function getTax(): float;
    public function getExtensionFee(): float;
    public function getDiscount(): float;
    public function getCouponDiscount(): float;
    public function getPrice(): float;
    public function getFlexFee(bool $return_base_fee = false): float;
    public function hasFlex(): bool;
    public function getGuestFee(): float;
    public function getTotal(): float;
    public function getTotals(): Totals;
    public function getPromos(): Collection;
    public function getPromo(): ?string;
    public function isExchange(): bool;
    public function isBooking(): bool;
    public function isRental(): bool;
    public function isExtend(): bool;
    public function isDeposit(): bool;
    public function isGuestFee(): bool;
    public function toArray(): array;
    public function jsonSerialize(): array;
}
