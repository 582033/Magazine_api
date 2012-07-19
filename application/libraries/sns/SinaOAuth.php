<?php
require_once 'sina/saetv2.ex.class.php';

class SinaOAuth extends SnsOAuth {
	/* const APP_KEY = '2457468674';
	const APP_SECRET = '2cc41dd67675e95e1c33e7820aba6a5c'; */
	const APP_KEY = '264646415';
	const APP_SECRET = '2dc868bf00ef506a3954a28bc0479d77';
	/* const APP_KEY = '1545000644';测试用
	const APP_SECRET = 'ed426dc7240e2483cf6e84aee9b78c47'; */
	
	protected $oAuth = null;
	/**
	 * 
	 * @return SaeTOAuthV2
	 */
	protected function getOAuth() {
		if(null === $this->oAuth) {
			$this->oAuth = new SaeTOAuthV2( self::APP_KEY , self::APP_SECRET );
		}
		return $this->oAuth;
	}
	/**
	 * 获取认证URL
	 */
	public function getOAuthorizeURL($appendUrl = '') {
		return $this->getOAuth()->getAuthorizeURL( self::CALLBACK ).'&state='.$appendUrl;
	}
	/**
	 * 获取access token
	 * @see SnsOAuth::getAccessToken()
	 */
	public function getAccessToken(array &$params, $type='code') {
		$data = array();
		switch ($type) {
			case 'code':
				if(!isset($params['code'])) {
					return false;
				}
				$data['code'] = $params['code'];
				$data['redirect_uri'] = self::CALLBACK;
				try {
					$return = $this->getOAuth()->getAccessToken($type,$data);
				}
				catch (OAuthException $e) {
					return false;
				}
				return $this->authResult = $return;
				break;
			default:
				return false;
		}
	}
	public function getUid() {
		return isset($this->authResult['uid'])?$this->authResult['uid']:null;
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
		return array('appkey'=>self::APP_KEY,'appsecret'=>self::APP_SECRET);
	}
}
