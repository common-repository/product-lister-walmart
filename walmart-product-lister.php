<?php
/**
 * @since             1.0.0
 * @package           Walmart Product Lister
 *
 * @wordpress-plugin
 * Plugin Name:      Walmart Product Lister
 * Description:       Configure once and sell your product to walmart from WooCommerce easily.
 * Version:           1.0.1
 * Author:            CedCommerce <plugins@cedcommerce.com>
 * Author URI:        cedcommerce.com
 * Text Domain:       ced-umb
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during Walmart Product Lister activation.
 * This action is documented in includes/class-ced-umb-activator.php
 * @name activate_ced_umb
 * @author CedCommerce <plugins@cedcommerce.com>
 * @since 1.0.0
 */
function activate_ced_umb() {
	require_once plugin_dir_path( __FILE__ ).'includes/class-ced-umb-activator.php';
	CED_UMB_Activator::activate();
}

/**
 * The code that runs during Walmart Product Lister deactivation.
 * This action is documented in includes/class-ced-umb-deactivator.php
 * @name deactivate_ced_umb
 * @author CedCommerce <plugins@cedcommerce.com>
 * @since 1.0.0
 */
function deactivate_ced_umb() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ced-umb-deactivator.php';
	CED_UMB_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ced_umb' );
register_deactivation_hook( __FILE__, 'deactivate_ced_umb' );

/**
 * The core Walmart Product Lister plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ced-umb.php';

/**
* This file includes core functions to be used globally in Walmart Product Lister.
* @author CedCommerce <plugins@cedcommerce.com>
* @link  http://www.cedcommerce.com/
*/
require_once plugin_dir_path(__FILE__).'includes/ced_umb_core_functions.php';

add_action('ced_umb_scheduled_mail', 'ced_umb_scheduled_process');
/**
 * function to handle scheduled process
 * @name ced_umb_scheduled_process
 * @author CedCommerce <plugins@cedcommerce.com>
 * 
 */
function ced_umb_scheduled_process()
{
	do_action('ced_umb_track_schedule');
}

/**
 * Check WooCommerce is Installed and Active.
 *
 * since CED UMB is extension for WooCommerce it's necessary,
 * to check that WooCommerce is installed and activated or not,
 * if yes allow extension to execute functionalities and if not
 * let deactivate the extension and show the notice to admin.
 * 
 * @author CedCommerce <plugins@cedcommerce.com>
 */
if(ced_umb_check_woocommerce_active()){

	run_ced_umb();
}else{

	add_action( 'admin_init', 'deactivate_ced_umb_woo_missing' );
}