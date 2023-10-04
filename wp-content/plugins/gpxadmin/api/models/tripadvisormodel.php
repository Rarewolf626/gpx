<?php
class TripadvisorModel
{

    public $url;
    public $key;

    public function __construct()

    {
        $this->url = 'https://api.tripadvisor.com/api/partner/2.0/';
        $this->key = '5f25817374274a4996789deaaf26c1d1';
    }

    public function retrieve($inputMember)
    {
        $type = $inputMember['type'];
        $data = $inputMember['data'];
        $q = $inputMember['q'] ?? '';

        $url = $this->url.$type.'/'.$data;

        $url .= '?key='.$this->key;

        if($type == 'location_mapper')
        {
            $url .= '-mapper';
        }
        if(!empty($q))
        {
            $url .= '&'.$q;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return $response;
    }
}
