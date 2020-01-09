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

    echo 'Last Price: '.$ticker['lastPrice'];

    $accountBalance = $exchange->getBalance('AUD');

    if($accountBalance > $tradeAmount)
    {
        $orderMarketID = 'BTC-AUD';
        $orderPrice = $ticker['bestAsk'];
        $orderAmount = number_format(0.0001, 8); // min amount 0.0001btc
        //$orderAmount = number_format(($tradeAmount / $orderPrice), 8); // commented out for testing
        $orderType = 'Market';
        $orderSide = 'Bid';

        $order = $exchange->createMarketOrder($orderMarketID, $orderPrice, $orderAmount, $orderType, $orderSide);
    }
    else
    {
        echo '$accountBalance < $tradeAmount - '.$accountBalance.' < '.$tradeAmount;
    }
    
?>
    </body>
</html>