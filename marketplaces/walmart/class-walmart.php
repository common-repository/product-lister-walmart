<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if( !class_exists( 'CED_UMB_walmart_manager' ) ) :

/**
 * single product related functionality.
*
* Manage all single product related functionality required for listing product on marketplaces.
*
* @since      1.0.0
* @package    Walmart Product Lister
* @subpackage Walmart Product Lister/marketplaces/walmart
* @author    CedCommerce <plugins@cedcommerce.com>
*/
class CED_UMB_walmart_manager{

	/**
	 * The Instace of CED_UMB_Walmart_Manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_UMB_Walmart_Manager class.
	 */
	private static $_instance;
	
	/**
	 * Instance of CED_UMB_walmart_api.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     $_api    Instance of CED_UMB_walmart_api class.
	 */
	private $_api;
	private $inventoryXMLString = '';
	private $isRunInventoryUpdate = false;

	public $walmart_xml_lib;
	public $walmart_inventory_update;
	public $walmart_order;
	public $walmart_feed_manager;

	public $profile_data = array();
	public $isProfileAssignedToProduct = false;
	
	/**
	 * CED_UMB_Walmart_Manager Instance.
	 *
	 * Ensures only one instance of CED_UMB_Walmart_Manager is loaded or can be loaded.
	 *
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 * @static
	 * @return CED_UMB_Walmart_Manager instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public $marketplaceID = 'walmart';
	public $marketplaceName = 'Walmart';
	
	/**
	 * Constructor.
	 *
	 * registering actions and hooks for walmart.
	 *
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	public function __construct() {
		
		require_once 'lib/class-walmart-request.php';

	 	add_action( 'ced_umb_order_refund_processing_Walmart', array($this,'ced_umb_process_refund' ),10,5);
		add_action('ced_umb_track_schedule', array($this,'ced_umb_track_process'));
		add_action( 'ced_umb_render_marketplace_feed_details', array( $this, 'ced_umb_render_marketplace_feed_details'), 10, 2 );
		add_filter( 'ced_umb_add_new_available_marketplaces' , array( $this, 'ced_umb_add_new_available_marketplaces' ), 10, 1 );
		add_filter( 'ced_umb_render_marketplace_configuration_settings' , array( $this, 'ced_umb_render_marketplace_configuration_settings' ), 10, 2 );
		add_action( 'ced_umb_save_marketplace_configuration_settings' , array( $this, 'ced_umb_save_marketplace_configuration_settings'), 10, 2 );
		add_action( 'ced_umb_validate_marketplace_configuration_settings' , array( $this, 'ced_umb_validate_marketplace_configuration_settings'), 10, 2 );
		add_action( 'admin_enqueue_scripts',array($this,'load_walmart_scripts'));
		add_action( 'wp_ajax_updateWalmartCategoriesInDB', array($this,'updateWalmartCategoriesInDB'));
		add_filter( 'ced_umb_required_product_fields', array( $this, 'add_walmart_required_fields' ), 11, 2 );
		add_action('wp_ajax_fetch_walmart_attribute_for_selected_category', array($this,'fetch_walmart_attribute_for_selected_category'));
		add_action('wp_ajax_fetch_walmart_attribute_for_selected_category_for_profile_section', array($this,'fetch_walmart_attribute_for_selected_category_for_profile_section'));
		add_action('ced_umb_required_fields_process_meta_simple', array($this,'ced_umb_required_fields_process_meta_simple'), 11, 1 );
		add_action('ced_umb_framework_product_fields',array($this,'ced_umb_framework_product_fields'),10,2);
		add_filter( 'umb_save_additional_profile_info', array( $this, 'umb_save_additional_profile_info' ), 11, 1 );
		add_action('wp_ajax_umb_walmart_acknowledge_order', array($this,'umb_walmart_acknowledge_order'));
		add_action('wp_ajax_ced_umb_taxcode_search', array($this,'ced_umb_taxcode_search'));
		add_action("ced_Walmart_additional_configuration", array($this,'ced_walmart_additional_configuration'));
		add_action('wp_ajax_ced_walmart_sync_product_save', array($this, 'ced_walmart_sync_product_save'));
		add_action('wp_ajax_ced_walmart_sync_price_save', array($this, 'ced_walmart_sync_price_save'));
		add_action('wp_ajax_ced_walmart_sync_inventory_save', array($this, 'ced_walmart_sync_inventory_save'));
		
		// show the date of feed.
		add_action('umb_file_status_time',array($this,'show_formatted_date_time'),10,2);
		
		//enable schedule 
		add_action('umb_advance_sync', array($this,'run_walmart_update_inventoryprice_schedule'));
		add_action('ced_umb_alter_csv_header_at_export_time', array($this,'ced_umb_alter_csv_header_at_export_time'),20);
		add_action('ced_umb_alter_csv_prefilled_data', array($this,'ced_umb_alter_csv_prefilled_data'),20,2);
		add_action('ced_umb_import_data_from_csv_to_DB', array($this,'ced_umb_import_data_from_csv_to_DB'),20);
		add_action('ced_umb_alter_data_to_render_on_profile', array($this,'ced_umb_alter_data_to_render_on_profile'),20,2);
		add_action('ced_umb_render_imp_links', array($this,'ced_umb_render_imp_links'),10);
		$this->load_dependencies();
	}

	/**
	* Function to change important links
	*/
	function ced_umb_render_imp_links() {
	 }

	/**
	* Function to change details for created profile
	*/
	function ced_umb_alter_data_to_render_on_profile( $valueForDropdown, $field_id ) {
		if( $field_id == 'umb_walmart_category' ) {
			unset($valueForDropdown[0]);
		}
		return $valueForDropdown;
	}
 
	/**
	* Functio to show formatted date and time on walmart
	*/
	function show_formatted_date_time($item,$marketplace){
		if($marketplace==$this->marketplaceID){
			$datetime = isset($item['name']) ? $item['name'] : '';
			if($datetime){
				$datetime = explode( " : ", $datetime);
				$datetime = end($datetime);
				echo date('d M, Y H:i:s', $datetime);
				return;
			}
			echo '-';
			return;
		}
	}
	 /**
	 * Function for additional configuration
	 */
	function ced_walmart_additional_configuration()
	{
		include_once 'partials/return_address.php';
	}
	
	/**
	 * function to get current status of the product on walmart
	 * @name getProductstatus
	 * 
	 */
	public function getProductstatus($sku)
	{
		echo __('Not UPloaded','ced-umb');die();
		 
	}
	/**
	 * function to handle number of post request on walmart
	 * @name ced_umb_tracking_post_request
	 */
	
	function ced_umb_tracking_upload_post_request($currenttime, $status = false)
	{
		delete_option('ced_umb_tracking_upload_post_request');
		$request_track = get_option('ced_umb_tracking_upload_post_request', false);
		if(isset($request_track['start']))
		{
			$starttime = $request_track['start'];
			$timediff = $currenttime - $starttime;
			$mins = intval(($timediff / 60) % 60);
			if($mins < 60)
			{
				if(isset($request_track['count']))
				{
					$count = $request_track['count'];
					if($count < 10 )
					{
						$request_track['count'] = $request_track['count'] + 1;
						$response['response'] = true;
					}
					else
					{
						$response['mins'] = $mins;
						$response['response'] = false;
					}		
				}
			}
			else
			{
				delete_option('ced_umb_tracking_upload_post_request');
				$request_track['start'] = $currenttime;
				$request_track['count'] = 1;
				$response['response'] = true;
			}		
		}
		else
		{
			$request_track['start'] = $currenttime;
			$request_track['count'] = 1;
			$response['response'] = true;
			
		}	
		
		if($status)
		{	
			update_option('ced_umb_tracking_upload_post_request', $request_track);
		}	
		return $response;
	}
		
	/**
	 * function to handle number of post request on walmart
	 * @name ced_umb_tracking_post_request
	 */
	
	function ced_umb_tracking_inventory_post_request($currenttime, $status = false)
	{
		delete_option('ced_umb_tracking_inventory_post_request');
		$request_track = get_option('ced_umb_tracking_inventory_post_request', false);

		if(isset($request_track['start']))
		{
			$starttime = $request_track['start'];
			$timediff = $currenttime - $starttime;
			$mins = intval(($timediff / 60) % 60);
			if($mins < 60)
			{
				if(isset($request_track['count']))
				{
					$count = $request_track['count'];
					if($count < 10 )
					{
						$request_track['count'] = $request_track['count'] + 1;
						$response['response'] = true;
					}
					else
					{
						$response['mins'] = $mins;
						$response['response'] = false;
					}
				}
			}
			else
			{
				delete_option('ced_umb_tracking_inventory_post_request');
				$request_track['start'] = $currenttime;
				$request_track['count'] = 1;
				$response['response'] = true;
			}
		}
		else
		{
			$request_track['start'] = $currenttime;
			$request_track['count'] = 1;
			$response['response'] = true;
				
		}
	
		if($status)
		{
			update_option('ced_umb_tracking_inventory_post_request', $request_track);
		}
		return $response;
	}
	
	/**
	 * function to handle number of post request on walmart
	 * @name ced_umb_tracking_post_request
	 */
	
