<?php

namespace GPX\Api\TripAdvisor;

use Exception;
use GuzzleHttp\Client;

class TripAdvisor
{
    const ENDPOINT = 'https://api.content.tripadvisor.com/api/v1/';
    private string $api_key;
    private Client $client;

    public function __construct(string $api_key = null)
    {
        $this->api_key = $api_key ?? GPX_TRIP_ADVISOR_API_KEY;
        $this->setClient();
    }

    public function setClient( Client $client = null ) {
        $this->client = $client ?? new Client();
    }

    /**
     * @return TripAdvisor
     */
    public static function instance(): TripAdvisor {
        return gpx( TripAdvisor::class );
    }

    public function location($location_id, string $language = 'en', string $currency = 'USD'): ?\stdClass
    {
        $url = self::ENDPOINT . "location/{$location_id}/details";
        $response = $this->client->request( 'GET', $url, [
            'headers' => [
                'accept' => 'application/json',
            ],
            'query' => [
                'key'     => $this->api_key,
                'language' => $language,
                'currency' => $currency,
            ],
        ] );
        if ( $response->getStatusCode() !== 200 ) {
            throw new Exception( 'Could not connect to trip advisor api' );
        }

        $location = json_decode( $response->getBody()->getContents(), false );
        if ( ! empty( $location->error->message ) ) {
            throw new Exception( $location->error->message, $location->error->code );
        }

        return $location;
    }

    public function location_mapper(float|string $latitude, float $longitude = null)
    {
        if(is_string($latitude) && str_contains($latitude, ',')) {
            $coords = $latitude;
        } else {
            $coords = $latitude . ',' . $longitude;
        }
        $url = self::ENDPOINT . 'location_mapper/' . $coords;
        $response = $this->client->request( 'GET', $url, [
            'query' => [
                'key'     => $this->api_key . '-mapper',
            ],
        ] );
        if ( $response->getStatusCode() !== 200 ) {
            throw new Exception( 'Could not connect to trip advisor api' );
        }

        $location = json_decode( $response->getBody()->getContents(), false );
        if ( ! empty( $location->error->message ) ) {
            throw new Exception( $location->error->message, $location->error->code );
        }

        return $location;
    }
}
