<?php

namespace GPX\Form\Checkout;

use GPX\Form\BaseForm;

class PaymentForm extends BaseForm {

    public function filters(): array {
        return [
            'i4go_response' => FILTER_DEFAULT,
            'i4go_responsecode' => FILTER_DEFAULT,
            'i4go_responsetext' => FILTER_DEFAULT,
            'i4go_accessblock' => FILTER_DEFAULT,
            'i4go_streetaddress' => FILTER_DEFAULT,
            'i4go_postalcode' => FILTER_DEFAULT,
            'i4go_cardholdername' => FILTER_DEFAULT,
            'i4go_maskedcard' => FILTER_DEFAULT,
            'i4go_cardtype' => FILTER_DEFAULT,
            'i4go_expirationmonth' => FILTER_DEFAULT,
            'i4go_expirationyear' => FILTER_DEFAULT,
            'i4go_uniqueid' => FILTER_DEFAULT,
            'i4go_utoken' => FILTER_DEFAULT,
            'otn' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'i4go_response' => [ 'required' ],
            'i4go_responsecode' => [ 'required' ],
            'otn' => [ 'required', 'array' ],
            'i4go_uniqueid' => [ 'required' ],
            'i4go_utoken' => [ 'required' ],
        ];
    }
}
