<?php

namespace GPX\Model\Checkout\Item;

use GPX\Model\Interval;
use GPX\Model\Checkout\Deposit;
use GPX\Repository\IntervalRepository;

class DepositWeek extends BaseItem implements CartItem {

    protected int $ownership_id;
    protected ?Interval $ownership = null;
    protected Deposit $deposit;

    public function __construct(int $cid, int|Interval $ownership) {
        $this->deposit = new Deposit();
        parent::__construct($cid, null);
        $this->setOwnership($ownership);
    }

    public function isDeposit(): bool {
        return true;
    }

    public function getType(): string {
        return 'DepositWeek';
    }

    public function setOwnership(int|Interval $ownership): static {
        if ($ownership instanceof Interval) {
            $this->ownership_id = $ownership->id;
            $this->ownership = $ownership;
        } else {
            $this->ownership_id = $ownership;
            $this->ownership = IntervalRepository::instance()->get_member_interval($this->cid, $ownership, true);
        }
        $this->setDeposit();

        return $this;
    }

    public function getOwnership(): ?Interval {
        return $this->ownership;
    }

    public function getOwnershipID(): int {
        return $this->ownership_id;
    }

    public function getDeposit(): Deposit {
        return $this->deposit;
    }

    public function setDeposit(array|Deposit $deposit = []): static {
        $this->deposit = $deposit instanceof Deposit ? $deposit : new Deposit($deposit);
        $this->calculateTotals();

        return $this;
    }

    public function calculateLateFee(string|\DateTimeInterface $checkin = null): float {
        $checkin = $checkin ?? $this->deposit->checkin ?? null;

        return gpx_calculate_late_fee($checkin);
    }

    public function getLateFee(): float {
        return $this->deposit->fee ? $this->late_fee : 0.00;
    }

    public function getThirdPartyDepositFee(): float {
        if (!$this->ownership?->third_party_deposit_fee_enabled) {
            return 0.00;
        }
        if ($this->deposit->waive_tp_fee) {
            return 0.00;
        }

        return $this->tp_deposit_fee;
    }

    protected function calculateTax(): float {
        return 0.00;
    }
}
