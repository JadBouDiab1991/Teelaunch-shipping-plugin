<?php

require_once plugin_dir_path(__FILE__) . 'class-teelaunch-shipping-api.php';

class Teelaunch_Shipping_Method {

    const WOO_TRUE = 'yes';
    const WOO_FALSE = 'no';
    const DEFAULT_ENABLED = self::WOO_TRUE;
    const DEFAULT_OVERRIDE = self::WOO_TRUE;
    const VERSION = '1.0';
    private $shipping_enabled;
    private $shipping_override;
    private $teelaunchApiClient;
    private $isTeelaunchPackage;
    private $teelaunch_shipping_cost; // Variable to store Teelaunch shipping cost

    public function __construct()
    {
        $this->id = 'teelaunch_shipping';
        $this->method_title = 'Teelaunch Shipping';
        $this->method_description = 'Calculate live shipping rates based on actual Teelaunch shipping costs.';
        $this->title = 'Teelaunch Shipping';
        $this->teelaunchApiClient = new Teelaunch_Shipping_API(self::VERSION);
        $this->shipping_enabled = self::DEFAULT_ENABLED;
        $this->shipping_override = self::DEFAULT_OVERRIDE;
        $this->init();
    }

    // public function init_form_fields()
    // {
    //     $this->form_fields = [
    //         'enabled' => [
    //             'title' => 'Enabled',
    //             'type' => 'checkbox',
    //             'label' => 'Enable Teelaunch Shipping Method plugin',
    //             'default' => self::DEFAULT_ENABLED,
    //         ],
    //         'override_defaults' => [
    //             'title' => 'Override',
    //             'type' => 'checkbox',
    //             'label' => 'Override standard WooCommerce shipping rates',
    //             'default' => self::DEFAULT_OVERRIDE,
    //         ],
    //     ];
    // }

