<?php
require('../../../../../../wp-load.php');

use GPX\Repository\CartRepository;


$cart = CartRepository::where('cartID','=','83305-47347555')->first();

echo "<pre>";
echo "start";
print_r($cart);
print_r( $cart->data);
echo "end";
echo "</pre>";


