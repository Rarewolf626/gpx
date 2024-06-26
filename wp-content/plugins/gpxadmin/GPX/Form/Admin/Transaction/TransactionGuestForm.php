<?php

namespace GPX\Form\Admin\Transaction;

use GPX\Form\BaseForm;

class TransactionGuestForm extends BaseForm {
    public function filters(): array {
        return [
            'id' => FILTER_DEFAULT,
            'first_name' => FILTER_DEFAULT,
            'last_name' => FILTER_DEFAULT,
            'email' => FILTER_DEFAULT,
            'phone' => FILTER_DEFAULT,
            'adults' => FILTER_DEFAULT,
            'children' => FILTER_DEFAULT,
            'owner' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'id' => ['nullable', 'integer'],
            'first_name' => ['nullable'],
            'last_name' => ['nullable'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'owner' => ['nullable'],
        ];
    }
}
