<?php

class Magazine extends MY_Controller {


	function Magazine (){	//{{{
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->library('session');
		$this->load->model('user_model');
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

		$return = $this->User_Model->regasReader($user_data['username'],$user_data['passwd']);
		
		$return['apiver'] = $this->config->item('api_version');
		$this->_json_output($return);
	}	//}}}

	function login (){ //{{{
		$keys = array('username', 'passwd', 'session_id');
		$user_data = $this->_get_more_non_empty($keys);
		$key = $this->session->userdata('key');		

		$return = $this->User_Model->login($user_data['username'],$user_data['passwd'],$key);

		$return['apiver'] = $this->config->item('api_version');
		$this->_json_output($return);
	}	//}}}
        function getKey (){
		$session_id = $this->session->userdata('session_id');
		$key = $this->_generate_key();
		$this->session->set_userdata('key',$key);
		$return['session_id']=$session_id;
		$return['key']=$key;
		$this->_json_output($return);
	}
	function _generate_key(){
		return 'aaaa';
	}
	function _get_user_info ($user_id){	//获得user表里用户的详细信息{{{
		$where = array('user_id' => $user_id);
		$user_info = $this->mag_db->row(USER_TABLE, $where);	
		return $user_info;
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

	function mag_list(){	//{{{
		$mag_list = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->_get_mag_list(),
				'extra' => $this->_mag_list_extra(),
				);
		$this->_json_output($mag_list);
	}	//}}}

	function _get_mag_list(){	//{{{
		$key = array('type', 'start', 'limit');
		$from_url = $this->_get_more_non_empty($key);
//		$where = $from_url['type'] ? array('mag_category' => $from_url['type']) : array();
		$where = array('mag_category' => $from_url['type']);
		$mag_list = $this->mag_db->rows(MAGAZINE_TABLE, $where);
		return $mag_list;
	}	//}}}
	
	function _mag_list_extra(){
		return "1";
	}
}