	function ced_umb_tracking_price_post_request($currenttime, $status = false)
	{
		delete_option('ced_umb_tracking_price_post_request');
		$request_track = get_option('ced_umb_tracking_price_post_request', false);
		if(isset($request_track['start']))
		{
			$starttime = $request_track['start'];
			$timediff = $currenttime - $starttime;
			$mins = intval(($timediff / 60) % 60);
			if($mins < 60)
			{
				if(isset($request_track['count']))
				{
					$count = $request_track['count'];
					if($count < 10 )
					{
						$request_track['count'] = $request_track['count'] + 1;
						$response['response'] = true;
					}
					else
					{
						$response['mins'] = $mins;
						$response['response'] = false;
					}
				}
			}
			else
			{
				delete_option('ced_umb_tracking_price_post_request');
				$request_track['start'] = $currenttime;
				$request_track['count'] = 1;
				$response['response'] = true;
			}
		}
		else
		{
			$request_track['start'] = $currenttime;
			$request_track['count'] = 1;
			$response['response'] = true;
	
		}
	
		if($status)
		{
			update_option('ced_umb_tracking_price_post_request', $request_track);
		}
		return $response;
	}
	
  
	/**
	 * function to process tracking
	 * @name ced_umb_track_process
	 * 
	 */
	public function ced_umb_track_process()
	{
		global $cedumbhelper;
		$options = get_option('ced_umb_wlamart_initialoption');
		if($options['validated'] != 'yes')
		{
			$issue = __("We have observed that you have activated the extension but credentials are  not validated yet",'ced-umb');
			$param['action'] = "Validation";
			$param['issue'] = $issue;
			$cedumbhelper->ced_umb_notifcation_mail($param);
			return;
		}
		if($options['uploaded'] != 'yes')
		{
			$issue = __("We have observed that you have not uploaded any product yet",'ced-umb');
			$param['action'] = "Upload";
			$param['issue'] = $issue;
			$cedumbhelper->ced_umb_notifcation_mail($param);
			return;
		}
		if($options['acknowledged'] != 'yes')
		{
			$issue = __("We have observed that your orders are not getting acknowledged",'ced-umb');
			$param['action'] = "Acknowledged";
			$param['issue'] = $issue;
			$cedumbhelper->ced_umb_notifcation_mail($param);
			return;
		}
	}
	/**
	 * Add new marketplace here for walmart.
	 *
	 * @name ced_umb_add_new_available_marketplaces
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
 	function ced_umb_add_new_available_marketplaces( $availableMarketPlace ) {
		$availableMarketPlace[] =array(
			'id' => $this->marketplaceID, 
			'name' => $this->marketplaceName, 
			'status' => (getMarketPlaceStatus( $this->marketplaceID )) ? '<b style="color:green">'.__("Enable","ced-umb").'</b>': '<b style="color:red">'.__("Disbale",'ced-umb').'</b>',
			'validate' => (isMarketPlaceConfigurationsValidated( $this->marketplaceID )) ?  '<b style="color:green">'.__('Validated','ced-umb').'</b>' : '<b style="color:red">'.__("Need Validation",'ced-umb').'</b>'
		);
		
		return $availableMarketPlace;
	}

	/**
	 * Walmart Configuration Setting
	 *
	 * @name ced_umb_render_marketplace_configuration_settings
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function ced_umb_render_marketplace_configuration_settings( $configSettings, $marketplaceID ) 
	{
		
		if( $marketplaceID != $this->marketplaceID ) {
			return $configSettings;
		}
		else {
			$configSettings=array();

			$saved_walmart_details = get_option( 'ced_umb_walmart_configuration', true );
			$client_id = isset( $saved_walmart_details['client_id'] ) ? esc_attr( $saved_walmart_details['client_id'] ) : '';
			$client_secret = isset( $saved_walmart_details['client_secret'] ) ? esc_attr( $saved_walmart_details['client_secret'] ) : '';
			$channel_id = isset( $saved_walmart_details['channel_id'] ) ?  $saved_walmart_details['channel_id']  : "";

			$configSettings['configSettings'] = array(
				'ced_umb_walmart_client_id' => array(
					'name' => 'Client Id',
					'type' => 'text',
					'value' => $client_id
					),
				'ced_umb_walmart_secret_key' => array(
					'name' => 'Client Secret',
					'type' => 'text',
					'value' => $client_secret
					),
				'ced_umb_walmart_channel_id' => array(
					'name' => 'Consumer Channel Type Id',
					'type' => 'text',
					'value' => "7b2c8dab-c79c-4cee-97fb-0ac399e17ade"
					)
				);

			$configSettings['showUpdateButton'] = isMarketPlaceConfigurationsValidated( $this->marketplaceID );
			$configSettings['marketPlaceName'] = $this->marketplaceName;
			return $configSettings;
		}
	}

	/**
	 * Save walmart Marketplace Configuration Setting
	 *
	 * @name ced_umb_save_marketplace_configuration_settings
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function ced_umb_save_marketplace_configuration_settings( $configSettingsToSave, $marketplaceID ) {
		global $cedumbhelper;
			if( $marketplaceID == $this->marketplaceID ) {
				 $walmart_calls_count=get_option('walmart_calls_count',true);
	    		if($walmart_calls_count<=50){
				$client_id = isset($configSettingsToSave['ced_umb_walmart_client_id']) ? sanitize_text_field( $configSettingsToSave['ced_umb_walmart_client_id'] ) : '';
				$client_secret = isset($configSettingsToSave['ced_umb_walmart_secret_key']) ? sanitize_text_field( $configSettingsToSave['ced_umb_walmart_secret_key'] ) : '';
				$channel_id = isset($configSettingsToSave['ced_umb_walmart_channel_id']) ? sanitize_text_field( $configSettingsToSave['ced_umb_walmart_channel_id'] ) : '';
				if($client_id && $client_secret)
				{
					$walmart_configuration = array();
					$walmart_configuration['client_id'] = $client_id;
					$walmart_configuration['client_secret'] = $client_secret;
					$walmart_configuration['channel_id'] = $channel_id;
					update_option( 'ced_umb_walmart_configuration', $walmart_configuration );
					$notice['message'] = __('Credentials saved successfully','ced-umb');
					$notice['classes'] = "notice notice-success";
					$validation_notice[] = $notice;
					$cedumbhelper->umb_print_notices($validation_notice);
					unset($validation_notice);
				}	
				else
				{
					$notice['message'] = __('Client Id and Client Secret can\'t be blank','ced-umb');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$cedumbhelper->umb_print_notices($validation_notice);
					unset($validation_notice);
				}
				update_option("ced_umb_save_".$this->marketplaceID,"yes");
				update_option("ced_umb_validate_".$this->marketplaceID,"no");
			}
		}
	}

	/**
	 * Validate walmart Marketplace Configuration Setting
	 *
	 * @name ced_umb_validate_marketplace_configuration_settings
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	public function ced_umb_validate_marketplace_configuration_settings( $configSettingsToSave=array(), $marketplaceID='' ) {
		global $cedumbhelper;
		
		try 
		{
			if( $marketplaceID == $this->marketplaceID ) 
			{
				$saved_walmart_details = get_option( 'ced_umb_walmart_configuration', true );
				$client_id = isset( $saved_walmart_details['client_id'] ) ? esc_attr( $saved_walmart_details['client_id'] ) : '';
				$client_secret = isset( $saved_walmart_details['client_secret'] ) ? esc_attr( $saved_walmart_details['client_secret'] ) : '';
				$channelId = isset( $saved_walmart_details['channel_id'] ) ?  $saved_walmart_details['channel_id']  : "7b2c8dab-c79c-4cee-97fb-0ac399e17ade";
	
				if($client_id && $client_secret)
				{
					$walmartRequest = new WalmartRequest();
					
					if(is_wp_error($walmartRequest)){
						
						$notice['message'] = __('Configuration setting is incompleted or skip validation, integration will not work unitll you validate the configuration data for Walmart','ced-umb');
						$notice['classes'] = "notice notice-success";
						$validation_notice[] = $notice;
						$cedumbhelper->umb_print_notices($validation_notice);
						unset($validation_notice);
					}else if(is_null($walmartRequest)){
						
						$notice['message'] = __('Configuration setting is incompleted or skip validation, integration will not work unitll you validate the configuration data for Walmart','ced-umb');
						$notice['classes'] = "notice notice-success";
						$validation_notice[] = $notice;
						$cedumbhelper->umb_print_notices($validation_notice);
						unset($validation_notice);
					}else{

						require_once CED_UMB_DIRPATH.'marketplaces/walmart/lib/walmart-sdk-master/autoload.php';
						 $client = new \Walmart\Feed([
				        'client_id' => $client_id,
				        'client_secret' => $client_secret
				   		 ]);

				   		 $walmart_calls_count=get_option('walmart_calls_count',true);
	    	
	    				if($walmart_calls_count<=50){

    					$token = $client->generateToken();

    					}else{

    						$notice['message'] = __('Maximum product upload call limit exceeded.','ced-umb');
							$notice['classes'] = "notice notice-error";
							$validation_notice[] = $notice;
							$cedumbhelper->umb_print_notices($validation_notice);
							unset($validation_notice);
    					}

						$token = isset($token['OAuthTokenDTO']['accessToken']) ? $token['OAuthTokenDTO']['accessToken'] :"";

						if(isset($token) && !empty($token))
						{
							update_option('ced_umb_walmart_token',$token);
							set_transient( 'ced_umb_walmart_token_transient',$token, 60*15 );
							update_option('ced_umb_enabled_marketplaces',array('walmart'));
							$notice['message'] = __('Configuration setting is validated','ced-umb');
							$notice['classes'] = "notice notice-success";
							$validation_notice[] = $notice;
							$cedumbhelper->umb_print_notices($validation_notice);
							unset($validation_notice);
						}
						else 
						{
							
							if(empty($token))
							{
								$notice['message'] = __('Configuration setting is not validated. Please check your credentials','ced-umb');
							}
							else
							{
								$response = __("There is some problem with your hosting. Please Contact your HOST provider about that issue $response.",'ced-umb');
								$notice['message'] = $response;
								
								$param['action'] = "API CREDENTIAL VALIDATION";
								$param['issue'] = "There is some problem with your hosting. Please Contact your HOST provider about that issue $response.";
								$cedumbhelper->ced_umb_notifcation_mail($param);
							}
							
							$notice['classes'] = "notice notice-error";
							$validation_notice[] = $notice;
							$cedumbhelper->umb_print_notices($validation_notice);
							unset($validation_notice);
						}
					}
				}
				else
				{
					$notice['message'] = __('Client Id and Client Secret can\'t be blank','ced-umb');
					$notice['classes'] = "notice notice-error";
					$validation_notice[] = $notice;
					$cedumbhelper->umb_print_notices($validation_notice);
					unset($validation_notice);
				}
			}
		}
		catch(Exception $e)
		{
			$message = $e->getMessage();
			$param['action'] = "API CREDENTIAL VALIDATION";
			$param['issue'] = __("API Cerdentials is not valid. Please check again. Issue is : $message",'ced-umb');
			$cedumbhelper->ced_umb_notifcation_mail($param);
			
			$notice['message'] = __("API Cerdentials is not valid. Please check again.",'ced-umb');
			$notice['classes'] = "notice notice-error";
			$validation_notice[] = $notice;
			$cedumbhelper->umb_print_notices($validation_notice);
			unset($validation_notice);
		}
	}
	
	/**
	 * Render walmart Marketplace Feed Details
	 *
	 * @name ced_umb_render_marketplace_feed_details
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function ced_umb_render_marketplace_feed_details( $feedID, $marketplaceID) 
	{
		if( $marketplaceID == $this->marketplaceID ) {
			
			global $wpdb;
			$prefix = $wpdb->prefix . CED_UMB_PREFIX;
			$tableName = $prefix.'fileTracker';
			$sql = "SELECT * FROM `$tableName` WHERE `id`=$feedID";
			$detail = $wpdb->get_results($sql,'ARRAY_A');
			if(!is_array($detail) || !is_array($detail[0])) {
			?><h2><?php	_e('Sorry Details Not Found','ced-umb');?></h2><?php
			}
			 
			$detail = $detail[0];
			$response = $detail['response'];
			$response = json_decode($response,true);

			$feedId = $detail['product_ids'];//temp added
			if( $feedId ) {
				$response = $this->walmart_feed_manager->getFeedItemsStatus($feedId);
				$statusTrackingArray = $this->walmart_feed_manager->getStatusTrackingArray($response);
			}
			 
			
			if(isset($response['error'])) {
				?><h2 class="ced_umb_setting_header ced_umb_bottom_margin"><?php _e('Feed General Information','ced-umb');?></h2><?php
				echo '<table class="wp-list-table widefat fixed striped">';
					echo '<tbody>';
					foreach ($response['error'] as $key => $value) {
						if(is_array($value)) {
							continue;
						}
						echo '<tr>';
						echo '<th class="manage-column">'.ucfirst($key).'</th>';
						if(is_array($value)) {
							echo '<td class="manage-column">'.''.'</td>';
						}
						else {
							echo '<td class="manage-column">'.$value.'</td>';
						}
						echo '</tr>';
					}
					echo '</tbody>';
				echo '</table>';		
			}
			else if(isset($response['meta']))
			{	
				$responseMeta = $response['meta'];
				$feedInfoArray = array();
				$feedInfoArray['feedId'] = $responseMeta['feedId'];
				$feedInfoArray['feedStatus'] = $responseMeta['feedStatus'];
				$feedInfoArray['itemsReceived'] = $responseMeta['itemsReceived'];
				$feedInfoArray['itemsSucceeded'] = $responseMeta['itemsSucceeded'];
				$feedInfoArray['itemsFailed'] = $responseMeta['itemsFailed'];
				$feedInfoArray['itemsProcessing'] = $responseMeta['itemsProcessing'];
				$feedInfoArray['ingestionErrors'] = $responseMeta['ingestionErrors'];
	
				?><h2 class="ced_umb_setting_header ced_umb_bottom_margin"><?php
				_e('Feed General Information','ced-umb');
				?></h2><?php
				echo '<table class="wp-list-table widefat fixed striped">';
					echo '<tbody>';
				foreach ($feedInfoArray as $key => $value) {
					echo '<tr>';
					echo '<th class="manage-column">'.ucfirst($key).'</th>';
					echo '<td class="manage-column">';
					if($key == 'ingestionErrors') {
						if(is_array($value) && !empty($value)) {
							foreach ($value as $inr_value) {
								echo '<span>'.$inr_value.'</span>';
							}
						}
						else {  __('NONE','ced-umb'); }
					}
					else {
						echo $value;
					}
					echo '</td>';
	
					echo '</tr>';
				}
					echo '</tbody>';
				echo '</table>';
				
				$tableHeader = array( __('Product SKU','ced-umb'), __('Product Status','ced-umb'), __('Errors','ced-umb') );
				if( is_array($statusTrackingArray) && !empty($statusTrackingArray) ) {
					echo '<br/>';
					echo '<h2 class="ced_umb_setting_header ced_umb_bottom_margin">'.__('Feed Details','ced-umb').'</h2>';
					echo '<table class="wp-list-table widefat fixed striped">';
					echo '<thead>';
					echo '<tr>';
					foreach ($tableHeader as $value) {
						echo '<th class="manage-column">'.$value.'</th>';
					}
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					foreach ($statusTrackingArray as $value) {
						echo '<tr>';
						echo '<td class="manage-column">'.$value['sku'].'</td>';
						echo '<td class="manage-column">'.$value['ingestionStatus'].'</td>';
						echo '<td class="manage-column">';
						if(is_array($value['ingestionErrors']) && !empty($value['ingestionErrors'])) {
							foreach ($value['ingestionErrors'] as $ingestionError) {
								if(is_array($ingestionError))
								{
									foreach ($ingestionError as $key=>$ingestError) {
										echo '<span><b>'.$key.': </b>'.$ingestError.'</span><br/>';
									}
								}	
								else
								{
									echo '<span>'.$ingestionError.'</span>';
								}	
								
							}
						}
						else {
							echo 'NONE';
						}
						echo '</td>';
						echo '</tr>';	
					}
					echo '</tbody>';
					echo '</table>';
				}
			}
			else
			{
				global $cedumbhelper;
				if(is_array($response) && !empty($response))
				{
					$message = __("There is some problem with your hosting. Please Contact your host provider about that issue : ",'ced-umb');
					$issue = __("There is some problem with your hosting. Please Contact your host provider about that issue : ",'ced-umb');
					foreach($response as $res) {
						$issue .= $res;
						$message .= $res;
					}	
				}
				else
				{
					$issue = __("There is some problem with your hosting. Please Contact your host provider.",'ced-umb');
						
					$message = __("There is some problem with your hosting. Please Contact your host provider.",'ced-umb');
				}

				$param['action'] = "GET FEED DETAILS";
				$param['issue'] = $issue;
				$cedumbhelper->ced_umb_notifcation_mail($param);
					
				$classes = "notice notice-error";
				$validation_notice[] = array('message'=>$message, 'classes'=>$classes);
				if(count($validation_notice))
				{
					$cedumbhelper->umb_print_notices($validation_notice);
					unset($validation_notice);
				}
			}
		}
	}
	
	/**
	 * Search Taxcode
	 *
	 * @name ced_umb_taxcode_search
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function ced_umb_taxcode_search()
	{
		$stringTobesearched = $_POST['stringTobesearched'];
		$taxcodes = $this->walmart_xml_lib->readWalmartInfoFromJsonFile('WalmartTaxCodes.json');
		$searched = array();
		$htmoToappend = "";
		if(is_numeric($stringTobesearched))
		{
			foreach ($taxcodes as $taxcode)
			{
				if(stripos($taxcode['tax_code'], $stringTobesearched) !== false)
				{
					$htmoToappend .= "<tr data-taxcode='".$taxcode['tax_code']."'>";
					$htmoToappend .=	"<td>".$taxcode['tax_code']."</td>";
					$htmoToappend .=	"<td>".__($taxcode['sub_cat_desc'],"ced-umb")."</td>";
					$htmoToappend .=	"<td>".__($taxcode['cat_desc'],"ced-umb")."</td>";
					$htmoToappend .=	"</tr>";
				}
			}
		}
		else{
			foreach ($taxcodes as $taxcode)
			{
				if(stripos($taxcode['sub_cat_desc'], $stringTobesearched) !== false)
				{
					$htmoToappend .= "<tr data-taxcode='".$taxcode['tax_code']."'>";
					$htmoToappend .=	"<td>".$taxcode['tax_code']."</td>";
					$htmoToappend .=	"<td>".__($taxcode['sub_cat_desc'],"ced-umb")."</td>";
					$htmoToappend .=	"<td>".__($taxcode['cat_desc'],"ced-umb")."</td>";
					$htmoToappend .=	"</tr>";
				}
			}
		}
		echo $htmoToappend;die;
	}
	
	/**
	 * Save Profile Information
	 *
	 * @name umb_save_additional_profile_info
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function umb_save_additional_profile_info( $profile_data ) 
	{
		if(isset($_POST['ced_umb_walmart_attributes_ids_array'])) {
			foreach ($_POST['ced_umb_walmart_attributes_ids_array'] as $key ) {
				if(isset($_POST[$key])) {
					$fieldid = isset($key) ? $key : '';
					$fieldvalue = isset($_POST[$key]) ? $_POST[$key][0] : null;
					$fieldattributemeta = isset($_POST[$key.'_attibuteMeta']) ? $_POST[$key.'_attibuteMeta'] : null;
					$profile_data[$fieldid] = array('default'=>$fieldvalue,'metakey'=>$fieldattributemeta);
				}
			}
		}
		return $profile_data;
	}
	
	
	/**
	 * updating product on walmart on any change in local shop
	 *
	 * @name ced_umb_update_product
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	public function ced_umb_update_product($post_id)
	{
		if(WC()->version<'3.0.0'){
			$WC_Product = new WC_Product($post_id);
			$umb_walmart_product_update_sync = get_option("umb_walmart_product_update_sync",false);
			if(isset($WC_Product->post->post_type) && $WC_Product->post->post_type == "product" && $umb_walmart_product_update_sync == 'true')
			{
				$xmlString = $this->walmart_inventory_update->updateInventoryOfSingleProduct($post_id);
				$result = $this->walmart_inventory_update->makeInventoryXMLFileToSendOnWalmart($xmlString);
				$this->runInventoryUpdate();
				$priceXmlstring = $this->walmart_price_update->updateRegularPriceOfSingleProduct($post_id);
				$priceresult = $this->walmart_price_update->makePriceXMLFileToSendOnWalmart($priceXmlstring);
				$this->runPriceUpdate();
			} 
		}else{
			$WC_Product = wc_get_product($post_id);
			$umb_walmart_product_update_sync = get_option("umb_walmart_product_update_sync",false);
			if( (null !==$WC_Product->get_type()) && $WC_Product->get_type() == "product" && $umb_walmart_product_update_sync == 'true')
			{
				$xmlString = $this->walmart_inventory_update->updateInventoryOfSingleProduct($post_id);
				$result = $this->walmart_inventory_update->makeInventoryXMLFileToSendOnWalmart($xmlString);
				$this->runInventoryUpdate();
				$priceXmlstring = $this->walmart_price_update->updateRegularPriceOfSingleProduct($post_id);
				$priceresult = $this->walmart_price_update->makePriceXMLFileToSendOnWalmart($priceXmlstring);
				$this->runPriceUpdate();
			} 
		} 
		
	}
	
	/**
	 * Get Time Zone
	 *
	 * @name ced_umb_update_product
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	public function getStandardOffsetUTC()
	{
		 
		$timezone = date_default_timezone_get();
	
		if($timezone == 'UTC') {
			return '';
		} else {
			$timezone = new DateTimeZone($timezone);
			$transitions = array_slice($timezone->getTransitions(), -3, null, true);
	
			foreach (array_reverse($transitions, true) as $transition)
			{
				if ($transition['isdst'] == 1)
				{
					continue;
				}
				return sprintf('UTC %+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
			}
	
			return false;
		}
	}


	/**
	 * Walmart Product Fields
	 *
	 * @name ced_umb_framework_product_fields
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function ced_umb_framework_product_fields($framework_fields, $post) 
	{
		$walmartSpecificFields = array(
			array(
				'type' => '_text_input',
				'id' => '_umb_walmart_Price',
				'fields' => array(
					'id'                => '_umb_walmart_Price',
					'label'             => __( 'Walmart Product Price', 'ced-umb' ),
					'desc_tip'          => true,
					'description'       => __( 'Product price you want to send to Walmart.', 'ced-umb' ),
					'type'              => 'text',
					'class'				=> 'wc_input_price'
				)
			),
			array(
				'type' => '_text_input',
				'id' => '_umb_walmart_Inventory',
				'fields' => array(
					'id'                => '_umb_walmart_Inventory',
					'label'             => __( 'Walmart Inventory', 'ced-umb' ),
					'desc_tip'          => true,
					'description'       => __( 'Product Inventory you want to send to Walmart.', 'ced-umb' ),
					'type'              => 'number'
				)
			)	
		);

		$framework_fields['walmart'] = $walmartSpecificFields;
		return $framework_fields;
	}
	/**
	 * validate the function.
	 *
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	public function validate($proId)
	{
		$this->walmart_product_update->fetchAssignedProfileDataOfProduct($proId);
		$assignedWalmartCategory = $this->walmart_product_update->fetchMetaValueOfProduct( $proId, '_umb_walmart_category' );
		
		if(!isset($assignedWalmartCategory) || empty($assignedWalmartCategory) || $assignedWalmartCategory == "" || $assignedWalmartCategory == "null"){
			$statusArray['isReady'] = false;
			$statusArray['missingData'] = array(__('Walmart Category Not Assigned.','ced-umb'));
		}
		else{
			$proid = array();
			$proid[] = $proId;
			$this->walmart_product_update->errorTrackArray = array();
			$this->makeProductXMLFileToSendOnWalmart($proid);
			$errorsArray = $this->walmart_product_update->errorTrackArray;
			$statusArray = array();
			if(empty($errorsArray)){
				$statusArray['isReady'] = true;
				update_post_meta( $proId , 'ced_umb_is_product_walmart_ready', "yes" );
				$_product = wc_get_product($proId);
				if( isset($_product->variation_id) ) {
					if(WC()->get_id()){
						update_post_meta( $_product->id , 'ced_umb_is_product_walmart_ready', "yes" );

					}else{
						update_post_meta( $_product->get_id() , 'ced_umb_is_product_walmart_ready', "yes" );

					}
				}
			}
			else{
				
				$attributesMissing = array();
				foreach ($errorsArray[$proId] as $key => $value) {
					$value = explode("/", $value);
					$attributeName = end($value);
					$attributesMissing[] = $attributeName;
				}
				
				$statusArray['isReady'] = false;
				$statusArray['missingData'] = $attributesMissing;
				update_post_meta( $proId , 'ced_umb_is_product_walmart_ready', "no" );
			}
		}
 
		return $statusArray;
	}
	/**
	 * including dependency files.
	 *
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */

