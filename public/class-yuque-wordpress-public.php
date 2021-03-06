<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Yuque_Wordpress
 * @subpackage Yuque_Wordpress/public
 * @author     Your Name <email@example.com>
 */
class Yuque_Wordpress_Public
{

    /**
     * The ID of this plugin.
     *
     * @var      string $yuque_wordpress The ID of this plugin.
     * @since    1.0.0
     * @access   private
     */
    private $yuque_wordpress;

    /**
     * The version of this plugin.
     *
     * @var      string $version The current version of this plugin.
     * @since    1.0.0
     * @access   private
     */
    private $version;


    private $config;

    private $postTitle;
    private $triggerTime;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $yuque_wordpress The name of the plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($yuque_wordpress, $version)
    {

        $this->yuque_wordpress = $yuque_wordpress;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style($this->yuque_wordpress, plugin_dir_url(__FILE__) . 'css/yuque-wordpress-public.css', [], $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script($this->yuque_wordpress, plugin_dir_url(__FILE__) . 'js/yuque-wordpress-public.js', ['jquery'], $this->version, FALSE);

    }

    /**
     * ??????????????????
     *
     * @param $content
     *
     * @return string
     */
    public function generateYuqueTips($content): string
    {
        $is_yuque = strpos($content, 'data-yuque_wordpress_plugin-version');
        if ($is_yuque) {
            $img = plugin_dir_url(dirname(__FILE__)) . 'assets/yq_logo_50x50.svg';
            $content .= '<div style="text-align: center;background-color: #f6ffe9cf;padding: 2px"><img style="vertical-align: middle" src="' . $img . '" alt=""><span style="vertical-align: middle;font-size: 13px;margin-left: 8px;color: grey">????????????  <a href="https://github.com/crazyming9528/yuque-wordpress" rel="nofollow" target="_blank">YUQUE WORDPRESS</a> ??????????????????????????????</span></div>';
        }
        return $content;
    }


    /**
     * ????????????token
     * @param string $token
     *
     * @return bool
     */
    public function verifyPluginToken(string $token = ''): bool
    {
        $dbToken = $this->config['pluginToken'];
        return $token !== '' && $token === $dbToken;

    }

    /**
     * ????????????
     *
     * @param string $text
     *
     * @return bool
     */
    public function saveLog(string $step, array $updateData = array(), $action = 'update')
    {


        global $wpdb;
        $row = 0;
        if ($action === 'create') {
            $willCreate = [];
            foreach (YUQUELOGFIELD as $key => $value) {
                $willCreate[$key] = $value;
            }
            $willCreate['trigger_at'] = date('Y-m-d H:i:s', $this->triggerTime);
            $willCreate['title'] = $updateData["title"];
//            $willCreate['trigger_at'] = $this->triggerTime;
            $row = $wpdb->insert($wpdb->prefix . $this->yuque_wordpress . "_log", $willCreate);
        } else if ($action === 'update') {
            if ($step) {
                $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . $this->yuque_wordpress . "_log" . " SET step = CONCAT(step, '=>" . $step . "') WHERE trigger_at = '%s'", date('Y-m-d H:i:s', $this->triggerTime)));
            }
            if (sizeof($updateData) === 0) return false;

            $willUpdate = [];
            foreach ($updateData as $key => $value) {
                if (array_key_exists($key, YUQUELOGFIELD)) {
                    $willUpdate[$key] = $value;
                }
            }
            $row = $wpdb->update($wpdb->prefix . $this->yuque_wordpress . "_log", $willUpdate, array('trigger_at' => date('Y-m-d H:i:s', $this->triggerTime)));

        }

