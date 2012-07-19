<?php
require_once 'qq/Tencent.php';

class QqApi extends SnsApi {
	
	public function __construct($oauth) {
		parent::__construct($oauth);
		$info = $this->oauth->getAppInfo();
		Tencent_OAuth::init($info['appkey'], $info['appsecret']);
		Tencent::init($info['access_token'],$info['openid'],$info['openkey']);
	}
	public function getUserInfo($uid) {
		$data = Tencent::api('user/info',array('name'=>$uid));
		$return = array();
		if($data['ret'] == 0) {
			$return['nickname'] = $data['data']['nick'];
			$return['avatar'] = $data['data']['head']?$data['data']['head'].'/180':'';
			$return['ext'] = 'jpg';
		}
		return $return;
	}
	
	public function shareText($content,$annotations=null) {
		$params = array('content'=>$content);
		$response = Tencent::api('t/add',$params,'POST');
		if($response['ret'] == 0) {
			return true;
		}
		return false;
	}
	public function sharePicture($content,$url,$annotations=null) {
		$params = array(
				'content'=>$content,
				'pic_url'=>$url
				);
		$response = Tencent::api('t/add_pic_url',$params,'POST');
		if($response['ret'] == 0) {
			return true;
		}
		return false;
	}
	public function shareVedio($content, $url,$annotations=null) {
		
	}
}