	public function load_dependencies()
	{

		require_once 'api/class-walmart-xml-lib.php';
		$this->walmart_xml_lib = new CED_UMB_Walmart_XML_Lib();

		require_once 'api/class-walmart-inventory-update.php';
		$this->walmart_inventory_update = new CED_UMB_Walmart_Inventory_Update();

		require_once 'api/class-walmart-product-update.php';
		$this->walmart_product_update = new CED_UMB_Walmart_Product_Update();

		require_once 'api/class-walmart-price-update.php';
		$this->walmart_price_update = new CED_UMB_Walmart_Price_Update();
	}

	/**
	 * Upload selected products on walmart.
	 * 
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 * @param array $proIds
	 */
	public function upload($proIds=array(), $isWriteXML=true) 
	{	
		$left_batch_elemetns=array();
		$origionalInputArray=$proIds;
		$limit_for_ids=300;
		$originalArrayCount=count($proIds);
		if ($originalArrayCount>$limit_for_ids) {
			$left_batch_elemetns=$origionalInputArray;
			
			$batch_elements=array_slice($left_batch_elemetns, 0,$limit_for_ids);
			$left_batch_elemetns=array_diff($left_batch_elemetns, $batch_elements);
			$proIds=$batch_elements;
			# code...
		}
		try
		{
			$currenttime = time();
			$restrictlimit = $this->ced_umb_tracking_upload_post_request($currenttime, true);
			if(!$restrictlimit['response'])
			{
				$min = $restrictlimit['mins'];
				$remaining_min = 60 - $min;
				$notice['message'] = "You have already send 10 request for product upload to walmart within $min Minute. Please try after $remaining_min Minute";
				$notice['classes'] = 'notice notice-error is-dismissable';
				if(!empty($left_batch_elemetns) && count($left_batch_elemetns)){
					$this->upload($left_batch_elemetns);
				}
				return json_encode($notice);
			}  	
			
			global $ced_walmart_helper;
			$walmart_calls_count=get_option('walmart_calls_count',true);
	    	
	    	if($walmart_calls_count<=50){
				if( !$this->makeProductXMLFileToSendOnWalmart($proIds,$isWriteXML)) {
					$notice['message'] = __("XML not valid",'ced-umb');
					$notice['classes'] = 'notice notice-error is-dismissable';
					if(!empty($left_batch_elemetns) && count($left_batch_elemetns)){
						$this->upload($left_batch_elemetns);
					}

					return json_encode($notice);
				}
			}

			$walmartRequest = new WalmartRequest();
			
			/****code to upload on walmart**/
			$saved_walmart_details = get_option( 'ced_umb_walmart_configuration', true );
			$client_id = isset( $saved_walmart_details['client_id'] ) ? esc_attr( $saved_walmart_details['client_id'] ) : '';
			$client_secret = isset( $saved_walmart_details['client_secret'] ) ? esc_attr( $saved_walmart_details['client_secret'] ) : '';
			$channel_id = isset( $saved_walmart_details['channel_id'] ) ?  $saved_walmart_details['channel_id']  : "";
		

			if(get_transient('ced_umb_walmart_token_transient') == ''){
				$this->ced_umb_validate_marketplace_configuration_settings();
			}
			$token = get_option('ced_umb_walmart_token');
			require_once CED_UMB_DIRPATH.'marketplaces/walmart/lib/walmart-sdk-master/autoload.php';
			$requestMethod = 'GET';
			$file['file'] = ABSPATH.'wp-content/uploads/umb/walmart/product.xml';
			
			$item = new \Walmart\Item([
	        'client_id' => $client_id,
	        'client_secret' => $client_secret,
	        'token' => $token
	    	]);
	    	$walmart_calls_count=get_option('walmart_calls_count',true);
	    	
	    	if($walmart_calls_count<=50){

	    		$response = $item->uploadItems($file);
	    		
	    	}
	    	else{
	    		$notice['message'] =  __("There is some problem with your hosting. Please Contact your host provider with that issue : $response.",'ced-umb');

	    		$notice['classes'] = 'notice notice-error is-dismissable';

				return json_encode($notice);
	    	}
	    		    	
			if(!empty($left_batch_elemetns) && count($left_batch_elemetns)){
				$this->upload($left_batch_elemetns);
			}

			if(empty($response))
			{
				$notice['message'] =  __("There is some problem with your hosting. Please Contact your host provider with that issue : $response.",'ced-umb');
				$notice['classes'] = 'notice notice-error is-dismissable';

				$issue = "There is some problem with your hosting. Please Contact your host provider with that issue : $response.";
				$param['action'] = "Upload Product";
				$param['issue'] = $issue;
				$ced_walmart_helper->ced_umb_notifcation_mail($param);
				
				return json_encode($notice);
			}
			$feedId = "";
			
			if(is_array($response))
			{		
				if(isset($response['data']['FeedAcknowledgement']['feedId']))
				{
					$feedId = $response['data']['FeedAcknowledgement']['feedId'];	
									
					$walmart_calls_count=get_option('walmart_calls_count',true);
					if($walmart_calls_count==null || $walmart_calls_count==''){
						$walmart_calls_count = 0;
					}else{
						$walmart_calls_count =$walmart_calls_count+1;
					}
					update_option('walmart_calls_count',$walmart_calls_count);
					$notice['message'] = 'Product is uploaded on walmart.';
					$notice['classes'] = 'notice notice-success is-dismissable';
					return json_encode($notice);
				}
				else
				{
					$notice['message'] = 'Product uploaded is failed on walmart.';
					$notice['classes'] = 'notice notice-error is-dismissable';
					if(!empty($left_batch_elemetns) && count($left_batch_elemetns)){
						$this->upload($left_batch_elemetns);
					}			
					return json_encode($notice);
				}
			}
			update_option('ced_umb_walmart_uploadfeed', $feedId);
		}
		catch(Exception $e)
		{
			$notice = array();
			$message = $e->getMessage();
			$issue = "Exception is Upload Product";
			$issue .= $message;
			$param['action'] = "UPLOAD PRODUCT";
			$param['issue'] = $issue;
			$ced_walmart_helper->ced_umb_notifcation_mail($param);
			
			$messages =  $message;
			$classes = "notice notice-error is-dismissible";
			$notices = array('message'=>$messages, 'classes'=>$classes);
			return json_encode($notices);
		}
	}

