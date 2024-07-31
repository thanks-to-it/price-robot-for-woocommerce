<?php
/*
Plugin Name: Price Robot for WooCommerce
Plugin URI: https://wpfactory.com/item/price-robot-for-woocommerce-plugin/
Description: The plugin calculates optimal price for products in WooCommerce.
Version: 1.3.3
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: price-robot-for-woocommerce
Domain Path: /langs
WC tested up to: 9.1
Requires Plugins: woocommerce
*/

defined( 'ABSPATH' ) || exit;

if ( 'price-robot-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	$plugin = 'price-robot-for-woocommerce-pro/price-robot-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

defined( 'ALG_WC_PRICE_ROBOT_VERSION' ) || define( 'ALG_WC_PRICE_ROBOT_VERSION', '1.3.3' );

defined( 'ALG_WC_PRICE_ROBOT_FILE' ) || define( 'ALG_WC_PRICE_ROBOT_FILE', __FILE__ );

require_once( 'includes/class-alg-wc-price-robot.php' );

if ( ! function_exists( 'alg_wc_price_robot' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Price_Robot to prevent the need to use globals.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function alg_wc_price_robot() {
		return Alg_WC_Price_Robot::instance();
	}
}

add_action( 'plugins_loaded', 'alg_wc_price_robot' );
