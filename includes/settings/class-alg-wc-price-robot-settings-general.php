<?php
/**
 * Price Robot for WooCommerce - Settings - General
 *
 * @version 1.3.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Price_Robot_Settings_General' ) ) :

class Alg_WC_Price_Robot_Settings_General extends Alg_WC_Price_Robot_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'price-robot-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [later] (desc) `alg_wc_price_robot_all_products`
	 * @todo    [now] [!!!] (desc) Data update
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'     => __( 'Price Robot Options', 'price-robot-for-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_price_robot_general_options',
			),
			array(
				'title'     => __( 'Automatic pricing', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Enable', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'This will change products prices on frontend. If disabled, will work in Advisor mode.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_general_auto_pricing_enabled',
				'default'   => 'yes',
				'type'      => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'desc_tip'  => __( '"Automatic pricing" option must be enabled.', 'price-robot-for-woocommerce' ) . ' ' .
					sprintf( __( 'If disabled, visit WooCommerce %s to enable the robot on per product basis.', 'price-robot-for-woocommerce' ),
						'<a href="' . admin_url( 'edit.php?post_type=product' ) . '">' . __( 'products page', 'price-robot-for-woocommerce' ) . '</a>' ) .
					apply_filters( 'alg_wc_price_robot_settings',
						'<br>You will need <a target="_blank" href="https://wpfactory.com/item/price-robot-for-woocommerce/">Price Robot for WooCommerce Pro</a> plugin version to enable this option.' ),
				'desc'      => __( 'All products', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_wc_price_robot_all_products',
				'default'   => 'no',
				'type'      => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_price_robot_settings', array( 'disabled' => 'disabled' ) ),
				'checkboxgroup' => 'end',
			),
			array(
				'title'     => __( 'Debug', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Enable', 'price-robot-for-woocommerce' ),
				'desc_tip'  => sprintf( __( 'Adds "Price Robot Debug" column to WooCommerce %s.', 'price-robot-for-woocommerce' ),
					'<a href="' . admin_url( 'edit.php?post_type=product' ) . '">' . __( 'products page', 'price-robot-for-woocommerce' ) . '</a>' ),
				'id'        => 'alg_price_robot_general_admin_products_debug_enabled',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Data update', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Schedule', 'price-robot-for-woocommerce' ) . '<br>' .
					'<a href="' . add_query_arg( 'get_orders_manual', 'yes' ) . '">' . __( 'Update data now', 'price-robot-for-woocommerce' ) . '</a>',
				'desc_tip'  => alg_wc_price_robot()->core->get_data_last_updated_message(),
				'id'        => 'alg_price_robot_general_admin_crons_update',
				'default'   => 'daily',
				'type'      => 'select',
				'class'     => 'chosen_select',
				'options'   => alg_wc_price_robot()->core->crons->get_cron_update_intervals(),
			),
			array(
				'desc'      => __( 'Timeframe in days', 'price-robot-for-woocommerce' ) . '<br>' .
					__( 'If you modify this timeframe, then data must be updated for prices to change. This will be done automatically, according to the "Schedule" option.', 'price-robot-for-woocommerce' ) .
					'<br>' . sprintf( __( 'You can also <a href="%s">update data manually</a>.', 'price-robot-for-woocommerce' ), add_query_arg( 'get_orders_manual', 'yes' ) ) .
					' ' . alg_wc_price_robot()->core->get_data_last_updated_message(),
				'desc_tip'  => __( 'You can enter a fraction of a day here, however we do recommend setting one full day at least.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_last_sale_discount_timeframe_days',
				'default'   => 30,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '0.0001', 'min' => '0.0001' ),
			),
			array(
				'title'     => __( 'Display as sale', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Enable', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Display as sale if robot price is lower than original price.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_general_display_as_sale',
				'default'   => 'yes',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Variable as single', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Enable', 'price-robot-for-woocommerce' ),
				'desc_tip'  => __( 'Treat variations as single product.', 'price-robot-for-woocommerce' ) . ' ' .
					__( 'Makes sense if you are going to have same (equal) prices for all variations of a variable product.', 'price-robot-for-woocommerce' ) . ' ' .
					__( 'You may need to clear transients after changing this option.', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_price_robot_general_variable_as_single',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'title'     => __( 'Empty/zero product prices', 'price-robot-for-woocommerce' ),
				'desc'      => __( 'Process products with empty price', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_wc_price_robot_process_empty_price',
				'default'   => 'no',
				'type'      => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'desc'      => __( 'Process products with zero price (i.e. free products)', 'price-robot-for-woocommerce' ),
				'id'        => 'alg_wc_price_robot_process_zero_price',
				'default'   => 'no',
				'type'      => 'checkbox',
				'checkboxgroup' => 'end',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_price_robot_general_options',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_Price_Robot_Settings_General();
