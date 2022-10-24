<?php
/**
 * Price Robot for WooCommerce - Robot - Last Sale
 *
 * @version 1.3.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Last_Sale' ) ) :

class Alg_WC_Price_Robot_Last_Sale extends Alg_WC_Price_Robot_Abstract {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) mark as deprecated
	 */
	function __construct() {

		$this->id          = 'last_sale';
		$this->desc        = __( 'Price by Sales', 'price-robot-for-woocommerce' );
		$this->priority    = 10;
		$this->robot_desc  = '<p style="padding:10px;font-weight:bold;">' .
			__( 'This robot is deprecated. Use "Formula" instead. If you disable the robot, its settings section will be removed.', 'price-robot-for-woocommerce' ) .
		'</p>' .
		'<ul style="list-style-type: disc; padding: revert;"><li>' . implode( '</li><li>', array(
			__( 'When enabled, modifies the product price based on current product\'s sales data (last sale price and time since last sale).', 'price-robot-for-woocommerce' ),
			__( 'The price will be decreased by <em>Price Change Step (%)</em> amount, if there is less than <em>Sale Rate Goal</em> pieces of product sold in selected <em>Timeframe in Days</em>.', 'price-robot-for-woocommerce' ),
			__( 'E.g. you set sale rate goal to 0 pieces, timeframe to 30 days, price change step to 5% and products original price to 100$.', 'price-robot-for-woocommerce' ),
			__( 'If there are no sales for that product in 30 days, then the price is decreased to 95$. If there are no sales for 30 days more, the price is again decreased by 5%. This continues until maximum discount percent is reached.', 'price-robot-for-woocommerce' ),
			__( 'Applied to all products with <strong>price robot enabled</strong> option.', 'price-robot-for-woocommerce' ),
		) ) . '</li></ul>';

		parent::__construct();
	}

	/**
	 * get_default_enabled.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function get_default_enabled() {
		return 'no';
	}

	/**
	 * get_price.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function get_price( $price, $product_id, $original_price ) {

		// Check min stock (if set)
		if ( 0 != ( $min_stock = get_option( 'alg_price_robot_last_sale_min_stock', 0 ) ) ) {
			$product       = wc_get_product( $product_id );
			$product_stock = ( alg_wc_price_robot()->core->is_wc_version_below_3 ? $product->get_total_stock() : $product->get_stock_quantity() );
			if ( $product_stock < $min_stock ) {
				return $price;
			}
		}

		// Last sale data
		$last_sale       = get_post_meta( $product_id, '_last_sale',       true );
		$price_last_sale = get_post_meta( $product_id, '_price_last_sale', true );
		$no_sales = ( '' == $last_sale );
		if ( $no_sales ) {
			$last_sale       = get_post_time( 'U', false, $product_id ); // product creation time
			$price_last_sale = $price;
		}
		$price_last_sale = apply_filters( 'alg_price_robot_last_sale_price_last_sale', $price_last_sale, $last_sale, $no_sales );

		// Discount coefficient
		$discount_step_percent     = get_option( 'alg_price_robot_last_sale_discount_step_percent', 5 );
		$min_no_sales_period_days  = get_option( 'alg_price_robot_last_sale_discount_timeframe_days', 30 );
		$min_no_sales_period       = $min_no_sales_period_days * 24 * 60 * 60;
		$time_no_sales             = ( current_time( 'timestamp' ) - $last_sale );
		if ( $time_no_sales / $min_no_sales_period >= 1 || $no_sales ) {
			$discount_percent = floor( $time_no_sales / $min_no_sales_period ) * $discount_step_percent;
			$discount_coef    = ( 100 - $discount_percent ) / 100;
		} else {
			$goal_sale_rate = get_option( 'alg_price_robot_last_sale_goal_sale_rate', 0 );
			if ( $goal_sale_rate > 0 ) {
				$real_sale_rate    = get_post_meta( $product_id, '_timeframe_sales', true );
				$discount_coef_min = ( 100 - $discount_step_percent ) / 100;
				$discount_coef_max = ( 100 + $discount_step_percent ) / 100;
				$discount_coef     = $real_sale_rate / $goal_sale_rate;
				if ( $discount_coef < $discount_coef_min ) {
					$discount_coef = $discount_coef_min;
				} elseif ( $discount_coef > $discount_coef_max ) {
					$discount_coef = $discount_coef_max;
				}
			} else {
				$discount_coef = 1;
			}
		}

		// Price
		$modified_price = $price_last_sale * $discount_coef;

		// Maximum discount
		$max_discount_percent = get_option( 'alg_price_robot_last_sale_discount_max_percent', 25 );
		$min_price = $original_price * ( ( 100 - $max_discount_percent ) / 100 );
		if ( $modified_price < $min_price ) {
			$modified_price = $min_price;
		}

		// Limit to regular price
		if ( 'yes' === get_option( 'alg_price_robot_limit_to_regular', 'no' ) ) {
			$regular_price = get_post_meta( $product_id, '_regular_price', true );
			if ( $modified_price > $regular_price ) {
				return $regular_price;
			}
		}

		// The end
		return round( $modified_price, get_option( 'woocommerce_price_num_decimals', 2 ) );
	}

	/**
	 * get_settings
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Robot Options', 'price-robot-for-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_price_robot_last_sale_discount_options',
			),
			array(
				'title'     => __( 'Timeframe in days', 'price-robot-for-woocommerce' ),
				'desc'      =>
					'<br>' . __( 'If you modify this timeframe - data must be updated for prices to change. This will be done automatically, according to settings in General section.', 'price-robot-for-woocommerce' ) .
					'<br>' . sprintf( __( 'You can also <a href="%s">update data manually</a>.', 'price-robot-for-woocommerce' ), add_query_arg( 'get_orders_manual', 'yes' ) ) .
					' ' . alg_wc_price_robot()->core->get_data_last_updated_message(),
				'desc_tip'  => __( 'You can enter a fraction of a day here, however we do recommend setting one full day at least.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_last_sale_discount_timeframe_days',
				'default'   => 30,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '0.0001', 'min' => '0.0001' ),
			),
			array(
				'title'     => __( 'Price change step (%)', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Price change step in percent.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_last_sale_discount_step_percent',
				'default'   => 5,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '0.0001', 'min' => '0.0001', 'max' => '100' ),
			),
			array(
				'title'     => __( 'Maximum discount (%)', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Maximum discount from original price in percent.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_last_sale_discount_max_percent',
				'default'   => 25,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '0.0001', 'min' => '0', 'max' => '100' ),
			),
			array(
				'title'     => __( 'Limit maximum price', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Enable', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Limit maximum price to regular product price.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_limit_to_regular',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Min stock', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Minimal product stock to apply the price robot. Set zero to disable and handle products with any stock.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_last_sale_min_stock',
				'default'   => 0,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '1', 'min' => '0' ),
			),
			array(
				'title'     => __( 'Sale rate goal', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Sale rate goal in selected timeframe. Set zero to disable.', 'price-robot-for-woocommerce' ),
				'desc'      => sprintf( __( 'Sales in your shop: on average %.2f pieces of each product are sold in %d days timeframe.', 'price-robot-for-woocommerce' ),
					round( get_option( 'timeframe_sales_average', 0 ), 2 ),
					get_option( 'alg_price_robot_last_sale_discount_timeframe_days', 30 )
				),
				'id'        => 'alg_price_robot_last_sale_goal_sale_rate',
				'default'   => 0,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '1', 'min' => '0' ),
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_price_robot_last_sale_discount_options',
			),
		);
		return array_merge( parent::get_settings(), $settings );
	}

}

endif;

return new Alg_WC_Price_Robot_Last_Sale();
