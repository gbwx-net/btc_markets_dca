<?php

    require('./conf.settings.php');
    require('./exchange.php');

    if($_GET['debug'] = "on")
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
    </head>
    <body>
<?php
    date_default_timezone_set('Australia/Sydney');
    echo 'Date: ' . date("D M d, Y G:i a") . '<br>';

    $trader = new BTCMarkets($apiKey, $apiSecret);
    $trader->symbol = $tradeSymbol;
    $trader->account = $tradeAccount;
    $temp_price = $trader->getTicker();
    $currentAsk = $temp_price['ask'];
    $currentBid = $temp_price['bid'];
    

?>
        <pre>
            <?php var_dump($trader); ?>
        </pre>
        <pre>
            <?php var_dump($ch); ?>
        </pre>
    </body>
</html>