<?php
    class BTCMarkets {
        const API_URL = 'https://api.btcmarkets.net';
        const API_PATH = '';

        private $apiKey;
        private $apiSecret;
        private $ch;

        public $error;
        public $printErrors = true;
        public $errorType;
        public $errorCode;
        public $errorMessage;

        public function __construct($apiKey = '', $apiSecret = '') {
            $this->apiKey = $apiKey;
            $this->apiSecret = $apiSecret;
            $this->curlInit();
        }

        private function makeHttpCall($method, $path, $queryString, $dataObj)
        {
            $data = null;
            if(!is_null($dataObj))
            {
                $data = $dataObj;
                if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo 'dataObj: <br><pre>'.var_dump($dataObj).'</pre></br>'; }
                if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo 'data: <br><pre>'.var_dump($data).'</pre></br>'; }
            }

            $headers = $this->buildAuthHeaders($method, $path, $data);
            if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<br>headers: <br><pre>'.var_dump($headers).'</pre><br>'; }

            $fullPath = $path;
            if(!is_null($queryString))
            {
                $fullPath += '?'.$queryString;
            }
            if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<br>fullPath: <br><pre>'.var_dump($fullPath).'</pre><br>'; }

            if($method == "GET") 
            {
                $post = "";
            }
            else 
            {
                $post = $queryString;
            }
            if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<br>post: <br><pre>'.var_dump($post).'</pre><br>'; }

            $url = self::API_URL . self::API_PATH . $path;
            if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<br>url: <br><pre>'.var_dump($url).'</pre><br>'; }

            curl_reset($this->ch);
            curl_setopt($this->ch, CURLOPT_URL, $url);
            if($method == "POST") {
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
            }
            if($method == "DELETE") {
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
                $headers[] = 'X-HTTP-Method-Override: DELETE';
            }
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER , false);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

            if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<br>ch: <br><pre>'.var_dump($this->ch).'</pre><br>'; }

            $return = curl_exec($this->ch);
            if(isset($_GET['debug']) && $_GET['debug'] =='true'){ echo '<br>return: <br><pre>'.var_dump($return).'</pre><br>'; }

            if(!$return) 
            {
                $this->curlError();
                $this->error = true;
                return false;
            }
            
            $return = json_decode($return,true);
            if(isset($return['error'])) 
            {
                $this->platformError($return);
                $this->error = true;
                return false;
            }

            $this->error = false;
            $this->errorCode = false;
            $this->errorMessage = false;

            return $return;
        }

        private function buildAuthheaders($method, $path, $data)
        {
            $now = round(microtime(true) * 1000);
            $message = $method.$path.$now;

            if(!is_null($data))
            {
                $message = $message.$data;
            }

            $signature = $this->signMessage($this->apiSecret, $message);

            $headers = array ();
            $headers[] = "Accept: application/json";
            $headers[] = "Accept-Charset: UTF-8";
            $headers[] = "Content-Type: application/json";
            $headers[] = "BM-AUTH-APIKEY: $this->apiKey";
            $headers[] = "BM-AUTH-TIMESTAMP: $now";
            $headers[] = "BM-AUTH-SIGNATURE: $signature";
            $headers[] = 'Connection: Keep-Alive';
            $headers[] = 'Keep-Alive: 90';

            return $headers;
        }

        private function curlInit() 
        {
            $this->ch = curl_init();
        }

        private function curlError() {
            if ($errno = curl_errno($this->ch)) {
            $this->errorType = 'cURL';
            $this->errorCode = $errno;
            $errorMessage = curl_strerror($errno);
            $this->errorMessage = $errorMessage;
            if($this->printErrors) echo "cURL error ({$errno}) : {$errorMessage}\n";
    
    
            return true;
            }
            return false;
        }

        private function platformError($return) {
            $this->errorType = 'Platform';
            $this->errorCode = $return['error']['name'];
            $this->errorMessage = $return['error']['message'];
            if($this->printErrors) echo "BitMex error ({$return['error']['name']}) : {$return['error']['message']}\n";
    
            return true;
        }

        private function signMessage($privateKey, $message)
        {
            $secret_key_encoded = base64_decode($privateKey);
            $encodedMsg =   hash_hmac('sha512', $message, $secret_key_encoded, true);
            $base64Msg = base64_encode($encodedMsg);

            return $base64Msg;
        }

        public function getTicker($marketId)
        {
            $path = '/v3/markets/'.$marketId.'/ticker';
            $return = $this->makeHttpCall('GET', $path, null, null);

            return $return;
        }

        public function getBalance($marketId)
        {
            $path = '/v3/accounts/me/balances';
            $return = $this->makeHttpCall('GET', $path, null, null);

            for($i=0; $i<(count($return)); $i++)
            {
                if($return[$i]['assetName'] == $marketId)
                {
                    $balance = $return[$i]['available'];
                }
            }

            return $balance;
        }

        public function createMarketOrder($orderMarketID, $orderPrice, $orderAmount, $orderType, $orderSide) 
        {    
            $path = '/v3/orders';
            $data = array(
                "marketId" => $orderMarketID,
                "price" => $orderPrice,
                "amount" => $orderAmount,
                "type" => $orderType,
                "side" => $orderSide
            );

            $data = json_encode($data);
            $return = $this->makeHttpCall('POST', $path, null, $data);

            return var_dump($return);//$return;
        }
    }
?>