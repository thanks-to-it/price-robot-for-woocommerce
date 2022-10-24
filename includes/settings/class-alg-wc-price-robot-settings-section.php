<?php
/**
 * Price Robot for WooCommerce - Settings - Section
 *
 * @version 1.3.0
 * @since   1.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Settings_Section' ) ) :

class Alg_WC_Price_Robot_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_price_robot',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_price_robot_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
