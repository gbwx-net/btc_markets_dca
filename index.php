<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('Australia/Sydney');

    require('conf.settings.php');
?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
<?php
    
    echo 'Date: ' . date("D M d, Y G:i a") . '<br>';

    $secret_key = $apiSecret;
    $secret_key_encoded = base64_decode($secret_key);
    $public_key = $apiKey;
    $milliseconds = round(microtime(true) * 1000);

    $msg = "/account/balance\n" . $milliseconds . "\n";
    echo "Message is: " . $msg ."<br>";

    $encodedMsg =   hash_hmac('sha512', $msg, $secret_key_encoded, true);
    $base64Msg = base64_encode($encodedMsg);
    echo "Encoded Message is:  " . $base64Msg . "<br>";

    // Create a stream
    $opts = array(
        'http'=>array(
              'method'=>"GET",
              'header'=>      "Accept: */*\r\n" .
                              "Accept-Charset: UTF-8\r\n" .
                              "Content-Type: application/json\r\n" .
                              "apikey: " . $public_key . "\r\n" .
                              "timestamp: " . $milliseconds . "\r\n" .
                              "User-Agent: btc markets php client\r\n" .
                              "signature: " . $base64Msg . "\r\n"
        )
    );

    $context = stream_context_create($opts);
    if(isset($_GET['debug']) && $_GET['debug'] == 'true'){ echo '<pre>'.var_dump($opts).'</pre>'; }
    
    // Open the file using the HTTP headers set above
    $fileGet = file_get_contents('https://api.btcmarkets.net/account/balance', false, $context);
    $jsonAccount = json_decode($fileGet, true);
    if(isset($_GET['debug']) && $_GET['debug'] == 'true'){ echo '<pre>'.var_dump($jsonAccount).'</pre>'; }

    // Get BTC-AUD Price
    $fileGet = file_get_contents('https://api.btcmarkets.net/v3/markets/BTC-AUD/ticker', false, $context);
    $jsonTicker = json_decode($fileGet, true);
    if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<pre>'.var_dump($jsonTicker).'</pre>'; }

    echo '$'.(($jsonAccount[0]['balance'])/100000000).' '.$jsonAccount[0]['currency'];
?>
    </body>
</html>