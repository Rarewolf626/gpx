<?php

namespace GPX\Repository;

use GPX\Model\Cart;

class CartRepository
{
    public static function instance(): CartRepository{
        return gpx(CartRepository::class);
    }

    public function findByCartId( string $cart_id ) {
        return Cart::where('cartID', '=', $cart_id)->first();
    }

}
