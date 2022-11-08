<?php

//some var

$user_name_by_sbl = "bdtaxUser2014";
$user_password_by_sbl = "duUserPayment2014";
$owner_code_by_sbl = "bdtax";
$api_url_by_sbl = "https://spg.sblesheba.com:6314"; //uat
$UI_url_by_sbl = "https://spg.sblesheba.com:6313/SpgLanding/SpgLanding/"; //uat
$request_id_1st_3_by_sbl = "099";

//client var
$request_id = $request_id_1st_3_by_sbl . rand( 1111111, 9999999 );
$reference_no = '698';

$ref_date = date( "Y-m-d" );
$credit_account_1 = "1111111111111";
$credit_account_2 = "0002601020864";
$credit_amt_on_account_1 = 55;
$credit_amt_on_account_2 = 45;
$total_credit_acc_list = "";

        if ( $credit_amt_on_account_2 > 0 ) {
            $total_credit_acc_list = $credit_account_1 . "-" . $credit_account_2;

            $CreditInformations = array(
                array(
                    "SLNO"          => "1",
                    "CreditAccount" => $credit_account_1,
                    "CrAmount"      => $credit_amt_on_account_1,
                    "Purpose"       => "CHL",
                    "Onbehalf"      => "Test1" ),
                array(
                    "SLNO"          => "2",
                    "CreditAccount" => $credit_account_2,
                    "CrAmount"      => $credit_amt_on_account_2,
                    "Purpose"       => "TRN",
                    "Onbehalf"      => "Test2" ),
            );

        } 
        else {
            $total_credit_acc_list = $credit_account_1;
            $CreditInformations = array(
                array(
                    "SLNO"          => "1",
                    "CreditAccount" => $credit_account_1,
                    "CrAmount"      => $credit_amt_on_account_1,
                    "Purpose"       => "CHL",
                    "Onbehalf"      => "Test" )
            );
        }

$total_tran_amount = $credit_amt_on_account_1 + $credit_amt_on_account_2;
$return_url = "https://10.10.7.240/callrestapi/spgtestdemo/response.php";

//1st api ...............

$url = $api_url_by_sbl . "/api/SpgService/GetSessionKey"; //uat

$data = array(
    "AccessUser"   => array(
        "userName" => $user_name_by_sbl,
        "password" => $user_password_by_sbl,
    ),
    "strUserId"    => $user_name_by_sbl,
    "strPassKey"   => $user_password_by_sbl,
    "strRequestId" => $request_id,
    "strAmount"    => $total_tran_amount,
    "strTranDate"  => $ref_date,
    "strAccounts"  => $total_credit_acc_list,
);

$CURLOPT_HTTPHEADER = array( 'Content-Type: application/json' );

$options = array(
    CURLOPT_POST           => 1,
    CURLOPT_RETURNTRANSFER => true, // return web page
    CURLOPT_HEADER         => false, // don't return headers
    CURLOPT_FOLLOWLOCATION => true, // follow redirects
    CURLOPT_ENCODING       => "", // handle all encodings
    CURLOPT_AUTOREFERER    => true, // set referer on redirect
    CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
    CURLOPT_TIMEOUT        => 120, // timeout on response
    CURLOPT_MAXREDIRS      => 10, // stop after 10 redirects
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER     => $CURLOPT_HTTPHEADER,
    CURLOPT_POST           => 1,
    CURLOPT_POSTFIELDS     => json_encode( $data ),
);

$ch = curl_init( $url );
curl_setopt_array( $ch, $options );
$content = curl_exec( $ch );
curl_close( $ch );

$response_arr = json_decode( json_decode( $content ), true );
//echo $response_arr["scretKey"];
if( $response_arr["scretKey"] == "Authorization is not valid" ||  $response_arr["scretKey"] == ""){
    echo "get session key error";
    exit();
}
else{


    //2nd api
    $url2 = $api_url_by_sbl . "/api/SpgService/PaymentByPortal"; //uat

    $data = array(
        "Authentication"     => array(
            "ApiAccessUserId"  => $user_name_by_sbl,
            "ApiAccessPassKey" => $response_arr["scretKey"],
        ),
        "ReferenceInfo"      => array(
            "RequestId"       => $request_id,
            "RefTranNo"       => $reference_no,
            "RefTranDateTime" => $ref_date,
            "ReturnUrl"       => $return_url,
            "ReturnMethod"    => "POST",
            "TranAmount"      => $total_tran_amount,
            "ContactName"     => "applicentName",
            "ContactNo"       => "01744558899",
            "PayerId"         => "na",
            "Address"         => "applicentAddress",
        ),
        "CreditInformations" => $CreditInformations
    );

    $CURLOPT_HTTPHEADER = array( 'Content-Type: application/json' );

    $options = array(
        CURLOPT_POST           => 1,

        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER         => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING       => "", // handle all encodings
        CURLOPT_AUTOREFERER    => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT        => 120, // timeout on response
        CURLOPT_MAXREDIRS      => 10, // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => $CURLOPT_HTTPHEADER,
        CURLOPT_POST           => 1,
        CURLOPT_POSTFIELDS     => json_encode( $data ),
    );

    $ch = curl_init( $url2 );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    curl_close( $ch );

    $response_arr2 = json_decode( json_decode( $content ), true );

    if($response_arr2['status'] == '200' ){
        $return_url_new = $UI_url_by_sbl . $response_arr2["session_token"]; 
        header('Location: '.$return_url_new);


        $response_xml = $_REQUEST['Request'];
        $xml_object = simplexml_load_string($response_xml);


        var_dump( $xml_object);

        
        // responde xml to array
       // $response_array = objectsIntoArray($xml_object);
  
    }

}


?>