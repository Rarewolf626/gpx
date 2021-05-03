<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 30/3/17
 * Time: 9:54 AM
 * This function is a test function to run a DAEReissueConfirmation call
 * Hopefully it will replicate blank responses as GPX is getting
 */

$AuthID = "TEST-GPX";
$MemberNo = 397091;
$WeekID = 381266;
$url = "http://test.daelive.com/api/daewebservice.asmx?wsdl";

$xmlBody = '<dae:DAEReIssueConfirmation xmlns="DAE">';
$xmlBody.= '<dae:AuthID>'.$AuthID.'</dae:AuthID>';
$xmlBody.= '<dae:DAEMemberNo>'.$MemberNo.'</dae:DAEMemberNo>';
$xmlBody.= '<dae:WeekID>'.$WeekID.'</dae:WeekID>';
$xmlBody.= '</dae:DAEReIssueConfirmation>';


$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:dae="DAE">';
$xml_post_string.= '<soapenv:Header/><soapenv:Body>
'.$xmlBody.'
</soapenv:Body>';
$xml_post_string.= '</soapenv:Envelope>';


$headers = array(
    "POST /api/daewebservice.asmx HTTP/1.1",
    "Content-Type: text/xml; charset=UTF-8",
    "SOAPAction: \"DAE/DAEReIssueConfirmation\"",
    "Host: test.daelive.com",
    "Connection: Keep-Alive",
    "Content-length: ".strlen($xml_post_string)
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);


$response1 = str_replace("<soap:Body>","",$response);
$response2 = str_replace("</soap:Body>","",$response1);
$parser = @simplexml_load_string($response2);

$pdf = base64_decode($parser->DAEReIssueConfirmationResponse->DAEReIssueConfirmationResult->PDFConfirmation);

header("Content-type:application/pdf");
echo $pdf;


