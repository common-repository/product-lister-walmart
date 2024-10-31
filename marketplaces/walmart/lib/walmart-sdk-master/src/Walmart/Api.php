<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 9/4/19
 * Time: 3:14 PM
 */

namespace Walmart;

class Api
{

    private $_consumerId;
    private $_secretKey;
    private $_consumerChannelId;

    private $_clientId;
    private $_clientSecret;
    private $_token;

    public $_signatureBased = false;
    private $_apiSignature;
    public $_apiHelper;

    private $_apiUrl;
    private $_lastResponseHeaders = null;

    /**
     * Api Constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        //var_dump('config',$config);
        $this->_apiUrl = "https://marketplace.walmartapis.com/";
        if (isset($config['consumer_id']) && isset($config['secret_key'])) {

            $this->_signatureBased = true;
            $this->_consumerId = trim($config['consumer_id']);
            $this->_secretKey = trim($config['secret_key']);
            $this->_consumerChannelId = '7b2c8dab-c79c-4cee-97fb-0ac399e17ade';
            $this->_apiSignature = new Signature();

        } elseif (isset($config['client_id']) && isset($config['client_secret'])) {

            $this->_clientId = $config['client_id'];
            $this->_clientSecret = $config['client_secret'];
            $this->_token = isset($config['token']) ? $config['token'] : '';
        }

        $this->_apiHelper = new ApiHelper();

    }

    /**
     * @param $method
     * @param $path
     * @param array $params
     * @param array $headers
     * @return array
     */
    public function call($method, $path, $params = array(), $headers = array())
    {
        if ($method == 'GET') {
            $response = $this->getRequestCall($path, $headers);
        } elseif ($method == 'DELETE') {
            $response = $this->deleteRequestCall($path, $headers);
        } elseif ($method == 'POST') {
            $response = $this->postRequestCall($path, $params, $headers);
        } elseif ($method == 'PUT') {
            $response = $this->putRequestCall($path, $params, $headers);
        }

        if (substr($response, 0, strlen('<?xml version')) === '<?xml version') {
            $responseArray = $this->_apiHelper->xmlToArray($response);
        } elseif ($this->_apiHelper->is_json($response)) {
            $responseArray = json_decode($response, true);
        } else {
            $responseArray = ['errors' => $response];
        }

        $responseArray = $this->_apiHelper->replaceNs($responseArray);

        if (isset($responseArray['errors'])) {
            if (isset($responseArray['errors']['error']['code'])) {
                $returnArray = ['status' => false, 'error' => $responseArray['errors']['error']['code'], 'original_response' => $responseArray];
            } else {
                $returnArray = ['status' => false, 'error' => $responseArray['errors'], 'original_response' => $responseArray];
            }
        } else {
            $returnArray = ['status' => true, 'data' => $responseArray];
        }

        return $returnArray;
    }

