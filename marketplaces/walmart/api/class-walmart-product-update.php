<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * main class for handling product update request
 *
 * @since      1.0.0
 *
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/marketplaces/walmart/api
 */

class CED_UMB_Walmart_Product_Update {

	public $errorTrackArray = array();
	public $isAllRequiredValuePresent = true;

	public $walmart_xml_lib;

	public $profile_data = array();
	public $isProfileAssignedToProduct = false;

	/**
	 * Constructor.
	 *
	 * registering actions and hooks for walmart.
	 *
	 * @since 1.0.0
	 */
	
	public function __construct() {
		require_once 'class-walmart-xml-lib.php';
		$this->walmart_xml_lib = new CED_UMB_Walmart_XML_Lib();
	}
	
	/**
	 * This function fetches data in accordance with profile assigned to product.
	 * @name fetchAssignedProfileDataOfProduct()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function fetchAssignedProfileDataOfProduct( $product_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix.CED_UMB_PREFIX.'profiles';
		$profileID = get_post_meta( $product_id, 'ced_umb_profile', true);
		$profile_data = array();
		if( isset($profileID) && !empty($profileID) && $profileID != "" ) {
			$this->isProfileAssignedToProduct = true;
			$profileid = $profileID;
			$query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
			$profile_data = $wpdb->get_results($query,'ARRAY_A');
			if(is_array($profile_data)) {
				$profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
				$profile_data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
			}
		}
		else {
			$this->isProfileAssignedToProduct = false;
		}
		$this->profile_data = $profile_data;
	}

	/**
	 * This function fetches meta value of a product in accordance with profile assigned and meta value available.
	 * @name fetchMetaValueOfProduct()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */

	function fetchMetaValueOfProduct( $product_id, $metaKey ) {

		if($this->isProfileAssignedToProduct) {
			$_product = wc_get_product($product_id);
				if(WC()->version<'3.0.0'){
					if( $_product->product_type == "variation" ) {
					$parentId = $_product->parent->id;
				}
				else {
					$parentId = "0";
				}
			}else{

				if( $_product->get_type() == "variation" ) {
					$parentId = $_product->parent->id;
				}
				else {
					$parentId = "0";
				}

			}
			
			if(!empty($this->profile_data) && isset($this->profile_data[$metaKey])) {
				$tempProfileData = $profileData = $this->profile_data[$metaKey];

				if( isset($tempProfileData['default']) && !empty($tempProfileData['default']) && $tempProfileData['default'] != "" && !is_null($tempProfileData['default']) ) {
					$value = $tempProfileData['default'];
				}
				else if( isset($tempProfileData['metakey']) && !empty($tempProfileData['metakey']) && $tempProfileData['metakey'] != "" && !is_null($tempProfileData['metakey']) ) {					
					//if woo attribute is selected
					if (strpos($tempProfileData['metakey'], 'umb_pattr_') !== false) {
						
						$wooAttribute = explode('umb_pattr_', $tempProfileData['metakey']);
						$wooAttribute = end($wooAttribute);
							
						if(WC()->version<'3.0.0'){
							if( $_product->product_type == "variation" ) {
							$attributes =  $_product->get_variation_attributes() ;
							if(isset($attributes['attribute_pa_'.$wooAttribute]) && !empty($attributes['attribute_pa_'.$wooAttribute])) {
								$wooAttributeValue = $attributes['attribute_pa_'.$wooAttribute];
								if( $parentId != "0" ){
									$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
								}
								else {
									$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
								}
							}
							else {
								$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
								
								$wooAttributeValue = explode(",", $wooAttributeValue);
								$wooAttributeValue = $wooAttributeValue[0];
								
								if( $parentId != "0" ) {
									$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
								}
								else {
									$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
								}
								
							}
							
							if(is_array($product_terms) && !empty($product_terms)) {
								foreach ($product_terms as $tempkey => $tempvalue) {
									if($tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
									$value = $wooAttributeValue;
								}
								else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}	
							}
							else {
								$value = get_post_meta( $product_id, $metaKey, true );
							}
						}
						else {
							$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
							$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
							if(is_array($product_terms) && !empty($product_terms)) {
								foreach ($product_terms as $tempkey => $tempvalue) {
									if($tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
									$value = $wooAttributeValue;
								}
								else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							}
							else {
								$value = get_post_meta( $product_id, $metaKey, true );
							}
						}
						}else{
							if( $_product->get_type() == "variation" ) {
							$attributes =  $_product->get_variation_attributes() ;
							if(isset($attributes['attribute_pa_'.$wooAttribute]) && !empty($attributes['attribute_pa_'.$wooAttribute])) {
								$wooAttributeValue = $attributes['attribute_pa_'.$wooAttribute];
								if( $parentId != "0" ){
									$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
								}
								else {
									$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
								}
							}
							else {
								$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
								
								$wooAttributeValue = explode(",", $wooAttributeValue);
								$wooAttributeValue = $wooAttributeValue[0];
								
								if( $parentId != "0" ) {
									$product_terms = get_the_terms($parentId, 'pa_'.$wooAttribute);
								}
								else {
									$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
								}
								
							}
							
							if(is_array($product_terms) && !empty($product_terms)) {
								foreach ($product_terms as $tempkey => $tempvalue) {
									if($tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
									$value = $wooAttributeValue;
								}
								else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}	
							}
							else {
								$value = get_post_meta( $product_id, $metaKey, true );
							}
						}
						else {
							$wooAttributeValue = $_product->get_attribute( 'pa_'.$wooAttribute );
							$product_terms = get_the_terms($product_id, 'pa_'.$wooAttribute);
							if(is_array($product_terms) && !empty($product_terms)) {
								foreach ($product_terms as $tempkey => $tempvalue) {
									if($tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if( isset($wooAttributeValue) && !empty($wooAttributeValue) ) {
									$value = $wooAttributeValue;
								}
								else {
									$value = get_post_meta( $product_id, $metaKey, true );
								}
							}
							else {
								$value = get_post_meta( $product_id, $metaKey, true );
							}
						}

						}
					}
					else {

						$value = get_post_meta( $product_id, $tempProfileData['metakey'], true );
						if($tempProfileData['metakey'] == '_thumbnail_id'){
							$value = wp_get_attachment_image_url( get_post_meta( $product_id,'_thumbnail_id',true), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $product_id,'_thumbnail_id',true), 'thumbnail' ) : '';
						}
						if( !isset($value) || empty($value) || $value == "" || is_null($value) || $value == "0" || $value == "null") {
							if( $parentId != "0" ) {

								$value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
								if($tempProfileData['metakey'] == '_thumbnail_id'){
									$value = wp_get_attachment_image_url( get_post_meta( $parentId,'_thumbnail_id',true), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId,'_thumbnail_id',true), 'thumbnail' ) : '';
								}

								if( !isset($value) || empty($value) || $value == "" || is_null($value) ) {
									$value = get_post_meta( $product_id, $metaKey, true );

								}
							}
							else {
								$value = get_post_meta( $product_id, $metaKey, true );
							}
						}

					}
				}
				else {
					$value = get_post_meta( $product_id, $metaKey, true );
				}
			}
			else {
				$value = get_post_meta( $product_id, $metaKey, true );
			}
		}
		else {
			$value = get_post_meta( $product_id, $metaKey, true );
		}

		return $value;
	}

	/**
	* Function to get words
	*/
	function get_words($string, $wordsreturned = 50) {

		$retval = $string;  //  Just in case of a problem
		$array = explode(" ", $string);
		/*  Already short enough, return the whole thing
		*/
		if (count($array)<=$wordsreturned) {
			$retval = $string;
		}
		/*  Need to chop of some words
		 */
		else {
			array_splice($array, $wordsreturned);
			$retval = implode(" ", $array)." ...";
		}
		return $retval;
	}

	/**
	* Function to make additional product attributes
	*/
	function makeAdditionalProductAttributes($product_id,$assignedWalmartCategory='') {

		$additionalProductAttributes = array();

		$walmartJsonFileName = 'WalmartVariation.json';
		$variation_attrs = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );
		$assignedWalmartCategoryToUse = explode("/", $assignedWalmartCategory);
		$assignedWalmartCategoryToUse = $assignedWalmartCategoryToUse[0];
		if(isset($variation_attrs[$assignedWalmartCategoryToUse])) {
			$variation_attrs = $variation_attrs[$assignedWalmartCategoryToUse];
		}
		else{
			$variation_attrs = array();
		}

		foreach ($variation_attrs as $key => $attributeLabel) {
			$attributeValue = $this->fetchMetaValueOfProduct( $product_id, $assignedWalmartCategory.'_'.$attributeLabel );
			$tempArray = array();
			if( $attributeValue != "" ) {
				$attributeLabel = explode("/", $attributeLabel);
				$attributeLabel = $attributeLabel[0];
				$tempArray['productAttributeName'] = $attributeLabel;
				$tempArray['productAttributeValue'] = $attributeValue;
				$additionalProductAttributes[] = $tempArray;
			}
		}
		return $additionalProductAttributes;

	}
	
