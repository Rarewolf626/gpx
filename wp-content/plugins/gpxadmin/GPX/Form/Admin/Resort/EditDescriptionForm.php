<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class EditDescriptionForm extends BaseForm
{

    public function default(): array {
        return [
            'booking' => '0',
            'profile' => '0',
            'value' => '',
        ];
    }

    public function rules(): array
    {
        $fields = ['AreaDescription', 'UnitDescription', 'AdditionalInfo', 'Description', 'Website', 'CheckInDays', 'CheckInEarliest', 'CheckInLatest', 'CheckOutEarliest', 'CheckOutLatest', 'Address1', 'Address2', 'Town', 'Region', 'Country', 'PostCode', 'Phone', 'Fax', 'Airport', 'Directions',];

        return [
            'resort' => ['required', Rule::exists('wp_resorts', 'ResortID')],
            'field' => ['required', Rule::in($fields)],
            'value' => ['nullable'],
            'booking' => ['nullable', 'boolean'],
            'profile' => ['nullable', 'boolean'],
        ];
    }
}
