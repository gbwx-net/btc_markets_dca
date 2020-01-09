<?php
    /*
    * Dollar Cost Average Bot for:
    * BTC Markets PHP REST API v3
    *
    * @author birdman
    * @version 0.2
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

    $exchange = new BTCMarkets($apiKey, $apiSecret);
    $ticker = $exchange->getTicker($tradeSymbol);
    //if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<pre>'.var_dump($exchange).'</pre>'; }
    //if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<pre>'.var_dump($ticker).'</pre>'; }

    echo 'Last Price: '.$ticker['lastPrice'];

    $accountBalance = (($exchange->getBalance('AUD'))/100000000);

    if($accountBalance > $tradeAmount)
    {
        $orderMarketID = 'BTC-AUD';
        $orderPrice = $ticker['bestAsk'];
        $orderAmount = number_format(($tradeAmount / $orderPrice), 8);
        $orderAmountString = (string)$orderAmount;
        $orderType = 'Market';
        $orderSide = 'Bid';
    }

    $order = $exchange->createMarketOrder($orderMarketID, $orderPrice, $orderAmountString, $orderType, $orderSide);
?>
    </body>
</html>