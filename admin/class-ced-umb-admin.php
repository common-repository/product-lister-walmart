<?php

/**
 * The admin-specific functionality of the walmart product lister.
 *
 * @since      1.0.0
 *
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/admin
 */

class CED_UMB_Admin {

	/**
	 * The ID of  walmart product lister.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of  walmart product lister.
	 */
	private $plugin_name;

	/**
	 * The version of walmart product lister.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of walmart product lister.
	 */
	private $version;
	
	/**
	 * helper for product management.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CED_UMB_product_manager    $product_manager    Maintains all single product related functionality.
	 */
	private $product_manager;
	
	/**
	 * helper for walmart product lister admin pages.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CED_UMB_menu_page_manager    $menu_page_manager    Maintains all walmart product lister pages related functionality.
	 */
	private $menu_page_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of Walmart Product Lister.
	 * @param      string    $version    The version of tWalmart Product Lister.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_admin_classes();
		$this->instantiate_admin_classes();
		add_action('wp_ajax_ced_umb_select_cat_prof', array($this,'ced_umb_select_cat_prof'));
		add_action('wp_ajax_ced_umb_select_cat_bulk_upload', array($this,'ced_umb_select_cat_bulk_upload'));
		
		add_action('wp_ajax_ced_umb_current_product_status', array($this,'ced_umb_current_product_status'));
	}
	/**
	* Function to get current product status
	*/
	public function ced_umb_current_product_status()
	{
		$prodId = isset($_POST['prodId']) ? $_POST['prodId'] : false;
		$marketPlace = isset($_POST['marketplace']) ? sanitize_text_field($_POST['marketplace']) : false;
		if($prodId && $marketPlace){
			$filePath = CED_UMB_DIRPATH.'marketplaces/'.$marketPlace.'/class-'.$marketPlace.'.php';
			if(file_exists($filePath))
				require_once $filePath;
			
			$class_name = "CED_UMB_".$marketPlace."_manager";
			
			$manager = $class_name :: get_instance();
			$productstatusresponse = $manager->getProductstatus($prodId);
			echo $productstatusresponse;die;
		}
		
	}

	/**
	* Function to seclect category for profile
	*/
	public function ced_umb_select_cat_prof()
	{
		global $wpdb;
		
		$catId  = isset($_POST['catId']) ?sanitize_text_field( $_POST['catId'] ) : "";
		$profId = isset($_POST['profId']) ? sanitize_text_field ( $_POST['profId'] ): "";
		
		if($profId == "removeProfile")
		{
			$profId = "";
		}
		$getSavedvalues = get_option('ced_umb_category_profile', false);
		if(is_array($getSavedvalues) && array_key_exists($catId, $getSavedvalues))
		{
			if($profId == "removeProfile")
			{
				unset($getSavedvalues["$catId"]);
			}
			else{
				$getSavedvalues["$catId"] = $profId;
			}
		}
		else{
			if($profId != "removeProfile")
			{
				$getSavedvalues["$catId"] = $profId;
			}
		}
		
		update_option('ced_umb_category_profile', $getSavedvalues);		
		$table_name = $wpdb->prefix.CED_UMB_PREFIX.'profiles';
		$query = "SELECT `id`, `name` FROM `$table_name` WHERE 1";
		$profiles = $wpdb->get_results($query,'ARRAY_A');
		$profName = __('Profile not selected', 'ced-umb');		
		if(is_array($profiles) && !empty($profiles))
		{
			foreach ($profiles as $profile)
			{
				if($profile['id'] == $profId)
				{
					$profName = $profile['name'];
				}
			}
		}
		
		$tax_query['taxonomy'] = 'product_cat';
		$tax_query['field'] = 'id';
		$tax_query['terms'] = $catId;
		$tax_queries[] = $tax_query;
		$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );		
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) {
			$loop->the_post();
			global $product;
			if(is_wp_error($product))
				return;
			
			$product_id = $loop->post->ID;
			$product_title = $loop->post->post_title;
			update_post_meta($product_id, "ced_umb_profile", $profId);
		}
		echo json_encode(array('status'=>'success','profile'=> $profName));
		wp_die();
	}
	
	/**
	* function to assign and upload category in bulk
	*/
	
	function ced_umb_select_cat_bulk_upload()
	{
		if(isset($_POST['catId']))
		{
			$products = array();
			$selected_cat = sanitize_text_field( $_POST['catId'] );
			$tax_query['taxonomy'] = 'product_cat';
			$tax_query['field'] = 'id';
			$tax_query['terms'] = $selected_cat;
			$tax_queries[] = $tax_query;
			$args = array( 'post_type' => 'product', 'posts_per_page' => -1, 'tax_query' => $tax_queries, 'orderby' => 'rand' );
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post(); global $product;			
			$product_id = $loop->post->ID;
			$product_title = $loop->post->post_title;
			$products[$product_id] = $product_title;
			endwhile;			
			$response['data'] = $products;
			$response['result'] = 'success';			
			echo json_encode($response);
			die;
			
		}	
	}
	
	/**
	 * Including all admin related classes.
	 * 
	 * @since 1.0.0
	 */
	private function load_admin_classes(){
		
		$classes_names = array(
			'admin/helper/class-product-fields.php',
			'admin/helper/class-menu-page-manager.php',
			'admin/helper/class-ced-umb-extended-manager.php'
			);
		
		foreach( $classes_names as $class_name ){
			require_once CED_UMB_DIRPATH . $class_name;
		}
		
		$activated_marketplaces = ced_umb_available_marketplace();
		if(is_array($activated_marketplaces)):
			foreach($activated_marketplaces as $marketplace_name){
				$file_path = CED_UMB_DIRPATH.'marketplaces/'.$marketplace_name.'/class-'.$marketplace_name.'.php';
				if(file_exists($file_path))
					require_once $file_path;
			}
			endif;
		}
		
	/**
	 * storing instance of admin related functionality classes.
	 * 
	 * @since 1.0.0 
	 */
	private function instantiate_admin_classes(){
		
		if( class_exists( 'CED_UMB_product_fields' ) )
			$this->product_fields = CED_UMB_product_fields::get_instance();
		
		if( class_exists( 'CED_UMB_menu_page_manager' ) )
			$this->menu_page_manager = CED_UMB_menu_page_manager::get_instance();		
		// creating instances of activated marketplaces classes.
		$activated_marketplaces = ced_umb_available_marketplace();
		if(is_array($activated_marketplaces)):
			foreach($activated_marketplaces as $marketplace){
				$class_name = 'CED_UMB_'.$marketplace.'_manager';
				if(class_exists($class_name))
					new $class_name();
			}
			endif;
		}
		
	/**
	 * Returns all the admin hooks.
	 * 
	 * @since 1.0.0
	 * @return array admin_hook_data.
	 */
	public function get_admin_hooks(){
		
		$admin_actions = array(
			array(
				'type'	=>	'action',
				'action' => 'woocommerce_product_data_tabs',
				'instance' => $this->product_fields,
				'function_name' => 'umb_required_fields_tab'
				),
			array(
				'type'	=>	'action',
				'action' => 'woocommerce_product_data_panels',
				'instance' => $this->product_fields,
				'function_name' => 'umb_required_fields_panel'
				),
			array(
				'type'	=>	'action',
				'action' => 'woocommerce_process_product_meta',
				'instance' => $this->product_fields,
				'function_name' => 'umb_required_fields_process_meta'
				),
			array(
				'type'	=>	'action',
				'action' => 'admin_menu',
				'instance' => $this->menu_page_manager,
				'function_name' => 'create_pages'
				),			
			
			array(
				'type'	=>	'action',
				'action' => 'wp_ajax_ced_umb_save_profile',
				'instance' => $this,
				'function_name' => 'ced_umb_save_profile',
				)
			); 
		
		return apply_filters( 'ced_umb_admin_actions', $admin_actions );
	}
	
	/**
	 * save assigned profile to the product.
	 * 
	 * @since 1.0.0
	 */
	public function ced_umb_save_profile()
	{
		$prodId    = isset($_POST['proId']) ? sanitize_text_field( $_POST['proId'] ): "";
		$profileId = isset($_POST['profileId']) ? sanitize_text_field( $_POST['profileId'] ): "";
		$_product = wc_get_product( $prodId );
		if(is_wp_error($_product))
			return;		
		update_post_meta($prodId, "ced_umb_profile", $profileId);
		$ced_umb_profile = get_post_meta($prodId, "ced_umb_profile", true);
		if($ced_umb_profile == $profileId) {
			echo "success";
		}
		else {
			echo "fail";
		}
		wp_die();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		
		if( $screen_id == 'toplevel_page_umb-main' || $screen_id == 'product' )
			wp_enqueue_style( $this->plugin_name.'config_style', plugin_dir_url( __FILE__ ) . 'css/ced_umb_config_style.css', array(), $this->version, 'all' );
		
		wp_enqueue_style( $this->plugin_name.'common_style', plugin_dir_url( __FILE__ ) . 'css/common_style.css', array(), $this->version, 'all' );		
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		
		if($screen_id=="umb_page_umb-fileStatus"){
			$activated_marketplaces = ced_umb_available_marketplace();
			if(is_array($activated_marketplaces)){
				foreach($activated_marketplaces as $marketplace){
					$handle = 'umb_'.$marketplace.'_fileStatus_script';
					
					wp_localize_script( $handle, $marketplace.'_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
				}
			}
		}
		
		if( $screen_id == 'toplevel_page_umb-main' || $screen_id == 'product' )
		{	
			wp_enqueue_script( $this->plugin_name.'config_script', plugin_dir_url( __FILE__ ) . 'js/ced_umb_config.js', array( 'jquery' ), $this->version, false );
			$activated_marketplaces = ced_umb_available_marketplace();
			if(is_array($activated_marketplaces)){
				foreach($activated_marketplaces as $marketplace){
					$handle = 'umb_'.$marketplace.'_configuration_script';
					
					wp_localize_script( $handle, $marketplace.'_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
				}
			}
		}	
		
		if( $screen_id == 'wpl_page_umb-pro-mgmt' ){
			wp_enqueue_script( $this->plugin_name.'profile', plugin_dir_url( __FILE__ ) . 'js/ced_umb_profile.js', array( 'jquery' ), $this->version, false );
			wp_localize_script( $this->plugin_name.'profile', 'profile_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
		
		wp_enqueue_script( $this->plugin_name.'common_script', plugin_dir_url( __FILE__ ) . 'js/common_script.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name.'common_script', 'common_action_handler', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
}