<?php
/**
 * Price Robot for WooCommerce - Robot - Formula
 *
 * @version 1.3.0
 * @since   1.3.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Formula' ) ) :

class Alg_WC_Price_Robot_Formula extends Alg_WC_Price_Robot_Abstract {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] (dev) `alg_price_robot_` to `alg_wc_price_robot_`
	 * @todo    [now] (desc) add examples, etc.
	 */
	function __construct() {

		$this->id         = 'formula';
		$this->desc       = __( 'Formula', 'price-robot-for-woocommerce' );
		$this->priority   = 5;
		$this->robot_desc = '<ul style="list-style-type: disc; padding: revert;"><li>' . implode( '</li><li>', array(
			__( 'Applied to all products with <strong>price robot enabled</strong> option.', 'price-robot-for-woocommerce' ),
		) ) . '</li></ul>';

		if ( 'yes' === get_option( 'alg_price_robot_' . $this->id . '_enabled', 'yes' ) ) {
			$this->shortcodes = require_once( 'class-alg-wc-price-robot-formula-shortcodes.php' );
			require_once( WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php' );
		}

		parent::__construct();
	}

	/**
	 * get_price.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] [!!!] (dev) remove new lines in the `$formula`
	 */
	function get_price( $price, $product_id, $original_price ) {
		if ( '' !== ( $formula = get_option( 'alg_wc_price_robot_formula', '' ) ) ) {

			// Process shortcodes
			$price = $this->shortcodes->do_shortcode( $formula, array( 'price' => $price, 'product_id' => $product_id, 'original_price' => $original_price ) );

			// Process math
			if ( '' !== $price ) {
				$price = WC_Eval_Math::evaluate( $price );
			}

		}
		return $price;
	}

	/**
	 * get_settings.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [now] [!!!] (dev) `if ( isset( $this->shortcodes ) )`
	 * @todo    [now] [!!!] (desc) add examples, e.g. how to replace the deprecated robots
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Robot Options', 'price-robot-for-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_wc_price_robot_formula_options',
			),
			array(
				'title'     => __( 'Formula', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_wc_price_robot_formula',
				'default'   => '',
				'type'      => 'textarea',
				'css'       => 'width:100%;',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_price_robot_formula_options',
			),
			array(
				'title'     => __( 'Advanced Options', 'price-robot-for-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_wc_price_robot_formula_advanced_options',
			),
			array(
				'title'     => __( 'Shortcode prefix', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_wc_price_robot_formula_shortcode_prefix',
				'default'   => '',
				'type'      => 'text',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_price_robot_formula_advanced_options',
			),
		);
		$shortcodes = array();
		if ( isset( $this->shortcodes ) ) {
			$shortcodes = array(
				array(
					'title'     => __( 'Shortcodes', 'price-robot-for-woocommerce' ),
					'desc'      => '<ul style="list-style-type: disc; padding: revert;"><li><code>[' .
						implode( ']</code></li><li><code>[' . $this->shortcodes->prefix, $this->shortcodes->shortcodes ) . ']</code></li></ul>',
					'type'      => 'title',
					'id'        => 'alg_wc_price_robot_formula_shortcodes',
				),
				array(
					'type'      => 'sectionend',
					'id'        => 'alg_wc_price_robot_formula_shortcodes',
				),
			);
		}
		return array_merge( parent::get_settings(), $settings, $shortcodes );
	}

}

endif;

return new Alg_WC_Price_Robot_Formula();
