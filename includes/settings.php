<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Callback function to render the WP Debug Master general section.
function wp_debug_master_general_section_callback()
{
    echo '<p>' . esc_html__('General settings for WP Debug Master.', 'wp-debug-master') . '</p>';
}

// Callback function to render the WP Debug Master settings page.
function wp_debug_master_render_settings_page()
{
    // Check if the settings were saved successfully
    $settings_saved = isset($_GET['settings_saved']) && $_GET['settings_saved'] === 'true';

    // Display the success message if the settings were saved successfully
    if ($settings_saved) {
        echo '<div class="updated notice"><p>' . esc_html__('Settings saved successfully.', 'wp-debug-master') . '</p></div>';
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('WP Debug Master Settings', 'wp-debug-master') . '</h1>';

    // Check if the WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY, SCRIPT_DEBUG, and SAVEQUERIES constants are defined
    $debug_constants_defined = defined('WP_DEBUG') || defined('WP_DEBUG_LOG') || defined('WP_DEBUG_DISPLAY') || defined('SCRIPT_DEBUG') || defined('SAVEQUERIES');

    // If none of the debug constants are defined, display a message
    $debug_constants_defined ? __('DEBUG constants were found in your wp-config.php file', 'wp-debug-master') : __('No DEBUG constants are defined in the wp-config.php file.', 'wp-debug-master');

    // New button to generate debug constants
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="wp_debug_master_generate_debug_constants">';
    wp_nonce_field('wp_debug_master_generate_nonce', '_wpnonce');
    echo '<button type="submit" class="button-primary" id="generate-debug-constants">Generate Debug Constants</button>';
    echo '</form>';

    echo '<div class="custom-notice-info grey">Use this button only if the Debug constants were removed from the wp-config.php file either manually or by a third-party plugin and you want to regenerate them.<br>The constants will be created with the "false" values by default. You can then change it by adjusting the settings below.</div>';
    echo '<div class="custom-notice-info orange">Please keep in mind that modifying the wp-config.php file directly from a plugin can be a risky operation. However, we have implemented the necessary precautions to ensure a secure and safe process by using the plugin interface, following the current development standards.<br> We have taken steps to mitigate potential risks and provide you with a controlled environment for making these changes.</div>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    settings_fields('wp_debug_master_settings');
    do_settings_sections('wp-debug-master-settings');
    echo '<input type="hidden" name="action" value="wp_debug_master_handle_settings">';
    wp_nonce_field('wp_debug_master_update_nonce', '_wpnonce');
    submit_button();
    echo '</form>';
    echo '</div>';
}

// Callback function to handle form submission.
function wp_debug_master_handle_settings_form()
{
    if (isset($_POST['action'])) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
            // Check if the current user has the 'manage_options' capability (typically administrators)
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            $enable_debug = isset($_POST['wp_debug_master_enable_debug']) ? 'enable' : 'disable';
            $enable_debug_logging = isset($_POST['wp_debug_master_enable_debug_logging']) ? 'enable' : 'disable';
            $enable_debug_display = isset($_POST['wp_debug_master_enable_debug_display']) ? 'enable' : 'disable';
            $enable_script_debug = isset($_POST['wp_debug_master_enable_script_debug']) ? 'enable' : 'disable';
            $enable_save_queries = isset($_POST['wp_debug_master_enable_save_queries']) ? 'enable' : 'disable';

            // Update the option values.
            update_option('wp_debug_master_enable_debug', $enable_debug);
            update_option('wp_debug_master_debug_log', $enable_debug_logging);
            update_option('wp_debug_master_debug_display', $enable_debug_display);
            update_option('wp_debug_master_script_debug', $enable_script_debug);
            update_option('wp_debug_master_enable_save_queries', $enable_save_queries);

            // Update the constants in wp-config.php
            $config_path = ABSPATH . 'wp-config.php';

            // Read the content of wp-config.php
            $config_content = file_get_contents($config_path);

            // Update the SAVEQUERIES constant
            $config_content = preg_replace("/define\('SAVEQUERIES',\s*.*?\);/i", "define('SAVEQUERIES', " . ($enable_save_queries === 'enable' ? 'true' : 'false') . ");", $config_content, -1, $count_queries);

            // Update the WP_DEBUG constant
            $config_content = preg_replace("/define\('WP_DEBUG',\s*.*?\);/i", "define('WP_DEBUG', " . ($enable_debug === 'enable' ? 'true' : 'false') . ");", $config_content, -1, $count_debug);

            // Update the WP_DEBUG_LOG constant
            $config_content = preg_replace("/define\('WP_DEBUG_LOG',\s*.*?\);/i", "define('WP_DEBUG_LOG', " . ($enable_debug_logging === 'enable' ? 'true' : 'false') . ");", $config_content, -1, $count_log);

            // Update the WP_DEBUG_DISPLAY constant
            $config_content = preg_replace("/define\('WP_DEBUG_DISPLAY',\s*.*?\);/i", "define('WP_DEBUG_DISPLAY', " . ($enable_debug_display === 'enable' ? 'true' : 'false') . ");", $config_content, -1, $count_display);

            // Update the SCRIPT_DEBUG constant
            $config_content = preg_replace("/define\('SCRIPT_DEBUG',\s*.*?\);/i", "define('SCRIPT_DEBUG', " . ($enable_script_debug === 'enable' ? 'true' : 'false') . ");", $config_content, -1, $count_script_debug);

            // Write the updated content back to wp-config.php
            $write_result = file_put_contents($config_path, $config_content);

            if ($write_result !== false) {
                error_log('WP_DEBUG constants updated successfully.');
                error_log('Number of replacements for WP_DEBUG: ' . $count_debug);
                error_log('Number of replacements for WP_DEBUG_LOG: ' . $count_log);
                error_log('Number of replacements for WP_DEBUG_DISPLAY: ' . $count_display);
                error_log('Number of replacements for SCRIPT_DEBUG: ' . $count_script_debug);
                error_log('Number of replacements for SAVEQUERIES: ' . $count_queries);
            } else {
                error_log('Failed to update constants in wp-config.php.');
            }

            // Redirect back to the settings page after form submission.
            wp_redirect(admin_url('admin.php?page=wp-debug-master-settings&settings_saved=true'));
            exit;
        }
    }
}

