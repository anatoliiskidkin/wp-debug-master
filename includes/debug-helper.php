<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Update the enable debug option.
 *
 * @param mixed $old_value The old option value.
 * @param mixed $value     The new option value.
 */
function wp_debug_master_update_enable_debug($old_value, $value)
{
    error_log('Old value: ' . $old_value);  // Log the old value for debugging
    error_log('New value: ' . $value);      // Log the new value for debugging

    if ($value === 'enable') {
        update_site_option('wp_debug_master_debug_mode', true);
    } else {
        update_site_option('wp_debug_master_debug_mode', false);
    }

    $debug_mode = get_site_option('wp_debug_master_debug_mode', false);

    if (!defined('WP_DEBUG')) {
        define('WP_DEBUG', $debug_mode);
    } else {
        error_log('WP_DEBUG constant is already defined.');
    }
}
add_action('update_option_wp_debug_master_enable_debug', 'wp_debug_master_update_enable_debug', 10, 2);

/**
 * Set the debug mode based on the option value.
 */
function wp_debug_master_set_debug_mode()
{
    $debug_mode = get_option('wp_debug_master_debug_mode', false);

    if (!defined('WP_DEBUG')) {
        define('WP_DEBUG', $debug_mode);
    }

    if (!defined('WP_DEBUG_LOG')) {
        define('WP_DEBUG_LOG', $debug_mode);
    }

    if (!defined('WP_DEBUG_DISPLAY')) {
        define('WP_DEBUG_DISPLAY', $debug_mode);
    }

    if (!defined('SCRIPT_DEBUG')) {
        define('SCRIPT_DEBUG', $debug_mode);
    }

    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', $debug_mode);
    }
}

add_action('wp_loaded', 'wp_debug_master_set_debug_mode');

/**
 * Print WP debug status in the console log.
 */
function wp_debug_master_print_debug_status()
{
    $wp_debug_status = defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled';
    $wp_debug_log_status = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Enabled' : 'Disabled';
    $wp_debug_display_status = defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Enabled' : 'Disabled';
    $script_debug_status = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Enabled' : 'Disabled';
    $save_queries_status = defined('SAVEQUERIES') && SAVEQUERIES ? 'Enabled' : 'Disabled';

    echo "<script>console.log('WP Debug Status: $wp_debug_status');";
    echo "console.log('WP Debug Log Status: $wp_debug_log_status');";
    echo "console.log('WP Debug Display Status: $wp_debug_display_status');";
    echo "console.log('Script Debug Status: $script_debug_status');";
    echo "console.log('Save Queries Status: $save_queries_status');</script>";
}
add_action('admin_enqueue_scripts', 'wp_debug_master_print_debug_status');

/**
 * Update the debug constants in the wp-config.php file.
 *
 * @param mixed $old_value The old option value.
 * @param mixed $value     The new option value.
 *
 * @return string|void Error message if updating fails.
 */
function wp_debug_master_update_debug_constants($old_value, $value)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!check_admin_referer('wp_debug_master_update_nonce', '_wpnonce')) {
        return;
    }
    $wp_config_path = ABSPATH . 'wp-config.php';
    if (file_exists($wp_config_path) && is_writable($wp_config_path)) {
        $config_lines = file($wp_config_path);

        $debug_constants = array(
            'WP_DEBUG' => isset($_POST['wp_debug_master_enable_debug']) ? 'true' : 'false',
            'WP_DEBUG_LOG' => isset($_POST['wp_debug_master_enable_debug_logging']) ? 'true' : 'false',
            'WP_DEBUG_DISPLAY' => isset($_POST['wp_debug_master_enable_debug_display']) ? 'true' : 'false',
            'SCRIPT_DEBUG' => isset($_POST['wp_debug_master_enable_script_debug']) ? 'true' : 'false',
            'SAVEQUERIES' => isset($_POST['wp_debug_master_enable_save_queries']) ? 'true' : 'false',
        );

        $constants_exist = array();

        // Check if constants exist and update them
        foreach ($debug_constants as $constant => $constant_value) {
            $found = false;
            foreach ($config_lines as $index => $line) {
                if (strpos($line, "define('$constant'") !== false) {
                    $config_lines[$index] = "define('$constant', $constant_value);" . PHP_EOL;
                    $found = true;
                }
            }
            $constants_exist[$constant] = $found;
        }

        if (in_array(false, $constants_exist)) {
            return "Debug Constants are missing in the wp-config.php file. To generate them click on the \"Generate Debug Constants\" button.";
        }

        $new_config_content = implode('', $config_lines);

        $write_result = file_put_contents($wp_config_path, $new_config_content);

        if ($write_result !== false) {
            error_log('Debug constants updated successfully.');
        } else {
            error_log('Failed to update debug constants.');
        }
    }
}

add_action('update_option_wp_debug_master_enable_debug', 'wp_debug_master_update_debug_constants', 10, 2);

/**
 * Generate the debug constants in the wp-config.php file.
 */
function wp_debug_master_generate_debug_constants()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!check_admin_referer('wp_debug_master_generate_nonce', '_wpnonce')) {
        return;
    }
    $wp_config_path = ABSPATH . 'wp-config.php';
    if (file_exists($wp_config_path) && is_writable($wp_config_path)) {
        $config_lines = file($wp_config_path);

        $debug_constants = array(
            'WP_DEBUG' => 'false',
            'WP_DEBUG_LOG' => 'false',
            'WP_DEBUG_DISPLAY' => 'false',
            'SCRIPT_DEBUG' => 'false',
            'SAVEQUERIES' => 'false',
        );

        $constant_lines = array();
        $added_constants = false;
        foreach ($debug_constants as $constant => $constant_value) {
            $constant_exists = false;
            foreach ($config_lines as $line) {
                if (preg_match("/define\s*\(\s*['\"]" . $constant . "['\"]\s*,\s*.+\s*\)/", $line)) {
                    $constant_exists = true;
                    break;
                }
            }

            if (!$constant_exists) {
                $constant_line = "define('$constant', $constant_value);";
                $constant_lines[] = $constant_line;
                $added_constants = true;
            }
        }

        if (!$added_constants) {
            set_transient('wp_debug_master_no_constants_notice', true, 5);
        } else {
            $insert_index = count($config_lines) - 1;
            while ($insert_index >= 0 && strpos($config_lines[$insert_index], "/* That's all, stop editing! Happy publishing. */") === false) {
                $insert_index--;
            }

            // Add a comment line before the constants
            array_splice($config_lines, $insert_index - 1, 0, "/* WP Debug Master Constants */" . PHP_EOL);

            foreach (array_reverse($constant_lines) as $constant_line) {
                array_splice($config_lines, $insert_index, 0, $constant_line . PHP_EOL);
            }

            $new_config_content = implode('', $config_lines);

            $write_result = file_put_contents($wp_config_path, $new_config_content);

            if ($write_result !== false) {
                error_log('Debug constants generated successfully.');
                set_transient('wp_debug_master_constants_generated_successfully_notice', true, 5);
            } else {
                error_log('Failed to generate debug constants.');
            }
        }
    }
    wp_redirect(admin_url('admin.php?page=wp-debug-master-settings'));
    exit;
}
add_action('admin_post_wp_debug_master_generate_debug_constants', 'wp_debug_master_generate_debug_constants');

if (isset($_POST['generate_debug_constants'])) {
    wp_debug_master_generate_debug_constants();
}