	/**
	 * SAVE FEEDID
	 *
	 * @name insertFeedInfoToDatabase
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function insertFeedInfoToDatabase( $feedId ) {
		
		$response = $this->walmart_feed_manager->getFeedItemsStatus( $feedId );
		$response = $response['data'];
		$response = json_encode($response);
		$marketPlace = 'walmart';
		$uploadingProIds = array();
		$uploadingProIds = json_encode($uploadingProIds);
		$uploadingProIds = $feedId;//temp
		$timeOfRequest = time();
		$name = "Feed Id : ".$timeOfRequest;
		global $wpdb;
		$prefix = $wpdb->prefix . CED_UMB_PREFIX;
		$tableName = $prefix.'fileTracker';
		$query = "INSERT INTO `$tableName`( `name`, `product_ids`, `framework`, `response` ) VALUES ('".$name."','".$uploadingProIds."','".$marketPlace."','".$response."');";
		$wpdb->query($query);
	}

	/**
	 * Function  used to convert xml to array formate
	 * 
	 */
	function xml2array ( $xmlObject, $out = array () ) {
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
			
		return $out;
	}
	 
	/**
	* Function to make inventory xml file to send on walmart
	*/
	
	function makeInventoryXMLFileToSendOnWalmart($inventoryXMLStringToUse) 
	{
		$inventoryXMLString = '<InventoryFeed xmlns="http://walmart.com/">';
		$inventoryXMLString .= '<InventoryHeader><version>1.4</version></InventoryHeader>';
		$inventoryXMLString .= $inventoryXMLStringToUse;
		$inventoryXMLString .= '</InventoryFeed>';
		$inventoryXMLString = $this->walmart_xml_lib->formatXMLStringAsItShouldBe($inventoryXMLString);
		$fileName = 'inventory.xml';
		$this->walmart_xml_lib->writeXMLStringToFile($inventoryXMLString, $fileName);
		update_option('_umb_walmart_isRunInventoryUpdate',"yes");
	}

		/**
	 * validate each product XML against xsd before sending to Walmart 
	 *
	 * @name validateXMLEachProduct
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function validateXMLEachProduct( $mainXMLString='',$xmlFileName='') {
		
				//return "Valid";
		$mainXMLString .= '</MPItemFeed>';
		$mainXMLString = trim($mainXMLString);

		$mainXMLString = $this->walmart_xml_lib->formatXMLStringAsItShouldBe($mainXMLString);

		$fileName = 'product_validation.xml';
		
		$this->walmart_xml_lib->writeXMLStringToFile($mainXMLString, $fileName);
		/* product xml validation */
		$xsdfile = __DIR__."/xsd/mp/MPItemFeed.xsd";
		
		/* product xml validation */
		$return = true;

		$XMLfilePath = ABSPATH.'wp-content/uploads/umb/';
		$XMLfilePath = $XMLfilePath."walmart/";
		$XMLfilePath .= $fileName;
		
