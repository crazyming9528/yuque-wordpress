<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.crazyming.com
 * @since             1.0.0
 * @package           Yuque_Wordpress
 *
 * @wordpress-plugin
 * Plugin Name:       YuQue WordPress
 * Plugin URI:        https://github.com/crazyming9528/yuque-wordpress
 * Description:       同步语雀文章到 WordPress
 * Version:           1.0.0
 * Author:            crazyming
 * Author URI:        https://github.com/crazyming9528
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       yuque-wordpress
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
define( 'YUQUE_WORDPRESS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-yuque-wordpress-activator.php
 */
function activate_yuque_wordpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-yuque-wordpress-activator.php';
	Yuque_Wordpress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-yuque-wordpress-deactivator.php
 */
function deactivate_yuque_wordpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-yuque-wordpress-deactivator.php';
	Yuque_Wordpress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_yuque_wordpress' );
register_deactivation_hook( __FILE__, 'deactivate_yuque_wordpress' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-yuque-wordpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_yuque_wordpress() {

	$plugin = new Yuque_Wordpress();
	$plugin->run();

}
run_yuque_wordpress();
