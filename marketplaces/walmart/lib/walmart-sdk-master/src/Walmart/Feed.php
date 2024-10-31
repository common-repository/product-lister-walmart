<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 9/4/19
 * Time: 1:30 PM
 */

namespace Walmart;

use Walmart\Exception\WalmartRequestException;

class Feed extends Api
{

    const GET_FEED_SUB_URL = 'v3/feeds';

    /**
     * Feed Constructor
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct(
            $config
        );
    }

    /**
     * @param array $params
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function getAllFeed($params = [], $headers = [], $subUrl = self::GET_FEED_SUB_URL)
    {
        if (!isset($params['limit']) || empty($params['limit'])) {
            $params['limit'] = '50';
        }
        if (!isset($params['offset']) || empty($params['offset'])) {
            $params['offset'] = '0';
        }

        $queryString = empty($params) ? '' : '?' . http_build_query($params);

        $response = $this->call('GET', $subUrl . $queryString, $params, $headers);
        
        return $response;
    }


    /**
     * $feedId is required
     * @param string $feedId
     * @param array $params
     * @param array $headers
     * @param string $subUrl
     * @return array
     */
    public function getAnFeed($feedId, $params = [], $headers = [], $subUrl = self::GET_FEED_SUB_URL)
    {
        if ($feedId == '') {
            $error_message = 'Invalid / Blank Feed Id';
            throw new WalmartRequestException($error_message);
        }
        if (!isset($params['limit']) || empty($params['limit'])) {
            $params['limit'] = '50';
        }
        if (!isset($params['offset']) || empty($params['offset'])) {
            $params['offset'] = '0';
        }

        $queryString = empty($params) ? '' : '?' . http_build_query($params);
        $url = $subUrl . '/' . $feedId . $queryString;

        $response = $this->call('GET', $url, $params, $headers);

        return $response;

    }

}