<?php

namespace GPX\Form\Admin\Resort;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use GPX\Model\ValueObject\Admin\Resort\ResortSearch;

class SearchResortsForm extends BaseForm {

    public function default(): array {
        return [
            'pg' => 1,
            'limit' => 20,
            'sort' => ResortSearch::$default_sort,
            'dir' => ResortSearch::$default_dir,
            'id' => null,
            'resort' => null,
            'city' => null,
            'region' => null,
            'country' => null,
            'ai' => null,
            'trip_advisor' => null,
            'active' => null,
        ];
    }

    public function filters(): array {
        return [
            'pg' => FILTER_DEFAULT,
            'limit' => FILTER_DEFAULT,
            'sort' => FILTER_DEFAULT,
            'dir' => FILTER_DEFAULT,
            'id' => FILTER_DEFAULT,
            'resort' => FILTER_DEFAULT,
            'city' => FILTER_DEFAULT,
            'region' => FILTER_DEFAULT,
            'country' => FILTER_DEFAULT,
            'ai' => FILTER_DEFAULT,
            'trip_advisor' => FILTER_DEFAULT,
            'active' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'pg' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
            'sort' => ['nullable', Rule::in(ResortSearch::$sortable)],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'id' => ['nullable'],
            'resort' => ['nullable'],
            'city' => ['nullable'],
            'region' => ['nullable'],
            'country' => ['nullable'],
            'ai' => ['nullable', Rule::in(['yes', 'no'])],
            'trip_advisor' => ['nullable'],
            'active' => ['nullable', Rule::in(['yes', 'no'])],
        ];
    }

    public function attributes(): array {
        return [
            'id' => 'resort id',
            'ai' => 'AI',
            'trip_advisor' => 'TripAdvisor ID',
        ];
    }

    public function search(): ResortSearch {
        try {
            $values = $this->validate(null, false);
        } catch (ValidationException $e) {
            $values = $this->default();
        }

        return new ResortSearch($values);
    }
}
