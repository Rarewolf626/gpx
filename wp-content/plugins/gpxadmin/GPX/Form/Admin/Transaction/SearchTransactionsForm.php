<?php

namespace GPX\Form\Admin\Transaction;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use GPX\Model\ValueObject\Admin\Transaction\TransactionSearch;

class SearchTransactionsForm extends BaseForm {

    public function default(): array {
        return [
            'pg' => 1,
            'limit' => 20,
            'sort' => 'id',
            'dir' => 'desc',
            'id' => null,
            'type' => null,
            'user' => null,
            'owner' => null,
            'owner_id' => null,
            'parent_id' => null,
            'adults' => null,
            'children' => null,
            'upgrade' => null,
            'cpo' => null,
            'cpo_fee' => null,
            'resort' => null,
            'room' => null,
            'week' => null,
            'deposit' => null,
            'week_type' => null,
            'balance' => null,
            'resort_id' => null,
            'sleeps' => null,
            'bedrooms' => null,
            'nights' => null,
            'checkin' => null,
            'paid' => null,
            'processed' => null,
            'promo' => null,
            'discount' => null,
            'coupon' => null,
            'occoupon' => null,
            'ocdiscount' => null,
            'date' => null,
            'cancelled' => null,
        ];
    }

    public function filters(): array {
        return [
            'pg' => FILTER_DEFAULT,
            'limit' => FILTER_DEFAULT,
            'sort' => FILTER_DEFAULT,
            'dir' => FILTER_DEFAULT,
            'id' => FILTER_DEFAULT,
            'type' => FILTER_DEFAULT,
            'user' => FILTER_DEFAULT,
            'owner' => FILTER_DEFAULT,
            'owner_id' => FILTER_DEFAULT,
            'parent_id' => FILTER_DEFAULT,
            'adults' => FILTER_DEFAULT,
            'children' => FILTER_DEFAULT,
            'upgrade' => FILTER_DEFAULT,
            'cpo' => FILTER_DEFAULT,
            'cpo_fee' => FILTER_DEFAULT,
            'resort' => FILTER_DEFAULT,
            'room' => FILTER_DEFAULT,
            'week_type' => FILTER_DEFAULT,
            'balance' => FILTER_DEFAULT,
            'resort_id' => FILTER_DEFAULT,
            'week' => FILTER_DEFAULT,
            'deposit' => FILTER_DEFAULT,
            'sleeps' => FILTER_DEFAULT,
            'bedrooms' => FILTER_DEFAULT,
            'nights' => FILTER_DEFAULT,
            'checkin' => FILTER_DEFAULT,
            'paid' => FILTER_DEFAULT,
            'processed' => FILTER_DEFAULT,
            'promo' => FILTER_DEFAULT,
            'discount' => FILTER_DEFAULT,
            'coupon' => FILTER_DEFAULT,
            'occoupon' => FILTER_DEFAULT,
            'ocdiscount' => FILTER_DEFAULT,
            'date' => FILTER_DEFAULT,
            'cancelled' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'pg' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
            'sort' => ['nullable', Rule::in(TransactionSearch::$sortable)],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'id' => ['nullable'],
            'type' => ['nullable', Rule::in(['booking', 'credit_donation', 'credit_transfer', 'deposit', 'extension', 'pay_debit', 'guest'])],
            'user' => ['nullable'],
            'owner' => ['nullable'],
            'owner_id' => ['nullable'],
            'parent_id' => ['nullable'],
            'adults' => ['nullable', 'integer', 'min:0'],
            'children' => ['nullable', 'integer', 'min:0'],
            'upgrade' => ['nullable', 'numeric'],
            'cpo' => ['nullable', Rule::in(['taken', 'nottaken', 'na'])],
            'cpo_fee' => ['nullable', 'numeric'],
            'resort' => ['nullable'],
            'room' => ['nullable'],
            'week_type' => ['nullable', Rule::in(['rental', 'exchange'])],
            'balance' => ['nullable', 'numeric'],
            'resort_id' => ['nullable'],
            'sleeps' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable'],
            'nights' => ['nullable'],
            'week' => ['nullable'],
            'deposit' => ['nullable'],
            'checkin' => ['nullable', 'date_format:Y-m-d'],
            'paid' => ['nullable', 'numeric'],
            'processed' => ['nullable'],
            'promo' => ['nullable'],
            'discount' => ['nullable', 'numeric'],
            'coupon' => ['nullable', 'numeric'],
            'occoupon' => ['nullable'],
            'ocdiscount' => ['nullable', 'numeric'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'cancelled' => ['nullable', Rule::in(['yes', 'no'])],
        ];
    }

    public function attributes(): array {
        return [
            'id' => 'transaction id',
            'type' => 'transaction type',
            'user' => 'member number',
            'owner_id' => 'owner',
            'parent_id' => 'related transaction',
            'resort' => 'resort name',
            'room' => 'room type',
            'week' => 'week id',
            'deposit' => 'deposit id',
            'week_type' => 'week type',
            'checkin' => 'checkin date',
            'date' => 'transaction date',
            'paid' => 'amount paid',
            'cancelled' => 'cancelled',
        ];
    }

    public function search(): TransactionSearch {
        try {
            $values = $this->validate(null, false);
        } catch (ValidationException $e) {
            $values = $this->default();
        }

        return new TransactionSearch($values);
    }

}
