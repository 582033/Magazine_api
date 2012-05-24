<?php

class Tool extends MY_Controller {


	function tool (){	//{{{
		parent::__construct();
		$this->load->model('tool_model');
	}	//}}}

	function _get_more_non_empty ($more){	//{{{
		$result = array();
		foreach ($more as $data){
			$result[$data] = $this->_get_non_empty($data);
		}
		return $result;
	}	//}}}

	function reg (){	//{{{
		$keys = array('username', 'passwd', 'sessionid');
		$user_data = $this->_get_more_non_empty($keys);
		$user_is_exist = $this-> _check_user_exists($user_data['username']);
		if (!$user_is_exist){
			$user_id = $this->tool_model->insert_row(USER_TABLE, array('user_type' => '0'));
			$user_info = array(
					'user_id' => $user_id,
					'account_type' => 'letou',
					'account_name' => $user_data['username'],
					'account_passwd' => $this->_passwd_encryption($user_data['passwd']),
					);
			$this->tool_model->insert_row(ACCOUNT_TABLE, $user_info);
			$return = array(
					'errcode' => '0',
					'msg' => 'ok',
					'username' => $user_data['username'],
					'account_type' => 'letou',
					'user_type' => '0',	//用户类型:0读者,1作者,2vip作者,3管理员
					);
		}
		else {
			$return = array(
					'errcode' => '0',
					'msg' => '用户名已存在',
					);
		}
		$this->_json_output($return);
	}	//}}}

	function login (){ //{{{
		$keys = array('username', 'passwd', 'sessionid');
		$user_data = $this->_get_more_non_empty($keys);
		$user_is_exist = $this->_check_user_exists($user_data['username']);
		$passwd_is_right = $this->_check_passwd($user_data['username'], $this->_passwd_encryption($user_data['passwd']));
		if ($user_is_exist && $passwd_is_right){
			$user_info = array_merge($user_is_exist, $this->_get_user_info($user_is_exist['user_id']));
			$return = $user_info;
		}
		else {
			$return = array(
					'errcode' => '1',
					'msg' => '用户名密码错误',
					); 
		}
		$this->_json_output($return);
	}	//}}}

	function _check_user_exists ($username){	//{{{
		$where = array('account_name' => $username);
		$user_result = $this->tool_model->row(ACCOUNT_TABLE, $where);
		if ($user_result == array())
			return	false;
		else
			return $user_result;
	}	//}}}

	function _check_passwd ($username, $passwd){	//{{{
		$where = array('account_name' => $username);
		$user_result = $this->tool_model->row(ACCOUNT_TABLE, $where);
		if ($passwd == $user_result['account_passwd'])
			return true;
		else
			return false;
	}	//}}}

	function _get_user_info ($user_id){	//{{{
		$where = array('user_id' => $user_id);
		$user_info = $this->tool_model->row(USER_TABLE, $where);	
		return $user_info;
	}	//}}}
	
	function _passwd_encryption ($passwd){	//{{{
		return $passwd;
	}	//}}}
}
