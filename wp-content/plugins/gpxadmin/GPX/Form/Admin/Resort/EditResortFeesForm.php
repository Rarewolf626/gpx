<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;

class EditResortFeesForm extends BaseForm
{
    public function rules(): array
    {
        return [
            'resort' => ['required', 'integer', Rule::exists('wp_resorts', 'id')],
            'key' => ['nullable'],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['nullable', 'date_format:Y-m-d', 'after:start'],
            'resortFee' => ['nullable', 'numeric'],
            'resortFees' => ['nullable', 'array'],
            'resortFees.*' => ['required', 'numeric', 'min:0'],
            'ExchangeFeeAmount' => ['nullable', 'numeric', 'min:0'],
            'RentalFeeAmount' => ['nullable', 'numeric', 'min:0'],
            'CPOFeeAmount' => ['nullable', 'numeric', 'min:0'],
            'GuestFeeAmount' => ['nullable', 'numeric', 'min:0'],
            'UpgradeFeeAmount' => ['nullable', 'numeric', 'min:0'],
            'SameResortExchangeFee' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'start' => 'Start Date',
            'end' => 'End Date',
            'resortFee' => 'Resort Fee',
            'resortFees' => 'Resort Fee',
            'ExchangeFeeAmount' => 'Exchange Fee',
            'RentalFeeAmount' => 'Rental Fee',
            'CPOFeeAmount' => 'CPO Fee',
            'GuestFeeAmount' => 'Guest Fee',
            'UpgradeFeeAmount' => 'Upgrade Fee',
            'SameResortExchangeFee' => 'Same Resort Exchange Fee',
        ];
    }
}
