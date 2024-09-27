<?php

namespace GPX\Form\Admin\Transaction;

use GPX\Form\BaseForm;
use GPX\DataObject\Transaction\RefundRequest;

class CancelTransactionForm extends BaseForm {

    public function default(): array {
        return [
            'cancel' => 'both',
            'amount' => 0.00,
            'booking' => false,
            'booking_amount' => 0.00,
            'cpo' => false,
            'cpo_amount' => 0.00,
            'upgrade' => false,
            'upgrade_amount' => 0.00,
            'guest' => false,
            'guest_amount' => 0.00,
            'late' => false,
            'late_amount' => 0.00,
            'third_party' => false,
            'third_party_amount' => 0.00,
            'extension' => false,
            'extension_amount' => 0.00,
            'tax' => false,
            'tax_amount' => 0.00,
        ];
    }

    public function filters(): array {
        return [
            'cancel' => FILTER_DEFAULT,
            'amount' => FILTER_VALIDATE_FLOAT,
            'booking' => FILTER_VALIDATE_BOOLEAN,
            'booking_amount' => FILTER_VALIDATE_FLOAT,
            'cpo' => FILTER_VALIDATE_BOOLEAN,
            'cpo_amount' => FILTER_VALIDATE_FLOAT,
            'upgrade' => FILTER_VALIDATE_BOOLEAN,
            'upgrade_amount' => FILTER_VALIDATE_FLOAT,
            'guest' => FILTER_VALIDATE_BOOLEAN,
            'guest_amount' => FILTER_VALIDATE_FLOAT,
            'late' => FILTER_VALIDATE_BOOLEAN,
            'late_amount' => FILTER_VALIDATE_FLOAT,
            'third_party' => FILTER_VALIDATE_BOOLEAN,
            'third_party_amount' => FILTER_VALIDATE_FLOAT,
            'extension' => FILTER_VALIDATE_BOOLEAN,
            'extension_amount' => FILTER_VALIDATE_FLOAT,
            'tax' => FILTER_VALIDATE_BOOLEAN,
            'tax_amount' => FILTER_VALIDATE_FLOAT,
        ];
    }

    public function rules(): array {
        return [
            'cancel' => 'required|in:both,cancel,refund',
            'amount' => 'required|numeric|min:0',
            'booking' => 'required|boolean',
            'booking_amount' => 'required|numeric|min:0',
            'cpo' => 'required|boolean',
            'cpo_amount' => 'required|numeric|min:0',
            'upgrade' => 'required|boolean',
            'upgrade_amount' => 'required|numeric|min:0',
            'guest' => 'required|boolean',
            'guest_amount' => 'required|numeric|min:0',
            'late' => 'required|boolean',
            'late_amount' => 'required|numeric|min:0',
            'third_party' => 'required|boolean',
            'third_party_amount' => 'required|numeric|min:0',
            'extension' => 'required|boolean',
            'extension_amount' => 'required|numeric|min:0',
            'tax' => 'required|boolean',
            'tax_amount' => 'required|numeric|min:0',
        ];
    }

    public function getRefundRequest(array $data = [], bool $send = true): RefundRequest {
        $values = $this->validate($data, $send);
        if ($values['cancel'] === 'cancel') {
            // if set to cancel only then not refunds should be given
            $values['amount'] = 0.00;
            $values['booking'] = false;
            $values['origin'] = 'admin';
            $values['booking_amount'] = 0.00;
            $values['cpo'] = false;
            $values['cpo_amount'] = 0.00;
            $values['upgrade'] = false;
            $values['upgrade_amount'] = 0.00;
            $values['guest'] = false;
            $values['guest_amount'] = 0.00;
            $values['late'] = false;
            $values['late_amount'] = 0.00;
            $values['third_party'] = false;
            $values['third_party_amount'] = 0.00;
            $values['extension'] = false;
            $values['extension_amount'] = 0.00;
            $values['tax'] = false;
            $values['tax_amount'] = 0.00;
        }
        $values['cancel'] = in_array($values['cancel'], ['both', 'cancel']);

        return new RefundRequest($values);
    }
}
