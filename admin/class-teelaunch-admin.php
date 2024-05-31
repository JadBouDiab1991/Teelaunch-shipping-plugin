<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jadiab1991.com
 * @since      1.0.0
 *
 * @package    Teelaunch
 * @subpackage Teelaunch/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Teelaunch
 * @subpackage Teelaunch/admin
 * @author     jad bou diab <jadiab1991@gmail.com>
 */
class Teelaunch_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Teelaunch_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Teelaunch_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/teelaunch-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Teelaunch_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Teelaunch_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/teelaunch-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function create_menu(){

		add_menu_page(__('Teelaunch Shipping', 'teelaunch-shipping'), __('Teelaunch Shipping', 'teelaunch-shipping'), 'manage_options', 'teelaunch-shipping', array($this, 'teelaunch_shipping'), 'dashicons-admin-site-alt', 67);

	}

	/**
     * Display the Teelaunch Shipping settings form.
     */
     public function teelaunch_shipping() {
		
        if (isset($_POST['submit'])) {
            // Update settings based on form submission
            $teelaunch_shipping_override = isset($_POST['teelaunch_shipping_override']) ? 'yes' : 'no';
            $teelaunch_shipping_enabled = isset($_POST['teelaunch_shipping_enabled']) ? 'yes' : 'no';

            // Retrieve current settings
            $settings = get_option('woocommerce_teelaunch_shipping_settings', array());

            // Update specific settings
            $settings['override_defaults'] = $teelaunch_shipping_override;
            $settings['enabled'] = $teelaunch_shipping_enabled;

            // Save updated settings
            update_option('woocommerce_teelaunch_shipping_settings', $settings);
            // Update other settings as needed
        }

        // Check if settings exist in the database
        $settings_exist = get_option('woocommerce_teelaunch_shipping_settings');

        // If settings don't exist, set default values
        if (!$settings_exist) {
            $teelaunch_shipping_override = 'yes';
            $teelaunch_shipping_enabled = 'yes';
        } else {
            // Retrieve settings
            $settings = get_option('woocommerce_teelaunch_shipping_settings', array());
            $teelaunch_shipping_override = isset($settings['override_defaults']) ? $settings['override_defaults'] : 'no';
            $teelaunch_shipping_enabled = isset($settings['enabled']) ? $settings['enabled'] : 'no';
        }

        // Display settings form with current settings
        ?>
        <div class="wrap">
            <h1><?php _e('Teelaunch Shipping Settings', 'teelaunch-shipping'); ?></h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enabled', 'teelaunch-shipping'); ?></th>
                        <td>
                            <label for="teelaunch_shipping_enabled">
                                <input type="checkbox" id="teelaunch_shipping_enabled" name="teelaunch_shipping_enabled" <?php checked($teelaunch_shipping_enabled, 'yes'); ?>>
                                <?php _e('Enable Teelaunch Shipping', 'teelaunch-shipping'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Override WooCommerce Shipping', 'teelaunch-shipping'); ?></th>
                        <td>
                            <label for="teelaunch_shipping_override">
                                <input type="checkbox" id="teelaunch_shipping_override" name="teelaunch_shipping_override" <?php checked($teelaunch_shipping_override, 'yes'); ?>>
                                <?php _e('Override WooCommerce shipping options', 'teelaunch-shipping'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Changes', 'teelaunch-shipping'), 'primary', 'submit', true); ?>
            </form>
        </div>
        <?php
    }


}