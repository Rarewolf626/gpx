<?php

namespace GPX\Command\Transaction;

use SObject;
use GPX\Model\Transaction;
use GPX\Api\Salesforce\Salesforce;

class UpdateGuestInfo {
    public function __construct(private Transaction $transaction, private array $values) {}

    public function handle() {
        $data = $this->transaction->data;
        $data['GuestFirstName'] = $this->values['first_name'];
        $data['GuestLastName'] = $this->values['last_name'];
        $data['GuestName'] = $this->values['name'] ?? trim($this->values['first_name'] . ' ' . $this->values['last_name']);
        $data['GuestEmail'] = mb_strtolower($this->values['email']);
        $data['GuestPhone'] = gpx_format_phone($this->values['phone']);
        $data['Adults'] = (int) $this->values['adults'];
        $data['Children'] = (int) $this->values['children'];
        $data['OwnerName'] = $this->values['owner'];
        if (array_key_exists('HasGuestFee', $this->values)) {
            $data['HasGuestFee'] = (bool)$this->values['HasGuestFee'];
        }
        $this->transaction->data = $data;
        $this->transaction->save();

        $sf = Salesforce::getInstance();
        $sfData = [
            'GPXTransaction__c' => $this->transaction->id,
            'Guest_First_Name__c' => $data['GuestFirstName'],
            'Guest_Last_Name__c' => $data['GuestLastName'],
            'Guest_Email__c' => $data['GuestEmail'],
            'Guest_Home_Phone__c' => $data['GuestPhone'],
            'Trade_Partner__c' => $data['OwnerName'],
        ];
        $sfWeekData = [
            'Guest_First_Name__c' => $data['GuestFirstName'],
            'Guest_Last_Name__c' => $data['GuestLastName'],
            'Guest_Email__c' => $data['GuestEmail'],
            'Guest_Phone__c' => $data['GuestPhone'],
            'of_Adults__c' => $data['Adults'],
            'of_Children__c' => $data['Children'],
            'GpxWeekRefId__c' => $this->transaction->weekId,
        ];

        $sfFields = new SObject();
        $sfFields->fields = $sfData;
        $sfFields->type = 'GPX_Transaction__c';
        $sf->gpxUpsert('GPXTransaction__c', [$sfFields]);

        $sfFields = new SObject();
        $sfFields->fields = $sfWeekData;
        $sfFields->type = 'GPX_Week__c';
        $sf->gpxUpsert('GpxWeekRefId__c', [$sfFields]);
    }
}
