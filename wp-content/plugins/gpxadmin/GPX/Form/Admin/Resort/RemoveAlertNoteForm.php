<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class RemoveAlertNoteForm extends BaseForm
{
    public function rules(): array
    {
        return [
            'resort' => ['required', Rule::exists('wp_resorts', 'ResortID')],
            'oldDates' => ['required'],
        ];
    }
}
