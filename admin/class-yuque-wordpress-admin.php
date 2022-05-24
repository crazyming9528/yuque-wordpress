<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/admin
 * @author     Your Name <email@example.com>
 */
class Yuque_Wordpress_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $yuque_wordpress The ID of this plugin.
     */
    private $yuque_wordpress;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $yuque_wordpress The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($yuque_wordpress, $version)
    {

        $this->yuque_wordpress = $yuque_wordpress;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Yuque_Wordpress_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Yuque_Wordpress_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // 默认的
        wp_enqueue_style($this->yuque_wordpress, plugin_dir_url(__FILE__) . 'css/yuque-wordpress-admin.css', array(), $this->version, 'all');
        // element ui
        wp_enqueue_style($this->yuque_wordpress . "_element-ui-yuque", plugin_dir_url(__FILE__) . 'css/element-ui-yuque.css', array(), $this->version, 'all');
        // 后台样式文件
        wp_enqueue_style($this->yuque_wordpress . "_admin_style", plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Yuque_Wordpress_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Yuque_Wordpress_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
//		默认加载的js 暂时空着
        wp_enqueue_script($this->yuque_wordpress . '_yuque-default', plugin_dir_url(__FILE__) . 'js/yuque-wordpress-admin.js', array('jquery'), $this->version, false);
//      Vue.js v2.6.14
        wp_enqueue_script($this->yuque_wordpress . '_vue', plugin_dir_url(__FILE__) . 'js/vue.min.js', array(), $this->version, false);
//		element-ui
        wp_enqueue_script($this->yuque_wordpress . '_element-ui-js', plugin_dir_url(__FILE__) . 'js/element-ui.js', array(), $this->version, true);
        wp_enqueue_script($this->yuque_wordpress . '_axios', plugin_dir_url(__FILE__) . 'js/axios.min.js', array(), $this->version, true);
//      业务逻辑
        wp_enqueue_script($this->yuque_wordpress . '_yuque-admin-index', plugin_dir_url(__FILE__) . 'js/admin.js', array(), $this->version, true);

    }

    /**
     * @return string
     */
    public function register_plugin_menu(): void
    {
        add_menu_page('Yuque WordPress', 'Yuque', 'administrator', 'yuque_wordpress_plugin', array($this, 'custom_menu_page'), plugin_dir_url(dirname(__FILE__)) . 'assets/yq.svg');
    }

    public function custom_menu_page()
    {


        require_once(plugin_dir_path(__FILE__) . "/index.php");
//		echo "Admin Page Test--".plugin_dir_url(dirname(__FILE__)).'assets/2.png';
    }


    /**
     * 保存配置信息
     */
    public function get_config()
    {

        $data = [];
//        $data['version'] = get_option($this->yuque_wordpress . "_version");
        $data['host'] = esc_url(home_url('/'));


        $data['pluginToken'] = get_option($this->yuque_wordpress . "_token");
        $data['accessToken'] = get_option($this->yuque_wordpress . "_access_token");
        $data['author'] = get_option($this->yuque_wordpress . "_author");
        $data['parseXml'] = boolval(get_option($this->yuque_wordpress . "_parse_xml"));
        $data['localImage'] = boolval(get_option($this->yuque_wordpress . "_local_image"));
        if (!$data['author']) {
            // 不存在 作者id时  返回当前的用户名
            $data['author'] = wp_get_current_user()->data->user_login;
        } else {
            // 通过id 查出用户名
            $data['author'] = get_user_by('id', $data['author'])->data->user_login;
        }

        $this->returnJson($data);
    }

    public function get_user_id()
    {
        $username = $_POST['username'];
        if ($username) {
            $data = get_user_by('login', $username);
            if ($data) {
                $this->returnJson($data->ID);
            } else {
                $this->returnJson(false, -1, '查询用户失败');
            }

        } else {
            $this->returnJson(false, -1, '请输入用户名');
        }
    }

    public function returnJson($data = true, $code = 200, $message = '操作成功')
    {
        $res = [];
        $res['code'] = $code;
        $res['message'] = $message;
        $res['data'] = $data;
        wp_die(json_encode($res));
    }


    /**
     * 保存配置信息
     */
    public function save_config()
    {
        $raw_data = $_POST['save'];
        $postData = json_decode(stripslashes($raw_data));
        update_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_token', $postData->pluginToken);
        update_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_access_token', $postData->accessToken);
        update_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_author', $postData->author);
        update_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_parse_xml', $postData->parseXml);
        update_option(YUQUE_WORDPRESS_PLUGIN_IDENTIFICATION . '_local_image', $postData->localImage);
        $this->returnJson();
    }


}
