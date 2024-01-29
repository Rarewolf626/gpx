<?php

namespace GPX\Api\Salesforce;

class SalesforceException extends \Exception
{
    private $response;

    public function response()
    {
        return $this->response;
    }

    public function setResponse($response): SalesforceException
    {
        $this->response = $response;
        return $this;
    }
}
