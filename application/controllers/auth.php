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
		$return = $this->User_Model->login($username, $passwd, $key);
		if(isset($return['user_id'])){
			$this->session->initSession();
			$this->session->set_userdata('user_id',$return['user_id']);
		}
		$return['session_id'] = $this->session->get_session_id();
		$this->_json_output($return);
	}	//}}}

	function pwd (){	//登录测试用{{{
		$usr = $this->input->get('u');
		$pwd = $this->input->get('p');
		$getkey = json_decode(file_get_contents($this->config->item('api_hosts')."/v1/auth/getkey"), TRUE);
		$key = $getkey['key'];
		$pwd = md5(md5($pwd).$key);
		$url = $this->config->item('api_hosts')."/v1/auth/signin?username=$usr&passwd=$pwd";
		echo "<a href=$url>$url</a>";
	}	//}}}
}
