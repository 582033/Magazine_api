<?php class Auth extends MY_Controller {

	function __construct () {
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('Msg_Model');
		$this->load->model('User_Model');
		$this->load->library('session');
	}

	function _generate_key(){	//{{{
		return random_string('alnum',7);
	}	//}}}

	function get_redis () {	//{{{
		$redis = new Redis();
		$redis->connect($this->config->item('redis_server'));
		return $redis;
	}	//}}}

	function getKey (){	//{{{
		$salt = $this->_generate_key();
		$redis = $this->get_redis();
		$redis->setex("auth:salt:$salt", $this->config->item('salt_expires'), "1");
		$return['key'] = $salt;
		$return['expires_in'] = $this->config->item('salt_expires');
		$this->_json_output($return);
	}	//}}}

	function signin() { //{{{
		$username = $this->_get_non_empty('username');
		$passwd = $this->_get_non_empty('passwd');
		$salt = $this->_get_non_empty('key');
		$rmsalt = $this->input->get('rmsalt');

		$redis = $this->get_redis();
		$r = $redis->get("auth:salt:$salt");
		if (!$r) {
			$result = array('status' => 'INVALID_KEY');
			$this->_json_output($result);
			return;
		}
		if ($username && ($rmsalt && $rmsalt != '')) {
			$user_info = $this->User_Model->remember_signin($username, $rmsalt);//记住密码时登录
		}
		else {
			$user_info = $this->User_Model->login($username, $passwd, $salt);//正常登录
		}
		if ($user_info['status'] == 'OK') {
			$redis->delete("auth:salt:$salt");
		}
		$this->_json_output($user_info);
	} //}}}

	function signup () {	//{{{
		$username = $this->input->post('username');
		$passwd = $this->input->post('passwd');
		$nickname = $this->input->post('nickname');
		$nickname = $nickname ? $nickname : null;
		$this->load->helper('email');
		if (!$username) {
			show_error_text(400, 'Empty username');
		}
		if (!$passwd) {
			show_error_text(400, 'Empty passwd');
		}
		if (!valid_email($username)) {
			show_error_text(400, 'username must be email');
		}
		$return = $this->User_Model->regasReader($username, $passwd, $nickname);
		if ($return['status'] == 'OK') {
			$arr_signok = array(
					//用户id
					'user_id' =>$return['id'],
					//当前时间
					'occur_time' => date("Y-m-d H:i:s"),
					// 发起者
					'actor'  => '0',
					//signup
					'verb'    => 'signup',
					//message
					'msg_content'  =>'恭喜您！已经成功注册1001夜的账号！',
					);
			$json_sign=json_encode($arr_signok);
			$this->Msg_Model->msg_add($json_sign);
		}

		$this->_json_output($return);
	}	//}}}

	function signout () {	//{{{
		$this->session->sess_destroy();
	}	//}}}

	function pwd (){	//登录测试用{{{
		$usr = $this->input->get('u');
		$pwd = $this->input->get('p');
		$getkey = json_decode(file_get_contents($this->config->item('api_host')."/auth/getkey"), TRUE);
		$key = $getkey['key'];
		$pwd = md5(md5($pwd).$key);
		$url = $this->config->item('api_host')."/auth/signin?username=$usr&passwd=$pwd";
		echo "<a href=$url&key=$key>$url&key=$key</a>";
	}	//}}}
}
