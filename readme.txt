=== Price Robot for WooCommerce ===
Contributors: wpcodefactory, omardabbas, karzin, anbinder, algoritmika, kousikmukherjeeli
Tags: woocommerce, price, robot, advisor, woo commerce
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.3.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The plugin calculates optimal price for products in WooCommerce. Can work as advisor or in automatic mode.

== Description ==

The **Price Robot for WooCommerce** plugin helps you calculate the optimal price for products in WooCommerce. Can work as an advisor or in automatic mode.

### &#9989; How it Works ###

The plugin uses formula to calculate the product prices. You can set the formula by using plugin's shortcodes and mathematical operations.

For example, the formula that automatically modifies the product price, so that it ends with *99* cents:

`
[ceil][product_price][/ceil]-0.01
`

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/price-robot-for-woocommerce-plugin/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Check settings in "WooCommerce > Settings > Price Robot", and on WooCommerce admin Products view.

== Screenshots ==

1. Plugin admin area - WooCommerce > Settings > Price Robot.
2. Plugin admin area - WooCommerce > Products.

== Changelog ==

= 1.3.3 - 31/07/2024 =
* WC tested up to: 9.1
* Tested up to: 6.6.

= 1.3.2 - 26/09/2023 =
* WC tested up to: 8.1.
* Tested up to: 6.3.

= 1.3.1 - 19/06/2023 =
* WC tested up to: 7.8.
* Tested up to: 6.2.

= 1.3.0 - 01/11/2022 =
* Dev - Pro - "Automatic pricing > All products" option added.
* Dev - "Price by Sales" price robot marked as deprecated.
* Dev - "Final Price" price robot marked as deprecated.
* Dev - "Formula" price robot added.
* Dev - "Empty/zero product prices" options added.
* Dev - Minimal `1` price step removed.
* Dev - Data update - "Timeframe in days" option duplicated in the "General" section.
* Dev - Data update - Now processing orders only in the selected timeframe (i.e., "Timeframe in days").
* Dev - Admin - "Reset Settings" options added.
* Dev - All input data is properly sanitized now.
* Dev - Images replaced with dashicons.
* Dev - Localization - `load_plugin_textdomain()` function moved to the `init` action.
* Dev - Plugin is initialized on the `plugins_loaded` action now.
* Dev - Code refactoring.
* Tested up to: 6.0.
* WC tested up to: 7.0.
* Readme.txt updated.
* Deploy script added.

= 1.2.0 - 23/03/2020 =
* Dev - Code refactoring.
* Dev - Admin settings descriptions updated.
* Tags updated.
* Requires at least: 5.0.
* Tested up to: 5.3.
* WC tested up to: 4.0.

= 1.1.1 - 29/10/2018 =
* Dev - Check for "price not empty" added to `get_robot_price()`.
* Dev - "Limit maximum price to regular product price" option added.
* Dev - Plugin URI updated.

= 1.1.0 - 05/08/2017 =
* Dev - WooCommerce version 3 compatibility - Product ID.
* Dev - WooCommerce version 3 compatibility - `woocommerce_get_price`, `woocommerce_get_sale_price` filters replaced with `woocommerce_product_get_price`, `woocommerce_product_get_sale_price`.
* Dev - WooCommerce version 3 compatibility - `get_total_stock()` replaced with `get_stock_quantity()`.
* Fix - Variations pricing fixed.
* Fix - Admin products filtering by "price robot disabled" fixed.
* Dev - `load_plugin_textdomain()` moved from `init` hook to constructor.
* Dev - Version system added.
* Dev - Plugin header (Text Domain) updated.
* Dev - Plugin link changed from http://coder.fm to https://wpcodefactory.com.
* Dev - Code refactoring, cleanup, minor dev and fixes.

= 1.0.1 - 01/10/2015 =
* Dev - General Section - Description extended.
* Dev - Price by Sales Section - Description extended.
* Dev - Final Price Section - Description extended. Rounding type option added. Maximum price option added.

= 1.0.0 - 29/09/2015 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