	/**
	 * This function make array to be places at MPItem key in product update xml.
	 * @name makeArrayFor_MPITEM()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function makeArrayFor_MPITEM($product_id,$categories,$isPrimaryVariant = "false") {
		$this->fetchAssignedProfileDataOfProduct( $product_id );
		$galleryImagesstr='';
		$this->isAllRequiredValuePresent = true;
		$this->fetchAssignedProfileDataOfProduct($product_id);
		$assignedWalmartCategory = $this->fetchMetaValueOfProduct( $product_id, '_umb_walmart_category' );
		$shortDescription='';
		if(WC()->version<'3.0.0'){
			$_product = new WC_Product($product_id); 

			$post_data = $_product->get_post_data();
			$longDescription = $post_data->post_content;
			 
			$longDescription = $this->get_words($longDescription,500);
			
		}else{
			$_product =wc_get_product($product_id); 

			$post_data = $_product->get_data();
			$longDescription = $post_data['description'];
			$longDescription = $post_data['description'];
			$longDescription = $this->get_words($post_data['description'],500);
		}
		 
		$shelfDescription = $_product->get_title();
		if(WC()->version<'3.0.0'){
			$shortDescription = $_product->post->post_excerpt;

		}else{
			$shortDescription = $_product->get_short_description();

		}
 
		if( empty($shortDescription) ) {
			if(WC()->version<'3.0.0'){
				$shortDescription = $this->get_words($post_data->post_content);

			}else{
				$shortDescription = $post_data['description'];
				$shortDescription = $this->get_words($post_data['description']);
			}
		}
		
		
		$sku = $_product->get_sku();

		$title = $_product->get_title();
		$productCustShortDet = $this->fetchMetaValueOfProduct( $product_id, '_umb_short_description_value' );
		$productCustShortDet2 = $this->fetchMetaValueOfProduct( $product_id, '_umb_short_description_value2' );
		$productCustShortDet3 = $this->fetchMetaValueOfProduct( $product_id, '_umb_short_description_value3' );
		$productCustShortDet4 = $this->fetchMetaValueOfProduct( $product_id, '_umb_short_description_value5' );
		$woocommerce_currency_symbol = get_woocommerce_currency_symbol();
  		require_once CED_UMB_DIRPATH.'marketplaces/walmart/api/class-walmart-product-update.php';
		$walmart_product_update = new CED_UMB_Walmart_Product_Update();
		$walmart_product_update -> fetchAssignedProfileDataOfProduct(  $product_id );
		$_umb_pricing_percentage = $walmart_product_update ->fetchMetaValueOfProduct( $product_id, '_umb_pricing_percentage' );
		$actual_price = get_marketplace_price( $product_id,'walmart');
		if(isset($_umb_pricing_percentage) && !empty($_umb_pricing_percentage) && $_umb_pricing_percentage!=''){
			$final_price=$actual_price+ ((((int)$_umb_pricing_percentage))/100) * ($actual_price);
		}else{
			$final_price =$actual_price;
		}
		 
		$regular_price = $final_price;

		$productCustomeDes = $this->fetchMetaValueOfProduct( $product_id, '_ced_walmart_editor_Description' );

		if( empty($productCustomeDes) || $productCustomeDes == "" || $productCustomeDes == "0" || $productCustomeDes == "null") {
			$productCustomeDes = get_post_meta( $product_id, '_ced_walmart_editor_Description', true);
		}
		if( isset($productCustomeDes) && $productCustomeDes!='' && !empty($productCustomeDes)){
			$longDescription_editor =$this->get_words($productCustomeDes,350);
		}
		 
		$weight = $_product->get_weight();
		$productType = $_product->get_type();
		$productID = $_product->get_id();
 		$image = wp_get_attachment_image_url( get_post_meta( $productID,'_thumbnail_id',true), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $productID,'_thumbnail_id',true), 'full' ) : '';


		$weight_unit = get_option("woocommerce_weight_unit", "LB");
		$weight_unit = ($weight_unit == "kg") ? "KG" : (($weight_unit == "g") ? "G" : (($weight_unit == "oz") ? "OZ" : (($weight_unit == "lbs") ? "LB" : "LB")));
		
		if(WC()->version<'3.0.0'){
			$attachment_ids = $_product->get_gallery_attachment_ids();

		}else{
			$attachment_ids = $_product->get_gallery_image_ids ();

		}
		
		$galleryImages = array();
		 
		$galleryImagesstr = "<productSecondaryImageURL>";
		foreach($attachment_ids as $attachment_id) {
			$attachment_url = wp_get_attachment_url( $attachment_id );
			if(!empty($attachment_url)) {
				$galleryImages[] = array('productSecondaryImageURLValue'=>$attachment_url);
				$galleryImagesstr .= "<productSecondaryImageURLValue>".$attachment_url."</productSecondaryImageURLValue>";
			}
		}
		$galleryImagesstr .= "</productSecondaryImageURL>";
		
		$galleryImgArrayToUse = array();
		$galleryImgArrayToUse['productSecondaryImageURL'] = $galleryImages;
		
		$variationData = array();
		$additionalProductAttributes = array();

		if(WC()->version<'3.0.0'){

			if($_product->post->post_type == 'product_variation') {
			
				$product_id_parent = $_product->post->post_parent;

				$_product_parent = new WC_Product($product_id_parent);
				if(WC()->version<'3.0.0'){
					$post_data_parent = $_product_parent->get_post_data();
				}else{
					$post_data_parent = $_product_parent->get_data();

				}

				$longDescription = $post_data_parent->post_content;
				 
				$longDescription = $this->get_words($longDescription,500);
				$shelfDescription = $_product_parent->get_title();
				 
				$shortDescription = $_product_parent->post->post_excerpt;
				if( empty($shortDescription) ) {
					$shortDescription = $this->get_words($post_data_parent->post_content);
				}
				$weight = $_product_parent->get_weight();
				$title = $_product_parent->get_title();
				if($image == "") {
					$image = wp_get_attachment_image_url( get_post_meta( $product_id_parent,'_thumbnail_id',true), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id_parent,'_thumbnail_id',true), 'full' ) : '';
				}
				
				$attachment_ids = $_product_parent->get_gallery_attachment_ids();
				$galleryImages = array();
				 
				$galleryImagesstr = "<productSecondaryImageURL>";
				foreach($attachment_ids as $attachment_id) {
					$attachment_url = wp_get_attachment_url( $attachment_id );
					if(!empty($attachment_url)) {
						$galleryImages[] = array('productSecondaryImageURLValue'=>$attachment_url);
						$galleryImagesstr .= "<productSecondaryImageURLValue>".$attachment_url."</productSecondaryImageURLValue>";
					}
				}
				$galleryImagesstr .= "</productSecondaryImageURL>";
				$galleryImgArrayToUse = array();
				$galleryImgArrayToUse['productSecondaryImageURL'] = $galleryImages;

				$parent_sku = $_product_parent->get_sku();
				
				$variationData = $this->makeVariationSpecificArray( $assignedWalmartCategory ,$product_id, $isPrimaryVariant ,$parent_sku , $categories);//$post_data_parent->ID //$parent_sku
				 
					
				$additionalProductAttributes = $this->makeAdditionalProductAttributes($product_id,$assignedWalmartCategory);

			}
		}else{
			
			if($_product->get_type() == 'variation') {


				$product_id_parent = $_product->get_parent_id();

				$_product_parent = wc_get_product($product_id_parent);
				if(WC()->version<'3.0.0'){
					$post_data_parent = $_product_parent->get_post_data();
				}else{
					$post_data_parent = $_product_parent->get_data();

				}
 
				$longDescription = $this->get_words($post_data_parent['description'],500);
			 
				$shelfDescription = $_product_parent->get_title();
				 
				$shortDescription = $_product_parent->get_short_description();
				
				if( empty($shortDescription) ) {
					$shortDescription = $post_data_parent['short_description'];
				}
				$weight = $_product_parent->get_weight();
				$title = $_product_parent->get_title();
				if($image == "") {
					$image = wp_get_attachment_image_url( get_post_meta( $product_id_parent,'_thumbnail_id',true), 'full' ) ? wp_get_attachment_image_url( get_post_meta( $product_id_parent,'_thumbnail_id',true), 'full' ) : '';
				}
					
				$attachment_ids = $_product_parent->get_gallery_image_ids();
				//for custom description
				$productCustomeDes = $this->fetchMetaValueOfProduct( $product_id_parent, '_ced_walmart_editor_Description' );
				if( empty($productCustomeDes) || $productCustomeDes == "" || $productCustomeDes == "0" || $productCustomeDes == "null") {
					$productCustomeDes = get_post_meta( $product_id, '_ced_walmart_editor_Description', true);
				}
				if($productCustomeDes!='' && !empty($productCustomeDes)){
					$longDescription_editor =$this->get_words($productCustomeDes,350);
				}


				$galleryImages = array();
				 
				$galleryImagesstr = "<productSecondaryImageURL>";
				foreach($attachment_ids as $attachment_id) {
					$attachment_url = wp_get_attachment_url( $attachment_id );
					if(!empty($attachment_url)) {
						$galleryImages[] = array('productSecondaryImageURLValue'=>$attachment_url);
						$galleryImagesstr .= "<productSecondaryImageURLValue>".$attachment_url."</productSecondaryImageURLValue>";
					}
				}
				$galleryImagesstr .= "</productSecondaryImageURL>";
				$galleryImgArrayToUse = array();
				$galleryImgArrayToUse['productSecondaryImageURL'] = $galleryImages;

				$parent_sku = $_product_parent->get_sku();
				

				$variationData = $this->makeVariationSpecificArray( $assignedWalmartCategory ,$product_id, $isPrimaryVariant ,$parent_sku , $categories);//$post_data_parent->ID //$parent_sku
				 
				$additionalProductAttributes = $this->makeAdditionalProductAttributes($product_id,$assignedWalmartCategory);
			
			}
		}
		if($weight == "") {
			$weight = "0.0";
		}
		$productIdType = $this->fetchMetaValueOfProduct( $product_id, '_umb_id_type' );
		if( empty($productIdType) || $productIdType == "" || $productIdType == "0" || $productIdType == "null") {
			$productIdType = get_post_meta( $product_id, '_umb_id_type', true);
		}
		$shortDescription2='';
		$shortDescription3='';
		$shortDescription4='';
		//for custom title
		$productCustomTitle = $this->fetchMetaValueOfProduct( $product_id, '_umb_title_value' );
		if( empty($productCustomTitle) || $productCustomTitle == "" || $productCustomTitle == "0" || $productIdType == "null") {
			$productCustomTitle = get_post_meta( $product_id, '_umb_title_value', true);
		}
		if($productCustomTitle!='' && !empty($productCustomTitle)){
			$title =$productCustomTitle;
		}
 
		if( empty($productCustShortDet) || $productCustShortDet == "" || $productCustShortDet == "0" || $productCustShortDet == "null") {
			$productCustShortDet = get_post_meta( $product_id, '_umb_short_description_value', true);
		}
		if($productCustShortDet!='' && !empty($productCustShortDet)){
			$shortDescription =$this->get_words($productCustShortDet);
		}

		if( empty($productCustShortDet2) || $productCustShortDet2 == "" || $productCustShortDet2 == "0" || $productCustShortDet2 == "null") {
			$productCustShortDet2 = get_post_meta( $product_id, '_umb_short_description_value2', true);
		}
		if($productCustShortDet2!='' && !empty($productCustShortDet2)){
			$shortDescription2 =$this->get_words($productCustShortDet2);
		}

		if( empty($productCustShortDet3) || $productCustShortDet3 == "" || $productCustShortDet3 == "0" || $productCustShortDet3 == "null") {
			$productCustShortDet3 = get_post_meta( $product_id, '_umb_short_description_value3', true);
		}
		if($productCustShortDet3!='' && !empty($productCustShortDet3)){
			$shortDescription3 =$this->get_words($productCustShortDet3);
		}
		if( empty($productCustShortDet4) || $productCustShortDet4 == "" || $productCustShortDet4 == "0" || $productCustShortDet4 == "null") {
			$productCustShortDet4 = get_post_meta( $product_id, '_umb_short_description_value4', true);
		}
		if($productCustShortDet4!='' && !empty($productCustShortDet4)){
			$shortDescription4 =$this->get_words($productCustShortDet4);
		}

		$keyFeaturesStr='';
		if($shortDescription!=''){

			$keyFeaturesStr  = "<keyFeatures>";
			if($shortDescription!=''){
				$keyFeaturesStr .= "<keyFeaturesValue>".$shortDescription."</keyFeaturesValue>";
			}
			if($shortDescription2!=''){
				$keyFeaturesStr .= "<keyFeaturesValue>".$shortDescription2."</keyFeaturesValue>";
			}
			if($shortDescription3!=''){
				$keyFeaturesStr .= "<keyFeaturesValue>".$shortDescription3."</keyFeaturesValue>";
			}
			if($shortDescription4!=''){
				$keyFeaturesStr .= "<keyFeaturesValue>".$shortDescription4."</keyFeaturesValue>";
			}
	        $keyFeaturesStr .= "</keyFeatures>";
		}

		$productId = $this->fetchMetaValueOfProduct( $product_id, '_umb_id_val' );
		$productId = preg_replace('/\s+/', '', $productId);
		$walmart_is_shipped_allowed_details = $this->fetchMetaValueOfProduct( $product_id, '_umb_walmart_is_shipped_allowed' );
		if($walmart_is_shipped_allowed_details){
			//$walmart_is_shipped_allowed_details='Yes';
			$walmart_shipping_price_details = $this->fetchMetaValueOfProduct( $product_id, '_umb_walmart_shipping_price' );
			$walmart_shipping_method_details = $this->fetchMetaValueOfProduct( $product_id, '_umb_walmart_shipping_method' );
			$walmart_shipping_region_details = $this->fetchMetaValueOfProduct( $product_id, '_umb_walmart_shipping_region' );

		} 
		if( empty($productId) || $productId == "" || $productId == "0" ) {
			$productId = get_post_meta( $product_id, '_umb_id_val', true);
			$productId = preg_replace('/\s+/', '', $productId);
		}
		$ProductTaxCode = $this->fetchMetaValueOfProduct( $product_id, '_umb_product_tax_code' );

		if( empty($ProductTaxCode) || $ProductTaxCode == "" || $ProductTaxCode == "0" ) {
			$ProductTaxCode = get_post_meta( $product_id, '_umb_product_tax_code', true);
		} 
		$productBrand = $this->fetchMetaValueOfProduct( $product_id, '_umb_brand' );
		//$productBrand = get_post_meta( $product_id, '_umb_brand', true);
		 $shortDescription=substr($shortDescription, 0,499);
		 $longDescription=substr($longDescription, 0,4999);
		$date = date('Y-m-d', time());
		if( isset($longDescription_editor) && $longDescription_editor!='' && !empty($longDescription_editor)){
			$longDescription =$this->get_words($longDescription_editor,350);
		}
		$attributesToEscape = array(
			'sku'	=>	$sku,
			'productIdentifiers/productIdentifier/productIdType' => $productIdType,
			'productIdentifiers/productIdentifier/productId' => $productId,
			'productName'	=>	htmlspecialchars($title),
		 
			'shelfDescription' => htmlspecialchars($shelfDescription),
			'shortDescription' => $longDescription,
			'mainImageUrl' => $image,
			'brand' => $productBrand,
			 
			'MPOffer/ProductTaxCode' => $ProductTaxCode,
			'ProductTaxCode' => $ProductTaxCode,
			 $assignedWalmartCategory => $assignedWalmartCategory,
			'MPOffer/price'	=>	$regular_price,
			'MPOffer/MinimumAdvertisedPrice'	=>	$regular_price,
			'MPOffer/StartDate'	=>	$date,
			'MPOffer/EndDate'	=>	'2021-01-01',
			'MPOffer/ShippingWeight/measure' =>	$weight,
			'MPOffer/ShippingWeight/unit' =>	'lb',
 
			);
		
		$productMetaInfoToSend = array();
		$productMetaInfoToSend['SkuUpdate'] = "No";
		$productMetaInfoToSend['msrp'] = $regular_price;	 	 
		$productMetaInfoToSend['ProductIdUpdate'] = "Yes";	 	 
		$productMetaInfoToSend['brand'] = "Yes"; 
		$outerArray = array();
		foreach ($categories as $key => $category) {
			if($category['path'] == $assignedWalmartCategory) {

				$walmart_required_attributes = $category['walmart_required_attributes'];

				$walmart_required_attributes = explode(",",$walmart_required_attributes);
				$walmart_attributes = $category['walmart_attributes'];
				$walmart_attributes = explode(",",$walmart_attributes);
				$walmart_attributes = $this->makeArrayToPutAtCategoryIndex($walmart_attributes,$category,$product_id,$variationData,$categories);
				 
				foreach ($walmart_required_attributes as $key => $attribute) {

					if($attribute == 'sku') { continue ;}
					if (strpos($attribute, '/') !== false) {
						$arrayIndex = explode('/', $attribute);
						if(array_key_exists($attribute,$attributesToEscape)) {
							$value = $attributesToEscape[$attribute];
						}
						else {
							$value = $this->fetchMetaValueOfProduct( $product_id, $assignedWalmartCategory.'_'.$attribute );
						}
						/* validation check */
						if(!$this->validateValueAndKeepTrackOfError($product_id,$value,$attribute)) { continue;}
						/* validation check */
						$arrayIndexTemp = $arrayIndex;
						array_shift($arrayIndex);
						$temp = $this->makeMultiDimensionalArray($arrayIndex,$value);

