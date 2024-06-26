<?php

namespace GPX\Model\Checkout\Item;

use GPX\Model\Transaction;

class GuestFee extends BaseItem implements CartItem {

    protected int $transaction_id;
    protected ?Transaction $transaction = null;

    public function __construct(int $cid, int|Transaction $transaction) {
        $transaction = $transaction instanceof Transaction ? $transaction : Transaction::find($transaction);
        parent::__construct($cid, $transaction?->weekId);
        $this->setTransaction($transaction);
    }

    public function isGuestFee(): bool {
        return true;
    }

    public function getType(): string {
        return 'guest';
    }

    public function setTransaction(int|Transaction $transaction): static {
        if ($transaction instanceof Transaction) {
            $this->transaction_id = $transaction->id;
            $this->transaction = $transaction;
        } else {
            $this->transaction_id = $transaction;
            $this->transaction = Transaction::where('cid', $this->cid)->find($transaction);
        }

        return $this;
    }

    public function getTransaction(): ?Transaction {
        return $this->transaction;
    }

    public function getTransactionID(): ?int {
        return $this->transaction_id;
    }

    public function getPrice(): float {
        return 0.00;
    }

    public function getSpecialPrice(): float {
        return 0.00;
    }

    protected function calculateTax(): float {
        return 0.00;
    }

    public function getFlexFee(bool $force = false): float {
        return 0.00;
    }
}
