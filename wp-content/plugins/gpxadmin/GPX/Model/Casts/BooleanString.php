<?php

namespace GPX\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class BooleanString implements CastsAttributes {

    public function get( $model, string $key, $value, array $attributes ) {
        if($value === null || $value === '') return null;
        if(in_array(mb_strtolower($value), [0, '0', 'f', 'n', 'no', 'false', false], true)) return false;
        if(in_array(mb_strtolower($value), [1, '1', 't', 'y', 'yes', 'true', true], true)) return true;
        return null;
    }

    public function set( $model, string $key, $value, array $attributes ) {
        if($value === null || $value === '') return null;
        if(in_array(mb_strtolower($value), [0, '0', 'f', 'n', 'no', 'false', false], true)) return 'false';
        if(in_array(mb_strtolower($value), [1, '1', 't', 'y', 'yes', 'true', true], true)) return 'true';
        return null;
    }
}
