<?php
require_once 'qq/Tencent.php';

class QqOAuth extends SnsOAuth {
	const APP_KEY = '801189893';
	const APP_SECRET = 'c93c5a86a90592b5a25841f46cfc39ad';
	/* const APP_KEY = '801170937';测试用
	const APP_SECRET = '1273085f1573db1cc2cf8eaa47a885b3'; */
	
	public function __construct($type) {
		parent::__construct($type);
		Tencent_OAuth::init(self::APP_KEY, self::APP_SECRET);
	}
	/**
	 * 获取认证URL
	 */
	public function getOAuthorizeURL($appendUrl = '') {
		return Tencent_OAuth::getAuthorizeURL(self::CALLBACK.'?state='.$appendUrl);
	}
	/**
	 * 获取access token
	 * @see SnsOAuth::getAccessToken()
	 */
	public function getAccessToken(array &$params, $type='code') {
		$data = array();
		switch ($type) {
			case 'code':
		        $code = $params['code'];
		        $openid = $params['openid'];
		        $openkey = $params['openkey'];
		        //获取授权token
		        $url = Tencent_OAuth::getAccessToken($code, self::CALLBACK.'?state='.$params['state']);
		        $r = Tencent_Http::request($url);
		        parse_str($r, $out);
		        //存储授权数据
		        if ($out['access_token']) {
		        	$out['code'] = $code;
		        	$out['openid'] = $openid;
		        	$out['openkey'] = $openkey;
		        	$this->authResult = $out;
		        	return $out;
		        }
		        break;
			default:
				return false;
	    }
	}
	public function getUid() {
		return isset($this->authResult['name'])?$this->authResult['name']:null;
	}
	public function isExpired(array $originResult) {
		if($originResult && $this->authResult) {
			if($originResult['access_token'] != $this->authResult['access_token']) {
				return true;
			}
		}
		return false;
	}
	public function getOAuthToSave() {
		$return = array();
		if($this->authResult) {
			$return['access_token'] = $this->authResult['access_token'];
			$return['refresh_token'] = $this->authResult['refresh_token'];
			$return['openid'] = $this->authResult['openid'];
			$return['openkey'] = $this->authResult['openkey'];
			$return['expires_in'] = $this->authResult['expires_in']+time();
		}
		return $return;
	}
	public function getExpiredTime($absolute=false) {
		if($this->authResult) {
			return $absolute?($this->authResult['expires_in']+time()):$this->authResult['expires_in'];
		}
		return false;
	}
	public function accessToken(){
		return isset($this->authResult['access_token'])?$this->authResult['access_token']:null;
	}
	public function getAppInfo() {
		return array(
				'appkey'=>self::APP_KEY,
				'appsecret'=>self::APP_SECRET,
				'access_token'=>$this->authResult['access_token'],
				'refresh_token'=>$this->authResult['refresh_token'],
				'openid' => $this->authResult['openid'],
				'openkey' => $this->authResult['openkey']
				);
	}
}
