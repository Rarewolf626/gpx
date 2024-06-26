<?php

namespace GPX\Model\Checkout\Item;

class RentalWeek extends BaseItem implements CartItem {

    public function getType(  ): ?string {
        return 'RentalWeek';
    }

    public function isRental(  ): bool {
        return true;
    }
}
