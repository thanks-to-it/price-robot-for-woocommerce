<?php
/*
Plugin Name: Advanced Conditional Pricing for WooCommerce
Plugin URI: https://wordpress.org/plugins/price-robot-for-woocommerce/
Description: The plugin automatically calculates the optimal price for products in WooCommerce. Can work as an advisor or in automatic mode.
Version: 2.0.0
Author: Algoritmika Ltd
Author URI: https://profiles.wordpress.org/algoritmika/
Requires at least: 5.0
Text Domain: price-robot-for-woocommerce
Domain Path: /langs
WC tested up to: 9.8
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'price-robot-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 2.0.0
	 * @since   1.3.0
	 */
	$plugin = 'price-robot-for-woocommerce-pro/price-robot-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		(
			is_multisite() &&
			array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
		)
	) {
		defined( 'ALG_WC_PRICE_ROBOT_FILE_FREE' ) || define( 'ALG_WC_PRICE_ROBOT_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'ALG_WC_PRICE_ROBOT_VERSION' ) || define( 'ALG_WC_PRICE_ROBOT_VERSION', '2.0.0' );

defined( 'ALG_WC_PRICE_ROBOT_FILE' ) || define( 'ALG_WC_PRICE_ROBOT_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-price-robot.php';

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
