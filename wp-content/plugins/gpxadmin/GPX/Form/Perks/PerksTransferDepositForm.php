<?php

namespace GPX\Form\Perks;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class PerksTransferDepositForm extends BaseForm {

    public function filters(): array {
        return [
            'type' => FILTER_DEFAULT,
            'deposit' => FILTER_DEFAULT,
            'credit' => FILTER_DEFAULT,
            'checkin' => FILTER_DEFAULT,
            'reservation' => FILTER_DEFAULT,
            'unit_type' => FILTER_DEFAULT,
            'coupon' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        $cid = gpx_get_switch_user_cookie();

        return [
            'type' => ['required', 'in:deposit,credit'],
            'deposit' => ['nullable', 'required_if:type,deposit', 'numeric', Rule::exists('wp_owner_interval', 'id')->where('userID', $cid)],
            'credit' => ['nullable', 'required_if:type,credit', 'numeric', Rule::exists('wp_credit', 'id')->where('owner_id', $cid)],
            'checkin' => ['nullable', 'required_if:type,deposit', 'date'],
            'reservation' => ['nullable'],
            'unit_type' => ['nullable'],
            'coupon' => ['nullable'],
        ];
    }
}
