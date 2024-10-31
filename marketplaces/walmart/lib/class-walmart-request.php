<?php

/**
 * Credit to : https://github.com/fillup/walmart-auth-signature-php
 */

/**
 * WalmartRequest Class to handle different rquests.
 *
 * @class WalmartRequest
 *
 * @version 1.0.0
 */
if (! class_exists ( 'WalmartRequest' )) {
	class WalmartRequest {
		/**
		 * WalmartRequest Constructor.
		 */
		public function __construct($consumerId = "", $privateKey = "") {
			$this->consumerId = $consumerId;
			$this->privateKey = $privateKey;
		}
		
		/**
		 * Function to handle GET type Request
		 *
		 * @name get
		 *      
		 * @param srting $endPoint        	
		 * @param array $headers        	
		 * @param string $requestUrl        	
		 * @param string $signature        	
		 * @param integer $timestamp        	
		 * @param string $cor_id        	
		 * @return array
		 * @since 1.0.0
		 *       
		 */
		public function get($endPoint, $headers, $signature, $timestamp, $cor_id) {
			if ($endPoint == "" || $endPoint == null || $headers == "" || $headers == null || $signature == "" || $signature == null || $timestamp == "" || $timestamp == null || $cor_id == "" || $cor_id == null) {
				return false;
			}
			
			$requestUrl = "https://marketplace.walmartapis.com/$endPoint";
			$ch = curl_init ();
			
			curl_setopt ( $ch, CURLOPT_URL, $requestUrl );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $ch, CURLOPT_CAINFO, ABSPATH . WPINC . '/certificates/ca-bundle.crt' );
			
			$server_output = curl_exec ( $ch );
		    $header_size = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
			$header = substr ( $server_output, 0, $header_size );
			$response = substr ( $server_output, $header_size );
			$error_number =  curl_errno($ch);
			curl_close ( $ch );
			if ($error_number > 0) {
				return curl_error ( $ch );
			}
			return $response = $this->parseResponse ( $response );
			$response = $this->parseResponse ( $response );
		}
		
		/**
		 * Function to handle POST type Request
		 *
		 * @name post
		 *      
		 * @param srting $endPoint        	
		 * @param array $headers        	
		 * @param string $requestUrl        	
		 * @param string $signature        	
		 * @param integer $timestamp        	
		 * @param string $cor_id        	
		 * @return array
		 * @since 1.0.0
		 *       
		 */
		public function post($endPoint, $headers, $signature, $timestamp, $cor_id, $file) {
			
			if ($endPoint == "" || $endPoint == null || $headers == "" || $headers == null || $signature == "" || $signature == null || $timestamp == "" || $timestamp == null || $cor_id == "" || $cor_id == null || $file == "" || $file == null) {
				return false;
			}
			$requestUrl = "https://marketplace.walmartapis.com/$endPoint";
			$body ['file'] = new \CurlFile ( $file, 'application/xml' );
			
			
			
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $requestUrl );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $body );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$server_output = curl_exec ( $ch );
			$header_size = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
			$header = substr ( $server_output, 0, $header_size );
			$response = substr ( $server_output, $header_size );
			
			$error_number =  curl_errno($ch);
			curl_close ( $ch );
			if ($error_number > 0) {
				return curl_error ( $ch );
			}
			
			return $response = $this->parseResponse ( $response );
		}
		
		/**
		 * Function to handle POST type Request
		 *
		 * @name post
		 *
		 * @param srting $endPoint
		 * @param array $headers
		 * @param string $requestUrl
		 * @param string $signature
		 * @param integer $timestamp
		 * @param string $cor_id
		 * @return array
		 * @since 1.0.0
		 *
		 */
		public function postCancel($endPoint, $headers, $signature, $timestamp, $cor_id, $xml) {
			
			if ($endPoint == "" || $endPoint == null || $headers == "" || $headers == null || $signature == "" || $signature == null || $timestamp == "" || $timestamp == null || $cor_id == "" || $cor_id == null || $xml == "" || $xml == null) {
				return false;
			}
			$requestUrl = "https://marketplace.walmartapis.com/$endPoint";
			
			$body = $xml;
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $requestUrl );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $body );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$server_output = curl_exec ( $ch );
			$header_size = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
			$header = substr ( $server_output, 0, $header_size );
			$response = substr ( $server_output, $header_size );
			
			$error_number =  curl_errno($ch);
			curl_close ( $ch );
			if ($error_number > 0) {
				return curl_error ( $ch );
			}
			
			return $response = $this->parseResponse( $response );
		}
		
		/**
		 * Function to handle retire(request to delete item from walmart) request
		 *
		 * @name retire
		 *      
		 * @param srting $endPoint        	
		 * @param array $headers        	
		 * @param string $requestUrl        	
		 * @param string $signature        	
		 * @param integer $timestamp        	
		 * @param string $cor_id        	
		 * @return array
		 * @since 1.0.0
		 *       
		 */
		public function retire($endPoint, $headers, $signature, $timestamp, $cor_id) {
			if ($endPoint == "" || $endPoint == null || $headers == "" || $headers == null || $signature == "" || $signature == null || $timestamp == "" || $timestamp == null || $cor_id == "" || $cor_id == null) {
				return false;
			}
			$requestUrl = "https://marketplace.walmartapis.com/$endPoint";
			
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $requestUrl );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$server_output = curl_exec ( $ch );
			$header_size = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
			$header = substr ( $server_output, 0, $header_size );
			$response = substr ( $server_output, $header_size );
			$error_number =  curl_errno($ch);
			curl_close ( $ch );
			if ($error_number > 0) {
				return curl_error ( $ch );
			}
			
			return $response = $this->parseResponse ( $response );
		}
		
		/**
		 * Function to handle update inventory
		 *
		 * @name updateinventory
		 *      
		 * @param srting $endPoint        	
		 * @param array $headers        	
		 * @param string $requestUrl        	
		 * @param string $signature        	
		 * @param integer $timestamp        	
		 * @param string $cor_id        	
		 * @return array
		 * @since 1.0.0
		 *       
		 */
		public function updateinventory($endPoint, $headers, $signature, $timestamp, $cor_id, $file) {
			if ($endPoint == "" || $endPoint == null || $headers == "" || $headers == null || $signature == "" || $signature == null || $timestamp == "" || $timestamp == null || $cor_id == "" || $cor_id == null || $file == "" || $file == null) {
				return false;
			}
			
			$requestUrl = "https://marketplace.walmartapis.com/$endPoint";
			
			$body ['file'] = new \CurlFile ( $file, 'application/xml' );
			
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $requestUrl );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$server_output = curl_exec ( $ch );
			$header_size = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
			$header = substr ( $server_output, 0, $header_size );
			$response = substr ( $server_output, $header_size );
			$error_number =  curl_errno($ch);
			curl_close ( $ch );
			if ($error_number > 0) {
				return curl_error ( $ch );
			}
			
			return $response = $this->parseResponse ( $response );
		}
		
	 
		
		/**
		 * Function to get digital signature
		 *
		 * @name getsignature
		 *      
		 * @param srting $consumerid        	
		 * @param string $privatekey        	
		 * @param string $endPoint        	
		 * @param string $requestMethod        	
		 * @param int $timestamp        	
		 * @return string
		 * @since 1.0.0
		 *       
		 */
		public function getsignature($consumerid, $privatekey, $endPoint, $requestMethod, $timestamp) {
			if ($consumerid == "" || $consumerid == null || $privatekey == "" || $privatekey == null || $endPoint == "" || $endPoint == null || $requestMethod == "" || $requestMethod == null) {
				return false;
			}
			$requestUrl = "https://marketplace.walmartapis.com/$endPoint";
			try {
				$signature = new Signature ( $consumerid, $privatekey, $requestUrl, $requestMethod );
				$atualsignature = $signature->getSignature ( $timestamp );
			} catch ( Exception $e ) {
				return false;
			}
			
			return $atualsignature;
		}
		
		/**
		 * Function to generate correlation_id
		 *
		 * @name getcorrelationid
		 * @since 1.0.0
		 */
		public function getcorrelationid() {
			try {
				$cor_id = base64_encode ( \phpseclib\Crypt\Random::string ( 16 ) );
			} catch ( Exception $e ) {
				return false;
			}
			
			return $cor_id;
		}
		
		/**
		 * Function to get timestamp
		 *
		 * @name gettimestamp
		 * @since 1.0.0
		 */
		public function gettimestamp() {
			try {
				$timestamp = round ( microtime ( true ) * 1000 );
			} catch ( Exception $e ) {
				return false;
			}
			
			return $timestamp;
		}
		
		/**
		 * Function to parse xml
		 *
		 * @name post
		 * @since 1.0.0
		 */
		public function parseResponse($response) {
			
			$sxe = new SimpleXMLElement ( $response );
			$ns2 = $sxe->children ( 'ns2', TRUE );
			return $ns2;
		}
	}
}
?>