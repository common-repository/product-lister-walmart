<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
/**
 * walmart product lister admin pages related functionality helper class.
 *
 * @since      1.0.0
 *
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/admin/helper
 */

if( !class_exists( 'CED_UMB_menu_page_manager' ) ) :
	class CED_UMB_menu_page_manager{
	/**
	 * The Instace of CED_UMB_menu_page_manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_UMB_menu_page_manager class.
	 */
	private static $_instance;
	
	/**
	 * CED_UMB_menu_page_manager Instance.
	 *
	 * Ensures only one instance of CED_UMB_menu_page_manager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_UMB_menu_page_manager instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Creating admin pages of Walmart product Lister.
	 * 
	 * @since 1.0.0
	 */
	public function create_pages(){

		add_menu_page('WPL', 'WPL', __('manage_woocommerce','ced-umb'), 'umb-main', array( $this, 'ced_umb_marketplace_page' ),'', 55.556 );
		add_submenu_page('umb-main', __('Configuration','ced-umb'), __('Configuration','ced-umb'), 'manage_woocommerce', 'umb-main', array( $this, 'ced_umb_marketplace_page' ) );
		add_submenu_page('umb-main', __('Category Mapping','ced-umb'), __('Category Mapping','ced-umb'), 'manage_woocommerce', 'umb-cat-map', array( $this, 'ced_umb_category_map_page' ) );
		add_submenu_page('umb-main', __('Profile','ced-umb'), __('Profile','ced-umb'), 'manage_woocommerce', 'umb-profile', array( $this, 'ced_umb_profile_page' ) );
		add_submenu_page('umb-main', __('Manage Products','ced-umb'), __('Manage Products','ced-umb'), 'manage_woocommerce', "umb-pro-mgmt", array( $this, 'ced_umb_pro_mgmt_page' ) );
		add_submenu_page('umb-main', __('Prerequisite','ced-umb'), __('Prerequisite','ced-umb'), 'manage_woocommerce', 'umb-prerequisites', array( $this, 'ced_umb_prerequisite_page' ) );
		add_submenu_page('umb-main', __('Features','ced-umb'), __('Features','ced-umb'), 'manage_woocommerce', 'umb-features', array( $this, 'ced_umb_features_page' ) );
	}
	/**
	 * file status page.
	 * 
	 * @since 1.0.0
	 */
	public function ced_umb_file_status_page(){
		
		require_once CED_UMB_DIRPATH.'admin/pages/fileStatus.php';
	}
	/**
	 * walmart page.
	 * 
	 * @since 1.0.0
	 */
	public function ced_umb_marketplace_page(){
		
		require_once CED_UMB_DIRPATH.'admin/pages/marketplaces.php';
		
	}
	
	/**
	 * Category mapping page panel.
	 * 
	 *  @since 1.0.0
	 */
	public function ced_umb_category_map_page(){
		
		require_once CED_UMB_DIRPATH.'admin/pages/category_mapping.php';
	}
	
	/**
	 * Products management page panel.
	 *
	 *  @since 1.0.0
	 */
	public function ced_umb_pro_mgmt_page(){
		
		require_once CED_UMB_DIRPATH.'admin/pages/manage_products.php';
	}
	
	/**
	 * Profile page for easy product uploading.
	 * 
	 * @since 1.0.0
	 */
	public function ced_umb_profile_page(){
		
		require_once CED_UMB_DIRPATH.'admin/pages/profile.php';
	}
	
	/**
	 * prerequisite page.
	 *
	 * @since 1.0.0
	 */
	public function ced_umb_prerequisite_page(){
		require_once CED_UMB_DIRPATH.'admin/pages/prerequisite.php';
	}

	/**
	 * features  page.
	 *
	 * @since 1.0.0
	 */
	public function ced_umb_features_page(){
		require_once CED_UMB_DIRPATH.'admin/pages/features.php';
	}
}
endif;