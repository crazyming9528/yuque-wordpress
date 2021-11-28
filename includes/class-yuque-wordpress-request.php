<?php


class Yuque_Wordpress_Request {

	protected $yuque_wordpress;
	protected $version;


	protected $headers;
	protected $raw_data;
	protected $yuque_api_url;
	protected $access_token;


	public function __construct( $yuque_wordpress, $version,$access_token  ) {
		$this->yuque_wordpress = $yuque_wordpress;
		$this->version = $version;
		$this->access_token = $access_token;
		$this->yuque_api_url ='https://www.yuque.com/api/v2';
	}

	/**
	 * 获取header
	 * @return array|false
	 */
	public  function getHeader(){
		if ( function_exists( 'getallheaders' ) ) {
			return getallheaders();
		}
		/**
		 * Nginx and pre 5.4 workaround.
		 * @see http://www.php.net/manual/en/function.getallheaders.php
		 */
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}

		return $headers;
	}

	/**
	 * 获取数据
	 * @return false|string
	 */
	public function get_raw_data() {
		return file_get_contents( 'php://input' );
	}

public function getJson($url,$header=array()) {
	$defaultHeader = array(
		'X-Auth-Token:'.$this->access_token,
		'user-agent:'.$this->yuque_wordpress,
	);
	$headers = wp_parse_args( $header, $defaultHeader );
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->yuque_api_url.$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return json_decode($output, true);
	}

	/**
	 * @return mixed
	 */
	public function getUserInfo() {
		$user = $this->getJson('/user');
		return  $user && $user['data'] ? $user['data'] : FALSE;
	}

	public  function  getDocDetail(string $namespace,string $slug){
		$path  = '/repos/' . $namespace . '/docs/' . $slug;
		$doc = $this->getJson($path);
		return  $doc && $doc['data'] ? $doc['data'] : FALSE;
	}







}