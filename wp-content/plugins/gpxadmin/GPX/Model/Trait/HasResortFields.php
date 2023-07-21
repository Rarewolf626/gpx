<?php

namespace GPX\Model\Trait;

use Illuminate\Support\Collection;

trait HasResortFields
{
    /**
     * @return Collection<int, array{name: string, label: string, type: string, booking: bool, profile: bool, enabled: bool, attributes: array<string, string>}>
     */
    public static function descriptionFields(): Collection
    {
        return new Collection([
            [
                'name' => 'AreaDescription',
                'label' => 'Area Description',
                'type' => 'textarea',
                'booking' => false,
                'profile' => false,
                'enabled' => false,
                'attributes' => [
                    'maxlength' => '10000'
                ]
            ],
            [
                'name' => 'UnitDescription',
                'label' => 'Unit Description',
                'type' => 'textarea',
                'booking' => false,
                'profile' => false,
                'enabled' => false,
                'attributes' => [
                    'maxlength' => '10000'
                ]
            ],
            [
                'name' => 'Description',
                'label' => 'Description',
                'type' => 'textarea',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '20000'
                ]
            ],
//            [
//                'name' => 'AlertNote',
//                'label' => 'Alert Note',
//                'type' => 'textarea',
//                'booking' => true,
//                'profile' => true,
//                'enabled' => true,
//                'attributes' => [
//                    'maxlength' => '20000'
//                ]
//            ],
            [
                'name' => 'HTMLAlertNotes',
                'label' => 'HTML Alert Notes',
                'type' => 'textarea',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '20000'
                ]
            ],
            [
                'name' => 'AdditionalInfo',
                'label' => 'Additional Info',
                'type' => 'textarea',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '10000'
                ]
            ],
            [
                'name' => 'Website',
                'label' => 'Website',
                'type' => 'url',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '1000'
                ]
            ],
            [
                'name' => 'CheckInDays',
                'label' => 'Check In Days',
                'type' => 'text',
                'booking' => false,
                'profile' => false,
                'enabled' => false,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'CheckInEarliest',
                'label' => 'Check In Earliest',
                'type' => 'text',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'CheckInLatest',
                'label' => 'Check In Latest',
                'type' => 'text',
                'booking' => false,
                'profile' => false,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'CheckOutEarliest',
                'label' => 'Check Out Earliest',
                'type' => 'text',
                'booking' => false,
                'profile' => false,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'CheckOutLatest',
                'label' => 'Check Out Latest',
                'type' => 'text',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Address1',
                'label' => 'Address 1',
                'type' => 'text',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Address2',
                'label' => 'Address 2',
                'type' => 'text',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Town',
                'label' => 'City',
                'type' => 'text',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Region',
                'label' => 'State/Region',
                'type' => 'text',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Country',
                'label' => 'Country',
                'type' => 'text',
                'booking' => true,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'PostCode',
                'label' => 'ZIP/Post Code',
                'type' => 'text',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Phone',
                'label' => 'Phone',
                'type' => 'tel',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Fax',
                'label' => 'Fax',
                'type' => 'tel',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '255'
                ]
            ],
            [
                'name' => 'Airport',
                'label' => 'Closest Airport',
                'type' => 'text',
                'booking' => false,
                'profile' => true,
                'enabled' => true,
                'attributes' => [
                    'maxlength' => '10000'
                ]
            ],
            [
                'name' => 'Directions',
                'label' => 'Directions',
                'type' => 'text',
                'booking' => false,
                'profile' => false,
                'enabled' => false,
                'attributes' => [
                    'maxlength' => '20000'
                ]
            ],
        ]);
    }
}
