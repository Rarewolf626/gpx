<?php

namespace GPX\GPXAdmin\Controller\Resort;

use DB;
use Exception;
use GPX\Api\Salesforce\Salesforce;
use GPX\Form\Admin\Resort\AddResortForm;
use GPX\Model\Resort;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use SObject;

class AddResortController
{
    public function __invoke(): string
    {
        $message = null;
        $resort = new Resort();
        $errors = new MessageBag();
        if (gpx_request()->isMethod('POST')) {
            try {
                $message = $this->create($resort);
                $resort = new Resort();
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();
                $resort->fill($e->validator->getData());
                $message = 'The given data was invalid.';
            } catch (Exception $e) {
                $message = $e->getMessage();
                $errors->add('salesforce', $message);
            }
        }

        return gpx_admin_view('resorts/resortadd.php', compact('message', 'resort', 'errors'), false);
    }

    private function create(Resort $resort): string
    {
        /** @var AddResortForm $form */
        $form = gpx(AddResortForm::class);
        $values = $form->validate(null, false);
        $resort->fill($values);
        $resort->search_name = gpx_search_string($resort->ResortName);


        DB::transaction(function () use ($resort) {
            $resort->save();
            $this->sendToSalesForce($resort);
        });


        return 'Resort Added!';
    }

    private function sendToSalesForce(Resort $resort)
    {
        $sf = Salesforce::getInstance();

        $sfFields = new SObject();
        $sfFields->type = 'GPX_Resort__c';
        $sfFields->fields = [
            'GPX_Resort__c' => $resort->ResortID,
            'Name' => $resort->ResortName,
            'GPX_Resort_ID__c' => $resort->id,
            'Additional_Info__c' => $resort->AdditionalInfo,
            'Address_Cont__c' => $resort->Address2,
            'Check_In_Days__c' => $resort->CheckInDays,
            'Check_In_Time__c' => $resort->CheckInEarliest,
            'Check_Out_Time__c' => $resort->CheckOutLatest,
            'City__c' => $resort->Town,
            'Closest_Airport__c' => $resort->Airport,
            'Country__c' => $resort->Country,
            'Directions__c' => $resort->Directions,
            'Fax__c' => $resort->Fax,
            'Phone__c' => $resort->Phone,
            'Resort_Description__c' => $resort->Description,
            'Resort_Website__c' => $resort->Website,
            'State_Region__c' => $resort->Region,
            'Street_Address__c' => $resort->Address1,
            'Zip_Postal_Code__c' => $resort->PostCode,
        ];

        $sfResortAdd = $sf->gpxUpsert('GPX_Resort_ID__c', [$sfFields]);
        $sfID = $sfResortAdd[0]->id ?? null;
        if (!$sfID) {
            throw new \Exception(is_string($sfResortAdd) ? $sfResortAdd : 'Failed to send resort to salesforce');
        }
        $resort->update(['sf_GPX_Resort__c' => $sfID]);


    }
}
