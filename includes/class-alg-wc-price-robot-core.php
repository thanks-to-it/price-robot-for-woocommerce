<?php
/**
 * Price Robot for WooCommerce - Core Class
 *
 * @version 1.3.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Core' ) ) :

class Alg_WC_Price_Robot_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function __construct() {

		$this->crons = require_once( 'class-alg-wc-price-robot-crons.php' );
		$this->admin = require_once( 'class-alg-wc-price-robot-admin.php' );

		// Robots
		require_once( 'robots/class-alg-wc-price-robot-abstract.php' );
		require_once( 'robots/class-alg-wc-price-robot-formula.php' );
		// Deprecated
		if ( 'yes' === get_option( 'alg_price_robot_last_sale_enabled', 'no' ) ) {
			require_once( 'robots/deprecated/class-alg-wc-price-robot-last-sale.php' );
		}
		if ( 'yes' === get_option( 'alg_price_robot_pretty_price_enabled', 'no' ) ) {
			require_once( 'robots/deprecated/class-alg-wc-price-robot-pretty-price.php' );
		}

		$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );

		// Automatic pricing
		if ( 'yes' === get_option( 'alg_price_robot_general_auto_pricing_enabled', 'yes' ) ) {
			$this->add_price_hooks();
		}

	}

	/**
	 * add_price_hooks.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] [!!!] (dev) `alg_price_robot_general_display_as_sale`: do we really need all filters - maybe `woocommerce_product_is_on_sale` alone is good enough?
	 */
	function add_price_hooks() {

		$this->price_filter = ( $this->is_wc_version_below_3 ? 'woocommerce_get_price' : 'woocommerce_product_get_price' );

		// Price filter
		add_filter( $this->price_filter, array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );

		// Price filter - variations
		add_filter( 'woocommerce_variation_prices_price', array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'get_variation_prices_hash' ), PHP_INT_MAX, 3 );
		if ( ! $this->is_wc_version_below_3 ) {
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
		}

		// Display as sale
		if ( 'yes' === get_option( 'alg_price_robot_general_display_as_sale', 'yes' ) ) {

			$sale_price_filter  = ( $this->is_wc_version_below_3 ? 'woocommerce_get_sale_price' : 'woocommerce_product_get_sale_price' );

			// Sale price filters
			add_filter( $sale_price_filter, array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_is_on_sale', array( $this, 'price_robot_on_sale' ), PHP_INT_MAX, 2 );

			// Sale price filters - variations
			add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
			if ( ! $this->is_wc_version_below_3 ) {
				add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
			}

		}
	}

	/**
	 * get_robot_price.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [now] [!!!] (dev) `$price = ( abs( $price - $original_price ) < 1 ? $original_price : $price );`?
	 * @todo    [now] [!!!] (dev) `woocommerce_get_sale_price`: rethink
	 * @todo    [next] (dev) rename function?
	 * @todo    [next] (dev) rename `alg_woocommerce_price_robot` filter?
	 */
	function get_robot_price( $price, $product, $advisor_mode = false ) {
		if (
			( $advisor_mode || $this->is_price_robot_enabled( $this->get_product_or_variation_parent_id( $product ) ) ) &&
			( 0  !== $price || 'yes' === get_option( 'alg_wc_price_robot_process_zero_price',  'no' ) ) &&
			( '' !== $price || 'yes' === get_option( 'alg_wc_price_robot_process_empty_price', 'no' ) )
		) {
			if ( '' === $price && in_array( current_filter(), array(
					'woocommerce_get_sale_price',
					'woocommerce_product_get_sale_price',
					'woocommerce_variation_prices_sale_price',
					'woocommerce_product_variation_get_sale_price',
				)
			) ) {
				return $price;
			}
			if ( ! $price ) {
				$price = 0;
			}
			$product_id     = $this->get_product_id( $product );
			$original_price = $price;
			$modified_price = apply_filters( 'alg_woocommerce_price_robot', $price, $product_id, $original_price );
			$price          = ( $modified_price > 0 ? round( $modified_price, get_option( 'woocommerce_price_num_decimals', 2 ) ) : 0 );
		}
		return $price;
	}

	/**
	 * get_product_id.
	 *
	 * @version 1.3.0
	 * @since   1.1.0
	 */
	function get_product_id( $product ) {
		return ( 'no' === get_option( 'alg_price_robot_general_variable_as_single', 'no' ) ?
			$this->get_product_or_variation_id( $product ) : $this->get_product_or_variation_parent_id( $product ) );
	}

	/**
	 * get_product_or_variation_id.
	 *
	 * @version 1.3.0
	 * @since   1.1.0
	 */
	function get_product_or_variation_id( $product ) {
		return ( $this->is_wc_version_below_3 ? ( isset( $product->variation_id ) ? $product->variation_id : $product->id ) : $product->get_id() );
	}

	/**
	 * get_product_or_variation_parent_id.
	 *
	 * @version 1.3.0
	 * @since   1.1.0
	 */
	function get_product_or_variation_parent_id( $product ) {
		return ( $this->is_wc_version_below_3 ? $product->id : ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ) );
	}

	/**
	 * get_variation_prices_hash.
	 *
	 * @version 1.3.0
	 * @since   1.1.0
	 *
	 * @todo    [next] (dev) redo this?
	 */
	function get_variation_prices_hash( $price_hash, $product, $display ) {
		$product_id = $this->get_product_or_variation_parent_id( $product );

		$price_hash['alg_wc_price_robot_price_hash'] = array(
			'products' => array(
				$product_id => array(
					'enabled' => ( $this->is_price_robot_enabled( $product_id ) ? 'yes' : 'no' ),
				),
			),
			'general' => array(
				'display_as_sale'    => get_option( 'alg_price_robot_general_display_as_sale', 'yes' ),
				'variable_as_single' => get_option( 'alg_price_robot_general_variable_as_single', 'no' ),
			),
			'last_sale' => array(
				'discount_timeframe_days' => get_option( 'alg_price_robot_last_sale_discount_timeframe_days', 30 ),
				'discount_step_percent'   => get_option( 'alg_price_robot_last_sale_discount_step_percent', 5 ),
				'discount_max_percent'    => get_option( 'alg_price_robot_last_sale_discount_max_percent', 25 ),
				'min_stock'               => get_option( 'alg_price_robot_last_sale_min_stock', 0 ),
				'goal_sale_rate'          => get_option( 'alg_price_robot_last_sale_goal_sale_rate', 0 ),
			),
			'pretty_price' => array(
				'round_type' => get_option( 'alg_price_robot_pretty_price_round_type', 'ceil' ),
				'max_price'  => get_option( 'alg_price_robot_pretty_price_max_price', 0 ),
			),
			'orders_cron' => array(
				'started' => get_option( 'alg_wc_price_robot_get_orders_cron_started', 0 ),
			),
		);

		return $price_hash;
	}

	/**
	 * get_product_price_original.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] [!!!] (dev) `woocommerce_variation_prices_price`, `woocommerce_get_variation_prices_hash`, etc.
	 */
	function get_product_price_original( $product ) {
		if ( isset( $this->price_filter ) ) {
			remove_filter( $this->price_filter,                       array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
			remove_filter( 'woocommerce_product_variation_get_price', array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
			$price = $product->get_price();
			add_filter(    'woocommerce_product_variation_get_price', array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
			add_filter(    $this->price_filter,                       array( $this, 'get_robot_price' ), PHP_INT_MAX, 2 );
		} else {
			$price = $product->get_price();
		}
		return $price;
	}

	/**
	 * price_robot_on_sale.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function price_robot_on_sale( $is_on_sale, $product ) {
		$product_id = $this->get_product_or_variation_parent_id( $product );
		if ( $this->is_price_robot_enabled( $product_id ) ) {
			// Get original price
			$original_price = $this->get_product_price_original( $product );
			// Get robot(s) price
			$robot_price = $this->get_robot_price( $original_price, $product, true );
			// Decide if on sale
			return ( $robot_price < $original_price ) ? true : $is_on_sale;
		}
		return $is_on_sale;
	}

	/**
	 * get_data_last_updated_message.
	 *
	 * @version 1.3.0
	 * @since   1.1.0
	 */
	function get_data_last_updated_message() {
		$cron_finished_time = get_option( 'get_orders_cron_finished', 0 );
		return ( 0 != $cron_finished_time ? sprintf( __( 'Data last updated on %s.', 'price-robot-for-woocommerce' ), date( 'Y-m-d H:i:s', $cron_finished_time ) ) : '' );
	}

	/**
	 * is_price_robot_enabled.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] [!!!] (dev) finish this, e.g. in admin
	 */
	function is_price_robot_enabled( $product_id ) {
		return ( apply_filters( 'alg_wc_price_robot_all_products', false ) || 'yes' === get_post_meta( $product_id, '_price_robot_enabled', true ) );
	}

}

endif;

return new Alg_WC_Price_Robot_Core();