    /**
     * @param $url
     * @param array $headers
     * @return bool|string
     */
    public function postRequestCall($url, $params = [], $headers = [])
    {
       // $body = [];
        if (isset($params['file'])) {
            $body['file'] = new \CurlFile($params['file'], 'application/xml');
            $headers['Content-Type'] = 'multipart/form-data';
        } elseif (isset($params['data'])) {
            $body = $params['data'];
        }

        $headers = $this->getHeaders($url, 'POST', $headers);
        $curlHeader = [];

        foreach ($headers as $headerKey => $headerValue) {
            $curlHeader[] = $headerKey . ': ' . $headerValue;
        }

        $url = $this->_apiUrl . $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeader);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, NULL);
        }
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $server_output = curl_exec($ch);
       
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $response = $this->curlParseHeaders($server_output, $header_size);
       
        return $response;
    }

    /**
     * @param $url
     * @param array $headers
     * @return bool|string
     */
    public function putRequestCall($url, $params = [], $headers = [])
    {
        $body = [];
        if (isset($params['file'])) {
            $body['file'] = new \CurlFile($params['file'], 'application/xml');
            $headers['Content-Type'] = 'multipart/form-data';
        } elseif (isset($params['data'])) {
            $body = $params['data'];
        }

        $headers = $this->getHeaders($url, 'PUT', $headers);
        $curlHeader = [];

        foreach ($headers as $headerKey => $headerValue) {
            $curlHeader[] = $headerKey . ': ' . $headerValue;
        }

        $url = $this->_apiUrl . $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeader);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, NULL);
        }
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $server_output = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $response = $this->curlParseHeaders($server_output, $header_size);

        return $response;
    }

    /**
     * @param $url
     * @param array $headers
     * @return bool|string
     */
    public function deleteRequestCall($url, $headers = [])
    {

        $headers = $this->getHeaders($url, 'DELETE', $headers);
        $curlHeader = [];

        foreach ($headers as $headerKey => $headerValue) {
            $curlHeader[] = $headerKey . ': ' . $headerValue;
        }

        $url = $this->_apiUrl . $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeader);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $server_output = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $response = $this->curlParseHeaders($server_output, $header_size);

        return $response;
    }

    /**
     * @param $url
     * @param array $headers
     * @return array
     */
    private function getHeaders($url, $method, $headers = [])
    {
        if ($this->_signatureBased) {

            $signature = $this->_apiSignature->getSignature($url, $method, $this->_consumerId, $this->_secretKey);

            $defaultHeader = [
                'WM_SVC.NAME' => 'Walmart Marketplace',
                'WM_QOS.CORRELATION_ID' => base64_encode(\phpseclib\Crypt\Random::string(16)),
                'WM_SEC.TIMESTAMP' => $this->_apiSignature->timestamp,
                'WM_SEC.AUTH_SIGNATURE' => $signature,
                'WM_CONSUMER.ID' => $this->_consumerId,
                'WM_CONSUMER.CHANNEL.TYPE' => $this->_consumerChannelId,
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml',
                'HOST' => 'marketplace.walmartapis.com',
            ];
        } else {

            $defaultHeader = [
                'Authorization' => "Basic " . base64_encode($this->_clientId . ':' . $this->_clientSecret),
                'Content-Type' => 'application/xml',
                'WM_SVC.NAME' => 'Walmart Marketplace',
                'WM_SEC.ACCESS_TOKEN' => $this->_token,
                'Accept' => 'application/xml',
                'HOST' => 'marketplace.walmartapis.com',
                'WM_QOS.CORRELATION_ID' => base64_encode(\phpseclib\Crypt\Random::string(16)),
            ];

        }

        $headers = array_merge($defaultHeader, $headers);

        return $headers;
    }

    /**
     * @param $server_output
     * @param $header_size
     * @return bool|string
     */
    private function curlParseHeaders($server_output, $header_size)
    {
        $header = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        $this->_lastResponseHeaders = $header;

        return $response;
    }


    /**
     * @return array
     */
    public function generateToken()
    {
        $url = $this->_apiUrl . 'v3/token';
        $body = [];
        $headers = [];
        $headers[] = "Authorization: Basic " . base64_encode($this->_clientId . ':' . $this->_clientSecret);
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Accept: application/xml";
        $headers[] = "WM_SVC.NAME: Walmart Marketplace";
        $headers[] = "WM_QOS.CORRELATION_ID: " . base64_encode(\phpseclib\Crypt\Random::string(16));
        $headers[] = "WM_SVC.VERSION: 1.0.0";
        $body = "grant_type=client_credentials";

        $ch = curl_init($url);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, NULL);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $server_output = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $response = $this->curlParseHeaders($server_output, $header_size);

        $response = $this->_apiHelper->replaceNs($response);

        if (substr($response, 0, strlen('<?xml version')) === '<?xml version') {
            $responseArray = $this->_apiHelper->xmlToArray($response);
        } elseif (substr($response, 0, strlen('<OAuthTokenDTO')) === '<OAuthTokenDTO') {
            $responseArray = $this->_apiHelper->xmlToArray($response);
        } else {
            $responseArray = ['errors' =>  $this->_apiHelper->xmlToArray($response)];
        }

        return $responseArray;
       
        if (isset($responseArray['errors'])) {
            if (isset($responseArray['errors']['error']['code'])) {
                $returnArray = ['status' => false, 'error' => $responseArray['errors']['error']['code'], 'original_response' => $responseArray];
            } else {
                $returnArray = ['status' => false, 'error' => $responseArray['errors'], 'original_response' => $responseArray];
            }
        } else {
            $returnArray = ['status' => true, 'data' => $responseArray];
        }
        return $returnArray;
    }

    /**
     * @return null
     */
    public function getLastResponseHeaders()
    {
        return $this->_lastResponseHeaders;
    }

    public function getWalmartReport($method, $url, $params = array(), $headers = array())
    {

        $headers = $this->getHeaders($url, $method, $headers);
        $curlHeader = [];

        foreach ($headers as $headerKey => $headerValue) {
            $curlHeader[] = $headerKey . ': ' . $headerValue;
        }

        $url = $this->_apiUrl . $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeader);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $server_output = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $response = $this->curlParseHeaders($server_output, $header_size);
        return $response;
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($httpcode == 200)
        {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            if(!isset($params['filePath']) && $params['filePath'] == ''){
                $filePath = __DIR__.'/item/item.zip';
            }

            if(!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0775, true);
            }

            $response = substr($server_output, $header_size);

            $file = fopen($filePath, "w");
            fwrite($file, $response);
            fclose($file);

            if($params['downloadable'])
            {
                $fileName = 'item.zip';
                if(strpos($filePath, $fileName)===false) {
                    $filePath = $filePath.$fileName;
                }

                header("Content-Description: File Transfer");
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$fileName.'"');
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: ".filesize($filePath));
                header('Connection: close');
                @readfile($filePath);
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
        curl_close($ch);

    }

}