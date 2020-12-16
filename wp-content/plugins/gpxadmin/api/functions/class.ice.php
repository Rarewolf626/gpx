<?php 

class Ice
{

    public function __construct($uri, $dir)
    {
        $this->uri = plugins_url('', __FILE__).'/api';
        $this->dir = trailingslashit( dirname(__FILE__) ).'/api' ;
        
        $this->icecred = array(
                'host' => "https://partneraccesspoint-api-qa-westus.azurewebsites.net/api/v1/",
                'AppId' => "cf302d49-065f-4135-80f8-768c1983461e",
                'AppKey' => 'v+LlrQjbnEsxgvKq8CESA+Z0uQhFxvlduT15sJRMZxI=',
                'prefix' => 'GPX.',
                'mode' => 'testing',
        );
//         $this->daecred = array(
//                 'host' => "https://partneraccesspoint-api-prod-westus.azurewebsites.net/api/v1/",
//                 'AppId' => "cf302d49-065f-4135-80f8-768c1983461e",
//                 'AppKey' => 'v+LlrQjbnEsxgvKq8CESA+Z0uQhFxvlduT15sJRMZxI=',
//                 'prefix' => 'GPX.',
//                 'mode' => 'production',
//         );
        
        require_once $dir.'/models/icemodel.php';
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
        
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
            $cid = $_COOKIE['switchuser'];
        
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
        
        $icePW = wp_generate_password();
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
            
           
//             $params = array(
//                 'thirdpartyId' => $usermeta->DAEMemberNo,
//                 'partnerId' => '185',
//                 'product' => 'default',
//                 'devIdentifier' => 'GPX',
//             );
            
//             $data = array(
//                 'function' => 'member',
//                 'inputMembers' => $params,
//                 'action' => 'GET'
//             );
            
//             $response = $this->ice_model->iceretrieve($this->icecred, $data);
//             $responseData = json_decode($response);
            if(empty($this->country_to_country_code($usermeta->Address5)))
            {
                $usermeta->Address5 = 'United States';
            }
//             echo '<pre>'.print_r($usermeta->Address5, true).'</pre>';
            //member must not exist -- we need to add them
            $email = 'gpresorts'.rand(0, 100000000).'@gpresorts.com';
            if(isset($usermeta->Email) && !empty($usermeta->Email))
                $email = $usermeta->Email;
            $params = array(
                'AuthCode' => '2VVD58V8',
                'CertNumber' => '7ZCSRPGC',
                'ThirdpartyId' => $usermeta->DAEMemberNo,
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
                'UserName' => "U".$usermeta->DAEMemberNo,
                'Password' => $icePW,
                'DevIdentifier' => 'GPX',
                'TcpaOptin' => false,
            );
            $data = array(
                'function'=>'createmember',
                'inputMembers'=>$params,
                'action'=>'POST',
            );
//                     if(get_current_user_id() == 5)
//                     echo '<pre>'.print_r($data, true).'</pre>';
            $response = $this->ice_model->iceretrieve($this->icecred, $data);
            $responseData = json_decode($response);
//                        if(get_current_user_id() == 5)
//                        echo '<pre>'.print_r($responseData, true).'</pre>';     
            //if ICE returns choose a different id then lets change it for them...
            if($responseData->ErrorMessage == 'Please choose a different third party id.')
            {
//                         if(get_current_user_id() == 5)
//                         echo '<pre>'.print_r("loop", true).'</pre>';
                $random = rand(5,9);
                $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random;
                $data['inputMembers']['Email'] = $random.$email;
                $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                $response = $this->ice_model->iceretrieve($this->icecred, $data);
                $responseData = json_decode($response);
                //if ICE returns choose a different id then lets change it for them...
                if($responseData->ErrorMessage == 'Please choose a different third party id.')
                {
                    $random = rand(0,4);
                    $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random;
                    $data['inputMembers']['Email'] = $random.$email;
                    $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                    $response = $this->ice_model->iceretrieve($this->icecred, $data);
                    $responseData = json_decode($response);
                    //if ICE returns choose a different id then lets change it for them...
                    if($responseData->ErrorMessage == 'Please choose a different third party id.')
                    {
                        $random = rand(10,19);
                        $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random;
                        $data['inputMembers']['Email'] = $random.$email;
                        $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                        $response = $this->ice_model->iceretrieve($this->icecred, $data);
                        $responseData = json_decode($response);
                        //if ICE returns choose a different id then lets change it for them...
                        if($responseData->ErrorMessage == 'Please choose a different third party id.')
                        {
                            $random = rand(20,29);
                            $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random;
                            $data['inputMembers']['Email'] = $random.$email;
                            $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                            $response = $this->ice_model->iceretrieve($this->icecred, $data);
                            $responseData = json_decode($response);
                            //if ICE returns choose a different id then lets change it for them...
                            if($responseData->ErrorMessage == 'Please choose a different third party id.')
                            {
                                $random = rand(30,39);
                                $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random;
                                $data['inputMembers']['Email'] = $random.$email;
                                $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                                $response = $this->ice_model->iceretrieve($this->icecred, $data);
                                $responseData = json_decode($response);
                                //if ICE returns choose a different id then lets change it for them...
                                if($responseData->ErrorMessage == 'Please choose a different third party id.')
                                {
                                    $random = rand(40,99);
                                    $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random;
                                    $data['inputMembers']['Email'] = $random.$email;
                                    $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                                    $response = $this->ice_model->iceretrieve($this->icecred, $data);
                                    $responseData = json_decode($response);
                                    
                                    if($responseData->ErrorMessage == 'Please choose a different third party id.')
                                    {
                                        $random = rand(40,99);
                                        $data['inputMembers']['ThirdpartyId'] = $usermeta->DAEMemberNo.$random.'a';
                                        $data['inputMembers']['Email'] = $random.$email;
                                        $data['inputMembers']['UserName'] = $random."U".$usermeta->DAEMemberNo;
                                        $response = $this->ice_model->iceretrieve($this->icecred, $data);
                                        $responseData = json_decode($response);
                                    }
                                }
                            }
                        }
                    }
                }
                    
            }
//             if(get_current_user_id() == 5)
//             {
//                 echo '<pre>'.print_r($responseData, true).'</pre>';
//                 return false;
//             }
            if(isset($responseData) && !empty($responseData->PrimaryNameRecId))
            {
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
    
    function getIceMember()
    {

        $response = '';
        
        $cid = get_current_user_id();
        
        if(isset($_COOKIE['switchuser']))
            $cid = $_COOKIE['switchuser'];
    
        $usermeta = (object) array_map( function( $a ){ return $a[0]; }, get_user_meta( $cid ) );
        
        if((!isset($usermeta->ICEStore) || (isset($usermeta->ICEStore) && $usermeta->ICEStore != 'No')) && (isset($usermeta->ICENameId) && !empty($usermeta->ICENameId)))
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
//             if(get_current_user_id() == 5)
//                 echo '<pre>'.print_r($data, true).'</pre>';
            $response = $this->ice_model->iceretrieve($this->icecred, $data);
            $responseJson = json_decode($response);
            
            if($responseJson->Success == True)
            {
                $redirectID = $usermeta->DAEMemberNo;
                if(isset($usermeta->ICEUserName) && !empty($usermeta->ICEUserName))
                {
                    $redirectID = $usermeta->ICEUserName;
                }
                $redirect = 'https://www.gpxcruise.com/?thirdpartyid='.$redirectID;
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