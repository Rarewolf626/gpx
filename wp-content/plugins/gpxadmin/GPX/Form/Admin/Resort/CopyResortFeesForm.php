<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class CopyResortFeesForm extends BaseForm
{
    public function rules(): array
    {
        return [
            'resort' => ['required', 'integer', Rule::exists('wp_resorts', 'id')],
            'key' => ['nullable'],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d', 'after:start'],
        ];
    }

    public function attributes(): array
    {
        return [
            'start' => 'Start Date',
            'end' => 'End Date',
        ];
    }
}
