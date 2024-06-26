<?php

class ShiftfourModel {


    public function shiftretrieve( $action, $url, $data, $token = '' ) {
        global $wpdb;

        if ( $action == 'GET' ) {
            $url .= '?' . http_build_query( $data );
        }

        $headers = [
            'InterfaceVersion: 2.0',
            'InterfaceName: VEST',
            'CompanyName: ResorTime',
        ];
        //is the access
        if ( ! empty( $token ) ) {
            $headers[] = 'AccessToken: ' . $token;
        }
        $ch = curl_init( $url );

        if ( $action == 'GET' || $action == 'DELETE' ) {
            if ( strpos( $url, 'transactions/invoice' ) ) {
                $headers[] = 'Invoice: ' . $data['invoice'];
            }
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $action );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        }
        if ( $action == 'POST' ) {
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $action );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
            $postData = json_encode( $data );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
        }

        if ( $action == 'DIRECTPOST' ) {
            $postData = http_build_query( $data );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
        }

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $response = curl_exec( $ch );
        $header_data = curl_getinfo( $ch );
        $wpdb->insert( 'wp_shift4', [
            'header' => json_encode( $headers ),
            'post' => $postData,
            'response' => $response,
            'headerInfo' => json_encode( $header_data ),
        ] );

        return $response;
    }


}
