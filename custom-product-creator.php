<?php
/**
 * Plugin Name: Custom WooCommerce Product Creator
 * Description: Allows creation of custom products with additional fields
 * Version: 1.0.0
 * Author: Aqsa Mumtaz
 * Text Domain: custom-product-creator
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CUSTOM_PRODUCT_CREATOR_VERSION', '1.0.0');
define('CUSTOM_PRODUCT_CREATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CUSTOM_PRODUCT_CREATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Plugin main class
class Custom_Product_Creator {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Check if WooCommerce is active
        if ($this->check_woocommerce()) {
            $this->init_hooks();
        } else {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
        }
    }

    private function check_woocommerce() {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        return is_plugin_active('woocommerce/woocommerce.php');
    }

    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Custom Product Creator requires WooCommerce to be installed and activated.', 'custom-product-creator'); ?></p>
        </div>
        <?php
    }

    private function init_hooks() {
        // Plugin activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Add menu item
        add_action('admin_menu', array($this, 'add_menu_page'));
        
        // Register scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'register_assets'));
        
        // Ajax handlers
        add_action('wp_ajax_create_custom_product', array($this, 'create_custom_product'));
    }

    public function activate() {
        // Activation code here
        flush_rewrite_rules();
    }

    public function init() {
        load_plugin_textdomain('custom-product-creator', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_menu_page() {
        add_menu_page(
            __('Custom Product Creator', 'custom-product-creator'),
            __('Product Creator', 'custom-product-creator'),
            'manage_woocommerce',
            'custom-product-creator',
            array($this, 'render_creator_page'),
            'dashicons-cart',
            56
        );
    }

    public function register_assets($hook) {
        // Only load on plugin page
        if ($hook != 'toplevel_page_custom-product-creator') {
            return;
        }

        wp_enqueue_style(
            'custom-product-creator-css',
            CUSTOM_PRODUCT_CREATOR_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CUSTOM_PRODUCT_CREATOR_VERSION
        );

        wp_enqueue_script(
            'custom-product-creator-js',
            CUSTOM_PRODUCT_CREATOR_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            CUSTOM_PRODUCT_CREATOR_VERSION,
            true
        );

        wp_localize_script('custom-product-creator-js', 'customProductCreator', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom-product-creator-nonce')
        ));
    }

    public function render_creator_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include_once CUSTOM_PRODUCT_CREATOR_PLUGIN_DIR . 'templates/creator-form.php';
    }

    public function create_custom_product() {
        check_ajax_referer('custom-product-creator-nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $product_data = array(
            'post_title' => sanitize_text_field($_POST['title']),
            'post_content' => wp_kses_post($_POST['description']),
            'post_status' => 'publish',
            'post_type' => 'product'
        );

        $product_id = wp_insert_post($product_data);

        if ($product_id) {
            // Set product type
            wp_set_object_terms($product_id, 'simple', 'product_type');

            // Set product price
            update_post_meta($product_id, '_regular_price', sanitize_text_field($_POST['price']));
            update_post_meta($product_id, '_price', sanitize_text_field($_POST['price']));
            
            // Set product visibility
            update_post_meta($product_id, '_visibility', 'visible');
            
            // Set virtual status
            update_post_meta($product_id, '_virtual', 'no');
            
            // Set product SKU
            update_post_meta($product_id, '_sku', sanitize_text_field($_POST['sku']));

            // Set stock status
            update_post_meta($product_id, '_stock_status', 'instock');
            update_post_meta($product_id, '_manage_stock', 'yes');
            update_post_meta($product_id, '_stock', intval($_POST['stock']));

            wp_send_json_success(array(
                'message' => __('Product created successfully', 'custom-product-creator'),
                'product_id' => $product_id,
                'product_url' => get_edit_post_link($product_id, 'url')
            ));
        } else {
            wp_send_json_error(__('Failed to create product', 'custom-product-creator'));
        }

        wp_die();
    }
}

// Initialize plugin
function custom_product_creator_init() {
    return Custom_Product_Creator::get_instance();
}

add_action('plugins_loaded', 'custom_product_creator_init');