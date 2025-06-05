<?php
/**
 * Price Robot for WooCommerce - Robot - Abstract
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Abstract' ) ) :

class Alg_WC_Price_Robot_Abstract extends Alg_WC_Price_Robot_Settings_Section {

	/**
	 * priority.
	 */
	public $priority;

	/**
	 * robot_desc.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	public $robot_desc;

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `Abstract`?
	 */
	function __construct() {
		parent::__construct();
		if ( 'yes' === get_option( 'alg_price_robot_' . $this->id . '_enabled', $this->get_default_enabled() ) ) {
			add_filter( 'alg_woocommerce_price_robot', array( $this, 'get_price' ), $this->priority, 3 );
		}
	}

	/**
	 * get_default_enabled.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function get_default_enabled() {
		return 'yes';
	}

	/**
	 * get_settings.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) better styling?
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'     => $this->desc . ' ' . __( 'Options', 'price-robot-for-woocommerce' ),
				'desc'      => '<div style="background-color: white; padding: 5px; border-radius: 5px;">' .
					$this->robot_desc .
				'</div>',
				'type'      => 'title',
				'id'        => 'alg_price_robot_' . $this->id . '_enable_options',
			),
			array(
				'title'     => $this->desc,
				'desc'      => '<strong>' . __( 'Enable', 'price-robot-for-woocommerce' ) . '</strong>',
				'id'        => 'alg_price_robot_' . $this->id . '_enabled',
				'default'   => $this->get_default_enabled(),
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_price_robot_' . $this->id . '_enable_options',
			),
		);
		return $settings;
	}

}

endif;
