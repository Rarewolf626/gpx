<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use GPX\Model\Resort;
use GPX\Rule\ResortDescriptionFieldRule;
use Illuminate\Validation\Rule;

class EditDescriptionForm extends BaseForm
{

    public function default(): array {
        return [
            'value' => '',
        ];
    }

    public function rules(): array
    {
        $fields = Resort::descriptionFields();

        return [
            'resort' => ['required', Rule::exists('wp_resorts', 'ResortID')],
            'field' => ['required', Rule::in($fields->pluck('name'))],
            'value' => ['nullable', new ResortDescriptionFieldRule('field')],
        ];
    }
}
