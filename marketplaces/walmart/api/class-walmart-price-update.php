<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/**
 * main class for handling walmart price update  reqests.
 *
 * @since      1.0.0
 *
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/marketplaces/walmart/api
 */
class CED_UMB_Walmart_Price_Update {

	public $walmart_xml_lib;
	public $errorTrackArray = array();

	public function __construct() {
		require_once 'class-walmart-xml-lib.php';
		$this->walmart_xml_lib = new CED_UMB_Walmart_XML_Lib();
	}

	
 
	/**
	 * This function is used to validate the product is ready or not 
	 *
	 * @name validateValueAndKeepTrackOfError
	 * @since 1.0.0
	 */

	function validateValueAndKeepTrackOfError($product_id,$validationCheckArray) {
		$isValid = true;
		foreach ($validationCheckArray as $key => $value) {
			if(!isset($value) || empty($value)) {
				$this->errorTrackArray[$product_id][] = $key;
				$isValid = false;
			}
		}
		return $isValid;
	}
	
	/**
	* function to prepare xml file to send on walmart to update inventory details
	*/
	function makePriceXMLFileToSendOnWalmart($initialinventoryXMLString) {		
		$inventoryXMLString = '<PriceFeed xmlns:gmp="http://walmart.com/">';
		$inventoryXMLString .= '<PriceHeader><version>1.5</version></PriceHeader>';
		$inventoryXMLString .= $initialinventoryXMLString;
		$inventoryXMLString .= '</PriceFeed>';		 
        $inventoryXMLString = $this->walmart_xml_lib->formatXMLStringAsItShouldBe($inventoryXMLString);
		$fileName = 'price.xml';
		$this->walmart_xml_lib->writeXMLStringToFile($inventoryXMLString, $fileName);
	}	
}