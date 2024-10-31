<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 9/4/19
 * Time: 1:28 PM
 */

namespace Walmart;

class Item extends Api
{
    const GET_ITEMS_SUB_URL = 'v3/items';
    const GET_ITEM_UPLOAD_URL = 'v3/feeds?feedType=item';

    public $testMode = false;

    /**
     * Item Constructor
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if(isset($config['consumer_id']) && $config['consumer_id']=='cedcommerce-consumer-id')
        {
            $this->testMode = true;
        }elseif (isset($config['client_id']) && $config['client_id']=='cedcommerce-client-id')
        {
            $this->testMode = true;
        }
        parent::__construct(
            $config
        );
    }

    /**
     * @param array $params
     * @param string $subUrl
     * @return array
     */
    public function getAllItem($params = [], $headers = [], $subUrl = self::GET_ITEMS_SUB_URL)
    {
        if (!isset($params['limit']) || empty($params['limit'])) {
            $params['limit'] = '20';
        }
        if (!isset($params['offset']) || empty($params['offset'])) {
            $params['offset'] = '0';
        }

        $queryString = empty($params) ? '' : '?' . http_build_query($params);
      
        $response = $this->call('GET', $subUrl . $queryString, $params, $headers);

        return $response;
    }

    /**
     * function to get an item from walmart
     * required $sku
     * @param array $params
     * @param string $subUrl
     * @return array
     */
    public function getAnItem($sku , $headers = [], $subUrl = self::GET_ITEMS_SUB_URL)
    {
        $response = $this->call('GET', $subUrl . '/' . $sku, [], $headers);

        return $response;
    }

    /**
     * function to remove / retire from walmart
     * @param  $sku
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function deleteAnItem($sku, $headers = [], $subUrl = self::GET_ITEMS_SUB_URL)
    {
        $response = $this->call('DELETE', $subUrl . '/' . $sku, [], $headers);

        return $response;
    }

    /**
     * $params['file'] is required
     * @param array $params
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function uploadItems($params = [], $headers = [], $subUrl = self::GET_ITEM_UPLOAD_URL)
    {
        $response = $this->call('POST', $subUrl, $params, $headers);

        return $response;
    }

}