		libxml_use_internal_errors(false);
		libxml_use_internal_errors(true);
		$feed = new DOMDocument();
		$feed->preserveWhitespace = false;
		$result = $feed->load($XMLfilePath);
		if($result === TRUE) 
		{
			if(@($feed->schemaValidate($xsdfile))) 
			{
				global $ced_walmart_helper;

				return "Valid";

			}else
			{
				$return = false;
				$errors=array();
				$errors = libxml_get_errors();
				$errorList = "";
				$errorList_array1=array();

				foreach($errors as $error) {


					$errorList .= "---\n";
					$errorList .= "Error: %s \nfile: %s, line: %s, column: %s, level: %s, code: %s\n";
					
					$errorList .= 	str_replace('{http://walmart.com/}', '', $error->message)."<br>";
					
					$errorList_array1[] = str_replace('{http://walmart.com/}', '', $error->message)."<br>";
					
					$errorList .= 	$error->file."<br>";
					$errorList .= 	$error->line."<br>";
					$errorList .= 	$error->column."<br>";
					$errorList .= 	$error->level."\n";
					$errorList .= 	$error->code."\n";

				}
				$this->xml_validation_error_array=$errorList_array1;

				global $ced_walmart_helper;
				$log_detail = "\nmessage: Product XML ERRORS \n";
				$log_detail .= $errorList."\n******************************************************************\n\n\n\n\n";
				//$ced_walmart_helper->umb_write_logs("walmart-per-product-xml.log",$log_detail);
			}	

		}
		else {
			return "DocumentInValid";
			/*$return = false;
			$errors = "! Document is not valid:\n";
			
			echo $errors;
			global $ced_walmart_helper;
			$log_detail = "\nmessage: Product XML ERRORS \n";
			$log_detail .= $errors."\n******************************************************************\n\n\n\n\n";
			$ced_walmart_helper->umb_write_logs("walmart-per-product-xml.log",$log_detail);*/
		}

	}
	 
	/**
	 * Load walmart scripts.
	 * 
	 * @name load_walmart_scripts
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	public function load_walmart_scripts(){
		
		$screen    = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		if ( in_array( $screen_id, array( 'edit-product','product' ) ) ) {
			wp_enqueue_script( 'ced_umb_walmart_edit_datatable', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js',array( 'jquery' ), time(), true);
			wp_register_script( 'ced_umb_walmart_edit_product', plugin_dir_url( __FILE__ ) . 'js/product-edit.js',array( 'jquery' ), time(), true);
			global $post;
			if($post!=null){
				wp_localize_script( 'ced_umb_walmart_edit_product', 'ced_umb_walmart_edit_product_script_AJAX', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'product_id' => $post->ID
				));
				wp_enqueue_script('ced_umb_walmart_edit_product');
			} 			
		}		
		//category mapping page.
		if( $screen_id == 'wpl_page_umb-cat-map' ){
			wp_register_script( 'ced_umb_walmart_cat_mapping', plugin_dir_url( __FILE__ ) . 'js/category_mapping.js', array( 'jquery' ), time(), true );
			$localization_params = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			);
			wp_localize_script( 'ced_umb_walmart_cat_mapping', 'umb_walmart_cat_map', $localization_params );
			wp_enqueue_script('ced_umb_walmart_cat_mapping');
		}

		/* manage product page js */
		if( $screen_id == 'umb_page_umb-pro-mgmt' ){
			 
			wp_localize_script( 'ced_umb_walmart_manage_product', 'ced_umb_walmart_manage_product_AJAX', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));
			wp_enqueue_script('ced_umb_walmart_manage_product');
		}

		/* manage things on edit profile page js */
		if( $screen_id == 'wpl_page_umb-profile' && isset($_GET['action'])){
			
			wp_register_script( 'ced_umb_walmart_profile_edit', plugin_dir_url( __FILE__ ) . 'js/profile-edit.js?ver=12345',array( 'jquery' ), time(), true);
			wp_localize_script( 'ced_umb_walmart_profile_edit', 'ced_umb_walmart_edit_profile_AJAX', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));
			wp_enqueue_script('ced_umb_walmart_profile_edit');
		}
	}

	/**
	 * Fetch selected category attribute for product
	 *
	 * @name fetch_walmart_attribute_for_selected_category
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function fetch_walmart_attribute_for_selected_category() {
		
		$productID = sanitize_text_field($_POST['productID']);
		$categoryID = sanitize_text_field($_POST['categoryID']);
		
		$walmartJsonFileName = 'WalmartCategories.json';
		$categories = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );

		$walmartJsonFileName = 'WalmartAttributes.json';
		$attributes = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );

		$walmartJsonFileName = 'WalmartVariation.json';
		$variation_attrs = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );
		$categoryIDToUse = explode("/", $categoryID);
		$categoryIDToUse  = $categoryIDToUse[0];
		
		if(isset($variation_attrs[$categoryIDToUse])) {
			$variation_attrs = $variation_attrs[$categoryIDToUse];
		}
		else{
			$variation_attrs = array();
		}
 
		global $global_CED_UMB_Render_Attributes;
		$marketPlace = 'ced_umb_walmart_attributes_ids_array';

		$attributesToEscape = array('sku','productName','longDescription','shelfDescription','shortDescription','mainImage/mainImageUrl','productIdentifiers/productIdentifier/productIdType','productIdentifiers/productIdentifier/productId','price/currency','price/amount','shippingWeight/value','shippingWeight/unit');

		$_product = wc_get_product($productID);
		if( $_product->product_type == "simple" ) {
			$extraKeysToEscape = array(
				'swatchImages/swatchImage/swatchImageUrl',
				'swatchImages/swatchImage/swatchVariantAttribute',
				'variantAttributeNames/variantAttributeName',
				'variantGroupId',
				'isPrimaryVariant'
			);
			$attributesToEscape = array_merge($attributesToEscape,$extraKeysToEscape);
		}

		$indexToUse = '0';
		if(isset($_POST['indexToUse'])) {
			$indexToUse = $_POST['indexToUse'];
		}

		echo '<div class="ced_umb_walmart_attribute_section">';
			echo '<div class="ced_umb_toggle_section"><span>';
				echo '<div class="ced_umb_toggle">';
					__('Walmart Attributes','ced-umb');
				echo '</span></div>';
				echo '<div class="ced_umb_toggle_div ced_attr_wrapper">';
		foreach ($categories as $key => $category) {
			if($category['path'] == $categoryID) {
				$walmart_required_attributes = $category['walmart_required_attributes'];
				$walmart_required_attributes = explode(",",$walmart_required_attributes);
				foreach ($walmart_required_attributes as $key => $attribute) {
					if(in_array($attribute,$attributesToEscape)) {
						continue;
					}
					$attributeData = $attributes[$attribute];
					
					if( isset($attributeData['walmart_attribute_enum']) && !empty($attributeData['walmart_attribute_enum']) && $attributeData['walmart_attribute_enum'] != "" ) {
						
						$valueForDropdown = explode(",",$attributeData['walmart_attribute_enum']);
						$tempValueForDropdown = array();
						foreach ($valueForDropdown as $key => $value) {
							$tempValueForDropdown[$value] = $value;
						}
						$valueForDropdown = $tempValueForDropdown;
						$name = ucfirst($attributeData['walmart_attribute_name']);
						$name .= '<span class="ced_umb_wal_required"> [ '.__('Required','ced-umb').'  ]</span>';

						$global_CED_UMB_Render_Attributes->renderDropdownHTML($attributeData['walmart_attribute_name'],$name,$valueForDropdown,$categoryID,$productID,$marketPlace,$attributeData['walmart_attribute_doc'],$indexToUse);
					}
					else {
						if($attributeData['walmart_attribute_name'] == "productTaxCode")
						{
							$taxcodes = $this->walmart_xml_lib->readWalmartInfoFromJsonFile('WalmartTaxCodes.json');
							echo "<div class='ced_umb_taxcode_overlay_wrapper'>";
							echo "<div class='ced_umb_taxcode_overlay'>";
							echo "<span class='ced_umb_cancel'><img src='".CED_UMB_URL."/admin/images/cross.png'></span>";
							echo "<div class='ced_umb_search_wrap'>";
							echo "<input type = 'text' id = 'ced_umb_walmart_taxcode_search' placeholder = '".__('Search', 'ced-umb')."'><br>";
							echo "</div>";
							echo "<table id='ced_umb_walmart_table'>";
							echo "<thead>";
							echo "<tr>";
							echo "<th width='150px'>".__("Tax code","ced_umb")."</th>";
							echo "<th>".__("Category","ced_umb")."</th>";
							echo "<th>".__("description","ced_umb")."</th>";
							echo "</tr>";
							echo "</thead>";
							echo "<tbody>";
							echo "</tbody>";
							echo "</table>";
							echo "</div>";
							echo "</div>";
						}
						
						$name = ucfirst($attributeData['walmart_attribute_name']);
						$name .= '<span class="ced_umb_wal_required"> [ '.__('Required','ced-umb').'  ]</span>';
						$global_CED_UMB_Render_Attributes->renderInputTextHTML($attributeData['walmart_attribute_name'],$name,$categoryID,$productID,$marketPlace,$attributeData['walmart_attribute_doc'],$indexToUse);
					}
				}

				$walmart_attributes = $category['walmart_attributes'];
				$walmart_attributes = explode(",",$walmart_attributes);
				 
				foreach ($walmart_attributes as $key => $attribute) {
					if(in_array($attribute,$attributesToEscape)) {
						continue;
					}

					$attributeData = $attributes[$categoryID.'::'.$attribute];

					$conditionally_required = false;
					$conditionally_required_text = '';
					$attributeNameToRender = end(explode("/",$attribute));
					$attributeNameToRender = ucfirst($attributeNameToRender);
					if( $attributeData['walmart_attribute_required'] == 'Required') {
						$attributeNameToRender .= '<span class="ced_umb_wal_required"> [ '.__('Required','ced-umb').'  ]</span>';
					}
					else if( $attributeData['walmart_attribute_required'] == 'Recommended') {
						$attributeNameToRender .= '<span class="ced_umb_wal_recommended"> [ '.__('Recommended','ced-umb').' ]</span>';
					}
					else if( $attributeData['walmart_attribute_required'] == 'Conditionally Required') {
						$attributeNameToRender .= '<span class="ced_umb_wal_conditionally_required"> [ '.__("Conditionally Required",'ced-umb').' ]</span>';
						$conditionally_required = true;
						$tempData = $attributeData['walmart_attribute_conditional_require'];
						if(!empty($tempData) && !is_null($tempData)) {
							$tempData = explode("::",$tempData);
							$conditionally_required_text = __('Required when ','ced-umb').$tempData[0].__(' is filled.','ced-umb');
						}
					}
					
					/* notice to all for attributes to be used to variate product */
					if( in_array($attribute, $variation_attrs) ) {
						$attributeNameToRender .= '<span class="ced_umb_wal_required"> [ '.__('Use For Variation','ced-umb').' ]</span>';
					}

					if( isset($attributeData['walmart_attribute_enum']) && !empty($attributeData['walmart_attribute_enum']) && $attributeData['walmart_attribute_enum'] != "" ) {
						
						$valueForDropdown = explode(",",$attributeData['walmart_attribute_enum']);
						$tempValueForDropdown = array();
						foreach ($valueForDropdown as $key => $value) {
							$tempValueForDropdown[$value] = $value;
						}
						$valueForDropdown = $tempValueForDropdown;
						 
						$global_CED_UMB_Render_Attributes->renderDropdownHTML($attributeData['walmart_attribute_name'],$attributeNameToRender,$valueForDropdown,$categoryID,$productID,$marketPlace,$attributeData['walmart_attribute_doc'],$indexToUse,array('case'=>"product"),$conditionally_required,$conditionally_required_text);
						 
					}
					else {
						$global_CED_UMB_Render_Attributes->renderInputTextHTML($attribute,($attributeNameToRender),$categoryID,$productID,$marketPlace,$attributeData['walmart_attribute_doc'],$indexToUse,array('case'=>"product"),$conditionally_required,$conditionally_required_text);
					}
	
				}
				break;
			}
		}
		 
			echo '</div>';
			echo '</div>';
		echo '</div>';
		wp_die();
	}

	/**
	 * Fetch selected category attribute for profile
	 *
	 * @name fetch_walmart_attribute_for_selected_category_for_profile_section
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function fetch_walmart_attribute_for_selected_category_for_profile_section() {
		if(isset($_POST['profileID'])) {
			$profileid = $_POST['profileID'];
		}
		global $wpdb;
		$table_name = $wpdb->prefix.CED_UMB_PREFIX.'profiles';
		$profile_data = array();
		if($profileid){
			$query = "SELECT * FROM `$table_name` WHERE `id`=$profileid";
			$profile_data = $wpdb->get_results($query,'ARRAY_A');
			if(is_array($profile_data)) {
				$profile_data = isset($profile_data[0]) ? $profile_data[0] : $profile_data;
				$profile_data = isset($profile_data['profile_data']) ? json_decode($profile_data['profile_data'],true) : array();
			}
		}

		/* select dropdown setup */
		$attributes		=	wc_get_attribute_taxonomies();
		$attrOptions	=	array();
		$addedMetaKeys = get_option('CedUmbProfileSelectedMetaKeys', false);

		if($addedMetaKeys && count($addedMetaKeys) > 0){
			foreach ($addedMetaKeys as $metaKey){
				$attrOptions[$metaKey]	=	$metaKey;
			}
		}
		if(!empty($attributes)){
			foreach($attributes as $attributesObject){
				$attrOptions['umb_pattr_'.$attributesObject->attribute_name]	=	$attributesObject->attribute_label;
			}
		}
		/* select dropdown setup */
		
		$productID = sanitize_text_field($_POST['productID']);
		$categoryID = sanitize_text_field($_POST['categoryID']);
		
		$walmartJsonFileName = 'WalmartCategories.json';
		$categories = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );

		$walmartJsonFileName = 'WalmartAttributes.json';
		$attributes = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );

		$walmartJsonFileName = 'WalmartVariation.json';
		$variation_attrs = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );
		$categoryIDToUse = explode("/", $categoryID);
		$categoryIDToUse = $categoryIDToUse[0];
		if(isset($variation_attrs[$categoryIDToUse])) {
			$variation_attrs = $variation_attrs[$categoryIDToUse];
		}
		else{
			$variation_attrs = array();
		}	

		
		global $global_CED_UMB_Render_Attributes;
		$marketPlace = 'ced_umb_walmart_attributes_ids_array';

		$attributesToEscape = array('sku','productIdentifiers/productIdentifier/productIdType','productIdentifiers/productIdentifier/productId','productName','keyFeatures/keyFeaturesValue','shortDescription','mainImageUrl','MPOffer/ProductTaxCode','MPOffer/price','MPOffer/MinimumAdvertisedPrice','MPOffer/StartDate','MPOffer/EndDate','MPOffer/ShippingWeight/measure','MPOffer/ShippingWeight/unit','brand');
		$extraKeysToEscape = array(

			'variantAttributeNames/variantAttributeName',
			'variantGroupId',
			'isPrimaryVariant',
			);
		$attributesToEscape = array_merge($attributesToEscape,$extraKeysToEscape);

		$indexToUse = '0';
		if(isset($_POST['indexToUse'])) {
			$indexToUse = $_POST['indexToUse'];
		}
		$categoryFound = false;
		echo '<div class="ced_umb_cmn active">';
		$selectDropdownHTML= renderMetaSelectionDropdownOnProfilePage();			
		foreach ($categories as $key => $category) {
			if($category['path'] == $categoryID) {
				$categoryFound = true;
				echo '<table class="wp-list-table widefat fixed striped">';
				$walmart_required_attributes = $category['walmart_required_attributes'];
				$walmart_required_attributes = explode(",",$walmart_required_attributes);

				$walmart_attributes = $category['walmart_attributes'];

				$walmart_attributes= ltrim($walmart_attributes,',');

				$walmart_attributes=str_replace(',,',',',$walmart_attributes);

				$walmart_attributes = explode(",",$walmart_attributes);
				
				foreach ($walmart_attributes as $key => $attribute) {
					if(in_array($attribute,$attributesToEscape)) {
						continue;
					}
					
					$isText = true;
					$attributeData = $attributes[$categoryID.'::'.$attribute];

					echo '<tr>';
					if( isset($attributeData['walmart_attribute_enum']) && !empty($attributeData['walmart_attribute_enum']) && $attributeData['walmart_attribute_enum'] != "" ) {
						$valueForDropdown = explode(",",$attributeData['walmart_attribute_enum']);
						$tempValueForDropdown = array();
						foreach ($valueForDropdown as $key => $value) {
							$tempValueForDropdown[$value] = $value;
						}
						$valueForDropdown = $tempValueForDropdown;
						/*fetch previously saved value*/
						$attributeNameToRender = end( (explode("/",$attributeData['walmart_attribute_name'])) );
						if($attributeNameToRender=='measure' || $attributeNameToRender=='unit'){
							$attributeNameToRender = str_replace("/"," ",$attribute);
						}
						$attributeNameToRender = ucfirst($attributeNameToRender);
						$conditionally_required = false;
						$conditionally_required_text = '';
						if( $attributeData['walmart_attribute_required'] == 'Required') {
							$attributeNameToRender .= '<span class="ced_umb_wal_required"> [ Required ]</span>';
						}
						else if( $attributeData['walmart_attribute_required'] == 'Recommended') {
							$attributeNameToRender .= '<span class="ced_umb_wal_recommended"> [ Recommended ]</span>';
						}
						else if( $attributeData['walmart_attribute_required'] == 'Conditionally Required') {
							$attributeNameToRender .= '<span class="ced_umb_wal_conditionally_required"> [ Conditionally Required ]</span>';
							$conditionally_required = true;
							$tempData = $attributeData['walmart_attribute_conditional_require'];
							if(!empty($tempData) && !is_null($tempData)) {
								$tempData = explode("::",$tempData);
								$conditionally_required_text = 'Required when '.$tempData[0].' is filled.';
							}
						}

						/* notice to all for attributes to be used to variate product */
						if( in_array($attribute, $variation_attrs) ) {
							$attributeNameToRender .= '<span class="ced_umb_wal_required"> [ Use For Variation ]</span>';
						}

						$fieldID = $categoryID.'_'.$attributeData['walmart_attribute_name'];
						$fielddata = isset($profile_data[$fieldID]) ? $profile_data[$fieldID] : array();
						$default = isset($fielddata['default']) ? $fielddata['default'] : null;
						$metakey = isset($fielddata['metakey']) ? $fielddata['metakey'] : null;
						/*fetch previously saved value*/
						echo '<td>';
						$global_CED_UMB_Render_Attributes->renderDropdownHTML($attributeData['walmart_attribute_name'],$attributeNameToRender,$valueForDropdown,$categoryID,$productID,$marketPlace,$attributeData['walmart_attribute_doc'],$indexToUse,array('case'=>'profile','value'=>$default),$conditionally_required,$conditionally_required_text);
						echo '</td>';
						$isText = false;
					}
					else {
						/*fetch previously saved value*/
						$attributeNameToRender = end( (explode("/",$attributeData['walmart_attribute_name'])) );

						$attributeNameToRender = end((explode("/",$attributeData['walmart_attribute_name'])) );
						if($attributeNameToRender=='measure' || $attributeNameToRender=='unit'){
							$attributeNameToRender = str_replace("/"," ",$attribute);
						}
						$attributeNameToRender = ucfirst($attributeNameToRender);
						$conditionally_required = false;
						$conditionally_required_text = '';
						if( $attributeData['walmart_attribute_required'] == 'Required') {
							$attributeNameToRender .= '<span class="ced_umb_wal_required"> [ Required ]</span>';
						}
						else if( $attributeData['walmart_attribute_required'] == 'Recommended') {
							$attributeNameToRender .= '<span class="ced_umb_wal_recommended"> [ Recommended ]</span>';
						}
						else if( $attributeData['walmart_attribute_required'] == 'Conditionally Required') {
							$attributeNameToRender .= '<span class="ced_umb_wal_conditionally_required"> [ Conditionally Required ]</span>';
							$conditionally_required = true;
							$tempData = $attributeData['walmart_attribute_conditional_require'];
							if(!empty($tempData) && !is_null($tempData)) {
								$tempData = explode("::",$tempData);
								$conditionally_required_text = 'Required when '.$tempData[0].' is filled.';
							}
						}

						/* notice to all for attributes to be used to variate product */
						if( in_array($attribute, $variation_attrs) || in_array( trim(str_replace("/measure","",$attribute)), $variation_attrs)  ) {
							$attributeNameToRender .= '<span class="ced_umb_wal_required"> [ Use For Variation ]</span>';
						}
						
						$fieldID = $categoryID.'_'.$attributeData['walmart_attribute_name'];
						$fielddata = isset($profile_data[$fieldID]) ? $profile_data[$fieldID] : array();
						$default = isset($fielddata['default']) ? $fielddata['default'] : null;
						$metakey = isset($fielddata['metakey']) ? $fielddata['metakey'] : null;
						/*fetch previously saved value*/
						echo '<td>';
						$global_CED_UMB_Render_Attributes->renderInputTextHTML($attributeData['walmart_attribute_name'],$attributeNameToRender,$categoryID,$productID,$marketPlace,$attributeData['walmart_attribute_doc'],$indexToUse,array('case'=>'profile','value'=>$default),$conditionally_required,$conditionally_required_text,$attributeData['walmart_attribute_type']);
						echo '</td>';
					}
					echo '<td>';
					if($isText) {
						$updatedDropdownHTML = str_replace('{{*fieldID}}', $fieldID, $selectDropdownHTML);
						$updatedDropdownHTML = str_replace('value="'.$metakey.'"', 'value="'.$metakey.'" selected="selected"', $updatedDropdownHTML);
						echo $updatedDropdownHTML;
					}
					echo '</td>';
					echo '</tr>';	
				}

				echo '</table>';
				break;
			}
		}
		

		if(!$categoryFound) {
			echo '<h3>No Category Selected Yet!</h3>'; 
		}
		echo '</div>';
		
		/* render framework specific fields */
		$pFieldInstance = CED_UMB_product_fields::get_instance();
		$framework_specific =$pFieldInstance->get_custom_fields('framework_specific',false);
		
		if(is_array($framework_specific) && is_array($framework_specific['walmart'])) {
			$attributesList = $framework_specific['walmart'];
			?>
			<div class="ced_umb_cmn">
				<table class="wp-list-table widefat fixed striped">
					<tbody>
					</tbody>
					<tbody>
						<?php
						global $global_CED_UMB_Render_Attributes;
						$marketPlace = "ced_umb_required_common";
						$productID = 0;
						$categoryID = '';
						$indexToUse = 0;
						$selectDropdownHTML= renderMetaSelectionDropdownOnProfilePage();
						foreach ($attributesList as $value) {
							$isText = true;
							$field_id = trim($value['fields']['id'],'_');
							$default = isset($profile_data[$value['fields']['id']]) ? $profile_data[$value['fields']['id']] : '';
							$default = $default['default'];
							echo '<tr>';
							echo '<td>';
							if( $value['type'] == "_select" ) {
								$valueForDropdown = $value['fields']['options'];
								$tempValueForDropdown = array();
								foreach ($valueForDropdown as $key => $value) {
									$tempValueForDropdown[$value] = $value;
								}
								$valueForDropdown = $tempValueForDropdown;
								$global_CED_UMB_Render_Attributes->renderDropdownHTML($field_id,ucfirst($value['fields']['label']),$valueForDropdown,$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
								$isText = false;
							}
							else {
								$global_CED_UMB_Render_Attributes->renderInputTextHTML($field_id,ucfirst($value['fields']['label']),$categoryID,$productID,$marketPlace,$value['fields']['description'],$indexToUse,array('case'=>'profile','value'=>$default));
							}
							echo '</td>';
							echo '<td>';
							if($isText) {
								$previousSelectedValue = 'null';
								if( isset($profile_data[$value['fields']['id']]) && $profile_data[$value['fields']['id']] != 'null') {
									$previousSelectedValue = $profile_data[$value['fields']['id']]['metakey'];
								}
								$updatedDropdownHTML = str_replace('{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML);
								$updatedDropdownHTML = str_replace('value="'.$previousSelectedValue.'"', 'value="'.$previousSelectedValue.'" selected="selected"', $updatedDropdownHTML);
								echo $updatedDropdownHTML;
							}
							echo '</td>';
							echo '</tr>';
						}	
						?>
					</tbody>
					<tfoot>
					</tfoot>
				</table>
			</div>
			<?php
		}
		else {
			echo '<div>';
			_e('No Walmart Specific Field','ced-umb');
			echo '</div>';
		}
		wp_die();
	}

	/**
	 * Update walmart categories
	 *
	 * @name updateWalmartCategoriesInDB
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function updateWalmartCategoriesInDB() {
		if(isset($_POST['categoryID']) && isset($_POST['actionToDo']) ) {
			$categoryID = sanitize_text_field( $_POST['categoryID'] );
			$actionToDo = sanitize_text_field( $_POST['actionToDo'] );
			$selectedWalmartCategories = get_option('ced_umb_selected_walmart_categories');
			$newWalmartCategories = array();
			if(isset($selectedWalmartCategories) && !empty($selectedWalmartCategories)) {
				$selectedWalmartCategories = json_decode($selectedWalmartCategories,TRUE);
				$newWalmartCategories = $selectedWalmartCategories;
			}
			if($actionToDo == 'delete') {
				if(array_key_exists($categoryID,$newWalmartCategories)) {
					unset($newWalmartCategories[$categoryID]);
				}
			}
			else if($actionToDo == 'append') {
				if(!array_key_exists($categoryID,$newWalmartCategories)) {
					$newWalmartCategories[$categoryID] = sanitize_text_field( $_POST['categoryNAME'] );
				}
			}
			$newWalmartCategories = json_encode($newWalmartCategories);
			update_option( 'ced_umb_selected_walmart_categories', $newWalmartCategories );
		}
		wp_die();
	}
	
	/**
	 * Walmart required fields.
	 *
	 * @name add_walmart_required_fields
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	public function add_walmart_required_fields($fields=array(),$post=''){
		
		$postId = isset($post->ID) ? intval($post->ID) : 0;

		$selectedWalmartCategories = get_option('ced_umb_selected_walmart_categories');
		if(isset($selectedWalmartCategories) && !empty($selectedWalmartCategories)) {
			$selectedWalmartCategories = json_decode($selectedWalmartCategories,TRUE);
		}
		if(is_array($selectedWalmartCategories))
		{
			array_unshift($selectedWalmartCategories,"-- Select --");
		}

		$fields[] = array(
			'type' => '_text_input',

			'id' => '_umb_walmart_fulfillmentLagTime',
			'fields' => array(
				'id' 				=> '_umb_walmart_fulfillmentLagTime',
				'label'             => __( 'Walmart FulfillmentLagTime', 'ced-umb' ),
				'desc_tip'          => true,
				'description'       => __( 'The number of days between when the item is ordered and when it is shipped', 'ced-umb' ),
				'type'				=> 'number'
			),
		);

		$fields[] = array(
			'type' => '_select',
			'id' => '_umb_walmart_category',
			'fields' => array(
					'id' => '_umb_walmart_category',
					'label' => __( 'Walmart Category', 'ced-umb' ),
					'options' => $selectedWalmartCategories,
					'desc_tip' => true,
					'description' => __( 'Identify the category specification. There is only one category can be used for any single item. NOTE: Once an item is created, this information cannot be updated.', 'ced-umb' )
			),
		);

		return $fields;
	}
	
	/**
	 * Make product xml file that uploaded on walmart
	 *
	 * @name makeProductXMLFileToSendOnWalmart
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function makeProductXMLFileToSendOnWalmart($proIds=array(), $isWriteXML=false) {
		$this->xml_validation_error_array=array();
		$return = true;
		$inventoryXMLStringToUse = '';		
		//date_default_timezone_set('Australia/Melbourne');2006-08-19T10:27:14-07:00
		$date = date('Y-m-d', time());
		$time = date('h:i:s', time());
		$date_time=$date.'T'.$time;
		$mainXMLString = ''; // variable to store all xml string to finally write in data.xml file
		$mainXMLString .= '<MPItemFeed xmlns:wal="http://walmart.com/" xmlns="http://walmart.com/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://walmart.com/MPItem.xsd ">';

		$productInfoToSendMain = array();
		$mainXMLString_hold_header='';
		$mainXMLString_hold='';
		/* set Feed Header */
		$MPItemFeedHeader = array();
		$MPItemFeedHeader['version'] = 	'3.1';
		$MPItemFeedHeader['requestId'] = time();
		$MPItemFeedHeader['requestBatchId'] = time(); 
		$MPItemFeedHeader['feedDate'] = $date_time; 
		$MPItemFeedHeader['mart'] = 'WALMART_US'; 

		$mainXMLString = $this->walmart_product_update->formatAndAppendDataToXmlString( '<MPItemFeedHeader/>', $MPItemFeedHeader, $mainXMLString );

		$walmartJsonFileName = 'WalmartCategories.json';
		$categories = $this->walmart_xml_lib->readWalmartInfoFromJsonFile( $walmartJsonFileName );
		$mainXMLString_hold_header=$mainXMLString;

		foreach ($proIds as $key => $product_id) {
			$mainXMLString='';
			if(WC()->version<'3.0.0'){
				$_product = new WC_Product( $product_id );

			}else{
				$_product = wc_get_product( $product_id );

			}

			$parent_product_id = $product_id;
			$_product = wc_get_product( $product_id );
			$productType = $_product->get_type();
			$isPrimaryVariant = "true";
			
			if($productType == 'variable') {

				$variations = $_product->get_available_variations();
				$val = array();
				foreach ($variations as $variation) {
					$this->xml_validation_error_array=array();
					$product_id = $variation['variation_id'];

					$this->walmart_product_update->fetchAssignedProfileDataOfProduct($product_id);
					$assignedWalmartCategory = $this->walmart_product_update->fetchMetaValueOfProduct( $product_id, '_umb_walmart_category' );
					

					if(!isset($assignedWalmartCategory) || empty($assignedWalmartCategory) || $assignedWalmartCategory == ""){
						continue;
					}

					$productInfoToSendMain = $this->walmart_product_update->makeArrayFor_MPITEM($product_id,$categories,$isPrimaryVariant);
					$isPrimaryVariant = "false";
					
					$assignedWalmartCategory = explode("/", $assignedWalmartCategory);
					if( is_array($assignedWalmartCategory) && isset($assignedWalmartCategory[1])){
						$assignedWalmartSubCategory = $assignedWalmartCategory[1]; 

					}
					$assignedWalmartCategory = $assignedWalmartCategory[0];
					$xsds_with_category_name = array('ArtAndCraft' => 'ArtAndCraftCategory', 'CarriersAndAccessories' => 'CarriersAndAccessoriesCategory', 'Clothing' => 'ClothingCategory','FoodAndBeverage' => 'FoodAndBeverageCategory', 'Footwear' => 'FootwearCategory','Furniture' => 'FurnitureCategory','GardenAndPatio' => 'GardenAndPatioCategory', 'Jewelry' => 'JewelryCategory','Office' => 'OfficeCategory', 'Other' => 'OtherCategory','Toy' => 'ToysCategory','Watches' => 'WatchesCategory');
					if(array_key_exists($assignedWalmartCategory, $xsds_with_category_name)){
						$assignedWalmartCategoryFileName=$assignedWalmartCategory.'Category';
					}else{
						$assignedWalmartCategoryFileName=$assignedWalmartCategory;

					}

					$stringToUse='';

					if(isset($assignedWalmartSubCategory) && !empty($assignedWalmartSubCategory)){
						
						if(isset($productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory]['variantAttributeNames'])){
							$variantAttributeNames = $productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory]['variantAttributeNames'];
							$stringToUse .= '<variantAttributeNames>';
							
							$variantAttributeNames = $variantAttributeNames['variantAttributeName'];
							foreach ($variantAttributeNames as $value) {
								$stringToUse .= '<variantAttributeName>'.$value.'</variantAttributeName>';
							}
							$stringToUse .= '</variantAttributeNames>';
						}

						if(isset($productInfoToSendMain['images']) && !empty($productInfoToSendMain['images'])){
							$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory]['productSecondaryImageURL'] = "ced";

							if(isset($productInfoToSendMain['keyFeatures1']) && !empty($productInfoToSendMain['keyFeatures1'])){
								$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory]['keyFeatures'] = "cedKeyFeatures";
							}

						}

					}else{
						
						if(isset($productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory]['variantAttributeNames'])){
							$variantAttributeNames = $productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory]['variantAttributeNames'];
							$stringToUse .= '<variantAttributeNames>';
							
							$variantAttributeNames = $variantAttributeNames['variantAttributeName'];
							foreach ($variantAttributeNames as $value) {
								$stringToUse .= '<variantAttributeName>'.$value.'</variantAttributeName>';
							}
							$stringToUse .= '</variantAttributeNames>';
						}
						if(isset($productInfoToSendMain['images']) && !empty($productInfoToSendMain['images'])){
							$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory]['productSecondaryImageURL'] = "ced";

							if(isset($productInfoToSendMain['keyFeatures1']) && !empty($productInfoToSendMain['keyFeatures1'])){
								$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory]['keyFeatures'] = "cedKeyFeatures";
							}


						}

					}

					$additionalProductAttributesStringToUse = '';
					if(isset($productInfoToSendMain['MPItem']['MPProduct']['additionalProductAttributes'])){
						$additionalProductAttributes = $productInfoToSendMain['MPItem']['MPProduct']['additionalProductAttributes'];
						$additionalProductAttributesStringToUse .= '<additionalProductAttributes>';
						foreach ($additionalProductAttributes as $value) {
							$additionalProductAttributesStringToUse .= '<additionalProductAttribute>';
							$additionalProductAttributesStringToUse .= '<productAttributeName>'.$value['productAttributeName'].'</productAttributeName>';
							$additionalProductAttributesStringToUse .= '<productAttributeValue>'.$value['productAttributeValue'].'</productAttributeValue>';
							$additionalProductAttributesStringToUse .= '</additionalProductAttribute>';
						}
						$additionalProductAttributesStringToUse .= '</additionalProductAttributes>';
					}

					$assetStringToUse = '';

					if($this->walmart_product_update->isAllRequiredValuePresent) {

						$tempInventoryXMLString = $this->walmart_inventory_update->updateInventoryOfSingleProduct( $product_id );
						$inventoryXMLStringToUse .= $tempInventoryXMLString;
						$mainXMLString = $this->walmart_product_update->formatAndAppendDataToXmlString( '<MPItem/>', $productInfoToSendMain['MPItem'], $mainXMLString , $isVariation=true, $stringToUse, $assetStringToUse, $additionalProductAttributesStringToUse );
						if(isset($productInfoToSendMain['images'])){

							$mainXMLString = str_replace('<productSecondaryImageURL>ced</productSecondaryImageURL>', $productInfoToSendMain['images'], $mainXMLString);
						}else{
							$mainXMLString = str_replace('<productSecondaryImageURL>ced</productSecondaryImageURL>', '', $mainXMLString);

						}

						if(isset($productInfoToSendMain['keyFeatures1'])){

							$mainXMLString = str_replace('<keyFeatures>cedKeyFeatures</keyFeatures>', $productInfoToSendMain['keyFeatures1'], $mainXMLString);
						}else{
							$mainXMLString = str_replace('<keyFeatures>cedKeyFeatures</keyFeatures>', '', $mainXMLString);

						}  
					}

				}

			}
			else {
				$this->walmart_product_update->fetchAssignedProfileDataOfProduct($product_id);
				$assignedWalmartCategory = $this->walmart_product_update->fetchMetaValueOfProduct( $product_id, '_umb_walmart_category' );

				if(!isset($assignedWalmartCategory) || empty($assignedWalmartCategory) || $assignedWalmartCategory == ""){
					continue;
				}
				$assignedWalmartCategory = explode("/", $assignedWalmartCategory);

				if( isset($assignedWalmartCategory[1]) && is_array($assignedWalmartCategory) ){
					$assignedWalmartSubCategory = $assignedWalmartCategory[1]; 
					$assignedWalmartCategory = $assignedWalmartCategory[0];

				}else{
					$assignedWalmartCategory = $assignedWalmartCategory[0];
				}
				$productInfoToSendMain = $this->walmart_product_update->makeArrayFor_MPITEM($product_id,$categories);
				$xsds_with_category_name = array('ArtAndCraft' => 'ArtAndCraftCategory', 'CarriersAndAccessories' => 'CarriersAndAccessoriesCategory', 'Clothing' => 'ClothingCategory','FoodAndBeverage' => 'FoodAndBeverageCategory', 'Footwear' => 'FootwearCategory','Furniture' => 'FurnitureCategory','GardenAndPatio' => 'GardenAndPatioCategory', 'Jewelry' => 'JewelryCategory','Office' => 'OfficeCategory', 'Other' => 'OtherCategory','Toy' => 'ToysCategory','Watches' => 'WatchesCategory');
				if(array_key_exists($assignedWalmartCategory, $xsds_with_category_name)){
					$assignedWalmartCategoryFileName=$assignedWalmartCategory.'Category';
				}else{
					$assignedWalmartCategoryFileName=$assignedWalmartCategory;

				}
				if($this->walmart_product_update->isAllRequiredValuePresent) {

					$tempInventoryXMLString = $this->walmart_inventory_update->updateInventoryOfSingleProduct( $product_id );

					$inventoryXMLStringToUse .= $tempInventoryXMLString;

					$assetStringToUse = '';

					if(isset($productInfoToSendMain['images']) && !empty($productInfoToSendMain['images'])){

						if(isset($assignedWalmartSubCategory)){
							$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory]['productSecondaryImageURL'] = "ced";
						}else{

							$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory]['productSecondaryImageURL'] = "ced";
						}

					}

					if(isset($productInfoToSendMain['keyFeatures1']) && !empty($productInfoToSendMain['keyFeatures1'])){

						if(isset($assignedWalmartSubCategory)){
							$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartSubCategory]['keyFeatures'] = "cedKeyFeatures";
						}else{

							$productInfoToSendMain['MPItem']['MPProduct']['category'][$assignedWalmartCategoryFileName][$assignedWalmartCategory]['keyFeatures'] = "cedKeyFeatures";
						}
						

					}
					$mainXMLString = $this->walmart_product_update->formatAndAppendDataToXmlString( '<MPItem/>', $productInfoToSendMain['MPItem'], $mainXMLString, false, "", $assetStringToUse);
					if(isset($productInfoToSendMain['images'])){

						$mainXMLString = str_replace('<productSecondaryImageURL>ced</productSecondaryImageURL>', $productInfoToSendMain['images'], $mainXMLString);
					}else{

						$$mainXMLString = str_replace('<productSecondaryImageURL>ced</productSecondaryImageURL>', '', $mainXMLString);

					}

					if(isset($productInfoToSendMain['keyFeatures1'])){

							$mainXMLString = str_replace('<keyFeatures>cedKeyFeatures</keyFeatures>', $productInfoToSendMain['keyFeatures1'], $mainXMLString);
						}else{
							$mainXMLString = str_replace('<keyFeatures>cedKeyFeatures</keyFeatures>', '', $mainXMLString);

					}  

				}
			}
			$mainXMLString_to_validate='';
			$mainXMLString_to_validate =$mainXMLString_hold_header.$mainXMLString;
			$validated_or_not =	$this->validateXMLEachProduct($mainXMLString_to_validate);
			if($validated_or_not=='Valid'){

				$mainXMLString_hold .=$mainXMLString;
			}

		}

		$mainXMLString=$mainXMLString_hold_header.$mainXMLString_hold;
		$mainXMLString .= '</MPItemFeed>';

		$mainXMLString = trim($mainXMLString);
		$mainXMLString = $this->walmart_xml_lib->formatXMLStringAsItShouldBe($mainXMLString);
		$fileName = 'product.xml';
		if($isWriteXML) {

			$this->walmart_xml_lib->writeXMLStringToFile($mainXMLString, $fileName);
			/* product xml validation */
			$xsdfile = __DIR__."/xsd/mp/MPItemFeed.xsd";
			$return = $this->validateXML($xsdfile,$fileName);	
			/* product xml validation */

			$this->makeInventoryXMLFileToSendOnWalmart($inventoryXMLStringToUse);
			
		}
		else{
			$return = false;
		}

		return $return;	
	}

	/**
	 * validate XML against xsd before sending to Walmart 
	 *
	 * @name ced_umb_required_fields_process_meta_simple
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	function validateXML( $xsdfile,$xmlFileName) {

		$return = true;

		$XMLfilePath = ABSPATH.'wp-content/uploads/umb/';
		$XMLfilePath = $XMLfilePath."walmart/";
		$XMLfilePath .= $xmlFileName;
		
		libxml_use_internal_errors(true);
		$feed = new DOMDocument();
		$feed->preserveWhitespace = false;
		$result = $feed->load($XMLfilePath);
		if($result === TRUE) 
		{
			if(@($feed->schemaValidate($xsdfile))) 
			{
				global $cedumbhelper;

				$log_detail = "\nmessage: Product XML ERRORS \n";
				$log_detail .= "No Errors :: Valid XML"."\n******************************************************************\n\n\n\n\n";
				$cedumbhelper->umb_write_logs("walmart-product-xml.log",$log_detail);
			} 
			else
			{
				$return = false;
				$errors = libxml_get_errors();
				$errorList = "";
				foreach($errors as $error) {
					$errorList .= "---\n";
					$errorList .= "Error: %s \nfile: %s, line: %s, column: %s, level: %s, code: %s\n";
					$errorList .= 	$error->message."\n";
					$errorList .= 	$error->file."\n";
					$errorList .= 	$error->line."\n";
					$errorList .= 	$error->column."\n";
					$errorList .= 	$error->level."\n";
					$errorList .= 	$error->code."\n";
				}
				 
				global $cedumbhelper;
				$log_detail = "\nmessage: Product XML ERRORS \n";
				$log_detail .= $errorList."\n******************************************************************\n\n\n\n\n";
				$cedumbhelper->umb_write_logs("walmart-product-xml.log",$log_detail);
			}	
		}
		else {
			$return = false;
			$errors = "! Document is not valid:\n";
			 
			global $cedumbhelper;
			$log_detail = "\nmessage: Product XML ERRORS \n";
			$log_detail .= $errors."\n******************************************************************\n\n\n\n\n";
			$cedumbhelper->umb_write_logs("walmart-product-xml.log",$log_detail);
		}

		return $return;
	}

	/**
	* Function to fetch details of all product which have assinged profile
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
	 * Process Meta data for Simple product 
	 *
	 * @name ced_umb_required_fields_process_meta_simple
	 * @author CedCommerce <plugins@cedcommerce.com>
	 * @since 1.0.0
	 */
	
	function ced_umb_required_fields_process_meta_simple( $post_id ) {
		$marketPlace = 'ced_umb_walmart_attributes_ids_array';
		if(isset($_POST[$marketPlace])) {
			foreach ($_POST[$marketPlace] as $key => $field_name) {
				update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name][0] ) );
			}
		}
	}
}
endif;