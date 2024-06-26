<?php

namespace GPX\Form\Profile\Transaction;

use GPX\Form\BaseForm;

class UpdateGuestForm extends BaseForm {
    public function filters(): array {
        return [
            'first_name' => FILTER_DEFAULT,
            'last_name' => FILTER_DEFAULT,
            'email' => FILTER_DEFAULT,
            'phone' => FILTER_DEFAULT,
            'adults' => FILTER_DEFAULT,
            'children' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'phone' => ['required'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['required', 'integer', 'min:0'],
        ];
    }
}
