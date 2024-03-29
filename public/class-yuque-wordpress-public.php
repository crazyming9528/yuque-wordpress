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
    private $request;
    private $category;

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
     * 生成语雀标识
     *
     * @param $content
     *
     * @return string
     */
    public function generateYuqueTips($content): string
    {
        global $wpdb;
        global $post;
        $sql = "select count(*) from " . $wpdb->prefix . $this->yuque_wordpress . "_post_map where post_id = " . $post->ID;
        $sqlRes = $wpdb->get_var($sql);
        if ($sqlRes != 0) {
            $img = plugin_dir_url(dirname(__FILE__)) . 'assets/yq_logo_50x50.svg';
            $content .= '<div style="text-align: center;background-color: #f6ffe9cf;margin-top:15px;margin-bottom:15px;padding: 2px"><img style="vertical-align: middle" width="36px" src="' . $img . '" alt=""><span style="vertical-align: middle;font-size: 12px;margin-left: 8px;color: grey">本文通过  <a href="https://github.com/crazyming9528/yuque-wordpress" rel="nofollow" target="_blank">YUQUE WORDPRESS</a> 同步自语雀</span></div>';
        }

        return $content;
    }


    /**
     * 验证插件token
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
     * 保存日志
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

    /**
     * 在文档中插入标记 (弃用 会导致部分内容丢失)
     * @param string $htmlStr
     * @return string[]
     */
    private function insertMark(string $htmlStr = ''){
        $new_html = '';
        try {
            $html_doc = new DOMDocument();
            $html_doc->loadHTML(mb_convert_encoding($htmlStr, 'HTML-ENTITIES', 'UTF-8'));
            $html_doc->normalizeDocument();
            $node = $html_doc->createElement("div");
            $new_node = $html_doc->appendChild($node);
            $new_node->setAttribute("data-" . $this->yuque_wordpress . '-version', $this->version);//  添加标记
            $new_html = $html_doc->saveHTML();

        } // 捕获异常
        catch (Exception $e) {
            $this->saveLog('解析xml发生错误', array('log_detail' => json_encode($e)));
        } finally {
            return array(
                'new_html' => $new_html,
            );
        }

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
                        $xml_text = $pre->nodeValue;//nodeValue 获取 dom中文本
                        $has_plugin_identification = strpos($xml_text, '<yuque_wordpress_plugin>') !== FALSE && strpos($xml_text, '</yuque_wordpress_plugin>') !== FALSE;
                        if ($has_plugin_identification !== FALSE) {
                            array_push($xml_array, $xml_text);
                            $pre->parentNode->removeChild($pre);//  从文章中移除掉该pre代码块
                        }
                    }
                }
            }

            $node = $html_doc->createElement("div");
            $new_node = $html_doc->appendChild($node);
            $new_node->setAttribute("data-" . $this->yuque_wordpress . '-version', $this->version);//  添加标记
            $new_html = $html_doc->saveHTML();
            if (!empty($xml_array)) {
                $yuque_wp_xml = simplexml_load_string($xml_array[0]);
            }

        } // 捕获异常
        catch (Exception $e) {
            $this->saveLog('解析xml发生错误', array('log_detail' => json_encode($e)));

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
    public function localImage($content): string
    {
        $preg = preg_match_all('/<img.*?src="(.*?)"/', stripslashes($content), $matches);
        if ($preg) {
            $i = 1;
            foreach ($matches[1] as $image_url) {
                if (empty($image_url)) continue;
                $pos = strpos($image_url, get_bloginfo('url'));
                if ($pos === false) {
//                    $file = file_get_contents($image_url);
                    $file = $this->request->getImage($image_url);
                    $filename = basename($image_url);
                        $res = wp_upload_bits($filename, '', $file);
//                    $dirs = wp_upload_dir();
//                    $filetype = wp_check_filetype($filename);
//                    $attachment = array(
//                        'guid' => $dirs['baseurl'] . '/' . $filename,
//                        'post_mime_type' => $filetype['type'],
//                        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
//                        'post_content' => '',
//                        'post_status' => 'inherit'
//                    );
//                    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
//                    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
//                    wp_update_attachment_metadata($attach_id, $attach_data);
//
                    $replace = $res['url'];
//                    $this->saveLog('replace:'.$replace);
//                    $this->saveLog('image_url:'.$image_url);
                    $content = str_replace($image_url, $replace, $content);
//                    $this->saveLog('$content:'.$image_url);
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
        $this->saveLog('本地化图片开始');
        $post_id2 = wp_update_post([
            'ID' => $post_id,
            //
            'post_content' => $this->localImage($content),
            //正文html
//            'post_content_filtered' => $doc_data['body'], todo 这里同样需要处理图片

        ]);
        $this->saveLog($post_id2 ? "本地化成功 " : '本地化失败');
        return true;
    }

    public function createOrUpdateWpPost($doc_data, $xml_obj_data = null)
    {
        $author = $this->config['author'];
        $isLocalImage = $this->config['localImage'];
        $isParseXml = $this->config['parseXml'];
        $post_status = 'publish';//文章状态
        $post_tag = array(); // 文章标签
        $post_category = array();//文章分类
        if ($this->category){
            $cat = get_term_by('name', $this->category, 'category');
            if ($cat) {
                array_push($post_category, $cat->term_id);
            } else {
                $this->saveLog('设置分类 ' . $this->category . ' 失败');
            }
        }

        if ($doc_data['public'] === 0) {
            // 语雀私密文档
            $post_status = 'private';
        } else if ($doc_data['public'] === 1) {
            // 语雀公开文档
            $post_status = $doc_data['status'] === 1 ? 'publish' : 'draft';
        }

//        if (!is_null($xml_obj_data) && $isParseXml) {
//            $this->saveLog('解析xml');
//            if ($xml_obj_data->category) {
//                if (is_object($xml_obj_data->category)) {
//                    $temp_Array = $this->object_array($xml_obj_data->category);
//                    $error_array = array();
//                    //					$post_tag = array_merge($post_tag,$xml_obj_data->tag);
//                    foreach ($temp_Array as $key => $value) {
//
//                        // get_cat_ID 要被废弃, 这里参考 get_cat_ID 函数
//                        $cat = get_term_by('name', $value, 'category');
//                        if ($cat) {
//                            array_push($post_category, $cat->term_id);
//                        } else {
//                            array_push($error_array, $value);
//                        }
//
//                    }
//                    if (!empty($error_array)) {
//                        $this->saveLog('设置分类 ' . implode($error_array, '、') . ' 失败');
//                    }
//                } else {
//                    // get_cat_ID 要被废弃, 这里参考 get_cat_ID 函数
//                    $cat = get_term_by('name', $xml_obj_data->category, 'category');
//                    if ($cat) {
//                        array_push($post_category, $cat->term_id);
//                    } else {
//                        $this->saveLog('设置分类 ' . $xml_obj_data->category . ' 失败');
//                    }
//                }
//
//            }
//            if ($xml_obj_data->tag) {
//                if (is_object($xml_obj_data->tag)) {
//                    $temp = $this->object_array($xml_obj_data->tag);
//                    $post_tag = $temp;
//                } else {
//                    array_push($post_tag, $xml_obj_data->tag);
//
//                }
//
//            }
//
//
//        }


        global $wpdb;
        $sql = "select count(*) from " . $wpdb->prefix . $this->yuque_wordpress . "_post_map where yuque_post_id = " . $doc_data['id'];
        $sqlRes = $wpdb->get_var($sql);

        if ($sqlRes == 0) {

            $post_id = wp_insert_post([
                    //			'post_content'=> $raw_data['data']['title'],
                    'post_content' => $doc_data['body_html'],
                    //正文html
//                    'post_content_filtered' => $doc_data['body'],// 暂时屏蔽  图片本地化处理了这个字段后在打开
                    // 正文 markdown
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
                $this->saveLog('从语雀新建文章成功,' . $post_id . ":" . $doc_data['title']);

                if ($isLocalImage) {
                    $this->updatePostForLocalImage($post_id, $doc_data['body_html']);
                }
            } else {
                $this->saveLog('从语雀新建文章失败');
            }
        } else {
            $sql = "select post_id from " . $wpdb->prefix . $this->yuque_wordpress . "_post_map where yuque_post_id = " . $doc_data['id'];
            $post_id_from_db = $wpdb->get_var($sql);
            $post_id = wp_update_post([
                'ID' => $post_id_from_db,
                //
                'post_content' => $doc_data['body_html'],
                //正文html
                'post_content_filtered' => $doc_data['body'],
                // 正文 markdown
                'post_title' => $doc_data['title'],
                'post_status' => $post_status,
                'post_category' => $post_category,
                'post_author' => $author,
                'tags_input' => $post_tag,
            ]);
            $this->saveLog($post_id ? "从语雀更新文章成功" . ":" . $doc_data['title'] : '从语雀更新文章失败');
            if ($post_id && $isLocalImage) {
                $this->updatePostForLocalImage($post_id, $doc_data['body_html']);
            }

        }

    }

    /**
     * 拉取webhook推送的数据
     *
     * @return mixed
     */
    public function pull_posts()
    {
        $this->config = Yuque_Wordpress_Utils::getConfigData($this->yuque_wordpress . "_config");
        if (!$this->config || !$this->config['switch']) wp_die('插件未开启');
        $this->triggerTime = time();
        $this->request = $request = new Yuque_Wordpress_Request($this->yuque_wordpress, $this->version, $this->config['accessToken']);
        $raw_data = $request->get_raw_data();
        $this->saveLog('', array('title'=> date('Y-m-d H:i:s', $this->triggerTime).' 同步日志'), 'create');
        $this->saveLog('接收到语雀推送', array('webhook_data_json' => $raw_data) );

        if (!$this->verifyPluginToken($_GET['token'])) {
            return $this->saveLog('插件token验证失败');
        };
        $this->category = $_GET['category'];

        $resData = json_decode($raw_data);
        $userRes = $request->getUserInfo();
        $userData = $userRes['data'];
        if ($userRes['status']) {
            $this->saveLog('获取用户信息成功', array('user_data_json' => json_encode($userData)));
            $namespace = $userData['login'] . '/' . $resData->data->book->slug;
            $docRes = $request->getDocDetail($namespace, $resData->data->slug);
            $docData = $docRes['data'];
            if ($docRes['status']) {
                $this->saveLog('获取文章信息成功', array('doc_data_json' => json_encode($docData)));
                // 取消了解析xml的功能
//                $parse_data = $this->parseXmlInHtml($docData['body_html']);
//                $docData['body_html'] = $parse_data['new_html']; // 使用经过解析处理的html
//                $this->createOrUpdateWpPost($docData, $parse_data['yuque_wp_xml']);



//                $parse_data = $this->insertMark($docData['body_html']);// 插入插件的标记  弃用 会导致部分内容丢失
//                $docData['body_html'] = $parse_data['new_html']; //
                $this->createOrUpdateWpPost($docData, null);

            }else{
                $this->saveLog('获取文章信息失败', array('doc_data_json' => json_encode($docData)));
            }
        }else{
            $this->saveLog('获取用户信息失败', array('user_data_json' => json_encode($userData)));
        }
    }

// 对象转数组
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
