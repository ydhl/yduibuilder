<?php
/**
 * 职责是担任HTTP客户端，负责构造OAuth get，post请求，并原样返回所请求的结果，请求的结果作何处理，由调用者决定
 * 
 * @author leeboo
 *
 */
class OAuthHttpClient{

	/* Contains the last HTTP status code returned. */
	public $http_code;

	/* Contains the last API call. */
	public $url;

	/* Set timeout default. */
	public $timeout = 120;

	/* Set connect timeout. */
	public $connecttimeout = 30;

	/* Verify SSL Cert. */
	public $ssl_verifypeer = FALSE;

	/* Respons format. */
	public $format = 'json';

	/* Decode returned json data. */
	public $decode_json = TRUE;

	/* Contains the last HTTP headers returned. */
	public $http_info;

	/* Set the useragnet. */
	public $useragent = 'jiaoliuping.com';
	
	/**
	 * access token放在header中 Authorization: Bearer token
	 * @var unknown
	 */
	private $authorization;
	
	public function __construct($authorization=""){
		$this->authorization 				= $authorization;
	}

	public function get($url, $params = array())
	{
		
		if($params){
			$url .= (strrpos($url, "?")!==FALSE ? "&"  : "?").OAuthUtil::build_http_query($params);
		}
		$response = $this->http($url,'GET');
		return $response;
	}

	function post($url, $params = array(), $multi = false) {
		
		$query = "";
		if($multi){
			$query = OAuthUtil::build_http_query_multi($params);
		}else{
			$query = OAuthUtil::build_http_query($params);
		}
		$response = $this->http($url,'POST', $query, $multi);
		return $response;
	}
	/**
	 * Make an HTTP request
	 *
	 * @return API results
	 */
	function http($url, $method, $postfields = NULL, $multi = false) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		
		
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		curl_setopt($ci, CURLINFO_HEADER_OUT , true);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}
		
		$header_array =array( "Expect: ");
		
		//douban 需要把access token放在header中，这里加上，对目前的支持网站也没有影响 leebboo
		if($this->authorization){
			$header_array[]  = "Authorization: ".$this->authorization;
		}
		
		if( $multi )
		{
			$header_array[] = "Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary;
		} 
		
		curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array );

		//echo $url;
		curl_setopt($ci, CURLOPT_URL, $url);
		
		$response = curl_exec($ci);
		$curl_info = curl_getinfo($ci);

		$this->http_code = $curl_info['http_code'];
		$this->http_info = array_merge($this->http_info, $curl_info);
		$this->url = $url;
		curl_close ($ci);
		//var_dump($url);var_dump($curl_info);var_dump($response);
		return $response;
	}

}