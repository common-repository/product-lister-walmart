<?php
class CedWalmartCurlRequest
{
	public $serverUrl;

	public function __construct()
	{
		$this->serverUrl = 'http://demo.cedcommerce.com/woocommerce/marketplaces/walmart/marketplaces-walmart.php';
	}

	public function sendHttpRequest( $requestBody )
	{
		$requestBody = array(
			'method' 		=> 'POST',
			'timeout' 		=> 45,
			'redirection' 	=> 5,
			'httpversion' 	=> '1.0',
			'blocking' 		=> true,
			'headers' 		=> array(),
			'body' 			=> $requestBody,
			'cookies' 		=> array()
	    );
		$response = wp_remote_post( $this->serverUrl, $requestBody );
		$response = isset( $response['body'] ) ? json_decode($response['body'], true) : array();
		return $response;
	}
}
?>