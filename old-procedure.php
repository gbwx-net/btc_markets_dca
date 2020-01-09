<?php
    /*
    * Dollar Cost Average Bot for:
    * BTC Markets PHP REST API v3
    *
    * @author birdman
    * @version 0.1
    * @link https://github.com/gbwx-net/btc_markets_dca
    * 
    * Further Reading - https://github.com/BTCMarkets/API
    */

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('Australia/Sydney');

    require('BTCMarkets.php');
    require('conf.settings.php');
?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
<?php
    
    echo 'Date: ' . date("D M d, Y G:i a") . '<br>';

    $url = 'https://api.btcmarkets.net';

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
    $gets = array(
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

    $posts = array(
        'http'=>array(
              'method'=>"POST",
              'header'=>      "Accept: */*\r\n" .
                              "Accept-Charset: UTF-8\r\n" .
                              "Content-Type: application/json\r\n" .
                              "BM-AUTH-APIKEY: " . $public_key . "\r\n" .
                              "BM-AUTH-TIMESTAMP: " . $milliseconds . "\r\n" .
                              "BM-AUTH-SIGNATURE: " . $base64Msg . "\r\n"
        )
    );
    
    $context = stream_context_create($gets);
    //if(isset($_GET['debug']) && $_GET['debug'] == 'true'){ echo '<pre>'.var_dump($gets).'</pre>'; }
    
    // Open the file using the HTTP headers set above
    $fileGet = file_get_contents('https://api.btcmarkets.net/account/balance', false, $context);
    $jsonAccount = json_decode($fileGet, true);
    echo '$'.(($jsonAccount[0]['balance'])/100000000).' '.$jsonAccount[0]['currency'];
    //if(isset($_GET['debug']) && $_GET['debug'] == 'true'){ echo '<pre>'.var_dump($jsonAccount).'</pre>'; }

    // Get BTC-AUD Price
    $fileGet = file_get_contents('https://api.btcmarkets.net/v3/markets/BTC-AUD/ticker', false, $context);
    $jsonTicker = json_decode($fileGet, true);
    //if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<pre>'.var_dump($jsonTicker).'</pre>'; }

    // Setup market Purchase
    $path = '/v3/orders';
    $orderMarketID = 'BTC-AUD';
    $orderPrice = $jsonTicker['lastPrice'];
    $orderAmount = number_format(($tradeAmount / $orderPrice), 8);
    $orderType = 'Market';
    $orderSide = 'Bid';

    

    $orderData = array(
        "marketId" => $orderMarketID,
        "price" => $orderPrice,
        "amount" => $orderAmount,
        "type" => $orderType,
        "side" => $orderSide
    );


    $milliseconds = round(microtime(true) * 1000);

    $msg = "POST\n"."/v3/orders\n" . $milliseconds . "\n" . json_encode($orderData)."\n";
    echo "Message is: " . $msg ."<br>";

    $encodedMsg =   hash_hmac('sha512', $msg, $secret_key_encoded, true);
    $base64Msg = base64_encode($encodedMsg);
    echo "Encoded Message is:  " . $base64Msg . "<br>";


    $context = stream_context_create($posts);
    $fileGet = file_get_contents('https://api.btcmarkets.net/v3/orders', false, $context);
    if(isset($_GET['debug']) && $_GET['debug'] == 'true'){ echo '<pre>'.var_dump($posts).'</pre>'; }

    $jsonOrder = json_decode($fileGet, true);
    if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<pre>'.var_dump($jsonOrder).'</pre>'; }


?>
    </body>
</html>