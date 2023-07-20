<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class AddResortForm extends BaseForm
{
    public function rules(): array
    {
        return [
            'ResortID' => ['required', 'max:255', Rule::unique('wp_resorts', 'ResortID')],
            'ResortName' => ['required', 'max:255'],
            'Website' => ['nullable', 'max:255'],
            'Address1' => ['nullable'],
            'Address2' => ['nullable'],
            'Town' => ['nullable'],
            'Region' => ['nullable'],
            'PostCode' => ['nullable'],
            'Country' => ['nullable'],
            'Phone' => ['nullable'],
            'Fax' => ['nullable'],
            'Email' => ['nullable', 'email'],
            'CheckInDays' => ['nullable'],
            'CheckInEarliest' => ['nullable'],
            'CheckOutLatest' => ['nullable'],
            'Airport' => ['nullable'],
            'Directions' => ['nullable'],
            'Description' => ['nullable'],
            'AdditionalInfo' => ['nullable'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ResortID' => 'resort id',
            //'ResortName' => 'Resort Name',
        ];
    }
}
