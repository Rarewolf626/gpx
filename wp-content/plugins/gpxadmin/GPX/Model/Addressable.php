<?php

namespace GPX\Model;

interface Addressable {
    public function toAddress(  ): Address;
}
