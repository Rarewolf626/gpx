<?php

namespace GPX\GPXAdmin\Controller\Resort;

use DB;
use Exception;
use GPX\Api\Salesforce\Salesforce;
use GPX\Form\Admin\Resort\AddResortForm;
use GPX\Model\Resort;
use GPX\Repository\ResortRepository;
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

        return gpx_render_blade('admin::resorts.resortadd', compact('message', 'resort', 'errors'), false);
    }

    private function create(Resort $resort): string
    {
        /** @var AddResortForm $form */
        $form = gpx(AddResortForm::class);
        $values = $form->validate(null, false);
        $resort->fill($values);
        $resort->search_name = gpx_search_string($resort->ResortName);
        $repository = ResortRepository::instance();

        DB::transaction(function () use ($resort, $repository) {
            $resort->save();
            $repository->send_to_salesforce($resort);
        });


        return 'Resort Added!';
    }
}
