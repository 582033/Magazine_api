<?php

class Magazine extends MY_Controller {


	function Magazine (){	//{{{
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->library('session');
		$this->load->model('User_Model');
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
		$keys = array('username', 'passwd');
		$user_data = $this->_get_more_non_empty($keys);

		$userdata = $this->User_Model->regasReader($user_data['username'],$user_data['passwd']);
		$userdata['session_id'] = $this->session->userdata('session_id');
		$return = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $userdata,
				);
		$this->_json_output($return);
	}	//}}}

	function login (){ //{{{
		$keys = array('username', 'passwd');
		$user_data = $this->_get_more_non_empty($keys);
		$key = $this->session->userdata('key');		

		$return = $this->User_Model->login($user_data['username'],$user_data['passwd'],$key);

		$return['apiver'] = $this->config->item('api_version');
		$return['session_id'] = $this->session->userdata('session_id');
		$this->_json_output($return);
	}	//}}}

	function getKey (){	//{{{
		$session_id = $this->session->userdata('session_id');
		$key = $this->_generate_key();
		$this->session->set_userdata('key',$key);
		$return['session_id']=$session_id;
		$return['key']=$key;
		$this->_json_output($return);
	}	//}}}

	function _generate_key(){	//{{{
		return random_string('alnum',7);
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

	function mag_list(){	//{{{
		$key = array('start', 'limit');
		$from_url = $this->_get_more_non_empty($key);
		$from_url['type'] = $this->input->get('type');
		if ($from_url['type']){
			$where = array('mag_category' => $from_url['type']);
		}
		else {
			$where = array();
			$from_url['type'] = null;
		}
		$mag_list = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->_get_mag_list($where, $from_url),
				'extra' => $this->_mag_list_extra($where, $from_url),
				);
		$this->_json_output($mag_list);
	}	//}}}

	function _get_mag_list($where, $from_url = null){	//获取杂志列表结果{{{
		if ($from_url){
			$mag_list = $this->mag_db->mag_rows(MAGAZINE_TABLE, MAG_FILE_TABLE, $where, $from_url['limit'], $from_url['start']);
		}
		else{
			$mag_list = $this->mag_db->mag_rows(MAGAZINE_TABLE, MAG_FILE_TABLE, $where);
		}
		$mag_list = $this->_get_mag_download($mag_list);
		if (!empty($mag_list['edit_index_img'])){
			$mag_list['edit_index_img'] = explode(',', trim($mag_list['edit_index_img']));
		}
		return $mag_list;
	}	//}}}	

	function _get_mag_download($mag_list){	//拼出杂志下载地址{{{
		foreach($mag_list as &$mag){
			if ($mag['filepath'] && $mag['filename_ftp'])
				$mag['download'] = $this->config->item('file_hosts').$mag['filepath'].$mag['filename_ftp'];
			else
				$mag['download'] = null;
		}	
		return $mag_list;
	}	//}}}

	function _mag_list_extra($where, $from_url){	//杂志列表附加值{{{
		$total = $this->mag_db->total(MAGAZINE_TABLE, $where);
		return array(
				'type' => $from_url['type'],
				'start' => $from_url['start'],
				'limit' => $from_url['limit'],
				'total' => $total,
				);;
	}	//}}}

	function detail(){	//{{{
		$id = $this->_get_non_empty('id');
		$where = array(MAGAZINE_TABLE.'.magazine_id' => $id);
		$detail = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->_get_mag_list($where),
				);	
		$this->_json_output($detail);
	}	//}}}

	function download(){	//获取杂志下载地址{{{
		$id = $this->_get_non_empty('id');
		$where = array(MAGAZINE_TABLE.'.magazine_id' => $id);
		$mag = $this->_get_mag_list($where);
		if ($mag != array())
			$return = $mag[0]['download'];
		else
			$return = null;
		$this->_json_output($return);
	}	//}}}

}

