<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manage xml related functions to use in walmart.
 *
 * @class    CED_UMB_Walmart_XML_Lib
 * @version  1.0.0
 * @category Class
 * @author   CedCommerce <plugins@cedcommerce.com>
 */

class CED_UMB_Walmart_XML_Lib {

	/**
	 * This function reads json data from json files and return data in array form.
	 * @name readWalmartInfoFromJsonFile()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function readWalmartInfoFromJsonFile( $walmartJsonFileName ) {
		$walmartJsonFilePath = 'json/'.$walmartJsonFileName;
		$walmartJsonFilePath = CED_UMB_DIRPATH.'marketplaces/walmart/partials/'.$walmartJsonFilePath;
		ob_start();
		readfile($walmartJsonFilePath);
		$json_data = ob_get_clean();
		$json_data_to_array = json_decode($json_data, TRUE);
		return $json_data_to_array;
	}

	/**
	 * This function formats xml finally in correct format.
	 * @name formatXMLStringAsItShouldBe()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function formatXMLStringAsItShouldBe($xmlString) {
		$mainStringupper = '<?xml version="1.0" encoding="UTF-8"?>';
		$xmlString = implode("\n", array_filter(explode("\n", $xmlString)));
		$xmlString = $mainStringupper.$xmlString;
		return $xmlString;
	}

	/**
	 * This function writes xml string to destination file.
	 * @name writeXMLStringToFile()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function writeXMLStringToFile( $xmlString, $fileName ) {
		$XMLfilePath = ABSPATH.'wp-content/uploads/umb/';
		if(!is_dir($XMLfilePath))
		{
			if(!mkdir($XMLfilePath,0755))
			{
				return false;
			}
		}
		$XMLfilePath = $XMLfilePath."walmart/";
		if(!is_dir($XMLfilePath))
		{
			if(!mkdir($XMLfilePath,0755))
			{
				return false;
			}
		}
		if(!is_writable($XMLfilePath))
		{
			return false;
		}
		$XMLfilePath .= $fileName;
		$XMLfile = fopen($XMLfilePath, 'w');
		fwrite($XMLfile, $xmlString);
		fclose($XMLfile);
	}

	/**
	 * This function removes uncessary tags that creates issue in making xml.
	 * @name removeXMLTagFromXMLString()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function removeXMLTagFromXMLString($xmlString) {
		$str = $this->get_string_between($xmlString, '<?', '?>');
		$str = '<?'.$str.'?>';
		$xmlString = str_replace($str, '', $xmlString);
		return $xmlString;
	}

	/**
	 * This function formats xml in case of variations in corect format.
	 * @name handleVariantAttributeNamesConditionInXMLString()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function handleVariantAttributeNamesConditionInXMLString($xmlString,$stringToUse) {
		$str = $this->get_string_between($xmlString, '<variantAttributeNames>', '</variantAttributeNames>');
		$str = '<variantAttributeNames>'.$str.'</variantAttributeNames>';
		$xmlString = str_replace($str, $stringToUse, $xmlString);
		return $xmlString;
	}

	/**
	* Function to handle assets xml string details
	*/
	function handleadditionalAssetsInXMLString($xmlString,$stringToUse)
	{
		$str = $this->get_string_between($xmlString, '<additionalAssets>', '</additionalAssets>');
		$str = '<additionalAssets>'.$str.'</additionalAssets>';
		
		$xmlString = str_replace($str, $stringToUse, $xmlString);
		return $xmlString;
	}

	/**
	 * This function formats php array in SIMPLE_XML_ELEMENT object.
	 * @name array2XML()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function array2XML($xml_obj, $array) {
		foreach ($array as $key => $value) {
			if(is_numeric($key)) {
				$key = $key;	
			}
			if (is_array($value)) {
				$node = $xml_obj->addChild($key);
				$this->array2XML($node, $value);
			}
			else {
				$xml_obj->addChild($key, htmlspecialchars($value));
			}
		}
	}

	/**
	 * This function gets substring between to string chunks.
	 * @name get_string_between()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function get_string_between($string, $start, $end){
		$string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}
}