<?php class Auth extends MY_Controller {

	function __construct () {
		parent::__construct();
		$this->load->model('mag_db');
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
		$key = $this->_generate_key();
		$redis = $this->get_redis();
		$redis->setex('key', $this->config->item('redis_expires'), $key);
		$return['key'] = $key;
		$return['expires_in'] = '600';
		$this->_json_output($return);
	}	//}}}

	function signin (){ //{{{
		$redis = $this->get_redis();
		$key = $redis->get('key');
		$redis->delete('key');
		$username = $this->_get_non_empty('username');
		$passwd = $this->_get_non_empty('passwd');
		$rmsalt = $this->input->get('rmsalt');
		if ($username && ($rmsalt && $rmsalt != '')) {
			$user_info = $this->User_Model->remember_signin($username, $rmsalt);//记住密码时登录
		}
		else {
			$user_info = $this->User_Model->login($username, $passwd, $key);//正常登录
		}
		$this->_json_output($user_info);
	}	//}}}

	function signup () {	//{{{
		$username = $this->input->post('username');
		$passwd = $this->input->post('passwd');
		$nickname = $this->input->post('nickname');
		$nickname = $nickname ? $nickname : null;
		$return = $this->User_Model->regasReader($username, $passwd, $nickname);
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
		echo "<a href=$url>$url</a>";
	}	//}}}
}
