<?php
/*
Plugin Name: Debug Master
Plugin URI: https://debugdrill.com/wp-debug-master
Description: A plugin to enable Debug functionality on WordPRess sites and provide full overview and control on the debug options inside the WP Dashboard.
Version: 1.0.0
Author: DebugDrill
Author URI: https://debugdrill.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-debug-master
Domain Path: /languages
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function wp_debug_master_load_files() {
    // Include the necessary files.
    require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
    require_once plugin_dir_path(__FILE__) . 'includes/debug-overview.php';
    require_once plugin_dir_path(__FILE__) . 'includes/debug-helper.php';
}
add_action('plugins_loaded', 'wp_debug_master_load_files');

// Register the AJAX action
add_action('wp_ajax_wp_debug_master_load_log_content', 'wp_debug_master_load_log_content');
add_action('wp_ajax_nopriv_wp_debug_master_load_log_content', 'wp_debug_master_load_log_content');

// Enqueue scripts and styles
function wp_debug_master_enqueue_scripts() {
    // Get the plugin directory URL
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_script('wp-debug-master', $plugin_url . 'includes/js/wp-debug-master.js', array('jquery'), '1.0', true);

    // Localize the script to pass the AJAX URL
    wp_localize_script('wp-debug-master', 'wpDebugMaster', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('wp_debug_master_nonce')
    ));

    // Enqueue the custom CSS file
    wp_enqueue_style('wp-debug-master', $plugin_url . 'includes/css/wp-debug-master.css', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'wp_debug_master_enqueue_scripts');

// Callback function to add the WP Debug Master menu and sub-menus.
function wp_debug_master_add_menu_items() {
    $parent_slug = 'wp-debug-master';
    $capability = 'manage_options';

    add_menu_page(
        __('WP Debug Master', 'wp-debug-master'),
        __('WP Debug Master', 'wp-debug-master'),
        $capability,
        $parent_slug,
        'wp_debug_master_render_overview_page', // Set the Overview page callback function here
        'dashicons-warning',
        30
    );

    add_submenu_page(
        $parent_slug,
        __('Debug Overview', 'wp-debug-master'),
        __('Debug Overview', 'wp-debug-master'),
        $capability,
        $parent_slug,
        'wp_debug_master_render_overview_page'
    );

    add_submenu_page(
        $parent_slug,
        __('Settings', 'wp-debug-master'),
        __('Settings', 'wp-debug-master'),
        $capability,
        'wp-debug-master-settings',
        'wp_debug_master_render_settings_page'
    );
}
add_action('admin_menu', 'wp_debug_master_add_menu_items');

// Initialize the plugin.
function wp_debug_master_init() {
    // Add initialization code here.
}
add_action('plugins_loaded', 'wp_debug_master_init');

// WordPress notices
function wp_debug_master_admin_notices() {
    if(get_transient('wp_debug_master_no_constants_notice')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('No debug constants were added. They already exist in the wp-config file.', 'wp-debug-master'); ?></p>
        </div>
        <?php
        delete_transient('wp_debug_master_no_constants_notice');
    }

    if(get_transient('wp_debug_master_constants_generated_successfully_notice')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Debug constants generated successfully.', 'wp-debug-master'); ?></p>
        </div>
        <?php
        delete_transient('wp_debug_master_constants_generated_successfully_notice');
    }
}
add_action('admin_notices', 'wp_debug_master_admin_notices');
