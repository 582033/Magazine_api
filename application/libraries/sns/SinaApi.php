<?php
require_once 'sina/saetv2.ex.class.php';

class SinaApi extends SnsApi {
	
	protected $client = null;
	/**
	 * 
	 * @return SaeTClientV2
	 */
	protected function getClient() {
		if(null===$this->client) {
			$appinfo = $this->oauth->getAppInfo();
			$this->client = new SaeTClientV2($appinfo['appkey'],$appinfo['appsecret'],$this->oauth->accessToken());
		}
		return $this->client;
	}
	public function getUserInfo($uid) {
		$data = $this->getClient()->show_user_by_id($uid);
		$return = array(
			'nickname' => $data['name'],
			'avatar'=>$data['avatar_large'],
			'ext'=>'jpg'
				);
		return $return;
	}
	public function shareText($content,$annotations=null) {
		$response = $this->getClient()->update($content,$annotations);
		if(isset($response['error_code'])) {
			return false;
		}
		return true;
	}
	public function sharePicture($content,$url,$annotations=null) {
		$response = $this->getClient()->upload($content,$url,$annotations);
		if(isset($response['error_code'])) {
			return false;
		}
		return true;
	}
	public function shareVedio($content, $url,$annotations=null) {
		
	}
}
