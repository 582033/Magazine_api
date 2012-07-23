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
	public function oauthzieurl() { // {{{
		$snsid = $this->input->get('snsid');
		$cbQuery = $this->input->get('state');
		$return = array();
		$oauth = SnsOAuth::factory($snsid);
		if(null === $oauth) {
			show_error_text(400);
		}
		$return[$snsid] = $oauth->getOAuthorizeURL($cbQuery);
		$this->_json_output($return);
	} //}}}
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
	public function callback() { // {{{
		$return = array();
		$snsid = $this->input->get('snsid');
		$oauth = SnsOAuth::factory($snsid);
		if(null === $oauth) {
			show_error_text(400, 'Bad Request');
		}
		$query = $this->input->get('query');
		if(!$query) {
			show_error_text(400, 'Bad Request');
		}
		parse_str($query,$params);
		$oauthResult = $oauth->getAccessToken($params,'code');
		if(isset($params['state'])) $oauthResult['state'] = $params['state'];
		if(!$oauthResult) {
			show_error_text(403);
		}
		$sessionid = $this->input->get('session_id');
		if($sessionid) { //绑定操作
			$userId = $this->_auth_check();
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
				$this->load->model('user_model');
				$user_short = $this->user_model->get_user_info($bind['account_id'], 'short');
				$return['session_id'] = $this->session->get_session_id();
				$return['oauthstring'] = base64_encode(json_encode($oauthResult));
				$return = array_merge($return, $user_short);
			}
			else {//未绑定
				require_once APPPATH.'libraries/SnsApi.php';
				$api = SnsApi::factory($oauth);
				$info = $api->getUserInfo($snsuid);
				$return = array(
						'unbind'=>true,
						'snsid'=>$snsid,
						'oauthstring'=>base64_encode(json_encode($oauthResult))
						);
				if ($info) {
					$return['nickname'] = $info['nickname'];
					$return['avatar'] = $info['avatar'];
				}
			}
		}
		return $this->_json_output($return);
	} // }}}

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
	public function unbind() { // {{{
		$return = array();
		$snsid = $this->input->get('snsid');
		if (!$snsid) {
			show_error_text(400);
		}
		$userId = $this->_auth_check();
		$result = $this->sns_model->unbind($userId,$snsid);
		if(!$result) {
			show_error_text(403);
		}
		$this->_json_output($result);
	} // }}}
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
	public function bind() { // {{{
		$userId = $this->_auth_check();
		$return = array();
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
		
		if ($this->input->get('do') === 'FETCH') {
			require_once APPPATH.'libraries/SnsApi.php';
			$api = SnsApi::factory($oauth);
			if ($info = $api->getUserInfo($oauth->getUid())) {
				$nickname = $info['nickname'];
				$avatar180 = $info['avatar'];
				if ($avatar180) {
					$avatarId = $this->__saveAvatar($avatar180, $userId,$info['ext']);
				}
				else {
					$avatarId=null;
				}
				if($avatarId) {
					$this->mag_db->update_row('user',array('nickname'=>$nickname,'avatar'=>$avatarId),array('user_id'=>$userId));
				}
			}
		}
		return $this->_json_output($result);
	} //}}}
	private function __saveAvatar($httpImg180,$userId,$ext='jpg') { // {{{
		$dir = '/mnt/mag/img/avatar/'.$userId;
		if(!is_dir($dir)) {
			mkdir($dir,0777);
		}
		$avatarId = uniqid().mt_rand(1000000, 9999999);
		$img180 = $dir.'/'.$avatarId.'_180.'.$ext;
		set_time_limit(100);
		try {
			file_put_contents($img180, file_get_contents($httpImg180));
			$this->load->helper('thumb');
			$img80 = $dir.'/'.$avatarId.'_80.'.$ext;
			$img50 = $dir.'/'.$avatarId.'_50.'.$ext;
			image_thumb($img180, $img80, 80, 80, false);//生成80x80
			image_thumb($img180, $img50, 50, 50, false);//生成50x50
			@chmod($img180,0777);
			@chmod($img80,0777);
			@chmod($img50,0777);
		}
		catch (Exception $e) {
			return false;
		}
		return $avatarId;
	} // }}}
	/**
	 * 接口说明：获取用户绑定信息
	 请求方式：get
	 参数说明：
	 返回结果：
	 {"account_id":"26","snsid":"sina","uid":"1792549643","updated_at":"1340161889","created_at":"1340161889","expired_in":1340171976}
	 *@return @todo
	 */
	public function bindinfo() { // {{{
		$return = array();
		$userId = $this->_auth_check();
		$return = $this->sns_model->getBindByUser($userId);
		foreach ($return AS $k=>$v) {
			$temp = json_decode($v['access_auth'],true);
			$auth = SnsOAuth::factory($v['snsid']);
			$auth->setOAuthResult($temp);
			unset($return[$k]['access_auth']);
			$return[$k]['expired_in'] = $auth->getExpiredTime();
		}
		return $this->_json_output($return);
	} // }}}
	/**
	 * 分享接口
	 */
	public function share() { // {{{
		require_once APPPATH.'libraries/SnsApi.php';
		$return = array();
		$types = array('text','picture','vedio');
		$type = $this->input->get('type');
		$type = $type?$type:'text';
		$url = $this->input->get('url');
		$content = $this->input->get('content');
		if(!in_array($type,$types) || !$content) {
			show_error_text(400, 'Bad Request');
		}
		$userId = $this->_auth_check();
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
				$return = $share;
			}
		}
		$this->_json_output($return);
	} //}}}

	function _auth_check() { // {{{
		if (!$this->session->checkAndRead()) {
			show_error_text(401, 'Unauthorized');
		}
		$userId = $this->session->userdata('user_id');
		if (!$userId) {
			show_error_text(401, 'Unauthorized');
		}
		return $userId;
	} //}}}
}

