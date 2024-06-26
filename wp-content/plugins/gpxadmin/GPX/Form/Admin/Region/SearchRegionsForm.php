<?php

namespace GPX\Form\Admin\Region;

use GPX\Form\BaseForm;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use GPX\Model\ValueObject\Admin\Region\RegionSearch;

class SearchRegionsForm extends BaseForm {

    public function default(): array {
        return [
            'pg' => 1,
            'limit' => 20,
            'sort' => 'id',
            'dir' => 'desc',
            'gpx' => null,
            'region' => null,
            'display' => null,
            'parent' => null,
        ];
    }

    public function filters(): array {
        return [
            'pg' => FILTER_DEFAULT,
            'limit' => FILTER_DEFAULT,
            'sort' => FILTER_DEFAULT,
            'dir' => FILTER_DEFAULT,
            'gpx' => FILTER_DEFAULT,
            'region' => FILTER_DEFAULT,
            'display' => FILTER_DEFAULT,
            'parent' => FILTER_DEFAULT,
        ];
    }

    public function rules(): array {
        return [
            'pg' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
            'sort' => ['nullable', Rule::in(RegionSearch::$sortable)],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'gpx' => ['nullable', Rule::in(['yes', 'no'])],
            'region' => ['nullable'],
            'display' => ['nullable'],
            'parent' => ['nullable'],
        ];
    }

    public function attributes(): array {
        return [
            'gpx' => 'GPX sub region',
            'region' => 'regionn',
            'display' => 'display name',
            'parent' => 'parent',
        ];
    }

    public function search(): RegionSearch {
        try {
            $values = $this->validate(null, false);
        } catch (ValidationException $e) {
            $values = $this->default();
        }

        return new RegionSearch($values);
    }

}
