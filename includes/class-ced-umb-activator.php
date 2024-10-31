<?php
/**
* Fired during walmart product lister activation.
*
* This class defines all code necessary to run during the walmart product lister activation.
*
* @since      1.0.0
* @package    Walmart Product Lister
* @subpackage Walmart Product Lister/includes
* @author     CedCommerce <plugins@cedcommerce.com>
*/

class CED_UMB_Activator {

	/**
	* Activation actions.
	*
	* All required actions on walmart product lister activation.
	*
	* @since    1.0.0
	*/
	public static function activate() {

		self::create_tables();
	}

	/**
	* Tables necessary for this walmart product lister.
	* 
	* @since 1.0.0
	*/
	private static function create_tables(){

		if(defined('CED_UMB_PREFIX')){
            global $wpdb;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $prefix = $wpdb->prefix . CED_UMB_PREFIX;
            $table_name = "{$prefix}profiles";
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                $create_profile = "CREATE TABLE {$prefix}profiles (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(255) NOT NULL DEFAULT '',active bool NOT NULL DEFAULT true,marketplace VARCHAR(255) DEFAULT 'all',profile_data longtext DEFAULT NULL,PRIMARY KEY (id));";
                dbDelta( $create_profile );
            }
            
            $table_name_ft = "{$prefix}fileTracker";
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name_ft'") != $table_name_ft) {
                $createFileTracker = "CREATE TABLE {$prefix}fileTracker (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,name VARCHAR(255) NOT NULL DEFAULT '',product_ids VARCHAR(1020),framework VARCHAR(255),response longtext DEFAULT NULL,PRIMARY KEY (id));";
                dbDelta( $createFileTracker );
            }
            update_option('ced_umb_database_version',CED_UMB_VERSION);
        }
	
	}
}
?>