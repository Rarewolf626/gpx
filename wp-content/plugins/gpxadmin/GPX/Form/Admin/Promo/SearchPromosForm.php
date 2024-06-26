<?php

namespace GPX\Form\Admin\Promo;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use GPX\Model\ValueObject\Admin\Promo\PromoSearch;

class SearchPromosForm extends BaseForm {

    public function default(): array {
        return [
            'pg' => 1,
            'limit' => 20,
            'sort' => PromoSearch::$default_sort,
            'dir' => PromoSearch::$default_dir,
            'id' => null,
            'pname' => null,
            'slug' => null,
            'type' => null,
            'availability' => null,
            'travel' => null,
            'active' => 'yes',
            'coupon' => null,
        ];
    }

    public function filters(): array {
        return [
            'pg' => FILTER_DEFAULT,
            'limit' => FILTER_DEFAULT,
            'sort' => FILTER_DEFAULT,
            'dir' => FILTER_DEFAULT,
            'id' => FILTER_DEFAULT,
            'pname' => FILTER_DEFAULT,
            'slug' => FILTER_DEFAULT,
            'type' => FILTER_DEFAULT,
            'availability' => FILTER_DEFAULT,
            'travel' => FILTER_DEFAULT,
            'coupon' => FILTER_DEFAULT,
            'active' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'pg' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
            'sort' => ['nullable', Rule::in(PromoSearch::$sortable)],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'id' => ['nullable'],
            'type' => ['nullable', Rule::in(['coupon', 'promo'])],
            'pname' => ['nullable'],
            'slug' => ['nullable'],
            'travel' => ['nullable', 'date'],
            'availability' => ['nullable', Rule::in(['landing', 'site'])],
            'coupon' => ['nullable'],
            'active' => ['nullable', Rule::in(['yes', 'no'])],
        ];
    }

    public function attributes(): array {
        return [
            'travel' => 'Travel Dates',
            'coupon' => 'Redeemed Coupon',
            'pname' => 'Name',
        ];
    }

    public function search(): PromoSearch {
        try {
            $values = $this->validate(null, false);
        } catch (ValidationException $e) {
            $values = $this->default();
        }

        return new PromoSearch($values);
    }
}
