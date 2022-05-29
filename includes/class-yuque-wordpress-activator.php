<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/includes
 * @author     Your Name <email@example.com>
 */
class Yuque_Wordpress_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate($version)
    {

        self::createPostMapDataTable();
        self::createLogDataTable();
        self::updateOptions($version);

    }


    /**
     * 创建 wp post 和 yuque post 的映射表
     */
    private static function createPostMapDataTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_post_map';
        $sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (
		id bigint(9) NOT NULL AUTO_INCREMENT,
		post_id bigint(20) NOT NULL,
		yuque_post_id bigint(20) NOT NULL,
		yuque_post_url varchar(150),
		create_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		update_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		)";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

    }

    /**
     * 创建日志表
     */
    private static function createLogDataTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_log';
        $sql = "CREATE TABLE IF NOT EXISTS " . $tableName . " (
		id bigint(9) NOT NULL AUTO_INCREMENT,
		title varchar(100),
		step varchar(100),
		log_detail varchar(200),
		webhook_data_json longtext,
		user_data_json longtext,
		doc_data_json longtext,
	    trigger_at timestamp NOT NULL,
		create_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		)";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

    }

    /**
     * 更新或创建插件版本号
     * @param $version
     */
    private static function updateOptions($version)
    {
        // update_option()方法，在options表里如果不存在更新字段，则会创建该字段,存在则更新该字段
        update_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_version', $version);

        if (!get_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . "_config")) {
            add_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_config');
        };

//        if (!get_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . "_token")) {
//            add_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_token');
//        };
//        if (!get_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . "_access_token")) {
//            add_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_access_token');
//        };
//        if (!get_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . "_author")) {
//            add_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_author');
//        };
//        if (!get_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . "_parse_xml")) {
//            add_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_parse_xml');
//        };
//        if (!get_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . "_local_image")) {
//            add_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_local_image');
//        };
    }

}
