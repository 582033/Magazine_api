<?php
/**
 * 第三方openapi
 * @author zshen
 *
 */

require_once 'SnsOAuth.php';

abstract class SnsApi {
	/**
	 * 
	 * @var SnsOAuth
	 */
	protected $oauth = null;
	
	protected function __construct(SnsOAuth $oauth) {
		$this->oauth = $oauth;
	}
	
	/**
	 * @return SnsApi
	 */
	static public function factory(SnsOAuth $oauth) {
		$snsType = $oauth->getSnsId();
		$class = SnsOAuth::getSnsTypes($snsType).'Api';
		require_once 'sns/'.$class.'.php';
		return new $class($oauth);
	}
	abstract public function getUserInfo($uid);
	/**
	 * 发送文字微博
	 * @param $content
	 */
	abstract public function shareText($content,$annotations=null);
	abstract public function sharePicture($content,$url,$annotations=null);
	abstract public function shareVedio($content,$url,$annotations=null);
}
