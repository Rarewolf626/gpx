<?php

namespace GPX\Form\Admin\Room;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class EditRoomForm extends BaseForm {

    public function rules(): array {
        $min_price = number_format((float)get_option('gpx_min_rental_fee', 0.00), 2, '.', '');
        return [
            'resort_confirmation_number' => ['nullable', 'string', 'max:30'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date'],
            'resort' => ['required', 'integer', 'exists:wp_resorts,id'],
            'unit_type' => ['required', 'integer', Rule::exists('wp_unit_type', 'record_id')->where('resort_id', $this->request->input('resort'))],
            'source_num' => ['required', 'integer', Rule::in([1,2,3])],
            'source_partner_id' => ['nullable', 'integer', Rule::requiredIf(fn() => in_array($this->request->input('source_num'), [1,3])), Rule::exists('wp_partner', 'user_id')],
            'active' => ['required', 'boolean'],
            'active_type' => ['nullable', 'string', Rule::in(['date','weeks','months'])],
            'active_specific_date' => ['nullable', Rule::requiredIf(fn() => !$this->request->input('active') && $this->request->input('active_type') === 'date'), 'date'],
            'active_week_month' => ['nullable', 'integer', Rule::requiredIf(fn() => !$this->request->input('active') && in_array($this->request->input('active_type'), ['weeks','months'])), 'min:0', 'max:50'],
            'availability' => ['required', 'integer', Rule::in([1,2,3])],
            'available_to_partner_id' => ['nullable', 'integer', Rule::exists('wp_partner', 'user_id')],
            'type' => ['required', 'integer', Rule::in([1,2,3])],
            'price' => ['nullable', 'numeric', Rule::requiredIf(fn() => in_array($this->request->input('type'), [2,3])), "min:$min_price"],
            'active_rental_push_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function filters(): array {
        return [
            'resort'   => FILTER_VALIDATE_INT,
            'unit_type' => FILTER_VALIDATE_INT,
            'source_num'   => FILTER_VALIDATE_INT,
            'source_partner_id'   => FILTER_VALIDATE_INT,
            'active'   => FILTER_VALIDATE_BOOLEAN,
            'active_week_month'   => FILTER_VALIDATE_INT,
            'availability'   => FILTER_VALIDATE_INT,
            'available_to_partner_id'   => FILTER_VALIDATE_INT,
            'type'   => FILTER_VALIDATE_INT,
            'price'   => FILTER_VALIDATE_FLOAT,
        ];
    }

    public function attributes(): array {
        return [
            'source_num' => 'Source',
            'source_partner_id' => 'Source Partner',
            'active_type' => 'Display Date',
            'active_specific_date' => 'Display Date',
            'active_week_month' => 'Display Date',
            'available_to_partner_id' => 'Available To',
            'active_rental_push_date' => 'Rental Available',
        ];
    }
}
