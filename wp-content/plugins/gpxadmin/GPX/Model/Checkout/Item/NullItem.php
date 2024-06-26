<?php

namespace GPX\Model\Checkout\Item;

class NullItem extends BaseItem implements CartItem {

    protected function loadFees(): static {
        $this->rental_fee = 0.00;
        $this->exchange_fee = 0.00;
        $this->cpo_fee = 0.00;
        $this->guest_fee = 0.00;
        $this->upgrade_fee = 0.00;
        $this->price = 0.00;
        $this->promos = collect();
        $this->promo = null;

        return $this;
    }

    protected function calculateTotals(): void {}
}
