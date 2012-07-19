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
		/* $data = $this->getClient()->show_user_by_id($uid);
		$return = array(
			'nickname' => $data['name'],
			'avatar'=>$data['avatar_large'],
			'ext'=>'jpg'
				); */
		/**临时用**/
		//return;
		$return = array();
		$appinfo = $this->oauth->getAppInfo();
		$myId = array('snsid'=>'sina','uid'=>'2804435152');
		$snsModel = new Sns_Model();
		$result = $snsModel->row('account_bind', $myId);
		if(!$result) return $return;
		$accessToken = json_decode($result['access_auth'],true);
		$accessToken = $accessToken['access_token'];
		$url = "https://api.weibo.com/2/users/show.json?source={$appinfo['appkey']}&access_token={$accessToken}&uid={$uid}";
		$data = file_get_contents($url);
		$data = @json_decode($data,true);
		if(isset($data['error']) && $data['error']) {
			
		}
		else {
			$return = array(
				'nickname'=>$data['name'],
				'avatar'=>$data['avatar_large']?$data['avatar_large']:'',
				'ext'=>'jpg'
					);
		}
		/**end 临时用**/
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
