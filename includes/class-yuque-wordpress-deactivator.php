<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/includes
 * @author     Your Name <email@example.com>
 */
class Yuque_Wordpress_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		self::deletePluginVersion();

	}
	private  static  function deletePluginVersion(){
		delete_option('yuque_wordpress_version');
	}

}
