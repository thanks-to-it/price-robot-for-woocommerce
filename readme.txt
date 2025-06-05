=== Advanced Conditional Pricing for WooCommerce ===
Contributors: algoritmika, thankstoit, anbinder, karzin
Tags: woocommerce, price, conditional pricing, dynamic pricing, price robot
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.0.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The plugin automatically calculates the optimal price for products in WooCommerce. Can work as an advisor or in automatic mode.

== Description ==

The **Advanced Conditional Pricing for WooCommerce** plugin is your tool to automatically determine the best prices for your products. This plugin can function as a consultative assistant or be set to an automatic mode to seamlessly integrate optimal pricing strategies into your WooCommerce store.

Leveraging mathematical operations and plugin-specific shortcodes, it allows you to formulate pricing rules. For instance, you can apply charming prices to ensure that all your product prices end with .99 cents, giving a perception of a deal to your customers.

Besides just adjusting prices to appear more attractive, it can intelligently react to your product's sales data to find the optimal price point. If a product hasn't been selling over a specified period, the plugin can be configured to automatically reduce its price, encouraging a potential increase in sales.

Experience a new level of pricing strategy, where technology meets commerce to drive success.

### ✅ How it Works ###

The plugin uses formula to calculate the product prices. You can set the formula by using plugin's shortcodes and mathematical operations.

For example, the formula that automatically modifies the product price, so that it ends with *99* cents:

`
[ceil][product_price][/ceil]-0.01
`

### 🗘 Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* Head to the plugin [GitHub Repository](https://github.com/thanks-to-it/price-robot-for-woocommerce) to find out how you can pitch in.

### ℹ More ###

* The plugin is **"High-Performance Order Storage (HPOS)"** compatible.

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Check settings in "WooCommerce > Settings > Price Robot", and on WooCommerce admin Products view.

== Screenshots ==

1. Plugin admin area - WooCommerce > Settings > Price Robot.
2. Plugin admin area - WooCommerce > Products.

== Changelog ==

= 2.0.0 - 05/06/2025 =
* Fix - Translation loading fixed.
* Dev - "Automatic pricing > All products" option moved to the free plugin version.
* Dev - The free plugin version now allows an unlimited number of price-robot-enabled products.
* Dev - Security - Output escaped.
* Dev - Security - Input sanitized escaped.
* Dev - "High-Performance Order Storage (HPOS)" compatibility.
* Dev - PHP v8.2 compatibility (dynamic properties).
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* Dev - Coding standards improved.
* Plugin renamed from "Price Robot for WooCommerce" to "Advanced Conditional Pricing for WooCommerce".
* WC tested up to: 9.8.
* Tested up to: 6.8.

= 1.3.3 - 31/07/2024 =
* WC tested up to: 9.1.
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
* WC tested up to: 7.0.
* Tested up to: 6.0.
* Readme.txt updated.
* Deploy script added.

= 1.2.0 - 23/03/2020 =
* Dev - Code refactoring.
* Dev - Admin settings descriptions updated.
* Tags updated.
* Requires at least: 5.0.
* WC tested up to: 4.0.
* Tested up to: 5.3.

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
