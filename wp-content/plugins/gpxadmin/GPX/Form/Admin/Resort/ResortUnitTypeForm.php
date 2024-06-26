<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class ResortUnitTypeForm extends BaseForm {

    public function filters(): array {
        return [
            'resort_id' => FILTER_VALIDATE_INT,
            'name' => FILTER_DEFAULT,
            'number_of_bedrooms' => FILTER_DEFAULT,
       //     'bedrooms_override' => FILTER_DEFAULT,
            'sleeps_total' => FILTER_VALIDATE_INT,
        ];
    }

    public function rules(): array {
        return [
            'resort_id' => ['required','integer', Rule::exists('wp_resorts', 'id')],
            'name' => 'required|string|max:128',
            'number_of_bedrooms' => ['required', Rule::in(['STD', 1, 2, 3])],
       //     'bedrooms_override' => ['nullable', Rule::in(['STD', 1, 2, 3])],
            'sleeps_total' => 'required|integer|min:2|max:12',
        ];
    }
}
