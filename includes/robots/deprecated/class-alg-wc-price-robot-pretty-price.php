<?php
/**
 * Price Robot for WooCommerce - Robot - Pretty Price
 *
 * @version 1.3.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Pretty_Price' ) ) :

class Alg_WC_Price_Robot_Pretty_Price extends Alg_WC_Price_Robot_Abstract {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) mark as deprecated
	 */
	function __construct() {

		$this->id         = 'pretty_price';
		$this->desc       = __( 'Final Price', 'price-robot-for-woocommerce' );
		$this->priority   = 100;
		$this->robot_desc  = '<p style="padding:10px;font-weight:bold;">' .
			__( 'This robot is deprecated. Use "Formula" instead. If you disable the robot, its settings section will be removed.', 'price-robot-for-woocommerce' ) .
		'</p>' .
		'<ul style="list-style-type: disc; padding: revert;"><li>' . implode( '</li><li>', array(
			__( 'When enabled, modifies the product price, so that it <strong>includes 99 cents</strong> in it. E.g. if current price is <code>$98.55</code>, robot will change it to <code>$98.99</code>.', 'price-robot-for-woocommerce' ),
			__( 'The robot changes the product price last, after all other robots made their modifications to the price. This insures that <strong>price is final</strong> and no other changes are made afterwards.', 'price-robot-for-woocommerce' ),
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
	 * @version 1.0.1
	 * @since   1.0.0
	 */
	function get_price( $price, $product_id, $original_price ) {
		$rounding_function = get_option( 'alg_price_robot_pretty_price_round_type', 'ceil' );
		$price             = $rounding_function( $price );
		$max_price         = get_option( 'alg_price_robot_pretty_price_max_price', 0 );
		if ( $price > 0 && ( 0 == $max_price || $price <= $max_price ) ) {
			$price = $price - 0.01;
		}
		return $price;
	}

	/**
	 * get_settings.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Robot Options', 'price-robot-for-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_price_robot_pretty_price_options',
			),
			array(
				'title'     => __( 'Rounding type', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Select how to round the price before setting 99 cents.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_pretty_price_round_type',
				'default'   => 'ceil',
				'type'      => 'select',
				'class'     => 'chosen_select',
				'options'   => array(
					'round' => __( 'Round', 'price-robot-for-woocommerce' ),
					'ceil'  => __( 'Round up', 'price-robot-for-woocommerce' ),
					'floor' => __( 'Round down', 'price-robot-for-woocommerce' ),
				),
			),
			array(
				'title'     => __( 'Maximum price', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Maximum price. Set zero to disable and apply robot to any price.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_pretty_price_max_price',
				'default'   => 0,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '0.0001', 'min'  => '0' ),
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_price_robot_pretty_price_options',
			),
		);
		return array_merge( parent::get_settings(), $settings );
	}

}

endif;

return new Alg_WC_Price_Robot_Pretty_Price();
