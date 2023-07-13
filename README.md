# WP Debug Master

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/wp-debug-master.svg)](https://wordpress.org/plugins/wp-debug-master/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/wp-debug-master.svg)](https://wordpress.org/plugins/wp-debug-master/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/r/wp-debug-master.svg)](https://wordpress.org/plugins/wp-debug-master/)
[![License](https://img.shields.io/github/license/anatoliiskidkin/wp-debug-master.svg)](https://github.com/anatoliiskidkin/wp-debug-master/blob/master/LICENSE)

> A powerful debugging tool for WordPress developers.

## Description

WP Debug Master is a comprehensive debugging plugin for WordPress developers. It provides an easy-to-use interface to enable and configure various debugging options, such as WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY, and SCRIPT_DEBUG. With WP Debug Master, you can quickly enable debugging on your WordPress site, log debug messages, control error display, and enable script debugging.

## Features

- Enable and disable WP_DEBUG mode.
- Log debug messages to a debug log file.
- Control the display of errors and warnings.
- Enable script debugging for enqueued scripts and styles.
- View and modify debugging settings from the WordPress admin dashboard.
- more features to come...

## Installation

1. Download the [latest release](https://github.com/anatoliiskidkin/wp-debug-master/releases/latest) of the plugin zip file.
2. Go to your WordPress admin dashboard.
3. Navigate to **Plugins > Add New**.
4. Click on the **Upload Plugin** button.
5. Select the plugin zip file you downloaded and click **Install Now**.
6. After installation, click **Activate Plugin** to activate WP Debug Master on your WordPress site.

## Usage

To enable debugging and configure the debug options:

1. Go to your WordPress admin dashboard.
2. Navigate to **WP Debug Master > Settings**.
3. If the Debug constants are missing in your wp-config.php file - click on the Generate Debug Constants button first.
4. Configure other options such as debug logging, error display, script debugging and Save Queries.
5. Click **Save Changes** to apply the settings.

## Settings

WP Debug Master provides the following settings:

- **Enable Debug**: Check this box to enable WP_DEBUG mode.
- **Enable Debug Logging**: Check this box to log debug messages to the debug log file.
- **Enable Debug Display**: Check this box to display errors and warnings.
- **Enable Script Debug**: Check this box to enable script debugging for enqueued scripts and styles.
- **Enable Save Queries**: Check this checkbox to Save database queries to an array for analysis. Disabled by default.

## Frequently Asked Questions

**Q: What is WP_DEBUG?**

A: WP_DEBUG is a constant in WordPress that enables or disables debugging mode. When enabled, it displays PHP errors, warnings, and notices.

**Q: Where can I find the debug log file?**

A: The debug log file is located at `wp-content/debug.log` in your WordPress installation directory.

**Q: How can I troubleshoot issues with my WordPress site using WP Debug Master?**

A: WP Debug Master provides a convenient way to enable debugging and log messages to the debug log file. You can check the log file for any errors or warnings that can help you diagnose and troubleshoot issues on your WordPress site.

## Changelog

### [1.0.0] - 2023-07-12
- Initial release of WP Debug Master.


## Contributing

Contributions are welcome! If you have any bug reports, feature requests, or pull requests, please submit them on the [GitHub repository](https://github.com/anatoliiskidkin/wp-debug-master).

Please make sure to read the [contribution guidelines](CONTRIBUTING.md) and adhere to the [code of conduct](CODE_OF_CONDUCT.md).

## License

This project is licensed under the GNU General Public License v2.0 or later. See the [LICENSE](LICENSE) file for details.

## Support

If you need help or have any questions, please create an issue on the [GitHub repository](https://github.com/anatoliiskidkin/wp-debug-master/issues).
