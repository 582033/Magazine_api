<?php
class User_Model extends mag_db {

	function  __construct(){
		 parent::__construct();
	}

	function regasReader ($username,$passwd){   //注册为读者{{{
		$user_is_exist = $this-> _get_user_by_accountname($username);
		if (!$user_is_exist){
			$user_id = $this->insert_row(USER_TABLE, array('user_type' => '0'));
			$account_info = array(
							'user_id' => $user_id,
							'account_type' => 'letou',
							'account_name' => $username,
							'passwd' => $this->_passwd_encryption($passwd)
							);
			$this->insert_row(ACCOUNT_TABLE, $account_info);
			$return = array(
							'errcode' => '0',
							'msg' => 'ok',
							'username' => $username,
							'account_type' => 'letou',
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
		$user_is_exist = $this->_get_user_by_accountname($username);
		if(!$user_is_exist){
		return array(
					'errcode' => '1',
					'msg' => '用户名密码错误',
					);	
		}else{
			if ($passwd == $this->_passwd_encryption($user_is_exist['passwd'].$key)){
				$user_info = array_merge($user_is_exist, $this->_get_user_info($user_is_exist['user_id']));
				$_SESSION['user_id'] = $user_is_exist['user_id'];
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
	
	function _get_user_info ($user_id){	//获得user表里用户的详细信息{{{
		$where = array('user_id' => $user_id);
		$user_info = $this->row(USER_TABLE, $where);	
		return $user_info;
	}	//}}}

	function _get_user_by_accountname($username){       //检测用户名是否存在{{{
		$where = array('account_name' => $username,'account_type' => 'letou');
		$user_result = $this->row(ACCOUNT_TABLE, $where);
		if ($user_result == array())
			return  false;
		else
			return $user_result;
	}       //}}}

	function _passwd_encryption ($passwd){  //密码加密{{{
		return $passwd;
	}       //}}}	

}
