<?php
/**
 * Price Robot for WooCommerce - Admin
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Admin' ) ) :

class Alg_WC_Price_Robot_Admin {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function __construct() {

		if (
			'no' === get_option( 'alg_price_robot_general_auto_pricing_enabled', 'yes' ) ||
			'no' === get_option( 'alg_wc_price_robot_all_products', 'no' )
		) {

			// Price robot column
			add_filter(
				'manage_edit-product_columns',
				array( $this, 'add_product_column_price_robot' ),
				PHP_INT_MAX
			);
			add_action(
				'manage_product_posts_custom_column',
				array( $this, 'render_product_column_price_robot' ),
				PHP_INT_MAX
			);

			// Filtering
			add_action(
				'restrict_manage_posts',
				array( $this, 'restrict_manage_posts' )
			);
			add_filter(
				'parse_query',
				array( $this, 'products_by_price_robot_admin_filter_query' )
			);

			// Button
			add_action(
				'admin_init',
				array( $this, 'enable_price_robot_for_product' ),
				PHP_INT_MAX
			);

		}

		// Debug column
		add_filter(
			'manage_edit-product_columns',
			array( $this, 'add_product_column_price_robot_debug' ),
			PHP_INT_MAX
		);
		add_action(
			'manage_product_posts_custom_column',
			array( $this, 'render_product_column_price_robot_debug' ),
			PHP_INT_MAX
		);

	}

	/**
	 * Filter the products in admin based on options.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @access  public
	 * @param   mixed $query
	 * @return  void
	 */
	function products_by_price_robot_admin_filter_query( $query ) {
		global $typenow;
		if (
			$typenow == 'product' &&
			isset( $_GET['price_robot'] ) &&
			'all' != ( $price_robot = wc_clean( wp_unslash( $_GET['price_robot'] ) ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			if ( 'yes' === $price_robot ) {
				$query->query_vars['meta_value'] = 'yes'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				$query->query_vars['meta_key']   = '_price_robot_enabled'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			} elseif ( 'no' === $price_robot ) {
				$query->query_vars['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => '_price_robot_enabled',
						'compare' => 'NOT EXISTS',
						'value'   => '', // this is ignored, but is necessary...
					),
					array(
						'key'     => '_price_robot_enabled',
						'value'   => 'no',
					),
				);
			}
		}
	}

	/**
	 * Filters for post types.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @todo    (v2.0.0) add nonce
	 */
	function restrict_manage_posts() {
		global $typenow;
		if ( 'product' === $typenow ) {
			$selected_value = (
				isset( $_GET['price_robot'] ) ?
				wc_clean( wp_unslash( $_GET['price_robot'] ) ) : // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'all'
			);
			$values = array(
				'all' => __( 'Price Robot: All Products', 'price-robot-for-woocommerce' ),
				'yes' => __( 'Price Robot: Enabled', 'price-robot-for-woocommerce' ),
				'no'  => __( 'Price Robot: Disabled', 'price-robot-for-woocommerce' ),
			);
			echo '<select id="price_robot" name="price_robot">';
			foreach ( $values as $code => $name ) {
				echo '<option value="' . esc_attr( $code ) . '" ' . selected( $code, $selected_value, false ) . '>' .
					esc_html( $name ) .
				'</option>';
			}
			echo '</select>';
		}
	}

	/**
	 * enable_price_robot_for_product.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function enable_price_robot_for_product() {
		if ( ! empty( $_GET['enable_price_robot'] ) ) {
			update_post_meta(
				absint( wp_unslash( $_GET['enable_price_robot'] ) ),
				'_price_robot_enabled',
				'yes'
			);
		}
		if ( ! empty( $_GET['disable_price_robot'] ) ) {
			update_post_meta(
				absint( wp_unslash( $_GET['disable_price_robot'] ) ),
				'_price_robot_enabled',
				'no'
			);
		}
	}

	/**
	 * add_product_column_price_robot_debug.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function add_product_column_price_robot_debug( $columns ) {
		if ( 'yes' === get_option( 'alg_price_robot_general_admin_products_debug_enabled', 'no' ) ) {
			$columns['price_robot_debug'] = __( 'Price Robot Debug', 'price-robot-for-woocommerce' );
		}
		return $columns;
	}

	/**
	 * render_product_column_price_robot_debug.
	 *
	 * @version 2.0.0
	 * @since   1.3.0
	 */
	function render_product_column_price_robot_debug( $column ) {

		if ( 'price_robot_debug' != $column ) {
			return;
		}

		$column_content = '';

		$product_id = get_the_ID();
		$product    = wc_get_product( $product_id );

		if (
			'no' === get_option( 'alg_price_robot_general_variable_as_single', 'no' ) &&
			$product->is_type( 'variable' )
		) {
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				$variation_product = wc_get_product( $variation['variation_id'] );
				$column_content .= $this->get_price_robot_admin_html( $variation_product, true );
				$column_content .= '<hr>';
			}
		} else {
			$column_content .= $this->get_price_robot_admin_html( $product, true );
		}

		echo wp_kses_post( $column_content );
	}

	/**
	 * Add price robot column to products list.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function add_product_column_price_robot( $columns ) {
		$columns['price_robot'] = __( 'Price Robot', 'price-robot-for-woocommerce' );
		return $columns;
	}

	/**
	 * render_product_column_price_robot.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @todo    (v2.0.0) add nonce
	 * @todo    (dev) merge with `render_product_column_price_robot_debug()`
	 * @todo    (dev) better icon?
	 */
	function render_product_column_price_robot( $column ) {

		if ( 'price_robot' != $column ) {
			return;
		}

		$column_content = '';

		$product_id = get_the_ID();
		$product    = wc_get_product( $product_id );

		// Button
		if ( 'yes' === get_post_meta( $product_id, '_price_robot_enabled', true ) ) {
			$href       = add_query_arg(
				'disable_price_robot',
				$product_id,
				remove_query_arg( 'enable_price_robot' )
			);
			$icon_color = '#32CD32';
			$icon_title = __( 'Disable Price Robot', 'price-robot-for-woocommerce' );
		} else {
			$href       = add_query_arg(
				'enable_price_robot',
				$product_id,
				remove_query_arg( 'disable_price_robot' )
			);
			$icon_color = '#808080';
			$icon_title = __( 'Enable Price Robot', 'price-robot-for-woocommerce' );
		}
		$column_content .= '<a href="' . $href . '">' .
			'<span' .
				' class="dashicons dashicons-video-alt3"' .
				' style="color: ' . $icon_color . '; margin-right: 3px;"' .
				' title="' . $icon_title . '"' .
			'></span>' .
		'</a>';

		// Prices
		if (
			'no' === get_option( 'alg_price_robot_general_variable_as_single', 'no' ) &&
			$product->is_type( 'variable' )
		) {
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				$variation_product = wc_get_product( $variation['variation_id'] );
				$column_content .= $this->get_price_robot_admin_html( $variation_product, false );
				$column_content .= ' ';
			}
		} else {
			$column_content .= $this->get_price_robot_admin_html( $product, false );
		}

		echo wp_kses_post( $column_content );
	}

	/**
	 * get_price_robot_admin_html
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function get_price_robot_admin_html( $product, $debug_html = false ) {

		$html = '';

		$product_id     = alg_wc_price_robot()->core->get_product_id( $product );
		$original_price = alg_wc_price_robot()->core->get_product_price_original( $product );
		$robot_price    = alg_wc_price_robot()->core->get_robot_price( $original_price, $product, true );
		$color          = (
			$robot_price < $original_price ?
			'red' :
			(
				$robot_price > $original_price ?
				'green' :
				'gray'
			)
		);

		$html .= '<span style="color:' . $color . ';">' .
			wc_price( $robot_price ) .
		'</span>';

		if ( $debug_html ) {

			$last_sale       = get_post_meta( $product_id, '_last_sale',       true );
			$price_last_sale = get_post_meta( $product_id, '_price_last_sale', true );
			$no_sales        = ( '' == $last_sale );
			if ( $no_sales ) {
				$last_sale       = get_post_time( 'U', false, $product_id ); // product creation time
				$price_last_sale = $original_price;
			}
			$price_last_sale = apply_filters(
				'alg_price_robot_last_sale_price_last_sale',
				$price_last_sale,
				$last_sale,
				$no_sales
			);

			$html .= '<br>' . sprintf(
				/* Translators: %s: Time and price. */
				__( 'Last sale: %s', 'price-robot-for-woocommerce' ),
				date( 'Y-m-d H:i:s', $last_sale ) . ' @ ' . wc_price( $price_last_sale )
			);
			$html .= '<br>' . sprintf(
				/* Translators: %s: Number of sales. */
				__( 'Timeframe sales: %s', 'price-robot-for-woocommerce' ),
				get_post_meta( $product_id, '_timeframe_sales', true )
			);
			$html .= '<br>' . sprintf(
				/* Translators: %s: Product ID. */
				__( 'Product ID: %s', 'price-robot-for-woocommerce' ),
				$product_id
			);
			$html .= '<br>' . sprintf(
				/* Translators: %s: Price. */
				__( 'Original price: %s', 'price-robot-for-woocommerce' ),
				wc_price( $original_price )
			);
		}

		return $html;
	}

}

endif;

return new Alg_WC_Price_Robot_Admin();
