<?php

namespace GPX\Form;

use GPX\Rule\EmptyWith;
use GPX\Rule\RegionNameExists;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use GPX\Rule\SubRegionNameExists;
use GPX\Rule\RequiredIfRegionHasSubregion;

class CustomRequestForm extends BaseForm {

    public function default(): array {
        return [
            'nearby' => false,
            'larger' => false,
            'miles'  => 30,
        ];
    }

    public function rules(): array {
        return [
            'resort'     => [
                'nullable',
                'required_without:region',
                new EmptyWith( 'region' ),
                Rule::exists( 'wp_resorts', 'ResortName' )->where( 'active', 1 ),
            ],
            'nearby'     => [ 'nullable', 'boolean' ],
            'region'     => [
                'nullable',
                'required_without:resort',
                'required_with:city',
                new EmptyWith( 'resort' ),
                new RegionNameExists(),
            ],
            'city'       => [ 'nullable', new SubRegionNameExists( 'region' ) ],
            'adults'     => [ 'required', 'integer', 'min:1' ],
            'children'   => [ 'required', 'integer', 'min:0' ],
            'email'      => [ 'required', 'email' ],
            'roomType'   => [ 'required', Rule::in( [ 'Any', 'Studio', '1BR', '2BR', '3BR' ] ) ],
            'larger'     => [ 'nullable', 'boolean' ],
            'preference' => [ 'nullable', Rule::in( [ 'Any', 'Rental', 'Exchange' ] ) ],
            'checkIn'    => [ 'required', 'date_format:Y-m-d' ],
            'checkIn2'   => [ 'nullable', 'date_format:Y-m-d', 'after_or_equal:checkIn' ],
        ];
    }

    public function messages(): array {
        return [
            'resort.required_without' => 'Please select a resort or region.',
            'resort.exists'           => 'Please select from available resorts.',
            'region.required_without' => 'Please select a resort or region.',
            'region.required_with'    => 'Please select a region.',
            'region.exists'           => 'Not a valid region.',
            'city.required'           => 'Please select from available cities / sub regions.',
            'city.exists'             => 'Not a valid city / sub region.',
        ];
    }

    public function attributes(): array {
        return [
            'emsID'      => 'emsID',
            'firstName'  => 'first name',
            'lastName'   => 'last name',
            'ada'        => 'ada',
            'roomType'   => 'room type',
            'checkIn'    => 'start date',
            'checkIn2'   => 'end date',
            'checkIn3'   => 'checkIn3',
            'adults'     => '# adults',
            'children'   => '# children',
            'preference' => 'travel week preference',
        ];
    }

    public function filters(): array {
        return [
            'adults'   => FILTER_VALIDATE_INT,
            'children' => FILTER_VALIDATE_INT,
            'larger'   => FILTER_VALIDATE_BOOLEAN,
            'nearby'   => FILTER_VALIDATE_BOOLEAN,
        ];
    }

    public function validate( array $data = null, bool $send = true ) {
        $data          = parent::validate( $data, $send );
        $data['miles'] = $this->default()['miles'];
        if ( $data['checkIn'] ) {
            $data['checkIn'] = Carbon::createFromFormat( 'Y-m-d', $data['checkIn'] )->format( 'm/d/Y' );
        }
        if ( $data['checkIn2'] ) {
            $data['checkIn2'] = Carbon::createFromFormat( 'Y-m-d', $data['checkIn2'] )->format( 'm/d/Y' );
        }

        return $data;
    }

    public function validator( array $data = null ) {
        $validator = parent::validator( $data );
        $validator->sometimes( 'city', 'required', function ( $input ) {
            $rule = new SubRegionNameExists( 'region' );
            return $rule->isRequired( $input['region'] );
        } );

        return $validator;
    }

}
