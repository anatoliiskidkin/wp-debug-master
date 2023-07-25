<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define the wp_debug_master_load_log_content function
function wp_debug_master_load_log_content() {
    check_ajax_referer('wp_debug_master_nonce', 'nonce');
    if(!current_user_can('manage_options')){
        wp_die(__('You do not have sufficient permissions to access this page.', 'wp-debug-master'));
    }
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'newest';

    $debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
    $logFile = WP_CONTENT_DIR . '/debug.log';

    $logContent = '';

    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);

        if (!empty($search)) {
            $lines = explode(PHP_EOL, $logContent);
            $filteredLines = array_filter($lines, function($line) use ($search) {
                return stripos($line, $search) !== false;
            });
            $logContent = implode(PHP_EOL, $filteredLines);
        }

        $lines = explode(PHP_EOL, $logContent);

        if ($sort === 'newest') {
            $lines = array_reverse($lines);
        }

        // Add line numbers to each line
        $lines = array_map(function($key, $line) {
            return ($key+1) . ': ' . $line;
        }, array_keys($lines), $lines);

        // Reassemble the log content
        $logContent = implode(PHP_EOL, $lines);

        $response = array(
            'content' => $logContent,
            'totalLines' => count($lines),
        );

        wp_send_json($response);
    } elseif (!$debug_enabled) {
        http_response_code(404);
        wp_send_json(array('content' => 'Debug is currently disabled. Log file exists but debug is disabled.', 'totalLines' => 1));
    } else {
        http_response_code(500);
        wp_send_json(array('content' => 'Debug file is missing.', 'totalLines' => 0));
    }
    wp_die();
}


