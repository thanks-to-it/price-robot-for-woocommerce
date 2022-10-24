<?php
/**
 * Price Robot for WooCommerce - Robot - Formula - Shortcodes
 *
 * @version 1.3.0
 * @since   1.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Formula_Shortcodes' ) ) :

class Alg_WC_Price_Robot_Formula_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [maybe] (feature) shortcode: `product_data`?
	 */
	function __construct() {
		$this->shortcodes = array(
			'if',
			'if_user_id',
			'if_user_id_not',
			'if_product_id',
			'if_product_id_not',
			'round',
			'floor',
			'ceil',
			'product_last_sale_time',
			'product_meta',
			'product_function',
			'product_price',
			'product_original_price',
			'product_id',
			'user_id',
			'function',
			'current_time',
			'min',
			'max',
		);
		$this->prefix = get_option( 'alg_wc_price_robot_formula_shortcode_prefix', '' );
		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode( $this->prefix . $shortcode, array( $this, 'shortcode_' . $shortcode ) );
		}
	}

	/**
	 * shortcode_min.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_min( $atts, $content = '' ) {
		if ( '' !== $content && isset( $atts['value'] ) ) {
			return max( $atts['value'], $this->process_content( $content ) );
		}
	}

	/**
	 * shortcode_max.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_max( $atts, $content = '' ) {
		if ( '' !== $content && isset( $atts['value'] ) ) {
			return min( $atts['value'], $this->process_content( $content ) );
		}
	}

	/**
	 * shortcode_current_time.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_current_time( $atts, $content = '' ) {
		return current_time( 'timestamp' );
	}

	/**
	 * shortcode_if.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] [!!!] (feature) more operators
	 */
	function shortcode_if( $atts, $content = '' ) {
		$res = false;
		if ( '' !== $content && isset( $atts['value1'], $atts['value2'], $atts['operator'] ) ) {
			$value1 = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value1'] ) );
			$value2 = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value2'] ) );
			switch ( $atts['operator'] ) {
				case 'in':
					$res = (   in_array( $value1, array_map( 'trim', explode( ',', $value2 ) ) ) );
					break;
				case 'not_in':
					$res = ( ! in_array( $value1, array_map( 'trim', explode( ',', $value2 ) ) ) );
					break;
			}
		}
		return ( $res ? do_shortcode( $content ) : '' );
	}

	/**
	 * shortcode_if_product_id.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_if_product_id( $atts, $content = '' ) {
		return ( '' !== $content && isset( $atts['ids'] ) && '' !== $atts['ids'] && isset( $this->data ) && in_array( $this->data['product_id'], array_map( 'trim', explode( ',', $atts['ids'] ) ) ) ?
			do_shortcode( $content ) : '' );
	}

	/**
	 * shortcode_if_product_id_not.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_if_product_id_not( $atts, $content = '' ) {
		return ( '' !== $content && isset( $atts['ids'] ) && '' !== $atts['ids'] && isset( $this->data ) && ! in_array( $this->data['product_id'], array_map( 'trim', explode( ',', $atts['ids'] ) ) ) ?
			do_shortcode( $content ) : '' );
	}

	/**
	 * shortcode_if_user_id.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_if_user_id( $atts, $content = '' ) {
		return ( '' !== $content && isset( $atts['ids'] ) && '' !== $atts['ids'] && in_array( get_current_user_id(), array_map( 'trim', explode( ',', $atts['ids'] ) ) ) ?
			do_shortcode( $content ) : '' );
	}

	/**
	 * shortcode_if_user_id_not.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_if_user_id_not( $atts, $content = '' ) {
		return ( '' !== $content && isset( $atts['ids'] ) && '' !== $atts['ids'] && ! in_array( get_current_user_id(), array_map( 'trim', explode( ',', $atts['ids'] ) ) ) ?
			do_shortcode( $content ) : '' );
	}

	/**
	 * shortcode_user_id.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_user_id( $atts, $content = '' ) {
		return get_current_user_id();
	}

	/**
	 * shortcode_function.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_function( $atts, $content = '' ) {
		return ( isset( $atts['name'] ) && '' !== $atts['name'] && function_exists( $atts['name'] ) ? $atts['name']() : '' );
	}

	/**
	 * shortcode_round.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [next] (feature) `$atts['precision']`
	 */
	function shortcode_round( $atts, $content = '' ) {
		$round_func = ( isset( $atts['function'] ) && '' !== $atts['function'] && function_exists( $atts['function'] ) ? $atts['function'] : 'round' );
		return $this->output( $round_func( $this->process_content( $content ) ), $atts );
	}

	/**
	 * shortcode_floor.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_floor( $atts, $content = '' ) {
		return $this->output( floor( $this->process_content( $content ) ), $atts );
	}

	/**
	 * shortcode_ceil.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_ceil( $atts, $content = '' ) {
		return $this->output( ceil( $this->process_content( $content ) ), $atts );
	}

	/**
	 * shortcode_product_meta.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_product_meta( $atts, $content = '' ) {
		if ( isset( $this->data ) && isset( $atts['key'] ) ) {
			return $this->output( get_post_meta( $this->data['product_id'], $atts['key'], true ), $atts );
		}
	}

	/**
	 * shortcode_product_last_sale_time.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_product_last_sale_time( $atts, $content = '' ) {
		if ( isset( $this->data ) ) {
			if ( '' === ( $last_sale_time = get_post_meta( $this->data['product_id'], '_last_sale', true ) ) ) {
				$last_sale_time = get_post_time( 'U', false, $this->data['product_id'] ); // product creation time
			}
			if ( isset( $atts['min_date'] ) ) {
				$min_time = strtotime( $atts['min_date'] );
				if ( $last_sale_time < $min_time ) {
					$last_sale_time = $min_time;
				}
			}
			if ( isset( $atts['max_date'] ) ) {
				$max_time = strtotime( $atts['max_date'] );
				if ( $last_sale_time > $max_time ) {
					$last_sale_time = $max_time;
				}
			}
			return $this->output( $last_sale_time, $atts );
		}
	}

	/**
	 * shortcode_product_function.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [next] (feature) function param(s)
	 */
	function shortcode_product_function( $atts, $content = '' ) {
		if ( isset( $this->data ) && isset( $atts['name'] ) && ( $product = wc_get_product( $this->data['product_id'] ) ) && is_callable( array( $product, $atts['name'] ) ) ) {
			return $this->output( $product->{$atts['name']}(), $atts );
		}
	}

	/**
	 * shortcode_product_id.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_product_id( $atts, $content = '' ) {
		if ( isset( $this->data ) ) {
			return $this->output( $this->data['product_id'], $atts );
		}
	}

	/**
	 * shortcode_product_price.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_product_price( $atts, $content = '' ) {
		if ( isset( $this->data ) ) {
			return $this->output( $this->data['price'], $atts );
		}
	}

	/**
	 * shortcode_product_original_price.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function shortcode_product_original_price( $atts, $content = '' ) {
		if ( isset( $this->data ) ) {
			return $this->output( $this->data['original_price'], $atts );
		}
	}

	/**
	 * process_content.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function process_content( $content ) {
		if ( '' !== ( $content = do_shortcode( $content ) ) ) {
			$content = WC_Eval_Math::evaluate( $content );
		}
		return $content;
	}

	/**
	 * output.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function output( $value, $atts ) {
		return ( '' === $value ? ( isset( $atts['on_empty'] ) ? $atts['on_empty'] : 0 ) : $value );
	}

	/**
	 * do_shortcode.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function do_shortcode( $content, $data ) {
		$this->data = $data;
		$value = do_shortcode( $content );
		unset( $this->data );
		return $value;
	}

}

endif;

return new Alg_WC_Price_Robot_Formula_Shortcodes();
