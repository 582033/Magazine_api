<?php
/**
 * 第三方验证接口
 * @author zshen
 *
 */

abstract class SnsOAuth {
	const CALLBACK = 'http://www.in1001.com/sns/callback';
	
	protected $type = null;
	protected $authResult = array();
	
	protected function __construct($type) {
		$this->type = $type;
	}
	
	static protected $snsTypes = array(
				'sina' => 'Sina',
				'qq' => 'Qq'
			);
	static public function getSnsTypes($snsid = null) {
		if(null === $snsid) {
			return self::$snsTypes;
		}
		else {
			return self::$snsTypes[$snsid];
		}
	}
	public function getSnsId() {
		return $this->type;
	}
	/**
	 * @return SnsOAuth
	 */
	static public function factory($snsType) {
		$snsType = strtolower($snsType);
		if(!isset(self::$snsTypes[$snsType])) return null;
		$class = self::$snsTypes[$snsType].'OAuth';
		require_once 'sns/'.$class.'.php';
		return new $class($snsType);
	}
	/**
	 * 获取认证url
	 */
	abstract public function getOAuthorizeURL($appendUrl);
	/**
	 * 获取Access Token
	 * @param $type
	 * @param $params
	 */
	abstract public function getAccessToken(array &$params,$type);
	/**
	 * 获取平台用户id
	 */
	abstract public function getUid();
	/**
	 * 获取是否过期,比对存储的access_token与当前获取的access_token是否相同
	 */
	abstract public function isExpired(array $originResult);
	/**
	 * 获取需要存储的信息
	 */
	abstract public function getOAuthToSave();
	/**
	 * 获取过期时间
	 */
	abstract public function getExpiredTime($absolute=false);
	/**
	 * 设置认证信息
	 */
	public function setOAuthResult(array $result) {
		$this->authResult = $result;
	}
	abstract public function getAppInfo();
	abstract public function accessToken();
}
