<?php

namespace GPX\Api;

use Exception;
use GuzzleHttp\Client;

class GoogleMap {
    const ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';
    private string $api_key;
    private Client $client;

    public function __construct( string $api_key = null ) {
        $this->api_key = $api_key ?? GPX_GOOGLE_MAPS_GEOCODE_API_KEY;
        $this->setClient();
    }

    public function setClient( Client $client = null ) {
        $this->client = $client ?? new Client();
    }

    /**
     * @return GoogleMap
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function instance(): GoogleMap {
        return gpx( GoogleMap::class );
    }

    public function geocode( $address ) {
        $response = $this->client->request( 'GET', static::ENDPOINT, [
            'query' => [
                'key'     => $this->api_key,
                'address' => $address,
            ],
        ] );
        if ( $response->getStatusCode() !== 200 ) {
            throw new Exception( 'Could not connect to googleapis.com/maps/api' );
        }
        $geocodingResponse = json_decode( $response->getBody()->getContents(), false );
        if ( ! empty( $geocodingResponse->error_message ) ) {
            throw new Exception( $geocodingResponse->error_message );
        }

        if ( ! count( $geocodingResponse->results ) ) {
            throw new Exception( sprintf( 'No results found for address %s', $address ) );
        }
        return $geocodingResponse->results[0]->geometry->location;
        // return $this->formatResponse( $geocodingResponse->results[0] );
    }


    private function formatResponse( $result ): array {
        return [
            'lat'                => $result->geometry->location->lat,
            'lng'                => $result->geometry->location->lng,
            'accuracy'           => $result->geometry->location_type,
            'formatted_address'  => $result->formatted_address,
            'viewport'           => $result->geometry->viewport,
            'partial_match'      => isset( $result->partial_match ) ? $result->partial_match : false,
            'place_id'           => $result->place_id,
        ];
    }
}
