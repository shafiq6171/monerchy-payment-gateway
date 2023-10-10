<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://profile.fraxzon.com
 * @since             1.0.0
 * @package           Monerchy_Payment_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Monerchy Payment
 * Plugin URI:        https://fraxzon.com
 * Description:       Monerchy Pay API offers a RESTful, HTTP-based interface with predictable URLs, enabling seamless integration for automating various workflows. It supports JSON for data exchange and provides comprehensive documentation for all app actions, facilitating efficient automation of essential tasks.
 * Version:           1.0.0
 * Author:            Shafiq
 * Author URI:        https://profile.fraxzon.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       monerchy-payment-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || ( is_multisite() && in_array( 'woocommerce/woocommerce.php', array_flip( get_site_option( 'active_sitewide_plugins' ) ) ) ) ) {

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( '', '1.0.0' );
function define_monerchy_payment_gateway() {
		define_monerchy_payment_gateway_constants( 'MONERCHY_PAYMENT_GATEWAY_VERSION', '1.0.0' );
		define_monerchy_payment_gateway_constants( 'MONERCHY_PAYMENT_GATEWAY_FILE', __FILE__ );
		define_monerchy_payment_gateway_constants( 'MONERCHY_PAYMENT_GATEWAY_BASE', plugin_basename( MONERCHY_PAYMENT_GATEWAY_FILE));
		define_monerchy_payment_gateway_constants( 'MONERCHY_PAYMENT_GATEWAY_DIR_PATH', plugin_dir_path( MONERCHY_PAYMENT_GATEWAY_FILE) );
		define_monerchy_payment_gateway_constants( 'MONERCHY_PAYMENT_GATEWAY_DIR_URL', plugin_dir_url( MONERCHY_PAYMENT_GATEWAY_FILE ) );

}
function define_monerchy_payment_gateway_constants( $key, $value ) {
		if ( ! defined( $key ) ) {
			define( $key, $value );
		}
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-monerchy-payment-gateway-activator.php
 */
function activate_monerchy_payment_gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-monerchy-payment-gateway-activator.php';
	Monerchy_Payment_Gateway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-monerchy-payment-gateway-deactivator.php
 */
function deactivate_monerchy_payment_gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-monerchy-payment-gateway-deactivator.php';
	Monerchy_Payment_Gateway_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_monerchy_payment_gateway' );
register_deactivation_hook( __FILE__, 'deactivate_monerchy_payment_gateway' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-monerchy-payment-gateway.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_monerchy_payment_gateway() {
	define_monerchy_payment_gateway();
	$plugin = new Monerchy_Payment_Gateway();
	$plugin->run();

}

add_action( 'woocommerce_init' , 'run_monerchy_payment_gateway');

} else {
    add_action('admin_notices', 'monerchy_payment_gateway_install_error_notice');
    function monerchy_payment_gateway_install_error_notice(){
        global $current_screen;
        if($current_screen->parent_base == 'plugins'){
				echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'monerchy payment gateway to be installed and active. You can download %s here.', 'monerchy-payment-gateway' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
        }
    }
    $plugin = plugin_basename(__FILE__);
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if(is_plugin_active($plugin)){
          deactivate_plugins( $plugin);
    }
    if ( isset( $_GET['activate'] ) ){
		 unset( $_GET['activate'] );
	}
}