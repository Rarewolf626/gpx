<?php

use GPX\Model\UserMeta;
use GPX\Repository\OwnerRepository;

class Ice
{


    public $uri;
    public $dir;
    public $icecred;
    public $ice_model;


    public function __construct($uri, $dir)
    {
        $this->uri = plugins_url('', __FILE__).'/api';
        $this->dir = trailingslashit( dirname(__FILE__) ).'/api' ;

        $this->icecred = array(
                        'host' => ICE_HOST,
                        'AppId' => ICE_APPID,
                        'AppKey' => ICE_APPKEY,
                        'prefix' => ICE_PREFIX,
                        'mode' => ICE_MODE,
                );
        $this->ice_model = new IceModel;
    }

    function ICEGetDailyKey()
    {
        $data = array('function'=>'dailyapikey');

        $response = $this->ice_model->iceretrieve($this->icecred, $data);
        echo '<pre>'.print_r($response, true).'</pre>';
        return $response;
    }

    function newIceMember()
    {

        global $wpdb;
        $response = '';

        $cid = gpx_get_switch_user_cookie();

        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

        $icePW = wp_generate_password();

            //If no existing ICE information
            if(!isset($usermeta->ICEStore) || (isset($usermeta->ICEStore) && $usermeta->ICEStore != 'No'))
            {
                //if the member already exists then we can redirect now
                $precheck = $this->getIceMember();
                if(!empty($precheck))
                {
                    foreach($precheck as $key=>$value)
                    {
                        if($key == 'redirect')
                        {
                            return $precheck;
                        }
                    }
                }

                if(empty($this->country_to_country_code($usermeta->Address5)))
                {
                    $usermeta->Address5 = 'United States';
                }

                //member must not exist -- we need to add them
                $email = 'gpresorts'.rand(0, 100000000).'@gpresorts.com';
                if(isset($usermeta->Email) && !empty($usermeta->Email))
                {
                    $email = $usermeta->Email;
                }

                $username = $usermeta->DAEMemberNo;
                if(empty($username))
                {
                    $username = $usermeta->GPX_Member_VEST__c;
                }
                $params = array(
                    'AuthCode' => 'QDT27VTZ',
                    'CertNumber' => 'N4L4WQWR',
                    'ThirdpartyId' => $username,
                    'PartnerId' => '185',
                    'AddressLine1' => $usermeta->Address1,
                    'City' => $usermeta->Address3,
                    'PostCode' => $usermeta->PostCode,
                    //                 'StateOrTerritory' => ,
                    'CountryCode' => $this->country_to_country_code($usermeta->Address5),
                    'Email' => $email,
                    'EmailOptin' => false,
                    'FirstName' => $usermeta->FirstName1,
                    'LastName' => $usermeta->LastName1,
                    'UserName' => $username,
                    'Password' => $icePW,
                    'DevIdentifier' => 'GPX',
                    'TcpaOptin' => false,
                );
                $data = array(
                    'function'=>'createmember',
                    'inputMembers'=>$params,
                    'action'=>'POST',
                );

                $response = $this->ice_model->iceretrieve($this->icecred, $data);
                $responseData = json_decode($response);

                //if ICE returns choose a different id then lets change it for them...
                if($responseData->ErrorMessage == 'Please choose a different third party id.')
                {


                    $precheck = $this->getIceMember();
                    if(!empty($precheck))
                    {
                        foreach($precheck as $key=>$value)
                        {
                            if($key == 'redirect')
                            {
                                return $precheck;
                            }
                        }
                    }
                }

                if(isset($responseData) && !empty($responseData->PrimaryNameRecId))
                {
                    update_user_meta($cid, 'ICECert', $params['CertNumber']);
                    update_user_meta($cid, 'ICENameId', $responseData->PrimaryNameRecId);
                    update_user_meta($cid, 'ICEToken', $responseData->Token);
                    update_user_meta($cid, 'ICEMemberId', $responseData->MemberId);
                    update_user_meta($cid, 'ICEPassword', $icePW);
                    update_user_meta($cid, 'ICEUserName', $data['inputMembers']['ThirdpartyId']);
                }

                //now that we have the member added lets
                if($getICEMember = $this->getIceMember())
                {
                    return $getICEMember;
                }

            }

            return $response;

    }

//Create the JWT SSO URL string
function newIceMemberJWT(){
    global $wpdb;
    $response = array();

    //Build out the JWT Object
    $issuedAt = time();
    $expire = $issuedAt + 120; //Two minuutes

    $cid = gpx_get_switch_user_cookie();

    $first_name = get_user_meta( $cid, 'first_name', true );
    $last_name = get_user_meta( $cid, 'last_name', true );
    $email = OwnerRepository::instance()->get_email( $cid );

    $usermeta = UserMeta::load($cid);
    if ( empty($this->country_to_country_code($usermeta->Address5)) ) {
        $usermeta->Address5 = 'United States';
    }

    $username = $usermeta->DAEMemberNo;
    if(empty($username)) {
        $username = $usermeta->GPX_Member_VEST__c;
    }

    //Build the Header
    $JWTHeader = '{"typ": "JWT","alg": "HS256"}';
    $JWTEncodedHeader = $this->base64url_encode( $JWTHeader );

    //Build the Payload
    $JWTPayload = json_encode(
        array(
        'iss'=>'www.gpxvacations.com',
        'aud'=>'www.gpxperks.com',
        'exp'=> $expire,
        'PartnerId' => '185',
        'ThirdpartyId' => $username,
        'Email' => $email,
        'FirstName' => $first_name,
        'LastName' => $last_name,
        'Address' => $usermeta->Address1,
        'City' => $usermeta->Address3,
        'PostalCode' => $usermeta->PostCode,
        'State' => $usermeta->Address4,
        'PhoneNumber' => $usermeta->DayPhone,
        'Country' => $this->country_to_country_code($usermeta->Address5),)
    );
    $JWTEncodedPayload = $this->base64url_encode( $JWTPayload );

    //Build the Un-hashed string
    $JWTString = $JWTEncodedHeader.'.'.$JWTEncodedPayload;

    //Set the Key
    $JWTKey = "eJBDWS7sxailN3hj@ZzlQi";

    //Build the Signature
    $JWTSignatureRaw = hash_hmac("sha256", $JWTString, $JWTKey, true);
    $JWTSignatureEncoded = $this->base64url_encode($JWTSignatureRaw);
    $JWTFinalSignature = trim($JWTSignatureEncoded);

    //Build the token
    $JWToken = $JWTEncodedHeader . '.' . $JWTEncodedPayload . '.' . $JWTFinalSignature;

    //Build the response
    $response['redirect'] = "https://gpxperks.com/redirect/jwt_sso_in?ssotoken=" . $JWToken;

    error_log("JWT Built");
    error_log("Header: " . $JWTHeader);
    error_log("Payload: " . $JWTPayload);
    error_log("Encoded Header: " . $JWTEncodedHeader);
    error_log("Encoded Payload: " . $JWTEncodedPayload);
    error_log("Final Signature: " . $JWTFinalSignature);
    error_log("Signature Raw: " . $JWTSignatureRaw);
    error_log($response['redirect']);

    return $response;
}

function base64url_encode($data) {
    // First of all you should encode $data to Base64 string
    $b64 = base64_encode($data);

    // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
    if ($b64 === false) {
        return false;
    }

    // Convert Base64 to Base64URL by replacing
    $url = strtr($b64, '+/', '-_');

    // Remove padding character from the end of line and return the Base64URL result
    return rtrim($url, '=');
}


