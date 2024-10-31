<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * main class for handling walmart feed  reqests.
 *
 * @since      1.0.0
 *
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/marketplaces/walmart/api
 */

if( !class_exists( 'CED_UMB_Walmart_Inventory_Update' ) ) :

class CED_UMB_Walmart_Inventory_Update {

	public $errorTrackArray = array();
	public $walmart_xml_lib;
	public $walmart_product_update;

	/**
	 * Constructor.
	 *
	 * registering actions and hooks for walmart.
	 *
	 * @since 1.0.0
	 */
	
	public function __construct() {
		require_once 'class-walmart-xml-lib.php';
		require_once 'class-walmart-product-update.php';
		$this->walmart_xml_lib = new CED_UMB_Walmart_XML_Lib();
		$this->walmart_product_update = new CED_UMB_Walmart_Product_Update();
	}
	
	/**
	 * This function is used to validate the product is ready or not 
	 *
	 * @name validateValueAndKeepTrackOfError
	 * @since 1.0.0
	 */

	/**
	 * This function is used to update inventory of single product
	 *
	 * @name updateInventoryOfSingleProduct
	 * @since 1.0.0
	 */
	
	function updateInventoryOfSingleProduct( $product_id ) {
		if(WC()->version<'3.0.0'){
			$_product = new WC_Product( $product_id );

		}else{
			$_product = wc_get_product( $product_id );
		}

		$sku = $_product->get_sku();
		$unit = 'EACH';
 
		$amount = get_marketplace_qty($product_id, 'walmart');
		$amount = intval($amount);
 

		if( $amount < 1 ) {
			$amount = "0";
		}
		
		$this->walmart_product_update->fetchAssignedProfileDataOfProduct($product_id);
		$fulfillmentLagTime = $this->walmart_product_update->fetchMetaValueOfProduct( $product_id, '_umb_walmart_fulfillmentLagTime' );


		$validationCheckArray = array(
			'sku' => $sku,
			'unit' => $unit,
			'fulfillmentLagTime' => $fulfillmentLagTime
		);
		
		
		if(!$this->validateValueAndKeepTrackOfError($product_id,$validationCheckArray)) {
			return '';
		}
		
		$inventory = array(
			'sku' => $sku,
			'quantity' => array(
				'unit' => $unit,
				'amount'	=> $amount
			),
			'fulfillmentLagTime' => $fulfillmentLagTime
		);

		$rootKey = 'inventory';
		$xml = new SimpleXMLElement("<$rootKey/>");
		
		$this->walmart_xml_lib->array2XML($xml, $inventory);
		$val = $xml->asXML();

		$val = $this->walmart_xml_lib->removeXMLTagFromXMLString($val);

		return $val;
	
	}
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
	 * This function is used to create inventory xml file
	 *
	 * @name makeInventoryXMLFileToSendOnWalmart
	 * @since 1.0.0
	 */
	function makeInventoryXMLFileToSendOnWalmart($initialinventoryXMLString) {
		$inventoryXMLString = '<InventoryFeed xmlns="http://walmart.com/">';
		$inventoryXMLString .= '<InventoryHeader><version>1.4</version></InventoryHeader>';
		$inventoryXMLString .= $initialinventoryXMLString;
		$inventoryXMLString .= '</InventoryFeed>';
		$inventoryXMLString = $this->walmart_xml_lib->formatXMLStringAsItShouldBe($inventoryXMLString);
		$fileName = 'inventory.xml';
		$this->walmart_xml_lib->writeXMLStringToFile($inventoryXMLString, $fileName);
		update_option('_umb_walmart_isRunInventoryUpdate',"yes");
	}
}
endif;