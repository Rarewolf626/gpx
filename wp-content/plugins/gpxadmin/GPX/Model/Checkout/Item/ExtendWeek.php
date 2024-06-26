<?php

namespace GPX\Model\Checkout\Item;

use GPX\Model\Credit;

class ExtendWeek extends BaseItem implements CartItem {
    private ?string $extension_date = null;
    private ?int $credit_id = null;
    private ?Credit $credit = null;

    public function __construct(int $cid, int|Credit $credit) {
        parent::__construct($cid, null);
        $this->setCredit($credit);
    }

    public function isExtend(): bool {
        return true;
    }

    public function getType(): ?string {
        return 'ExtendWeek';
    }

    public function setExtensionDate(?string $date = null): static {
        $this->extension_date = $date;

        return $this;
    }

    public function getExtensionDate(): ?string {
        return $this->extension_date;
    }

    public function setCredit(int|Credit $credit): static {
        if ($credit instanceof Credit) {
            $this->credit_id = $credit->id;
            $this->credit = $credit;
        } else {
            $this->credit_id = $credit;
            $this->credit = Credit::forUser($this->cid)->approved()->hasExpiration()->find($credit);
        }
        $this->extension_date = null;
        $this->calculateTotals();

        return $this;
    }

    public function getCredit(): ?Credit {
        return $this->credit;
    }

    public function getCreditID(): int {
        return $this->credit_id;
    }

    protected function calculateTax(): float {
        return 0.00;
    }

    public function getExtensionFee(): float {
        return $this->extension_fee;
    }
}
