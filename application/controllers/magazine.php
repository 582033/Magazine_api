<?php class Magazine extends MY_Controller { 

	public $apiver = '';
	
	function Magazine (){	//{{{ 
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->model('Ads_Model');
		$this->load->config();
		$this->apiver = $this->config->item('api_version');
		$this->_get_session();
	}	//}}}

	function _get_session(){	//{{{
                if(!session_id()) {
                       session_start(); 
                       $sid=session_id();
                }else{
                        $sid = $this->_get_non_empty('session_id');
                        session_id($sid);
                        session_start(); 
                        if(!session_id()) {session_start();} 
                }
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
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $userdata,
				);
		
		$this->_json_output($return);
	}	//}}}

	function login (){ //{{{
		$keys = array('username', 'passwd');
		$user_data = $this->_get_more_non_empty($keys);
		$key = $_SESSION['key'];		

		$return = $this->User_Model->login($user_data['username'],$user_data['passwd'],$key);

		$return['session_id'] = $this->session->userdata('session_id');
		$this->_json_output($return);
	}	//}}}

	function getKey (){	//{{{
		$key = $this->_generate_key();
		$_SESSION['key'] = $key;
		$return['session_id']=session_id();
		$return['key']=$key;
		$this->_json_output($return);
	}	//}}}

	function _generate_key(){	//{{{
		return random_string('alnum',7);
	}	//}}}
	
	function _passwd_encryption ($passwd){	//密码加密{{{
		return $passwd;
	}	//}}}
	


	function category (){	//{{{
		$category = array(
				'apiver' => $this->apiver,
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
				'apiver' => $this->apiver,
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
				'apiver' => $this->apiver,
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

	function ads(){		//广告接口{{{
		$keys = array('start', 'limit');
		$items = $this->_get_more_non_empty($keys);
		$items['position'] = $this->input->get('position');
		$return = array(
				'apiver' => $this->config->item('api_version'),
				'errcode' => '0',
				'data' => $this->ads_model->_get_ads_links($items),
				);
		$this->_json_output($return);
	}	//}}}
	
	
	
	
	
	/*喜欢接口*/
	function _loved_check($loved_id, $user_id, $loved_type){
		$where = array(
					'loved_id' => $loved_id,
					'user_id' => $user_id,
					'loved_type' => $loved_type,
					);
		$row = $this->mag_db->row(USER_LOVE_TABLE, $where);
		if ($row == array()){
			return 'empty';
		}else{
			return $row;
		}
	}
	
	function love(){
		$loved_id = $this->_get_non_empty('loved_id');
		$user_id = $this->_get_non_empty('user_id');
		$loved_type = $this->_get_non_empty('loved_type');
		$result = $this->_loved_check($loved_id,$user_id,$loved_type);
		$data = array('loved_id' => $loved_id, 'user_id' => $user_id, 'loved_type' => $loved_type);
		if($result == 'empty'){
			$this->mag_db->insert_row(USER_LOVE_TABLE,$data);
			$item = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $data,
						);
		}else{
			$item = array(
						'apiver' => $this->apiver,
						'errcode' => '1',
						'data' => null,
						'msg' => '您已经喜欢过这个元素了',
						);
		}
		$this->_json_output($item);
	}
	
	function get_loved_nums(){
		$user_id = $this->_get_non_empty('user_id');
		$where_mag = array('user_id' => $user_id, 'loved_type' => 'magazine');
		$where_author = array('user_id' => $user_id, 'loved_type' => 'author');
		$where_elem = array('user_id' => $user_id, 'loved_type' => 'element');
		$result_mag = $this->mag_db->total(USER_LOVE_TABLE, $where_mag);			//收藏的杂志
		$result_author = $this->mag_db->total(USER_LOVE_TABLE, $where_author);		//订阅的作者
		$result_elem = $this->mag_db->total(USER_LOVE_TABLE, $where_elem);			//喜欢的元素
		$item = array(
					'apiver' => $this->apiver,
					'data' => array(
								'mag_num' => $result_mag,
								'author_num' => $result_author,
								'elem_num' => $result_elem,
								),
					);
		$this->_json_output($item);
	}
	
	function get_loved_data(){
//		limit start type user_id
		
		$user_id = $this->_get_non_empty('user_id');
		$where_mag = array('user_id' => $user_id, 'loved_type' => 'magazine');
		$where_author = array('user_id' => $user_id, 'loved_type' => 'author');
		$where_elem = array('user_id' => $user_id, 'loved_type' => 'element');
		$result_mag = $this->mag_db->rows(USER_LOVE_TABLE,$where_mag);
		$result_author = $this->mag_db->rows(USER_LOVE_TABLE,$where_author);
		$result_elem = $this->mag_db->rows(USER_LOVE_TABLE,$where_elem);
		$item = array(
					'apiver' => $this->apiver,
					'data' => array(
								'loved_mag' => $result_mag,
								'loved_author' => $result_author,
								'loved_elem' => $result_elem,
								),
					);
		$this->_json_output($item);
	}
}

