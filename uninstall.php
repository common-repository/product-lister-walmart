<?php
/**
 * Fired when the Walmart Product Lister is uninstalled.
 * @link  http://www.cedcommerce.com/
 * @since      1.0.0
 *
 * @package    walmart product lister
 */
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
wp_clear_scheduled_hook( 'ced_umb_scheduled_mail' );
?>