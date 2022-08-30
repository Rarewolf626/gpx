<?php

namespace GPX\Api;

use Dompdf\Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class GoogleMap
{

    private $dev_key = 'AIzaSyD-4oqc-FAqrvDtcFF1_XWe9ihkTkau1v8';

    private $url = 'https://maps.googleapis.com/maps/api/geocode/';
    private $format = 'json';

    private $address;

    private $json;

    private $error;

    /**
     * @return GoogleMap
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function instance(): GoogleMap{
        return gpx(GoogleMap::class);
    }

    /**
     *
     */
    public function __construct (){}


    /**
     * @param $address
     * @return void
     * @throws GuzzleException
     */
    public function geocode($address)
    {

        $this->address = urlencode($address);

        $endpoint = $this->url . $this->format;

        $params = array('query' => [
            'key' => $this->dev_key,
            'address' => $this->address,
            'key' => $this->dev_key
        ]);
        $str = '?key=' . $this->dev_key . '&address=' . $this->address;

        $request = $endpoint;
        $client = new Client();


        try {
            $result = $client->request('GET', $request, $params);
            $this->json = json_decode($result->getBody());

        } catch (Exception $e) {
            $this->error = $e;
        }

        return $this->json->results[0]->geometry->location;
    }



}
