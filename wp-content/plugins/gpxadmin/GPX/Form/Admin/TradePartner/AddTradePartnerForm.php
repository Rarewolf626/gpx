<?php

namespace GPX\Form\Admin\TradePartner;

use GPX\Form\BaseForm;
use GPX\Rule\EmailIsUnique;
use GPX\Rule\AllowedUsername;
use GPX\Rule\UsernameIsUnique;

class AddTradePartnerForm extends BaseForm
{
    public function default(): array {
        return [
            'sf_account_id' => null,
            'name' => null,
            'username' => null,
            'email' => null,
            'phone' => null,
            'address'  => null,
        ];
    }

    public function rules(): array
    {
        return [
            'sf_account_id' => ['required', 'max:18'],
            'name' => ['required', 'max:50'],
            'username' => ['required', 'max:255', new UsernameIsUnique(), new AllowedUsername()],
            'email' => ['required', 'email', 'max:50', new EmailIsUnique()],
            'phone' => ['nullable', 'max:20'],
            'address' => ['nullable', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sf_account_id' => 'Salesforce Account ID',
        ];
    }
}
