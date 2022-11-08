<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>


<?php



function randomName() {
    $firstname = array(
        'Johnathon',
        'Anthony',
        'Erasmo',
        'Raleigh',
        'Nancie',
        'Tama',
        'Camellia',
        'Augustine',
        'Christeen',
        'Luz',
        'Diego',
        'Lyndia',
        'Thomas',
        'Georgianna',
        'Leigha',
        'Alejandro',
        'Marquis',
        'Joan',
        'Stephania',
        'Elroy',
        'Zonia',
        'Buffy',
        'Sharie',
        'Blythe',
        'Gaylene',
        'Elida',
        'Randy',
        'Margarete',
        'Margarett',
        'Dion',
        'Tomi',
        'Arden',
        'Clora',
        'Laine',
        'Becki',
        'Margherita',
        'Bong',
        'Jeanice',
        'Qiana',
        'Lawanda',
        'Rebecka',
        'Maribel',
        'Tami',
        'Yuri',
        'Michele',
        'Rubi',
        'Larisa',
        'Lloyd',
        'Tyisha',
        'Samatha',
    );

    $lastname = array(
        'Mischke',
        'Serna',
        'Pingree',
        'Mcnaught',
        'Pepper',
        'Schildgen',
        'Mongold',
        'Wrona',
        'Geddes',
        'Lanz',
        'Fetzer',
        'Schroeder',
        'Block',
        'Mayoral',
        'Fleishman',
        'Roberie',
        'Latson',
        'Lupo',
        'Motsinger',
        'Drews',
        'Coby',
        'Redner',
        'Culton',
        'Howe',
        'Stoval',
        'Michaud',
        'Mote',
        'Menjivar',
        'Wiers',
        'Paris',
        'Grisby',
        'Noren',
        'Damron',
        'Kazmierczak',
        'Haslett',
        'Guillemette',
        'Buresh',
        'Center',
        'Kucera',
        'Catt',
        'Badon',
        'Grumbles',
        'Antes',
        'Byron',
        'Volkman',
        'Klemp',
        'Pekar',
        'Pecora',
        'Schewe',
        'Ramage',
    );

    $name = $firstname[rand ( 0 , count($firstname) -1)];
    $name .= ' ';
    $name .= $lastname[rand ( 0 , count($lastname) -1)];

    return $name;
}


//some var

$user_name_by_sbl = "a2i@pmo";
$user_password_by_sbl = "sbPayment0002";
$owner_code_by_sbl = "du";
$api_url_by_sbl = "https://spg.sblesheba.com:6314"; //uat
$UI_url_by_sbl = "https://spg.sblesheba.com:6313/SpgLanding/SpgLanding/"; //uat
$request_id_1st_3_by_sbl = "099";

//client var
$request_id = $request_id_1st_3_by_sbl . rand( 1111111, 9999999 );
$reference_no = 'IMU-'.rand( 1111111, 9999999 );
$exReference_no = rand( 1111111, 9999999 );
$applicentName = randomName();
$applicentContactNo = "01710".rand( 111111, 999999 );;

$invoiceDate = date( "Y-m-d" );
$credit_account = "0002601020864";
$credit_amt_on_account = rand( 111, 999 );

$total_tran_amount = $credit_amt_on_account;
$return_url = "http://localhost:8080/api-sbl";

//1st api ...............
$url = $api_url_by_sbl . "/api/v2/SpgService/GetAccessToken"; //uat

$data = array(
    "AccessUser"   => array(
        "userName" => $user_name_by_sbl,
        "password" => $user_password_by_sbl,
    ),
    "invoiceNo" => $reference_no,
    "amount" => $credit_amt_on_account,
    "invoiceDate" => $invoiceDate, 
    "accounts" => [array(
        "crAccount" => $credit_account,
        "crAmount" => $credit_amt_on_account
    )]
);

// print_r($data);

$CURLOPT_HTTPHEADER = array( 'Content-Type: application/json' , 'Authorization: Basic ZHVVc2VyMjAxNDpkdVVzZXJQYXltZW50MjAxNA==');
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
    CURLOPT_POSTFIELDS     => json_encode( $data ),
);

// print_r( $options);
// die();

$ch = curl_init( $url );
curl_setopt_array( $ch, $options );
$content = curl_exec( $ch );
curl_close( $ch );

$split = explode(":",$content);

$AccessTocen = $split[3];
$brackBad = explode("}",$AccessTocen);
$onlyToken = $brackBad[0];

$onlyTokenwithOutQuotes = explode('"',$onlyToken);
$acToken = $onlyTokenwithOutQuotes[1];
// die();
if( $acToken == "Authorization is not valid" ||  $acToken == ""){
    echo "get session key error";
    exit();
}
else{


    //2nd api
    $url2 = $api_url_by_sbl . "/api/v2/SpgService/CreatePaymentRequest"; //uat
    $data = array(
        "authentication"   => array(
            "apiAccessUserId" => $user_name_by_sbl,
            "apiAccessToken" => $acToken,
        ),

        "referenceInfo"   => array(
            "InvoiceNo" => $reference_no, 
            "invoiceDate" => $invoiceDate,
            "returnUrl" => "http://localhost",
            "totalAmount" => $credit_amt_on_account,
            "applicentName" => $applicentName, 
            "applicentContactNo" => $applicentContactNo,
            "extraRefNo" => "2132"
        ),

        "invoiceNo" => $reference_no,
        "amount" => $credit_amt_on_account,
        "invoiceDate" => $invoiceDate, 

        "creditInformations" => [array(
            "slno" => "1",
            "crAccount" => $credit_account,
            "crAmount" => $credit_amt_on_account,
            "tranMode" => "TRA"
        )]
    );



    $CURLOPT_HTTPHEADER = array( 'Content-Type: application/json', 'Authorization: Basic ZHVVc2VyMjAxNDpkdVVzZXJQYXltZW50MjAxNA==');

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

    $split = explode(":",$content);
    $SessionTocen = $split[2];

    $onlySesWithOutQuotes = explode('"',$SessionTocen);
    $finalSession = $onlySesWithOutQuotes[1];
    // echo $finalSession; 
    // echo "<br>";
    // echo $split[1];
    // echo "<br>";
    // echo $SessionTocen;

    $return_url_new = $UI_url_by_sbl . $finalSession; 
    header('Location: '.$return_url_new);

    // if($split[1] == 200 ){
    //    echo $return_url_new = $UI_url_by_sbl . $finalSession; 
    //     header('Location: '.$return_url_new);


    //     $response_xml = $_REQUEST['Request'];
    //     $xml_object = simplexml_load_string($response_xml);


    //     var_dump( $xml_object);

        
    //     // responde xml to array
    //    // $response_array = objectsIntoArray($xml_object);
  
    // }

}


?>

</body>
</html>