<?php

namespace GPX\Form\Checkout;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;
use GPX\Repository\WeekRepository;

class GuestForm extends BaseForm {

    public function filters(): array {
        return [
            'week' => FILTER_VALIDATE_INT,
            'type' => FILTER_DEFAULT,
            'guest.fee' => FILTER_VALIDATE_BOOLEAN,
            'guest.first_name' => FILTER_DEFAULT,
            'guest.last_name' => FILTER_DEFAULT,
            'guest.email' => FILTER_VALIDATE_EMAIL,
            'guest.phone' => FILTER_DEFAULT,
            'guest.adults' => FILTER_VALIDATE_INT,
            'guest.children' => FILTER_VALIDATE_INT,
            'guest.special_request' => FILTER_DEFAULT,
            'deposit.deposit' => FILTER_VALIDATE_INT,
            'deposit.credit' => FILTER_VALIDATE_INT,
            'deposit.waive_late_fee' => FILTER_VALIDATE_BOOLEAN,
            'deposit.waive_tp_fee' => FILTER_VALIDATE_BOOLEAN,
            'deposit.waive_tp_date' => FILTER_VALIDATE_BOOLEAN,
            'deposit.date' => FILTER_DEFAULT,
            'deposit.reservation' => FILTER_DEFAULT,
            'deposit.unit_type' => FILTER_DEFAULT,
        ];
    }


    public function rules(): array {
        $pid = $this->request->input( 'week' );
        $week = WeekRepository::instance()->get_week_for_checkout( $pid );

        $types = match ( (int) $week?->WeekType ) {
            1 => [ 'ExchangeWeek' ],
            2 => [ 'RentalWeek' ],
            3 => [ 'ExchangeWeek', 'RentalWeek' ],
            default => []
        };

        $is_exchange = $this->request->input( 'type' ) === 'ExchangeWeek';
        $is_deposit = $is_exchange && $this->request->input( 'deposit.type' ) === 'deposit';
        $is_credit = $is_exchange && $this->request->input( 'deposit.type' ) === 'credit';

        return [
            'week' => [ 'required', 'integer', Rule::in( $week->id ) ],
            'type' => [ 'required', Rule::in( $types ) ],
            'guest.fee' => [ 'required', 'boolean' ],
            'guest.first_name' => [ 'required', 'max:255' ],
            'guest.last_name' => [ 'required', 'max:255' ],
            'guest.email' => [ 'required', 'max:255', 'email' ],
            'guest.phone' => [ 'required', 'max:25' ],
            'guest.adults' => [ 'required', 'integer', 'min:1' ],
            'guest.children' => [ 'required', 'integer', 'min:0' ],
            'guest.special_request' => [ 'nullable', 'max:250' ],
            'deposit.type' => [
                'nullable',
                Rule::requiredIf( $is_exchange ),
                Rule::in( [ 'credit', 'deposit' ] ),
            ],
            'deposit.deposit' => [
                'nullable',
                Rule::requiredIf( $is_deposit ),
                'integer',
                Rule::exists( 'wp_owner_interval', 'id' ),
            ],
            'deposit.credit' => [
                'nullable',
                Rule::requiredIf( $is_credit ),
                'integer',
                Rule::exists( 'wp_credit', 'id' ),
            ],
            'deposit.waive_late_fee' => [ 'nullable', 'boolean' ],
            'deposit.waive_tp_fee' => [ 'nullable', 'boolean' ],
            'deposit.waive_tp_date' => [ 'nullable', 'boolean' ],
            'deposit.date' => [
                'nullable',
                Rule::requiredIf( $is_deposit ),
                'date',
                'after:today',
            ],
            'deposit.reservation' => [ 'nullable' ],
            'deposit.unit_type' => [ 'nullable' ],
        ];
    }
}
