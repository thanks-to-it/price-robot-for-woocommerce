<?php
/**
 * Price Robot for WooCommerce - Crons
 *
 * @version 1.3.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Crons' ) ) :

class Alg_WC_Price_Robot_Crons {

	/**
	 * Constructor.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function __construct() {

		$this->event_hook = 'alg_price_robot_get_data_hook';

		add_action( 'wp',              array( $this, 'schedule_the_events' ) );
		add_action( $this->event_hook, array( $this, 'get_orders' ) );
		add_filter( 'cron_schedules',  array( $this, 'cron_add_custom_intervals' ) );

		add_action( 'admin_init',      array( $this, 'get_orders_manual' ) );

	}

	/**
	 * get_cron_update_intervals.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function get_cron_update_intervals() {
		return array(
			'minutely'   => __( 'Update every minute', 'price-robot-for-woocommerce' ),
			'hourly'     => __( 'Update hourly', 'price-robot-for-woocommerce' ),
			'twicedaily' => __( 'Update twice daily', 'price-robot-for-woocommerce' ),
			'daily'      => __( 'Update daily', 'price-robot-for-woocommerce' ),
			'weekly'     => __( 'Update weekly', 'price-robot-for-woocommerce' ),
		);
	}

	/**
	 * On an early action hook, check if the hook is scheduled - if not, schedule it.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function schedule_the_events() {
		$selected_interval = get_option( 'alg_price_robot_general_admin_crons_update', 'daily' );
		$update_intervals  = $this->get_cron_update_intervals();
		foreach ( $update_intervals as $interval => $desc ) {
			$event_timestamp = wp_next_scheduled( $this->event_hook, array( $interval ) );
			if ( ! $event_timestamp && $selected_interval === $interval ) {
				wp_schedule_event( time(), $selected_interval, $this->event_hook, array( $selected_interval ) );
			} elseif ( $event_timestamp && $selected_interval !== $interval ) {
				wp_unschedule_event( $event_timestamp, $this->event_hook, array( $interval ) );
			}
		}
	}

	/**
	 * cron_add_custom_intervals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function cron_add_custom_intervals( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'price-robot-for-woocommerce' )
		);
		$schedules['minutely'] = array(
			'interval' => 60,
			'display'  => __( 'Once a Minute', 'price-robot-for-woocommerce' )
		);
		return $schedules;
	}

	/**
	 * get_orders_manual.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function get_orders_manual() {
		if ( isset( $_GET['get_orders_manual'] ) ) {
			$this->get_orders();
			wp_safe_redirect( add_query_arg( 'get_orders_manual_finished', 'yes', remove_query_arg( 'get_orders_manual' ) ) );
			exit;
		}
		if ( isset( $_GET['get_orders_manual_finished'] ) ) {
			echo '<div id="message" class="updated"><p><strong>' . __( 'Price robot data have been updated.', 'price-robot-for-woocommerce' ) . '</strong></p></div>';
		}
	}

	/**
	 * get_order.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [now] [!!!] (dev) remove "Timeframe sales" check
	 */
	function get_order( $products_data, $item, $product_id, $order_id ) {

		// Init (if necessary)
		if ( ! isset( $products_data[ $product_id ]['timeframe_sales'] ) ) {
			$products_data[ $product_id ]['timeframe_sales'] = 0;
		}

		// Timeframe sales
		$order_date   = get_post_time( 'U', false, $order_id );
		$current_time = current_time( 'timestamp' );
		if ( $order_date > ( $current_time - get_option( 'alg_price_robot_last_sale_discount_timeframe_days', 30 ) * DAY_IN_SECONDS ) ) {
			$products_data[ $product_id ]['timeframe_sales'] += $item['qty'];
		}

		// Last sale time
		if ( ! isset( $products_data[ $product_id ]['last_sale'] ) ) {
			$products_data[ $product_id ]['last_sale'] = $order_date;
		}

		// Last sale price
		if ( ! isset( $products_data[ $product_id ]['price_last_sale'] ) ) {
			$line_subtotal = wc_prices_include_tax() ? ( $item['line_subtotal'] + $item['line_subtotal_tax'] ) : $item['line_subtotal'];
			$products_data[ $product_id ]['price_last_sale'] = round( ( $line_subtotal / $item['qty'] ), get_option( 'woocommerce_price_num_decimals', 2 ) );
		}

		return $products_data;
	}

	/**
	 * get_orders.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [maybe] (dev) `round ( $timeframe_sales_average / $timeframe_sales_average_counter )`?
	 * @todo    [maybe] (dev) `update_option( 'timeframe_sales_average', $timeframe_sales_average . ' / ' . $timeframe_sales_average_counter );`?
	 * @todo    [maybe] (dev) `$args_products`: `'post_status' => 'publish'`?
	 */
	function get_orders() {

		update_option( 'alg_wc_price_robot_get_orders_cron_started', current_time( 'timestamp' ) );

		// Getting $products_data from orders
		$block_size    = 512;
		$offset        = 0;
		$products_data = array();
		while ( true ) {
			$args_orders = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'wc-completed',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'offset'         => $offset,
				'fields'         => 'ids',
				'date_query'     => array(
					array( 'after' => date( 'Y-m-d H:i:s', strtotime( '-' . get_option( 'alg_price_robot_last_sale_discount_timeframe_days', 30 ) . ' days' ) ) )
				),
			);
			$loop_orders = new WP_Query( $args_orders );
			if ( ! $loop_orders->have_posts() ) {
				break;
			}
			foreach ( $loop_orders->posts as $order_id ) {
				$order = wc_get_order( $order_id );
				$items = $order->get_items();
				foreach ( $items as $item ) {
					$products_data = $this->get_order( $products_data, $item, $item['product_id'], $order_id );
					if ( isset( $item['variation_id'] ) && 0 != $item['variation_id'] ) {
						$products_data = $this->get_order( $products_data, $item, $item['variation_id'], $order_id );
					}
				}
			}
			$offset += $block_size;
		}

		// Updating all products meta and calculating `timeframe_sales_average`
		$offset                  = 0;
		$timeframe_sales_average = $timeframe_sales_average_counter = 0;
		while ( true ) {
			$args_products = array(
				'post_type'      => 'product',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'offset'         => $offset,
				'fields'         => 'ids',
			);
			$loop_products = new WP_Query( $args_products );
			if ( ! $loop_products->have_posts() ) {
				break;
			}
			foreach ( $loop_products->posts as $product_id ) {
				$_product = wc_get_product( $product_id );
				$ids = array();
				if ( 'no' === get_option( 'alg_price_robot_general_variable_as_single', 'no' ) && $_product->is_type( 'variable' ) ) {
					$variations = $_product->get_available_variations();
					foreach ( $variations as $variation ) {
						$ids[] = $variation['variation_id'];
					}
				} else {
					$ids[] = $product_id;
				}

				foreach ( $ids as $product_id ) {
					$timeframe_sales = isset( $products_data[ $product_id ]['timeframe_sales'] ) ? $products_data[ $product_id ]['timeframe_sales'] : 0;
					$last_sale       = isset( $products_data[ $product_id ]['last_sale'] )       ? $products_data[ $product_id ]['last_sale']       : '';
					$price_last_sale = isset( $products_data[ $product_id ]['price_last_sale'] ) ? $products_data[ $product_id ]['price_last_sale'] : '';

					update_post_meta( $product_id, '_timeframe_sales', $timeframe_sales );
					update_post_meta( $product_id, '_last_sale',       $last_sale );
					update_post_meta( $product_id, '_price_last_sale', $price_last_sale );

					if ( $timeframe_sales > 0 ) {
						$timeframe_sales_average += intval( $timeframe_sales );
						$timeframe_sales_average_counter++;
					}
				}
			}
			$offset += $block_size;
		}
		$timeframe_sales_average = ( 0 != $timeframe_sales_average_counter ) ? ( $timeframe_sales_average / $timeframe_sales_average_counter ) : 0;
		update_option( 'timeframe_sales_average', $timeframe_sales_average );

		update_option( 'get_orders_cron_finished', current_time( 'timestamp' ) );
	}

}

endif;

return new Alg_WC_Price_Robot_Crons();
