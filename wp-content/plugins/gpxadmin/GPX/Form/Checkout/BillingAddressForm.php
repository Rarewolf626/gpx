<?php

namespace GPX\Form\Checkout;

use GPX\Form\BaseForm;

class BillingAddressForm extends BaseForm {

    public function default(): array {
        return [
            'name' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'zip' => null,
            'country' => null,
            'email' => null,
        ];
    }

    public function filters(): array {
        return [
            'name' => FILTER_DEFAULT,
            'address' => FILTER_DEFAULT,
            'city' => FILTER_DEFAULT,
            'state' => FILTER_DEFAULT,
            'zip' => FILTER_DEFAULT,
            'country' => FILTER_DEFAULT,
            'email' => FILTER_DEFAULT,
        ];
    }

    public function rules(  ): array {
        return [
            'name' => ['required'],
            'address' => ['required'],
            'city' => ['required'],
            'state' => ['required'],
            'zip' => ['required'],
            'country' => ['required'],
            'email' => ['required', 'email'],
        ];
    }
}
