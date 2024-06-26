<?php

namespace GPX\Form\Checkout;

use GPX\Form\BaseForm;

class DepositWeekForm extends BaseForm {
    public function filters(): array {
        return [
            'id' => FILTER_DEFAULT,
            'checkin' => FILTER_DEFAULT,
            'waive_late_fee' => FILTER_VALIDATE_BOOLEAN,
            'waive_tp_fee' => FILTER_VALIDATE_BOOLEAN,
            'waive_tp_date' => FILTER_VALIDATE_BOOLEAN,
            'reservation_number' => FILTER_DEFAULT,
            'unit_type' => FILTER_DEFAULT,
            'coupon' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'id' => [ 'required', 'integer' ],
            'checkin' => [ 'required', 'string', 'date_format:Y-m-d' ],
            'waive_late_fee' => [ 'required', 'boolean' ],
            'waive_tp_fee' => [ 'required', 'boolean' ],
            'waive_tp_date' => [ 'required', 'boolean' ],
            'reservation_number' => [ 'nullable', 'string' ],
            'unit_type' => [ 'nullable', 'string' ],
            'coupon' => [ 'nullable', 'string' ],
        ];
    }
}