        return !!$row;

    }


    private function parseXmlInHtml(string $htmlStr = '')
    {
        $xml_array = array();
        $yuque_wp_xml = NULL;
        $new_html = '';

        try {
            $html_doc = new DOMDocument();
            $html_doc->loadHTML(mb_convert_encoding($htmlStr, 'HTML-ENTITIES', 'UTF-8'));
            $html_doc->normalizeDocument();
            $pres = $html_doc->getElementsByTagName('pre');
            foreach ($pres as $pre) {
                if ($pre->hasAttributes()) {
                    $is_xml = $pre->getAttribute('data-language') === 'xml';
                    if ($is_xml) {
                        $xml_text = $pre->nodeValue;//nodeValue ?????? dom?????????
                        $has_plugin_identification = strpos($xml_text, '<yuque_wordpress_plugin>') !== FALSE && strpos($xml_text, '</yuque_wordpress_plugin>') !== FALSE;
                        if ($has_plugin_identification !== FALSE) {
                            array_push($xml_array, $xml_text);
                            $pre->parentNode->removeChild($pre);//  ????????????????????????pre?????????
                        }
                    }
                }
            }

            $node = $html_doc->createElement("div");
            $new_node = $html_doc->appendChild($node);
            $new_node->setAttribute("data-" . $this->yuque_wordpress . '-version', $this->version);//  ????????????
            $new_html = $html_doc->saveHTML();
            if (!empty($xml_array)) {
                $yuque_wp_xml = simplexml_load_string($xml_array[0]);
            }

        } // ????????????
        catch (Exception $e) {
            $this->saveLog('??????xml????????????', array('log_detail' => json_encode($e)));

        } finally {
//            return $yuque_wp_xml;
            return array(
                'new_html' => $new_html,
                'yuque_wp_xml' => $yuque_wp_xml,
            );
        }


    }

    /**
     * @return string
     */
    public function localImage($post_id, $content): string
    {
        $preg = preg_match_all('/<img.*?src="(.*?)"/', stripslashes($content), $matches);
        if ($preg) {
            $i = 1;
            foreach ($matches[1] as $image_url) {
                if (empty($image_url)) continue;
                $pos = strpos($image_url, get_bloginfo('url'));
                if ($pos === false) {
                    $file = file_get_contents($image_url);
                    $filename = basename($image_url);
                    $res = wp_upload_bits($filename, '', $file);
                    $dirs = wp_upload_dir();
                    $filetype = wp_check_filetype($filename);
                    $attachment = array(
                        'guid' => $dirs['baseurl'] . '/' . $filename,
                        'post_mime_type' => $filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                    //wp_generate_attachment_metadata
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    //if($i==1 ){
                    //set_post_thumbnail( $post_id, $attach_id );
                    //}
                    $replace = $res['url'];
                    $content = str_replace($image_url, $replace, $content);
                }
                $i++;
            }
        }
        return $content;
    }

    /**
     * @return string
     */
    public function updatePostForLocalImage($post_id, $content): string
    {
        $this->saveLog('?????????????????????');
        $post_id2 = wp_update_post([
            'ID' => $post_id,
            //
            'post_content' => $this->localImage($post_id, $content),
            //??????html
//            'post_content_filtered' => $doc_data['body'], todo ??????????????????????????????

        ]);
        $this->saveLog($post_id2 ? "??????????????? " : '???????????????');
        return true;
    }

    public function createOrUpdateWpPost($doc_data, $xml_obj_data = null)
    {
        $author = $this->config['author'];
        $isLocalImage = $this->config['localImage'];
        $isParseXml = $this->config['parseXml'];
        $post_status = 'publish';//????????????
        $post_tag = array(); // ????????????
        $post_category = array();//????????????
        if ($doc_data['public'] === 0) {
            // ??????????????????
            $post_status = 'private';
        } else if ($doc_data['public'] === 1) {
            // ??????????????????
            $post_status = $doc_data['status'] === 1 ? 'publish' : 'draft';
        }

        if (!is_null($xml_obj_data) && $isParseXml) {
            $this->saveLog('??????xml');
            if ($xml_obj_data->category) {
                if (is_object($xml_obj_data->category)) {
                    $temp_Array = $this->object_array($xml_obj_data->category);
                    $error_array = array();
                    //					$post_tag = array_merge($post_tag,$xml_obj_data->tag);
                    foreach ($temp_Array as $key => $value) {

                        // get_cat_ID ????????????, ???????????? get_cat_ID ??????
                        $cat = get_term_by('name', $value, 'category');
                        if ($cat) {
                            array_push($post_category, $cat->term_id);
                        } else {
                            array_push($error_array, $value);
                        }

                    }
                    if (!empty($error_array)) {
                        $this->saveLog('???????????? ' . implode($error_array, '???') . ' ??????');
                    }
                } else {
                    // get_cat_ID ????????????, ???????????? get_cat_ID ??????
                    $cat = get_term_by('name', $xml_obj_data->category, 'category');
                    if ($cat) {
                        array_push($post_category, $cat->term_id);
                    } else {
                        $this->saveLog('???????????? ' . $xml_obj_data->category . ' ??????');
                    }
                }

            }
            if ($xml_obj_data->tag) {
                if (is_object($xml_obj_data->tag)) {
                    $temp = $this->object_array($xml_obj_data->tag);
                    $post_tag = $temp;
                } else {
                    array_push($post_tag, $xml_obj_data->tag);

                }

            }


        }


        global $wpdb;
        $sql = "select count(*) from " . $wpdb->prefix . $this->yuque_wordpress . "_post_map where yuque_post_id = " . $doc_data['id'];
        $sqlRes = $wpdb->get_var($sql);

        if ($sqlRes == 0) {

            $post_id = wp_insert_post([
                    //			'post_content'=> $raw_data['data']['title'],
                    'post_content' => $doc_data['body_html'],
                    //??????html
                    'post_content_filtered' => $doc_data['body'],
                    // ?????? markdown
                    'post_title' => $doc_data['title'],
                    'post_status' => $post_status,
                    'post_category' => $post_category,
                    'post_author' => $author,
                    'tags_input' => $post_tag,
                ]
            );


            if ($post_id) {
                $wpdb->insert($wpdb->prefix . $this->yuque_wordpress . "_post_map", [
                    'post_id' => $post_id,
                    'yuque_post_id' => $doc_data['id'],
                    'yuque_post_url' => '2',
                ]);
                $this->saveLog('???????????????????????????,' . $post_id . ":" . $doc_data['title']);

                if ($isLocalImage) {
                    $this->updatePostForLocalImage($post_id, $doc_data['body_html']);
                }
            } else {
                $this->saveLog('???????????????????????????');
            }
        } else {
            $sql = "select post_id from " . $wpdb->prefix . $this->yuque_wordpress . "_post_map where yuque_post_id = " . $doc_data['id'];
            $post_id_from_db = $wpdb->get_var($sql);
            $post_id = wp_update_post([
                'ID' => $post_id_from_db,
                //
                'post_content' => $doc_data['body_html'],
                //??????html
                'post_content_filtered' => $doc_data['body'],
                // ?????? markdown
                'post_title' => $doc_data['title'],
                'post_status' => $post_status,
                'post_category' => $post_category,
                'post_author' => $author,
                'tags_input' => $post_tag,
            ]);
            $this->saveLog($post_id ? "???????????????????????????" . ":" . $doc_data['title'] : '???????????????????????????');
            if ($post_id && $isLocalImage) {
                $this->updatePostForLocalImage($post_id, $doc_data['body_html']);
            }

        }

    }

    /**
     * ??????webhook???????????????
     *
     * @return mixed
     */
    public function pull_posts()
    {


        $this->config = Yuque_Wordpress_Utils::getConfigData($this->yuque_wordpress . "_config");
        if (!$this->config || !$this->config['switch']) wp_die('???????????????');
        $this->triggerTime = time();
        $request = new Yuque_Wordpress_Request($this->yuque_wordpress, $this->version, $this->config['accessToken']);
        $raw_data = $request->get_raw_data();
        $this->saveLog('', array('title'=> date('Y-m-d H:i:s', $this->triggerTime).' ????????????'), 'create');
        $this->saveLog('?????????????????????', array('webhook_data_json' => $raw_data) );

        if (!$this->verifyPluginToken($_GET['token'])) {
            return $this->saveLog('??????token????????????');
        };

        $resData = json_decode($raw_data);
        $userRes = $request->getUserInfo();
        $userData = $userRes['data'];
        if ($userRes['status']) {
            $this->saveLog('????????????????????????', array('user_data_json' => json_encode($userData)));
            $namespace = $userData['login'] . '/' . $resData->data->book->slug;
            $docRes = $request->getDocDetail($namespace, $resData->data->slug);
            $docData = $docRes['data'];
            if ($docRes['status']) {
                $this->saveLog('????????????????????????', array('doc_data_json' => json_encode($docData)));
                $parse_data = $this->parseXmlInHtml($docData['body_html']);
                $docData['body_html'] = $parse_data['new_html']; // ???????????????????????????html
                $this->createOrUpdateWpPost($docData, $parse_data['yuque_wp_xml']);

            }else{
                $this->saveLog('????????????????????????', array('doc_data_json' => json_encode($docData)));
            }
        }else{
            $this->saveLog('????????????????????????', array('user_data_json' => json_encode($userData)));
        }
    }

// ???????????????
    private function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

}
