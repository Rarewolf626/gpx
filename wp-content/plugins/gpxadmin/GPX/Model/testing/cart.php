<?php
require('../../../../../../wp-load.php');

use GPX\Model\Cart;
use GPX\Repository\CartRepository;

$cart = CartRepository::instance()->findByCartId('83305-47347555');
$cart = Cart::where('cartID','=','83305-47347555')->first();

echo "<pre>";
echo "start";
print_r($cart);
print_r( $cart->data);
echo "end";
echo "</pre>";


