<?php
/**
 * 第三方操作
 * @author zshen
 *
 */
class Sns extends MY_Controller {

	public function __construct () {
		parent::__construct();
		require_once APPPATH.'libraries/SnsOAuth.php';
		$this->load->model('mag_db');
		$this->load->model('sns_model');
		$this->load->library('session');
	}
	/**
	 * 获取认证url;请求方式GET,参数:平台类型 snsids:是sina/qq（同时申请多个，用逗号隔开）,append:是callback赋加返回值
	 * @return @todo
	 */
	public function oauthzieurl() {
		$snsid = $this->input->get('snsid');
		$cbQuery = $this->input->get('state');
		$return = array();
		$oauth = SnsOAuth::factory($snsid);
		if(null === $oauth) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$return[$snsid] = $oauth->getOAuthorizeURL($cbQuery);
		$this->_json_output($return);
	}
	/**
	 *  接口说明：验证第三方授权有效性
        请求方式：get
        参数说明：
                session_id:可选，如果传入且有值，则认为是绑定操作，不传则为登陆操作
                snsid:平台id{sina/qq}
                query:第三方返回的相关授权信息（code等）
        返回结果：
                如果是绑定操作:绑定成功后返回绑定成功状态
                如果是登陆操作:
                        1).第三方账号已绑定1001账号，登陆成功，返回session_id;
                        2).第三方账号未绑定1001账号，返回{snsid,oauthstring(平台验证返回数据,包括access_token等,www不需要知
道数据结构，只是做临时存储)}
	 *
	 *@return @todo
	 */
	public function callback() {
		$return = array();
		$snsid = $this->input->get('snsid');
		$oauth = SnsOAuth::factory($snsid);
		if(null === $oauth) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$query = $this->input->get('query');
		if(!$query) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		parse_str($query,$params);
		$oauthResult = $oauth->getAccessToken($params,'code');
		if(isset($params['state'])) $oauthResult['state'] = $params['state'];
		if(!$oauthResult) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$sessionid = $this->input->get('session_id');
		if($sessionid) { //绑定操作
			$this->session->initSession();
			$userId = $this->session->userdata('user_id');
			if(!$userId) {
				header('HTTP/1.1 403');
				return $this->_json_output($return);
			}
			$result = $this->sns_model->bind($userId,$snsid,$oauth->getUid(),$oauth->getOAuthToSave(),false);
			return $this->_json_output($result);
		}
		else { //登陆操作
			$snsuid = $oauth->getUid();
			$bind = $this->sns_model->getBindBySns($snsid,$snsuid);
			if($bind) { //已绑定
				if($oauth->isExpired((array)@json_decode($bind['access_auth'],true))) {
					$this->sns_model->bind($bind['account_id'],$bind['snsid'],$snsuid,$oauth->getOAuthToSave(),true);
				}
				$this->session->initSession();
				$this->session->set_userdata('user_id',$bind['account_id']);
				$return['session_id'] = $this->session->get_session_id();
				$return['oauthstring'] = base64_encode(json_encode($oauthResult));
			}
			else {//未绑定
				$return = array(
						'unbind'=>true,
						'snsid'=>$snsid,
						'oauthstring'=>base64_encode(json_encode($oauthResult))
						);
			}
		}
		return $this->_json_output($return);
	}

	/**
	 * 接口说明：取消绑定用户账号
        请求方式：get
        参数说明：
                snsids:平台id{sina/qq}
		session_id:登陆身份标志
        返回结果：
                {true}
	 *@return @todo
	 */
	public function unbind() {
		$return = array();
		$snsid = $this->input->get('snsid');
		$this->session->initSession();
		$userId = $this->session->userdata('user_id');
		if(!$userId || !$snsid) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$result = $this->sns_model->unbind($userId,$snsid);
		if(!$result) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$this->_json_output($result);
	}
	/**
	 * 接口说明：绑定用户账号
        请求方式：get
        参数说明：
                snsid:平台id{sina/qq}
		session_id:登陆身份标志
                authstring:认证消息字符串	
        返回结果：
                {bindresult}
	 *@return @todo
	 */
	public function bind() {
		$return = array();
		$this->session->initSession();
		$userId = $this->session->userdata('user_id');
		if(!$userId) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$snsid = $this->input->get('snsid');
		$authstring = $this->input->get('authstring');
		$oauthResult = @json_decode(base64_decode($authstring),true);
		
		$oauth = SnsOAuth::factory($snsid);
		if(null === $oauth || !$oauthResult) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$oauth->setOAuthResult($oauthResult);
		$result = $this->sns_model->bind($userId,$snsid,$oauth->getUid(),$oauth->getOAuthToSave(),false);
		return $this->_json_output($result);
	}
	/**
	 * 接口说明：获取用户绑定信息
	 请求方式：get
	 参数说明：
	 返回结果：
	 {"account_id":"26","snsid":"sina","uid":"1792549643","updated_at":"1340161889","created_at":"1340161889","expired_in":1340171976}
	 *@return @todo
	 */
	public function bindinfo() {
		$return = array();
		$this->session->initSession();
		$userId = $this->session->userdata('user_id');
		if(!$userId) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$return = $this->sns_model->getBindByUser($userId);
		foreach ($return AS $k=>$v) {
			$temp = json_decode($v['access_auth'],true);
			$auth = SnsOAuth::factory($v['snsid']);
			$auth->setOAuthResult($temp);
			unset($return[$k]['access_auth']);
			$return[$k]['expired_in'] = $auth->getExpiredTime();
		}
		return $this->_json_output($return);
	}
	/**
	 * 分享接口
	 */
	public function share() {
		require_once APPPATH.'libraries/SnsApi.php';
		$return = array();
		$types = array('text','picture','vedio');
		$type = $this->input->get('type');
		$type = $type?$type:'text';
		$url = $this->input->get('url');
		$content = $this->input->get('content');
		if(!in_array($type,$types) || !$content) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$this->session->initSession();
		$userId = $this->session->userdata('user_id');
		if(!$userId) {
			header('HTTP/1.1 403');
			return $this->_json_output($return);
		}
		$result = $this->sns_model->getBindByUser($userId);
		$expired = array();//过期授权平台
		$now = time();
		foreach ($result AS $k=>$v) {
			$temp = json_decode($v['access_auth'],true);
			$auth = SnsOAuth::factory($v['snsid']);
			$auth->setOAuthResult($temp);
			if($now >= $auth->getExpiredTime()) { //已过期
				$expired[] = $v['snsid'];
			}
			else {
				if($v['snsid']!='qq') continue;
				$api = SnsApi::factory($auth);
				switch ($type) {
					case 'text':
						$share = $api->shareText($content);
						break;
					case 'picture':
						$share = $api->sharePicture($content,$url);
						break;
					case 'vedio':
						$share = $api->shareVedio($content,$url);
						break;
				}
				print_r($share);
			}
		}
	}
}

