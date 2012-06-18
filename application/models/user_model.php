<?php
class User_Model extends mag_db {

	function  __construct(){
		 parent::__construct();
	}

	function regasReader ($username, $passwd){   //注册为读者{{{
		$user_is_exist = $this-> _get_user_by_accountname($username);
		if (!$user_is_exist){
			$account_info = array(
							'account_name' => $username,
							'passwd' => $this->_passwd_encryption($passwd),
							'created_at' => time(),
							);
			$user_id = $this->insert_row(ACCOUNT_TABLE, $account_info);
			$nickname = preg_replace('/@.+/', '', $username);
			$this->insert_row(USER_TABLE, array('user_id' => $user_id, 'nickname' => $nickname));
			$return = array(
							'errcode' => '0',
							'msg' => 'ok',
							'username' => $username,
							'user_type' => '0',     //用户类型:0读者,1作者,2vip作者,3管理员
							);
		}
		else { 
			$return = array(
							'errcode' => '1',
							'msg' => '用户名已存在',
							);
		}
		return $return;
	} //}}}
	
	function login ($username, $passwd, $key){ //{{{
		if ($key == null)
		{
			return array(
					'errcode' => '1',
					'msg' => '缺少key',
					);	
		}
		$user_is_exist = $this->_get_user_by_accountname($username);
		if(!$user_is_exist){
		return array(
					'errcode' => '1',
					'msg' => '用户不存在',
					);	
		}else{
			if ($passwd == $this->_passwd_encryption($user_is_exist['passwd'].$key)){
				$user_info = array_merge($user_is_exist, $this->_get_user_info($user_is_exist['account_id']));
				return $user_info;
			 }
			 else {
				return  array(
					'errcode' => '1',
					'msg' => '用户名密码错误',
					);
			}
		}
	}       //}}}
	
	function _get_user_info ($account_id){	//获得user表里用户的详细信息{{{
		$where = array('user_id' => $account_id);
		$user_info = $this->row(USER_TABLE, $where);	
		return $user_info;
	}	//}}}

	function _get_user_by_accountname($username){       //检测用户名是否存在{{{
		$where = array('account_name' => $username);
		$user_result = $this->row(ACCOUNT_TABLE, $where);
		if ($user_result == array())
			return  false;
		else
			return $user_result;
	}       //}}}

	function _passwd_encryption ($passwd){  //密码加密{{{
		return md5($passwd);
	}       //}}}	

	function get_nickname ($user_id){	//获取用户昵称{{{
		$where = array('user_id' => $user_id);
		$userdata = $this->mag_db->row(USER_TABLE, $where);
		if ($userdata == array()) {
			$return = array(
					'apiver' => $this->apiver,
					'errcode' => '1',	//代表无此用户
					);
		}
		else {
			$return = array(
					'apiver' => $this->apiver,
					'errcode' => '0',
					'nickname' => $userdata['nickname'],
					);
		}
		return $return;
	}	//}}}

	function get_user_info ($user_id) {	//{{{
		$where = array('user_id' => $user_id);
		$user_info = $this->row(USER_TABLE, $where);
		return $user_info;
	}	//}}}

	function set_user_info ($user_id, $user_info){	//{{{
		$where = array('user_id' => $user_id);
		$user_is_exists = $this->get_user_info($user_id);
		$return = array('apiver' => $this->config->item('api_version'));
		if($user_is_exists != array()){
			$data = json_decode($user_info, TRUE);
			$this->update_row(USER_TABLE, $data, $where);
			$return['errcode'] = '0';
			$return['data'] = $this->get_user_info($user_id);
		}
		return $return;
	}	//}}}

	function user_index($user_id){	//{{{
		$data = array(
			'apiver' => $this->apiver,
			'errcode' => '0',
			'data' => array(
				'user_info' => $this->_get_user_info($user_id),
				'user_loved_author' => $this->_get_user_loved_user($user_id),
				'user_mag' => $this->_get_user_mag($user_id),
			),
		);
		return $data;
	}	//}}}
	
	function _get_user_mag($user_id){	//{{{
		$sql = "select * from magazine where user_id='$user_id'";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		return $result;
	}	//}}}

	function _get_user_loved_user($user_id){	//{{{
		$sql = "select * from user_love as L,user as U where L.user_id='$user_id' and L.loved_type='author' and U.user_id=L.loved_id";
		$result = $this->db->query($sql);
		$result = $result->result_array();
		return $result;
	}	//}}}

}