    function getIceMember()
    {

        $response = '';

        $cid = gpx_get_switch_user_cookie();

            $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );

            $authtypes = [

                [
                    'function'=>'member',
                    'param'=>'memberId',
                    'meta'=>'ICEMemberId',
                ],

            ];



            if( (!isset($usermeta->ICEStore) || (isset($usermeta->ICEStore) && $usermeta->ICEStore != 'No')) )
            {
                $params = array(
                    'partnerId' => '185',
                    'devIdentifier' => 'GPX',
                    'AuthCode' => 'QDT27VTZ',
                    'CertNumber' => 'N4L4WQWR',

                );
                $data = array(
                    'inputMembers'=>$params,
                    'action'=>'GET',
                );

                $data['action'] = 'GET';

                if(!isset($usermeta->ICENameId) || empty($usermeta->ICENameId))
                {
                    //we need to get the ice info
                    foreach($authtypes as $atV)
                    {
                        $func = $atV['function'];
                        $param = $atV['param'];
                        $meta = $atV['meta'];

                        if(!empty($usermeta->$meta))
                        {
                            $params[$param] = $usermeta->$meta;
                            $data['function'] = $func;
                        }
                        else
                        {
                            $data['function'] = 'authenticatemember';
                            $params['nameId'] = $usermeta->GPX_Member_VEST__c;
                        }

                        $data['inputMembers'] = $params;

                        $response = $this->ice_model->iceretrieve($this->icecred, $data);
                        $responseJson = json_decode($response);

                        if($responseJson->identity)
                        {
                            $identity = $responseJson->identity;
                            update_user_meta($cid, 'ICENameId', $responseData->ice_name_id);
                            update_user_meta($cid, 'ICEMemberId', $responseData->ice_member_id);
                            $usermeta->ICENameId = $responseJson->ice_name_id;
                            break;
                        }
                    }
                }

                if(isset($usermeta->ICENameId))
                {
                    $params = array(
                        'nameId' => $usermeta->ICENameId,
                        'partnerId' => '185',
                        'devIdentifier' => 'GPX',

                    );
                    $data = array(
                        'function'=>'authenticatemember',
                        'inputMembers'=>$params,
                        'action'=>'GET',
                    );
                    $response = $this->ice_model->iceretrieve($this->icecred, $data);
                    $responseJson = json_decode($response);
                }
                if(isset($_REQUEST['icedebug']))
                {
                    echo '<pre>'.print_r($data, true).'</pre>';
                    echo '<pre>'.print_r($response, true).'</pre>';
                    echo '<pre>'.print_r($responseJson, true).'</pre>';
                }
                if($responseJson->Success == True)
                {
                    $redirectID = $usermeta->DAEMemberNo;
                    if(isset($usermeta->ICEUserName) && !empty($usermeta->ICEUserName))
                    {
                        $redirectID = $usermeta->ICEUserName;
                    }
                    $link = 'https://gpxperks.com/';

                    if(isset($_POST['redirect']))
                    {
                        $link .= $_POST['redirect'];
                    }
                    $redirect = $link.'/?thirdpartyid='.$redirectID;

                    if(isset($_REQUEST['icedebug']))
                    {
                        echo '<pre>'.print_r($redirect, true).'</pre>';
                    }
                    return array('redirect'=>$redirect);
                }
            }
            return $response;
    }


    function country_to_country_code($country)
    {
        $countries = array
        (
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua And Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia And Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo, Democratic Republic',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Cote D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island & Mcdonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic Of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle Of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States Of',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts And Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre And Miquelon',
            'VC' => 'Saint Vincent And Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome And Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia And Sandwich Isl.',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard And Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad And Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks And Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis And Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

        $iso_code = array_search(strtolower($country), array_map('strtolower', $countries));

        return $iso_code;
    }
}