    function init()
    {
        //$this->init_form_fields();
        //$this->init_settings();
        //add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_load_shipping_methods', [$this, 'load_shipping_methods']);
        add_filter('woocommerce_shipping_methods', [$this, 'add_teelaunch_shipping_method']);
        add_filter('woocommerce_cart_shipping_packages', [$this, 'calculate_shipping_rates']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'calculate_totals']);
        // Add JavaScript for triggering shipping calculation on address change
        add_action('wp_footer', [$this, 'trigger_shipping_calculation_js']);
    }

   public function add_teelaunch_shipping_method($methods)
    {
        return self::WOO_TRUE === $this->shipping_override && true === $this->isTeelaunchPackage ? [] : $methods;
    }

   public function load_shipping_methods($package)
    {
        $this->isTeelaunchPackage = false;

        // Retrieve current settings
        // $settings = get_option('woocommerce_teelaunch_shipping_settings', array());
        // $this->shipping_enabled = isset($settings['enabled']) ? $settings['enabled'] : self::DEFAULT_ENABLED;
        // $this->shipping_override = isset($settings['override_defaults']) ? $settings['override_defaults'] : self::DEFAULT_OVERRIDE;

        if (!$package) {
            WC()->shipping()->register_shipping_method($this);
            return;
        }

        if (self::WOO_FALSE === $this->shipping_enabled) {
            return;
        }

        if (isset($package['managed_by_teelaunch']) && true === $package['managed_by_teelaunch']) {
            if (self::WOO_TRUE === $this->shipping_override) {
                WC()->shipping()->unregister_shipping_methods();
            }
            $this->isTeelaunchPackage = true;
            WC()->shipping()->register_shipping_method($this);

            // Calculate shipping rates if both enabled and override are true
            // if (self::WOO_TRUE === $this->shipping_enabled && self::WOO_TRUE === $this->shipping_override) {
                $this->calculate_shipping_rates();
            // }
        }
    }

   public function calculate_shipping_rates($packages = [])
   {
    // Retrieve current settings
    $settings = get_option('woocommerce_teelaunch_shipping_settings', array());
    $shipping_enabled = isset($settings['enabled']) ? $settings['enabled'] : self::DEFAULT_ENABLED;
    $shipping_override = isset($settings['override_defaults']) ? $settings['override_defaults'] : self::DEFAULT_OVERRIDE;

    // Check if both enabled and override are active
    if ($shipping_enabled === self::WOO_TRUE && $shipping_override === self::WOO_TRUE) {
        $requestParameters = [
            'items' => [], // Initialize array to collect variation metadata
            'address' => [],
        ];
        foreach ($packages as $package) {
            // Collect skus, quantity, and metadata for each variation
            foreach ($package['contents'] as $variation) {
                /** @var WC_Product_Variation $productVariation */
                if ($variation && $variation['data']) {
                    $productVariation = $variation['data'];
                    // Extract SKU, quantity, and price
                    $sku = $productVariation->get_sku();
                    $quantity = $variation['quantity'];
                    $price = $productVariation->get_price();
                    // Extract additional metadata if needed (e.g., meta_data)
                    $metadata = $productVariation->get_meta_data();
                    // Add variation data to request parameters
                    $requestParameters['items'][] = [
                        'sku' => $sku,
                        'quantity' => $quantity,
                        'price' => $price, // Include price
                        'metadata' => $metadata, // Include metadata
                    ];
                }
            }
            // Collect shipping address
            $requestParameters['address'] = [
                'country' => $package['destination']['country'],
                'state' => $package['destination']['state'],
                'zip' => isset($package['destination']['postcode']) ? $package['destination']['postcode'] : null,
            ];
        }
        if (!count($requestParameters['address'])) {
            return $packages;
        }
        // Call the Teelaunch API with the collected parameters
        $teelaunchShippingRates = $this->teelaunchApiClient->get_shipping_rates($requestParameters);
        // Store the Teelaunch shipping cost
        if (isset($teelaunchShippingRates['totalShippingPrice'])) {
            $this->teelaunch_shipping_cost = $teelaunchShippingRates['totalShippingPrice'];
        }
        // Process the response and calculate shipping rates for each package
        return $packages;
    } else {
        // If either enabled or override is not active, return packages without calculation
        return $packages;
    }
}

    public function trigger_shipping_calculation_js()
    {
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                // Function to update address fields dynamically
                function updateAddressFields(country) {
                    var countryField = document.querySelectorAll('#billing_country, #shipping_country');
                    var stateField = document.querySelectorAll('#billing_state, #shipping_state');
                    var postcodeField = document.querySelectorAll('#billing_postcode, #shipping_postcode');
                    // Update country field
                    countryField.forEach(function (element) {
                        // Check if the option exists in the dropdown
                        if ([...element.options].some(option => option.value === country)) {
                            element.value = country;
                        } else {
                            // If the option doesn't exist, create a new option and select it
                            var newOption = document.createElement('option');
                            newOption.value = country;
                            newOption.text = country;
                            element.appendChild(newOption);
                            element.value = country;
                        }
                    });
                    // Clear state and postcode fields
                    stateField.forEach(function (element) {
                        element.value = '';
                    });
                    postcodeField.forEach(function (element) {
                        element.value = '';
                    });
                    // Trigger change event for WooCommerce compatibility
                    var event = new Event('change', { bubbles: true });
                    countryField.forEach(function (element) {
                        element.dispatchEvent(event);
                    });
                }
                // Trigger shipping calculation on country change
                var countrySelectors = document.querySelectorAll('select#billing_country, select#shipping_country');
                countrySelectors.forEach(function (selector) {
                    selector.addEventListener('change', function () {
                        var country = this.value;
                        updateAddressFields(country);
                        document.body.dispatchEvent(new Event('update_checkout'));
                    });
                });
            });
        </script>
        <?php
    }
    
    public function calculate_totals()
    {
        if ($this->teelaunch_shipping_cost) {
            // Add Teelaunch shipping cost to cart total
            WC()->cart->add_fee(__('Teelaunch Shipping', 'teelaunch_shipping'), $this->teelaunch_shipping_cost);
        }
    }
    
    
    public function admin_options() {
       //This method is intentionally left empty to prevent errors
    }

    public function has_settings() {
        return true;
    }
    
    // Add the supports method
    public function supports($feature)
    {
        // Check if the shipping method supports the given feature
        switch ($feature) {
            case 'shipping-zones':
                return true; // This indicates that the shipping method supports shipping zones.
            case 'instance-settings':
                return true; // This indicates that the shipping method supports instance settings.
            default:
                return false; // By default, the shipping method does not support the feature.
        }
    }

}