// Callback function to render the Debug Overview page.
function wp_debug_master_render_overview_page() {
    if(!current_user_can('manage_options')){
        wp_die(__('You do not have sufficient permissions to access this page.', 'wp-debug-master'));
    }
    // Check if the log was cleared successfully
    $log_cleared = isset($_GET['log_cleared']) && $_GET['log_cleared'] === 'true';

    // Display the success message if the log was cleared successfully
    if ($log_cleared) {
        echo '<div class="updated notice"><p>' . esc_html__('Log file cleared successfully.', 'wp-debug-master') . '</p></div>';
    }
    // Check if the Debug function is  (old way).
    // $debug_enabled = get_option('wp_debug_master_enable_debug', 'enable');
    
    // Check if the DEBUG constants are defined and enabled
    $debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
    $debug_log_enabled = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
    $debug_display_enabled = defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY;
    $debug_script_enabled = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;
    $debug_savequeries_enabled = defined('SAVEQUERIES') && SAVEQUERIES;

    $status_message = $debug_enabled ? '' : '<p>' . __('Debug is currently disabled but existing log records are still displayed.', 'wp-debug-master') . '</p>';

    // Get the status of other debug parameters from the settings (old way)
    //$debug_status = get_option('wp_debug_master_enable_debug', 'disable') === 'enable' ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master');
    //$debug_log = get_option('wp_debug_master_debug_log', 'disable');
    //$debug_display = get_option('wp_debug_master_debug_display', 'disable');
    //$script_debug = get_option('wp_debug_master_script_debug', 'disable');
    //$savequeries_debug = get_option('wp_debug_master_enable_save_queries', 'disable');

    // Get the status of other debug parameters from the settings
    $debug_status = $debug_enabled ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master');
    $debug_log = $debug_log_enabled ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master');
    $debug_display = $debug_display_enabled ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master');
    $script_debug = $debug_script_enabled ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master');
    $savequeries_debug = $debug_savequeries_enabled ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master');

    // Set the color classes based on the status
    $debug_status_class = $debug_status === 'Enabled' ? 'wp-debug-master-status-enabled' : 'wp-debug-master-status-disabled';
    $debug_log_class = $debug_log === 'enable' ? 'wp-debug-master-status-enabled' : 'wp-debug-master-status-disabled';
    $debug_display_class = $debug_display === 'enable' ? 'wp-debug-master-status-enabled' : 'wp-debug-master-status-disabled';
    $script_debug_class = $script_debug === 'enable' ? 'wp-debug-master-status-enabled' : 'wp-debug-master-status-disabled';
    $savequeries_debug_class = $savequeries_debug === 'enable' ? 'wp-debug-master-status-enabled' : 'wp-debug-master-status-disabled';

    // Start the output buffer.
    ob_start();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('WP Debug Overview', 'wp-debug-master') . '</h1>';
    // Display the status of debug parameters
    echo '<h3>' . esc_html__('Debug Parameters Status:', 'wp-debug-master') . '</h3>';
    echo '<ul>';
    echo '<li>' . __('WP Debug:', 'wp-debug-master') . ' <span class="' . $debug_status_class . '">' . $debug_status . '</span></li>';
    echo '<li>' . __('Debug Log:', 'wp-debug-master') . ' <span class="' . $debug_log_class . '">' . ($debug_log === 'enable' ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master')) . '</span></li>';
    echo '<li>' . __('Debug Display:', 'wp-debug-master') . ' <span class="' . $debug_display_class . '">' . ($debug_display === 'enable' ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master')) . '</span></li>';
    echo '<li>' . __('Script Debug:', 'wp-debug-master') . ' <span class="' . $script_debug_class . '">' . ($script_debug === 'enable' ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master')) . '</span></li>';
    echo '<li>' . __('Save Queries:', 'wp-debug-master') . ' <span class="' . $savequeries_debug_class . '">' . ($savequeries_debug === 'enable' ? __('Enabled', 'wp-debug-master') : __('Disabled', 'wp-debug-master')) . '</span></li>';
    echo '</ul>';
    echo $status_message;

    // Implement search functionality.
    echo '<h3>' . esc_html__('Search in logs:', 'wp-debug-master') . '</h3>';
    echo '<input type="text" id="wp-debug-master-search" placeholder="' . esc_attr__('Search log...', 'wp-debug-master') . '">';

    // Add the control for changing the sort order
    echo '<h3>' . esc_html__('Sort Order:', 'wp-debug-master') . '</h3>';
    echo '<select id="wp-debug-master-sort-order">';
    echo '<option value="newest">' . esc_html__('Newest First', 'wp-debug-master') . '</option>';
    echo '<option value="oldest">' . esc_html__('Oldest First', 'wp-debug-master') . '</option>';
    echo '</select>';

    // Display the content of the debug log file.
    echo '<h3>' . esc_html__('Debug Log:', 'wp-debug-master') . '</h3>';
    echo '<div id="wp-debug-master-log-content-wrap" style="border: 1px solid #ccc; padding: 10px; max-height: 300px; overflow-y: scroll;">';
    echo '<pre id="wp-debug-master-log-content" style="white-space: pre-wrap; word-wrap: break-word;"></pre>';
    echo '</div>';

    // Add a "Clear Log" button and a "Download Log File" button.
    echo '<div class="wp-debug-master-button-group">';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="wp_debug_master_clear_log">';
    wp_nonce_field('wp_debug_master_clear_log_action', 'wp_debug_master_clear_log_nonce');
    echo '<button type="submit" class="button">' . esc_html__('Clear Log', 'wp-debug-master') . '</button>';
    echo '</form>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php?action=wp_debug_master_download_log')) . '">';
    wp_nonce_field('wp_debug_master_download_log_action', 'wp_debug_master_download_log_nonce');
    echo '<button type="submit" class="button button-primary">' . esc_html__('Download Log File', 'wp-debug-master') . '</button>';
    echo '</form>';

    echo '</div>'; // End of .wrap

    // End the output buffer and capture the page content.
    $content = ob_get_clean();

    echo $content;
}

// Callback function to handle the "Clear Log" button action.
function wp_debug_master_clear_log_action() {
    check_admin_referer('wp_debug_master_clear_log_action', 'wp_debug_master_clear_log_nonce');
    if(!current_user_can('manage_options')){
        wp_die(__('You do not have sufficient permissions to access this page.', 'wp-debug-master'));
    }
    $debug_log_path = WP_CONTENT_DIR . '/debug.log';

    if (file_exists($debug_log_path)) {
        // Attempt to delete the log file
        if (unlink($debug_log_path)) {
            // Log file cleared successfully
            error_log('Log file cleared successfully.');

            // Set a query parameter indicating log clearance success
            $redirect_url = add_query_arg('log_cleared', 'true', admin_url('admin.php?page=wp-debug-master'));

            // Redirect to the Debug Overview page with the success message
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            // Failed to delete the log file
            error_log('Failed to clear the log file.');
            wp_die(__('Failed to clear the log file.', 'wp-debug-master'));
        }
    } else {
        // Log file doesn't exist
        error_log('Log file does not exist.');
        wp_die(__('Log file does not exist.', 'wp-debug-master'));
    }

    // Redirect to the Debug Overview page without the success message
    wp_safe_redirect(admin_url('admin.php?page=wp-debug-master'));
    exit;
}

// Callback function to handle the "Download Log File" button action.
function wp_debug_master_download_log_action() {
    check_admin_referer('wp_debug_master_download_log_action', 'wp_debug_master_download_log_nonce');
    if(!current_user_can('manage_options')){
        wp_die(__('You do not have sufficient permissions to access this page.', 'wp-debug-master'));
    }
    $debug_log_path = WP_CONTENT_DIR . '/debug.log';

    if (file_exists($debug_log_path)) {
        $file_name = 'debug.log';

        header('Content-Type: application/octet-stream');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($debug_log_path));
        header('Pragma: public');
        header('Expires: 0');
        
        readfile($debug_log_path);
        exit;
    }
    else{
        wp_die(__('Log file does not exist.', 'wp-debug-master'));
    }
}

// Register the form submission and button action handlers.
add_action('admin_post_wp_debug_master_clear_log', 'wp_debug_master_clear_log_action');
add_action('admin_post_wp_debug_master_download_log', 'wp_debug_master_download_log_action');