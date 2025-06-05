<?php
/**
 * Price Robot for WooCommerce - Main Class
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot' ) ) :

final class Alg_WC_Price_Robot {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.1.0
	 */
	public $version = ALG_WC_PRICE_ROBOT_VERSION;

	/**
	 * core.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	public $core;

	/**
	 * @var Alg_WC_Price_Robot The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Price_Robot Instance.
	 *
	 * Ensures only one instance of Alg_WC_Price_Robot is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return Alg_WC_Price_Robot - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Price_Robot Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @access  public
	 *
	 * @todo    (v2.0.0) Plugin Check (PCP), etc.
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'price-robot-for-woocommerce-pro.php' === basename( ALG_WC_PRICE_ROBOT_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-wc-price-robot-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function localize() {
		load_plugin_textdomain(
			'price-robot-for-woocommerce',
			false,
			dirname( plugin_basename( ALG_WC_PRICE_ROBOT_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @see     https://developer.woocommerce.com/docs/hpos-extension-recipe-book/
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = (
				defined( 'ALG_WC_PRICE_ROBOT_FILE_FREE' ) ?
				array( ALG_WC_PRICE_ROBOT_FILE, ALG_WC_PRICE_ROBOT_FILE_FREE ) :
				array( ALG_WC_PRICE_ROBOT_FILE )
			);
			foreach ( $files as $file ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'custom_order_tables',
					$file,
					true
				);
			}
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function includes() {
		require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-price-robot-settings-section.php';
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-price-robot-core.php';
	}

	/**
	 * admin.
	 *
	 * @version 1.3.0
	 * @since   1.2.0
	 */
	function admin() {

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( ALG_WC_PRICE_ROBOT_FILE ), array( $this, 'action_links' ) );

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );

		// Version update
		if ( get_option( 'alg_wc_price_robot_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_price_robot' ) . '">' .
			__( 'Settings', 'price-robot-for-woocommerce' ) .
		'</a>';

		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Woocommerce settings tab to WooCommerce settings.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-price-robot-settings.php';
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function version_updated() {
		update_option( 'alg_wc_price_robot_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_WC_PRICE_ROBOT_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_WC_PRICE_ROBOT_FILE ) );
	}

}

endif;
