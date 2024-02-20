<?php
class YDHttp{

	/* Contains the last HTTP status code returned. */
	public $http_code;

	/* Contains the last API call. */
	public $url;

	/* Set timeout default. */
	public $timeout = 30;

	/* Set connect timeout. */
	public $connecttimeout = 30;

	/* Contains the last HTTP headers returned. */
	public $http_info;

	/* Set the useragnet. */
	public $useragent = 'ydwx';

	static $boundary = "";
	public $errno;
	public $error;
	public $request_header = [];

	protected $ci;

	public function __construct(){
	    $this->ci = curl_init();
	}

	public function get($url, $params = array())
	{
		if($params){
			$url .= "?".http_build_query($params);
		}
		return $this->http($url,'GET');
	}

	public function post($url, $params = array(), $multi = false) {
		$query = "";
		if( is_array($params)){
			if($multi)
				$query = self::build_http_query_multi($params);
			else
				$query = http_build_query($params);
		}else{
			$query = $params;
		}

		return $this->http($url,'POST', $query, $multi);
	}

	public static function build_http_query_multi($params) {
		if (!$params) return '';

		// Urlencode both keys and values
		$keys = array_keys($params);
		$values = array_values($params);
		$params = array_combine($keys, $values);

		// Parameters are sorted by name, using lexicographical byte value ordering.
		// Ref: Spec: 9.1.1 (1)
		uksort($params, 'strcmp');
		$pairs = array();
		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value)
		{
			if($value{0} == '@' )
			{
				$url = ltrim( $value , '@' );
				$content = file_get_contents( $url );
                $filename = explode( '?' , basename( $url ) );
				$filename = reset($filename);
				$mime = self::get_image_mime($url);

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= 'Content-Type: '. $mime . "\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			}
			else
			{
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="'.$parameter."\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
			}
		}
		$multipartbody .=  $endMPboundary."\r\n";//微信服务器不加\r\n会提示media data is missing
		return $multipartbody;

	}
	public static function get_image_mime( $file )
	{
		$ext = strtolower(pathinfo( $file , PATHINFO_EXTENSION ));
		switch( $ext )
		{
			case 'jpg':
			case 'jpeg':
				$mime = 'image/jpg';
				break;

			case 'png';
			$mime = 'image/png';
			break;

			case 'gif';
			default:
				$mime = 'image/gif';
				break;
		}
		return $mime;
	}
	/**
	 * Make an HTTP request
	 *
	 * @return API results
	 */
	protected function http($url, $method, $postfields = NULL, $multi = false) {
	    $ci = $this->ci;
		$this->http_info = array();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);

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

        $header_array = $this->request_header;
		if( $multi )
		{
			$header_array[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
            $header_array[] = "Expect: ";
		}

        curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
		//echo $url;
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->errno     = curl_errno($ci);
		$this->error     = curl_error($ci);
		$this->url = $url;
		curl_close ($ci);
		return trim($response);
	}

}
/**
 * @author leeboo
 *
 */
class YDHttps extends YDHttp{
    /**
     * Make an HTTP request
     *
     * @return API results
     */
    protected function http($url, $method, $postfields = NULL, $multi = false) {
        curl_setopt($this->ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ci, CURLOPT_SSL_VERIFYHOST, 1);

//        if($cert_file){
//            curl_setopt($this->ci,CURLOPT_SSLCERTTYPE,'PEM');
//            curl_setopt($this->ci,CURLOPT_SSLCERT, $cert_file);
//        }
//
//		if($key_file){
//            curl_setopt($this->ci,CURLOPT_SSLKEYTYPE,'PEM');
//            curl_setopt($this->ci,CURLOPT_SSLKEY,  $key_file);
//        }
//

        return parent::http($url, $method, $postfields, $multi);
    }
}
