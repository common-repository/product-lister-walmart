<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for  walmart product lister
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package     Walmart Product Lister
 * @subpackage  Walmart Product Lister/includes
 * @author     CedCommerce <plugins@cedcommerce.com>
 */


class CED_UMB_i18n {

	/**
	 * The instance of CED_UMB_Loader.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static $_instance;
	
	/**
	 * CED_UMB_i18n Instance.
	 *
	 * Ensures only one instance of CED_UMB_i18n is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_UMB_i18n - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Load the walmart product lister text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ced-umb',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}