// Register the form submission handler.
add_action('admin_post_wp_debug_master_handle_settings', 'wp_debug_master_handle_settings_form');

// Register the WP Debug Master settings.
add_action('admin_init', 'wp_debug_master_register_settings');

// Callback function to register the WP Debug Master settings.
function wp_debug_master_register_settings()
{
    register_setting(
        'wp_debug_master_settings',
        'wp_debug_master_enable_debug',
        array(
            'type' => 'string',
            'default' => 'disable',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    register_setting(
        'wp_debug_master_settings',
        'wp_debug_master_debug_log',
        array(
            'type' => 'string',
            'default' => 'disable',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    register_setting(
        'wp_debug_master_settings',
        'wp_debug_master_debug_display',
        array(
            'type' => 'string',
            'default' => 'disable',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    register_setting(
        'wp_debug_master_settings',
        'wp_debug_master_script_debug',
        array(
            'type' => 'string',
            'default' => 'disable',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    register_setting(
        'wp_debug_master_settings',
        'wp_debug_master_enable_save_queries',
        array(
            'type' => 'string',
            'default' => 'disable',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    // Register the settings sections.
    add_settings_section(
        'wp_debug_master_general_section',
        __('General Settings', 'wp-debug-master'),
        'wp_debug_master_general_section_callback',
        'wp-debug-master-settings'
    );

    // Add the settings fields to the sections.
    add_settings_field(
        'wp_debug_master_enable_debug',
        __('Enable WP_DEBUG mode', 'wp-debug-master'),
        'wp_debug_master_enable_debug_callback',
        'wp-debug-master-settings',
        'wp_debug_master_general_section'
    );
    add_settings_field(
        'wp_debug_master_enable_debug_logging',
        __('Enable Debug Logging<br><p>Enabling logging to /wp-content/debug.log file</p>', 'wp-debug-master'),
        'wp_debug_master_enable_debug_logging_callback',
        'wp-debug-master-settings',
        'wp_debug_master_general_section'
    );
    add_settings_field(
        'wp_debug_master_enable_debug_display',
        __('Enable Debug Display <br> <p>Enable site errors and warnings publically visible. Disabled by default (recommended).</p>', 'wp-debug-master'),
        'wp_debug_master_enable_debug_display_callback',
        'wp-debug-master-settings',
        'wp_debug_master_general_section'
    );
    add_settings_field(
        'wp_debug_master_enable_script_debug',
        __('Enable Script Debug <br><p>Use dev versions of core JS and CSS files (only needed if you are modifying these core files)</p>', 'wp-debug-master'),
        'wp_debug_master_enable_script_debug_callback',
        'wp-debug-master-settings',
        'wp_debug_master_general_section'
    );
    add_settings_field(
        'wp_debug_master_enable_save_queries',
        __('Enable Save Queries<br><p>Save database queries to an array for analysis. Disabled by default (recommended).</p>', 'wp-debug-master'),
        'wp_debug_master_enable_save_queries_callback',
        'wp-debug-master-settings',
        'wp_debug_master_general_section'
    );

    // Add additional settings sections and fields here.
}

// Callback function to render the Enable Debug field.
function wp_debug_master_enable_debug_callback()
{
    $enable_debug = get_option('wp_debug_master_enable_debug', 'disable');
    echo '<label><input type="checkbox" name="wp_debug_master_enable_debug" value="1" ' . checked($enable_debug, 'enable', false) . '> ' . __('Enable Debug', 'wp-debug-master') . '</label>';
}

// Callback function to render the Debug Logging field.
function wp_debug_master_enable_debug_logging_callback()
{
    $enable_debug_logging = get_option('wp_debug_master_debug_log', 'disable');
    echo '<label><input type="checkbox" name="wp_debug_master_enable_debug_logging" value="1" ' . checked($enable_debug_logging, 'enable', false) . '> ' . __('Enable Debug Logging', 'wp-debug-master') . '</label>';
}

// Callback function to render the Debug Display field.
function wp_debug_master_enable_debug_display_callback()
{
    $enable_debug_display = get_option('wp_debug_master_debug_display', 'disable');
    echo '<label><input type="checkbox" name="wp_debug_master_enable_debug_display" value="1" ' . checked($enable_debug_display, 'enable', false) . '> ' . __('Enable Debug Display', 'wp-debug-master') . '</label>';
}

// Callback function to render the Script Debug field.
function wp_debug_master_enable_script_debug_callback()
{
    $enable_script_debug = get_option('wp_debug_master_script_debug', 'disable');
    echo '<label><input type="checkbox" name="wp_debug_master_enable_script_debug" value="1" ' . checked($enable_script_debug, 'enable', false) . '> ' . __('Enable Script Debug', 'wp-debug-master') . '</label>';
}

// Callback function to render the Save Queries field.
function wp_debug_master_enable_save_queries_callback()
{
    $enable_save_queries = get_option('wp_debug_master_enable_save_queries', 'disable');
    echo '<label><input type="checkbox" name="wp_debug_master_enable_save_queries" value="1" ' . checked($enable_save_queries, 'enable', false) . '> ' . __('Enable Save Queries', 'wp-debug-master') . '</label>';
}