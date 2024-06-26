<?php

namespace GPX\Form\Admin\Region;

use GPX\Form\BaseForm;

class RegionForm extends BaseForm {
    public function filters(): array {
        return [
            'id' => FILTER_VALIDATE_INT,
            'name' => FILTER_DEFAULT,
            'displayName' => FILTER_DEFAULT,
            'CountryID' => FILTER_VALIDATE_INT,
            'parent' => FILTER_VALIDATE_INT,
            'featured' => FILTER_VALIDATE_BOOLEAN,
            'ddHidden' => FILTER_VALIDATE_BOOLEAN,
            'show_resort_fees' => FILTER_VALIDATE_BOOLEAN,
        ];
    }

    public function rules(): array {
        return [
            'id' => ['required', 'integer', 'exists:wp_gpxRegion,id'],
            'name' => ['required', 'string', 'max:255'],
            'displayName' => ['nullable', 'string', 'max:255'],
            'CountryID' => ['required', 'integer', 'exists:wp_gpxCategory,CountryID'],
            'parent' => ['required', 'integer', 'different:id', 'exists:wp_gpxRegion,id'],
            'featured' => ['required', 'boolean'],
            'ddHidden' => ['required', 'boolean'],
            'show_resort_fees' => ['required', 'boolean'],
        ];
    }
}