						if(!empty($productMetaInfoToSend[$arrayIndexTemp[0]])) {
							$productMetaInfoToSend[$arrayIndexTemp[0]] = $this->performRecursiveArrayMerge($temp,$productMetaInfoToSend,$arrayIndexTemp[0]);
						}
						else {
							$productMetaInfoToSend[$arrayIndexTemp[0]] = $temp;
						}
					}
					else {
						if(array_key_exists($attribute,$attributesToEscape)) {
							$value = $attributesToEscape[$attribute];

							/* validation check */
							if(!$this->validateValueAndKeepTrackOfError($product_id,$value,$attribute)) { continue;}
							/* validation check */

							$productMetaInfoToSend[$attribute] = $value;	
							if($attribute == 'mainImageUrl') {

								$keyToUseInOuterSpace = array_search ('ProductTaxCode', $walmart_required_attributes);
								$outerArray = array_splice($walmart_required_attributes,$keyToUseInOuterSpace+1,count($walmart_required_attributes)-$keyToUseInOuterSpace); 
								
								if(!empty($additionalProductAttributes)) {
									$productMetaInfoToSend['additionalProductAttributes'] = $additionalProductAttributes;
								}
								$xsds_with_category_name = array('ArtAndCraft' => 'ArtAndCraftCategory', 'CarriersAndAccessories' => 'CarriersAndAccessoriesCategory', 'Clothing' => 'ClothingCategory','FoodAndBeverage' => 'FoodAndBeverageCategory', 'Footwear' => 'FootwearCategory','Furniture' => 'FurnitureCategory','GardenAndPatio' => 'GardenAndPatioCategory', 'Jewelry' => 'JewelryCategory','Office' => 'OfficeCategory', 'Other' => 'OtherCategory','Toy' => 'ToysCategory','Watches' => 'WatchesCategory');
								
								 
								 $assignedWalmartCategory = explode("/", $assignedWalmartCategory);
	 	
								if(isset($assignedWalmartCategory[1])){
									$assignedWalmartSubCategory = $assignedWalmartCategory[1]; 
								}
								$assignedWalmartCategory = $assignedWalmartCategory[0];
								//print_r($productMetaInfoToSend); die('hello');
								$productMetaInfoToSend['brand'] = 'ced';
								if(isset($productMetaInfoToSend['shortDescription']) && isset($productMetaInfoToSend['brand'])){
									    $description = strip_tags($productMetaInfoToSend['shortDescription'], '<p><a>');
									    $description= preg_replace('/\[.*?\]/', '', $description);
									    //print_r($description); die('dfd');
									$general_category_desc = array('shortDescription' => $description,'mainImageUrl' =>$productMetaInfoToSend['mainImageUrl'],'brand'=>$productMetaInfoToSend['brand']);
									if(isset($productMetaInfoToSend['keyFeatures'])){

										$general_category_desc['keyFeatures'] =array('keyFeaturesValue' =>$productMetaInfoToSend['keyFeatures']);
									}

									if(is_array($walmart_attributes)){
										$walmart_attributes = array_merge( $walmart_attributes,$general_category_desc);

									}else{
										$walmart_attributes=$general_category_desc;
									}
								}
								
								if(array_key_exists($assignedWalmartCategory, $xsds_with_category_name)){
									$assignedWalmartCategoryFileName=$assignedWalmartCategory.'Category';
								}else{
									$assignedWalmartCategoryFileName=$assignedWalmartCategory;

								}

								if(isset($assignedWalmartSubCategory) && !empty($assignedWalmartSubCategory)){


									$productMetaInfoToSend['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory] = $walmart_attributes;
									 
									 

								}else{
									$productMetaInfoToSend['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory] = $walmart_attributes;
								 

								}
								break;
							}
						}
						else {
							$value = $this->fetchMetaValueOfProduct( $product_id, $assignedWalmartCategory.'_'.$attribute );
							/* validation check */
							if(!$this->validateValueAndKeepTrackOfError($product_id,$value,$attribute)) { continue;}
							/* validation check */
							
							$productMetaInfoToSend[$attribute] = $value;
						}
					}
				}
				break;
			}
		}
  	  	
		$MPItem = array();
		$date = date('Y-m-d', time());
		$time = date('h:i:s', time());
		$date_time=$date.'T'.$time;
		$MPItem['processMode'] = "CREATE";
		$MPItem['feedDate'] = $date_time; 
		$MPItem['sku'] = $sku;
		
		if(isset($productMetaInfoToSend['productIdentifiers'])){
			$productIdentifiers = str_replace(' ', '', $productMetaInfoToSend['productIdentifiers']);
			$MPItem['productIdentifiers'] = $productIdentifiers;

		}
		if(isset($productMetaInfoToSend['MPOffer'])){
			$MPOffer_Save_Temp['MPOffer']  =$productMetaInfoToSend['MPOffer'];
		}
		if (array_key_exists('MPOffer', $productMetaInfoToSend)) {
		    unset($productMetaInfoToSend['MPOffer']);
		} 
		unset($productMetaInfoToSend['ProductTaxCode']);
		unset($productMetaInfoToSend['brand']);
		unset($productMetaInfoToSend['mainImageUrl']);
		unset($productMetaInfoToSend['keyFeatures']);
		unset($productMetaInfoToSend['shortDescription']);
		unset($productMetaInfoToSend['productIdentifiers']);
		 
		$MPItem['MPProduct'] = $productMetaInfoToSend;
  
		if(isset($MPOffer_Save_Temp['MPOffer'])){
			$MPItem['MPOffer']  =$MPOffer_Save_Temp['MPOffer'];
		}

		if($walmart_is_shipped_allowed_details){

			$MPItem['MPOffer']['ShippingOverrides']['ShippingOverrideAction'] = 'REPLACE_ALL';

			$MPItem['MPOffer']['ShippingOverrides']['shippingOverride']['ShippingOverrideIsShippingAllowed'] = $walmart_is_shipped_allowed_details;

			if(!($walmart_shipping_region_details=='null')){	
				$MPItem['MPOffer']['ShippingOverrides']['shippingOverride']['ShippingOverrideShipRegion'] = $walmart_shipping_region_details;
			}	
			
			if(!($walmart_shipping_method_details=='null')){
				$MPItem['MPOffer']['ShippingOverrides']['shippingOverride']['ShippingOverrideShipMethod'] = $walmart_shipping_method_details;
			}
			 
			if(!($walmart_shipping_price_details=='null')){
				$MPItem['MPOffer']['ShippingOverrides']['shippingOverride']['ShippingOverrideshipPrice'] = $walmart_shipping_price_details;
			}
		}
		unset($MPItem['ProductTaxCode']);
		unset($MPItem['brand']);
		unset($MPItem['mainImageUrl']);
		unset($MPItem['keyFeatures']);
		unset($MPItem['shortDescription']);
		unset($MPItem['productName']);
		 
		$productInfoToSend['MPItem'] = $MPItem;
		if(isset($galleryImages) && !empty($galleryImages)){
			$productInfoToSend['images'] = $galleryImagesstr;
		}
		 
		if(isset($keyFeaturesStr) && !empty($keyFeaturesStr)){
			$productInfoToSend['keyFeatures1'] = $keyFeaturesStr;
		}

		//print_r($productInfoToSend); die('fdgd');
	 
		return $productInfoToSend;	
	}
	/**
	 * This function makes xml section in case of variations of variable product.
	 * @name makeVariationSpecificArray()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */

	function makeVariationSpecificArray( $assignedWalmartCategory, $product_id, $isPrimaryVariant , $parentID , $categories) {
		
		$walmartJsonFileName = 'WalmartAttributes.json';
		$attributeInfo = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );
		/* list of all attributes possible for current category*/
		foreach ($categories as $key => $category) {
			if($category['path'] == $assignedWalmartCategory) {
				$walmart_attributes = $category['walmart_attributes'];
				$walmart_attributes = explode(",",$walmart_attributes);
			}
		}

		/* list of all variations attributes possible for parent category*/
		$walmartJsonFileName = 'WalmartVariation.json';
		$variation_attrs = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );
		$assignedWalmartCategoryToUse = explode("/", $assignedWalmartCategory);
		$assignedWalmartCategoryToUse = $assignedWalmartCategoryToUse[0];
		if(isset($variation_attrs[$assignedWalmartCategoryToUse])) {
			$variation_attrs = $variation_attrs[$assignedWalmartCategoryToUse];
		}
		else{
			$variation_attrs = array();
		}	

		/* list of all variations attributes possible for current category*/
		$variation_attrs = array_intersect($walmart_attributes, $variation_attrs);

		$variantKeysArray = array();
		foreach ($walmart_attributes as $key => $attribute) {
			if( in_array($attribute, $variation_attrs)) {
				$value = $this->fetchMetaValueOfProduct( $product_id, $assignedWalmartCategory.'_'.$attribute );
				/* validation check */
				if($attributeInfo[$assignedWalmartCategory.'::'.$attribute]['walmart_attribute_required'] == 'Required') {
					if(!$this->validateValueAndKeepTrackOfError($product_id,$value,$attribute)) { continue;}
				}
				/* validation check */
				if(!isset($value) || empty($value) || $value == "0" || $value == "null") { continue;}
				
				$variantKey = explode("/", $attribute);
				$variantKey = $variantKey[0];
				$variantKeysArray[] = $variantKey;
			}
		}

		$variationSpecificArrayMain = array();
		if( !empty($variantKeysArray) ) {
			$variationSpecificArrayMain['variantAttributeNames'] = $variantKeysArray;
			$variationSpecificArrayMain['variantGroupId'] = $parentID;
			$variationSpecificArrayMain['isPrimaryVariant'] = $isPrimaryVariant;
		}
		 
		return $variationSpecificArrayMain;
		
	}
	
	/**
	 * This function formats product xml in correct format.
	 * @name formatAndAppendDataToXmlString()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function formatAndAppendDataToXmlString( $key, $arrayToUse, $mainXMLString , $isVariation=false, $stringToUse='', $assetStringToUse = "", $additionalProductAttributesStringToUse = "" ) {
		$xml = new SimpleXMLElement($key);
		$this->walmart_xml_lib->array2XML($xml, $arrayToUse);
		
		if(isset( $arrayToUse['Product']['longDescription'] ))
		{	
			$xml->Product->longDescription = NULL;
			$node1 = dom_import_simplexml($xml->Product->longDescription);
			$no   = $node1->ownerDocument;
			$node1->appendChild($no->createCDATASection($arrayToUse['Product']['longDescription']));
		}
		
		if(isset($arrayToUse['Product']['shelfDescription']))
		{
			$xml->Product->shelfDescription = NULL;
			$node1 = dom_import_simplexml($xml->Product->shelfDescription);
			$no   = $node1->ownerDocument;
			$node1->appendChild($no->createCDATASection($arrayToUse['Product']['shelfDescription']));
		}
		
		if(isset($arrayToUse['Product']['shortDescription']))
		{
			$xml->Product->shortDescription = NULL;
			$node1 = dom_import_simplexml($xml->Product->shortDescription);
			$no   = $node1->ownerDocument;
			$node1->appendChild($no->createCDATASection($arrayToUse['Product']['shortDescription']));
		}
		
		$val = $xml->asXML();
		if($isVariation) {
			$val  = $this->walmart_xml_lib->handleVariantAttributeNamesConditionInXMLString($val,$stringToUse);
		}

		if( $additionalProductAttributesStringToUse != "" ) {
			$str = $this->walmart_xml_lib->get_string_between($val, '<additionalProductAttributes>', '</additionalProductAttributes>');
			$str = '<additionalProductAttributes>'.$str.'</additionalProductAttributes>';
			$val = str_replace($str, $additionalProductAttributesStringToUse, $val);
		}
		
		$val = $this->walmart_xml_lib->removeXMLTagFromXMLString($val);
		$mainXMLString .= $val;
		return $mainXMLString;
	}
	
	/**
	 * This function makes multi-dimensional array to format array correct as needed by walmart.
	 * @name makeMultiDimensionalArray()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function makeMultiDimensionalArray($indexs,$value) {
		$counter = count($indexs)-1;
		$arrayToReturn = array();
		while ( $counter>=0 ) {
			if($counter == count($indexs)-1) {
				$arrayToReturn[$indexs[$counter]] = $value;
			}
			else {
				$arrayToReturn[$indexs[$counter]] = $arrayToReturn;
				unset($arrayToReturn[$indexs[$counter+1]]);
			}
			$counter--;
		}
		return $arrayToReturn;
	}

	/**
	 * This function makes xml to put specific to selected walmart category.
	 * @name makeArrayToPutAtCategoryIndex()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function makeArrayToPutAtCategoryIndex($walmart_attributes,$assignedWalmartCategory,$product_id,$variationData,$categories) {
		if(is_array($assignedWalmartCategory)){
			$assignedWalmartCategory =$assignedWalmartCategory['path'];

		}
		/* keys to escape in case of simple product :: start */
		$_product = wc_get_product($product_id);
		if( isset($_product->variation_id) ) {
			$extraKeysToEscape = array();
			$keysToReplaceInAttributeCase = array(
				'variantAttributeNames',
				'variantGroupId',
				'isPrimaryVariant'
				);
		}
		else {
			$extraKeysToEscape = array(
				'swatchImages/swatchImage/swatchImageUrl',
				'swatchImages/swatchImage/swatchVariantAttribute',
				'variantAttributeNames/variantAttributeName',
				'variantGroupId',
				'isPrimaryVariant',
				 
			);
			$keysToReplaceInAttributeCase = array();	
		}
		/* keys to escape in case of simple product :: end */
		

		$walmartJsonFileName = 'WalmartAttributes.json';
		$attributeInfo = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );

		$arrayToReturn = array();

		$variation_array_used = true;
 
		foreach ($walmart_attributes as $key => $attribute) {
			
			if( in_array($attribute, $extraKeysToEscape)) {
				continue;
			}
 
			if($attribute == 'variantAttributeNames/variantAttributeName')
			{	
				if($variation_array_used)
				{

					$_product = wc_get_product($product_id);

					if(WC()->version<'3.0.0'){
						if( $_product->product_type == "variation") {


							$changedVariationData = array();
							foreach ($variationData as $key => $value) {
								if( in_array($key, $keysToReplaceInAttributeCase)) {

									if($key == 'variantAttributeNames') {
											 
										$arrayToReturn['variantAttributeNames']['variantAttributeName'] = $value;
									}
									else {
										$arrayToReturn[$key] = $value;
									}
								}
								else {
									$changedVariationData[$key] = $value;
								}
							}
							$variationData = $changedVariationData;
							$variation_array_used = false;
						}
					}else{
						if( $_product->get_type() == "variation") {
 
							$changedVariationData = array();
							foreach ($variationData as $key => $value) {
								if( in_array($key, $keysToReplaceInAttributeCase)) {

									if($key == 'variantAttributeNames') {
				

											 
										$arrayToReturn['variantAttributeNames']['variantAttributeName'] = $value;
									}
									else {
										$arrayToReturn[$key] = $value;
									}
								}
								else {
									$changedVariationData[$key] = $value;
								}
							}
							$variationData = $changedVariationData;
							$variation_array_used = false;
						}

					}
				}
				continue;	
			}
			 
			if (strpos($attribute, '/') !== false) {

				$arrayIndex = explode('/', $attribute);
				$value = $this->fetchMetaValueOfProduct( $product_id, $assignedWalmartCategory.'_'.$attribute );

				/* validation check */
				if( isset($attributeInfo[$assignedWalmartCategory.'::'.$attribute]['walmart_attribute_required']) && ($attributeInfo[$assignedWalmartCategory.'::'.$attribute]['walmart_attribute_required'] == 'Required')) {
					if(!$this->validateValueAndKeepTrackOfError($product_id,$value,$attribute)) { continue;}
				}
				/* validation check */
				if(!isset($value) || empty($value) || $value == "0" || $value == "null") { continue;}
				
				$arrayIndexTemp = $arrayIndex;
				array_shift($arrayIndex);
				$temp = $this->makeMultiDimensionalArray($arrayIndex,$value);

				if($attribute == 'variantAttributeNames/variantAttributeName')
				{	
					if($variation_array_used)
					{
						$_product = wc_get_product($product_id);
						if(WC()->version<'3.0.0'){
							if( $_product->product_type == "variation") {
								$changedVariationData = array();
								foreach ($variationData as $key => $value) {
									if( in_array($key, $keysToReplaceInAttributeCase)) {
										if($key == 'variantAttributeNames') {
												 
											$arrayToReturn['variantAttributeNames']['variantAttributeName'] = $value;
										}
										else {
											$arrayToReturn[$key] = $value;
										}
									}
									else {
										$changedVariationData[$key] = $value;
									}
								}
								$variationData = $changedVariationData;
								$variation_array_used = false;
							}
						}else{
							if( $_product->get_type() == "variation") {
								$changedVariationData = array();
								foreach ($variationData as $key => $value) {
									if( in_array($key, $keysToReplaceInAttributeCase)) {
										if($key == 'variantAttributeNames') {
												 
											$arrayToReturn['variantAttributeNames']['variantAttributeName'] = $value;
										}
										else {
											$arrayToReturn[$key] = $value;
										}
									}
									else {
										$changedVariationData[$key] = $value;
									}
								}
								$variationData = $changedVariationData;
								$variation_array_used = false;
							}

						}
						
					}	
				}
				else
				{
					if(!empty($arrayToReturn[$arrayIndexTemp[0]])) 
					{
						$arrayToReturn[$arrayIndexTemp[0]] = $this->performRecursiveArrayMerge($temp,$arrayToReturn,$arrayIndexTemp[0]);
					}
					else 
					{
						$arrayToReturn[$arrayIndexTemp[0]] = $temp;
					}
				} 

			}
			else {
				
				if($attribute == 'brand')
				{
					$value = $this->fetchMetaValueOfProduct( $product_id, '_umb_brand' );
					/* validation check */

					if(!$this->validateValueAndKeepTrackOfError($product_id,$value,'Product Brand')) { continue;} 
					/* validation check */
				}	
				else
				{
					 
					$value = $this->fetchMetaValueOfProduct( $product_id, $assignedWalmartCategory.'_'.$attribute );
					/* validation check */
					
					if(isset($attributeInfo[$assignedWalmartCategory.'::'.$attribute]['walmart_attribute_required']) && ($attributeInfo[$assignedWalmartCategory.'::'.$attribute]['walmart_attribute_required'] == 'Required')) {
						if(!$this->validateValueAndKeepTrackOfError($product_id,$value,$attribute)) { continue;}
					}
					/* validation check */
				}
 
				if(!isset($value) || empty($value)) { continue;}
				 
				$arrayToReturn[$attribute] = $value;
				
			}
		}

		/* additional setup in case both child and parent category exists :: start */
		$assignedWalmartCategory = explode("/", $assignedWalmartCategory);

		$parentCategory = $assignedWalmartCategory[0];
		$childCategory = end($assignedWalmartCategory);
		
		if( count($assignedWalmartCategory) > 1 ) {
			
			foreach ($categories as $key => $category) {
				
				if($category['name'] == $childCategory) {
 				 
					$walmart_attributes = $category['walmart_attributes'];
					$walmart_attributes = explode(",",$walmart_attributes);
					$alteredWalmartAttribute = array();
					foreach ($walmart_attributes as $key => $walmart_attribute) {
						$walmart_attribute = explode("/", $walmart_attribute);
						$walmart_attribute = $walmart_attribute[0];
						$alteredWalmartAttribute[] = $walmart_attribute;
					}
					$walmart_attributes = $alteredWalmartAttribute;
					unset($alteredWalmartAttribute);

					$assignedWalmartCategory = end($assignedWalmartCategory);
					
					foreach ($arrayToReturn as $key => $value) {
						if( in_array($key, $walmart_attributes) ) {
							$arrayToReturnAltered[$key] = $value;
						}
						else {
							$arrayToReturnAltered[$key] = $value;
						}
					}
					break;
				}
			}

			/* additional check for nothing inside child category :: start */
			if( !isset($arrayToReturnAltered) ) {
				$arrayToReturnAltered = '';
			}
			/* additional check for nothing inside child category :: end */

			$arrayToReturn = $arrayToReturnAltered;
		}
		/* additional setup in case both child and parent category exists :: end */
		return $arrayToReturn;
		
	}
 
	/**
	 * This function performs recursive array merge to format xml correctly as needed by walmart.
	 * @name performRecursiveArrayMerge()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function performRecursiveArrayMerge($temp,$productMetaInfoToSend,$currentIndex){
		if(array_key_exists ($currentIndex,$productMetaInfoToSend)) {
			return array_merge_recursive($productMetaInfoToSend[$currentIndex],$temp);
		}
	}
 
	/**
	 * This function check if all data available or not in array before making xml and send it to walmart.
	 * @name validateValueAndKeepTrackOfError()
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @link  http://www.cedcommerce.com/
	 */
	function validateValueAndKeepTrackOfError($product_id,$value,$attribute) {
		if(!isset($value) || empty($value)) {
			$this->errorTrackArray[$product_id][] = $attribute;
			$this->isAllRequiredValuePresent = false;
			return false;
		}
		else{
			return true;
		}
	}
}