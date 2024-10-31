<?php
/**
 * The file that defines the global helper functions using throughout the walmart product lister.
 *
 * @since      1.0.0
 *
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/includes
 */
class CED_UMB_Helper {
	
	/**
	 * The instance of CED_UMB_Helper.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static $_instance;
	
	/**
	 * CED_UMB_Helper Instance.
	 *
	 * Ensures only one instance of CED_UMB_Helper is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_UMB_Helper - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * print notices.
	 * 
	 * @since 1.0.0
	 */
	public function umb_print_notices($notices=array()){
		if(count($notices)){
			foreach($notices as $notice_array){

				$message = isset($notice_array['message']) ? esc_html($notice_array['message']) : '';
				$classes = isset($notice_array['classes']) ? esc_attr($notice_array['classes']) : 'error is-dismissable';
				if(!empty($message)){ ?>
					 <div class="<?php echo $classes;?>">
					 	<p><?php echo $message;?></p>
					 </div>
				<?php 	
				}
			}
		}
	}
	
	/**
	 * get conditional product id.
	 * 
	 * @since 1.0.0
	 */
	public function umb_get_product_by($params){
		global $wpdb;

		$where = '';
		if(count($params)){
			$Flag = false;
			foreach($params as $meta_key=>$meta_value){
				if(!empty($meta_value) && !empty($meta_key)){
					if(!$Flag){
						$where .= 'meta_key="'.sanitize_key($meta_key).'" AND meta_value="'.$meta_value.'"';
						$Flag = true;
					}else{
						$where .= ' OR meta_key="'.sanitize_key($meta_key).'" AND meta_value="'.$meta_value.'"';
					}
				}
			}
			if($Flag){
				$product_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE $where LIMIT 1" );
				if($product_id)
					return $product_id;
			}
		}
		return false;
	}
	
	/**
	 * writing logs.
	 *
	 * @since 1.0.0
	 * @param string $filename
	 * @param string $stringTowrite
	 */
	public function umb_write_logs($filename, $stringTowrite)
	{
		$dirTowriteFile = CED_UMB_LOG_DIRECTORY;
		if(defined("CED_UMB_LOG_DIRECTORY"))
		{
			if(!is_dir($dirTowriteFile))
			{
				if(!mkdir($dirTowriteFile,0755))
				{
					return;
				}
			}
			$fileTowrite = $dirTowriteFile."/$filename";
			if(!$fp = fopen($fileTowrite, "a"))
			{
				return;
			}
			$fr = fwrite($fp,$stringTowrite."\n");
			fclose($fp);
		}
		else {
			return;
		}
	}
	
	/**
	 * get profile details,
	 * 
	 * @since 1.0.0
	 */
	public function ced_umb_profile_details( $params=array() ){
		global $wpdb;
		$profile_name = "";
		if(isset($params['id'])){
			$id = $params['id'];
			$prefix = $wpdb->prefix . CED_UMB_PREFIX;
			$tablename = $prefix.'profiles';
			$profile_name = $wpdb->get_var("SELECT `name` FROM `$tablename` WHERE `id` = $id AND `active` = 1");
		}
		return $profile_name;
	}
	
	/**
	 * get profile details,
	 *
	 * @since 1.0.0
	 */
	public function ced_umb_notifcation_mail( $params )
	{
		$cronRelatedData = get_option('ced_umb_cronRelatedData', false);
		if(isset($cronRelatedData['ced_umb_allow_access_to_dev']))
		{	
			if($cronRelatedData['ced_umb_allow_access_to_dev']=="yes")
			{
				if(isset($params['action']))
				{	
					$home_url = home_url();

					$action   = $params['action'];
					$issue    = $params['issue'];
					$subject  =__( "Tracking Mail from Domain: ",'ced-umb').$home_url;

					$to = "plugins@cedcommerce.com";

					$message = __('Hi CedCommerce This email is send as tracking purpose for update ourselves that client is facing any issue or not','ced-umb').".\r\n";
					$message .= __("Domain : $home_url",'ced-umb')."\r\n";
					$message .= "Action : $action\r\n";
					$message .= "Error/Issue : $issue\r\nThanks";
					
					wp_mail($to, $subject, $message);
				}
			}
		}
	}
}