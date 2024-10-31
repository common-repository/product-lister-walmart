<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 11/4/19
 * Time: 5:05 PM
 */

namespace Walmart;


use Walmart\Xml\Parser;

class ApiHelper
{

    /**
     * @param $response
     * @return mixed
     */
    public function replaceNs($response)
    {

        $response = str_replace('ns1:', '', $response);
        $response = str_replace('ns2:', '', $response);
        $response = str_replace('ns3:', '', $response);
        $response = str_replace('ns4:', '', $response);
        $response = str_replace('ns5:', '', $response);
        $response = str_replace('ns:2', '', $response);
        $response = str_replace('ns:3', '', $response);
        $response = str_replace('ns:4', '', $response);
        $response = str_replace(':ns2', '', $response);
        $response = str_replace(':ns3', '', $response);
        $response = str_replace(':ns4', '', $response);

        return $response;
    }

    /**
     * @param $xml
     * @param bool $inventory
     * @return mixed
     */
    public function xmlToArray($xml)
    {
        $parser = new Parser();
        $data = $parser->loadXML($xml)->xmlToArray();
        $data = $this->replaceString($data);
        return $data;
    }

    /**
     * @param array $data
     * @return array|mixed
     */
    public function replaceString($data = [])
    {
        if (is_array($data)) {
            $string = json_encode($data);
            $string = preg_replace('/(ns\d:)+/', '', $string);
            $data = json_decode($string, true);
        }
        return $data;
    }

    /**
     * @param $string
     * @return bool
     */
    public function is_json($string)
    {
        try {
            $data = json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE) ?: FALSE;
        } catch (\Exception $e) {
            return false;
        }
    }

}