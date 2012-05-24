<?php

class Magazine extends MY_Controller {


	function magazine (){	//{{{
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->config();
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
			$user_id = $this->mag_db->insert_row(USER_TABLE, array('user_type' => '0'));
			$user_info = array(
					'user_id' => $user_id,
					'account_type' => 'letou',
					'account_name' => $user_data['username'],
					'account_passwd' => $this->_passwd_encryption($user_data['passwd']),
					);
			$this->mag_db->insert_row(ACCOUNT_TABLE, $user_info);
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
		$return['apiver'] = $this->config->item('api_version');
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
		$return['apiver'] = $this->config->item('api_version');
		$this->_json_output($return);
	}	//}}}

	function _check_user_exists ($username){	//检测用户名是否存在{{{
		$where = array('account_name' => $username);
		$user_result = $this->mag_db->row(ACCOUNT_TABLE, $where);
		if ($user_result == array())
			return	false;
		else
			return $user_result;
	}	//}}}

	function _check_passwd ($username, $passwd){	//检测用户密码是否匹配{{{
		$where = array('account_name' => $username);
		$user_result = $this->mag_db->row(ACCOUNT_TABLE, $where);
		if ($passwd == $user_result['account_passwd'])
			return true;
		else
			return false;
	}	//}}}

	function _get_user_info ($user_id){	//获得user表里用户的详细信息{{{
		$where = array('user_id' => $user_id);
		$user_info = $this->mag_db->row(USER_TABLE, $where);	
		return $user_info;
	}	//}}}
	
	function _passwd_encryption ($passwd){	//密码加密{{{
		return $passwd;
	}	//}}}
	
	
	function config (){ //{{{
		$base_config = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->_api_config(),
				'extra' => $this->_config_extra(),
				);
		$this->_json_output($base_config);
	}	//}}}

	function _config_extra (){	//{{{
		$return = array(
				'api_hosts' => $this->config->item('api_hosts'),
				'file_hosts' => $this->config->item('file_hosts'),
				);	
		return $return;
	}	//}}}

	function _api_config (){ //{{{
		$return = array(
				'visitor' => $this->_api_visitor(),
				);
		return $return;
	} //}}}

	function _api_visitor (){	//{{{
		$return = array(
				'category' => '/magazine/category',
				'mag_list' => '/magazine/mag_list?type=&start=&limit=',
				'download' => '/magazine/download?id=1',
				);

		return $return;
	}	//}}}

	function category (){	//{{{
		$category = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->_get_category_list(),
				);
		$this->_json_output($category);
	}	//}}}

	function _get_category_list (){	//获取杂志分类{{{
		$where = array('dic_name' => 'mag_category');
		$category_list = $this->mag_db->rows(DICTIONARY_TABLE, $where);
		$category_return = array();
		foreach ($category_list as $cat){
			$category_return[] = array(
					'id' => $cat['dic_key'],
					'name' => $cat['dic_value'],
					);
		}
		return $category_return;
	}	//}}}

}
