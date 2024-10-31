<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 9/4/19
 * Time: 1:27 PM
 */

namespace Walmart;

class Promotion extends Api
{

    const GET_PROMOTION_SUB_URL = 'v3/promo';
    const PUT_PROMOTION_SUB_URL = 'v3/price?promo=true';
    const POST_PROMOTION_SUB_URL = 'v3/feeds?feedType=promo';

    /**
     * Price Constructor
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct(
            $config
        );
    }

    /**
     * function to get single promotion from Walmart.com
     * @param array $params
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function getPromotion($sku, $headers = [], $subUrl = self::GET_PROMOTION_SUB_URL)
    {

        $queryString = empty($sku) ? '' : '/sku/' . urlencode($sku);

        $response = $this->call('GET', $subUrl . $queryString, [], $headers);

        return $response;
    }

    /**
     * @param $param
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function putPromotion($param, $headers = [], $subUrl = self::PUT_PROMOTION_SUB_URL)
    {
        $response = $this->call('PUT', $subUrl, $param, $headers);

        return $response;
    }


    /**
     * @param array $params
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function postPromotion($params = [], $headers = [], $subUrl = self::POST_PROMOTION_SUB_URL)
    {
        $response = $this->call('POST', $subUrl, $params, $headers);

        return $response;
    }


}