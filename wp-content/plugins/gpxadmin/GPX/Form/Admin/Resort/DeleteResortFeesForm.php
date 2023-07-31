<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class DeleteResortFeesForm extends BaseForm
{
    public function rules(): array
    {
        return [
            'resort' => ['required', 'integer', Rule::exists('wp_resorts', 'id')],
            'key' => ['nullable'],
        ];
    }
}
