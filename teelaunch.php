<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://jadiab1991.com
 * @since             1.0.0
 * @package           Teelaunch
 *
 * @wordpress-plugin
 * Plugin Name:       teelaunch
 * Plugin URI:        https://teelaunch.com
 * Description:       teelaunch shipping
 * Version:           1.0.0
 * Author:            jad bou diab
 * Author URI:        https://jadiab1991.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       teelaunch
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TEELAUNCH_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-teelaunch-activator.php
 */
function activate_teelaunch() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-teelaunch-activator.php';
	Teelaunch_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-teelaunch-deactivator.php
 */
function deactivate_teelaunch() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-teelaunch-deactivator.php';
	Teelaunch_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_teelaunch' );
register_deactivation_hook( __FILE__, 'deactivate_teelaunch' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-teelaunch.php';


// Include the class file
require_once plugin_dir_path(__FILE__) . 'includes/class-teelaunch-shipping-method.php';

// Define a function to initialize the shipping method
function initialize_teelaunch_shipping_method() {
    if (!class_exists('Teelaunch_Shipping_Method')) {
        return;
    }
    new Teelaunch_Shipping_Method();
}

// Hook the function to the plugins_loaded action
add_action('plugins_loaded', 'initialize_teelaunch_shipping_method');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_teelaunch() {

	$plugin = new Teelaunch();
	$plugin->run();

}
run_teelaunch();
