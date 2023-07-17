<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class EditAlertNoteForm extends BaseForm
{

    public function default(): array
    {
        return [
            'booking' => '0',
            'profile' => '0',
            'value' => '',
            'from' => null,
            'to' => null,
            'oldDates' => null,
        ];
    }

    public function rules(): array
    {
        $fields = ['AlertNote'];

        return [
            'resort' => ['required', Rule::exists('wp_resorts', 'ResortID')],
            'field' => ['required', Rule::in($fields)],
            'value' => ['nullable'],
            'booking' => ['nullable', 'boolean'],
            'profile' => ['nullable', 'boolean'],
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after:from'],
            'oldDates' => ['nullable'],
        ];
    }
}
