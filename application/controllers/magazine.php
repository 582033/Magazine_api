<?php class Magazine extends MY_Controller { 

	var $apiver;
	
	function Magazine (){	//{{{ 
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->model('Ads_Model');
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
		foreach ($mag_list as &$mag){
			if ($mag['edit_index_img']){
				$mag['edit_index_img'] = explode(',', trim($mag['edit_index_img']));
			}
		}
		if ($mag_list == array()) $mag_list = null;
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
				'data' => $this->Ads_Model->_get_ads_links($items),
				);
		$this->_json_output($return);
	}	//}}}
	
	
	
	
	
	function _loved_check($loved_id, $user_id, $loved_type){		//喜欢接口{{{
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
	}		//}}}
	
	function love(){					//喜欢		//{{{
		$loved_id = $this->_get_non_empty('loved_id');
		$user_id = $_SESSION['user_id'];
		$loved_type = $this->_get_non_empty('loved_type');
		$result = $this->_loved_check($loved_id,$user_id,$loved_type);
		$data = array('loved_id' => $loved_id, 'user_id' => $user_id, 'loved_type' => $loved_type);
		if($result == 'empty'){
			$this->mag_db->insert_row(USER_LOVE_TABLE,$data);
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $data,
						);
		}else{
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '1',
						'data' => null,
						'msg' => '已经喜欢过这个元素了',
						);
		}
		$this->_json_output($return);
	}		//}}}
	
	function get_loved_nums(){			//喜欢数量取得	{{{
		$user_id = $_SESSION['user_id'];
		$where_mag = array('user_id' => $user_id, 'loved_type' => 'magazine');
		$where_author = array('user_id' => $user_id, 'loved_type' => 'author');
		$where_elem = array('user_id' => $user_id, 'loved_type' => 'element');
		$result_mag = $this->mag_db->total(USER_LOVE_TABLE, $where_mag);			//收藏的杂志
		$result_author = $this->mag_db->total(USER_LOVE_TABLE, $where_author);		//订阅的作者
		$result_elem = $this->mag_db->total(USER_LOVE_TABLE, $where_elem);			//喜欢的元素
		$return = array(
					'apiver' => $this->apiver,
					'errcode' => '0',
					'data' => array(
								'mag_num' => $result_mag,
								'author_num' => $result_author,
								'elem_num' => $result_elem,
								),
					);
		$this->_json_output($return);
	}	//}}}
	
	function get_loved_data(){				//喜欢数据取得	{{{
		$user_id = $_SESSION['id'] = '1';
		$type = $this->input->get('type');
		$limit = $this->_get_non_empty('limit');
		$start = $this->_get_non_empty('start');
		if (!$type){
				$where_mag = array('user_love.user_id' => $user_id, 'loved_type' => 'magazine');
				$where_author = array('user_love.user_id' => $user_id, 'loved_type' => 'author');
				$where_elem = array('user_love.user_id' => $user_id, 'loved_type' => 'element');
				$result_mag = $this->mag_db->loved_rows(USER_LOVE_TABLE,MAGAZINE_TABLE,'magazine_id',$where_mag,$limit,$start);
				$result_author = $this->mag_db->loved_rows(USER_LOVE_TABLE,USER_TABLE,'user_id',$where_author,$limit,$start);
				$result_elem = $this->mag_db->loved_rows(USER_LOVE_TABLE,MAG_ELEMENT_TABLE,'mag_element_id',$where_elem,$limit,$start);
				$return = array(
							'apiver' => $this->apiver,
							'errcode' => '0',
							'data' => array(
											'loved_mag' => $result_mag,
											'loved_elem' => $result_elem,
											'loved_author' => $result_author,
											),
							);
		}else{
				$where = array('user_love.user_id' => $user_id, 'loved_type' => $type);
				if ($type == 'element'){
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE,MAG_ELEMENT_TABLE,'mag_element_id',$where,$limit,$start);
				}else if ($type == 'author'){
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE,USER_TABLE,'user_id',$where,$limit,$start);
				}else if ($type == 'magazine'){
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE,MAGAZINE_TABLE,'magazine_id',$where,$limit,$start);
				}else{
					$result = NULL;
				}
				$return = array(
							'apiver' => $this->apiver,
							'errcode' => '0',
							'data' => $result,
							);
				if ($return['data'] == 'null'){
					$return['errcode'] = '1';
					$return['data'] = null;
				}
		}
		$this->_json_output($return);
	}	//}}}
	
	function comment(){ //{{{		杂志评论
		$now = new DateTime;
		$date = $now->format("Y-m-d H:i:s");
		$_SESSION['user_id'] = 2;
		$user_id = $_SESSION['user_id'];
		$com_data = $this->input->post('data');
//		$com_data = '{"magazine_id":"1","comment":"good!good!good!","user_name":"zhangsan","parents_id":"2"}';
		$data = json_decode($com_data, true);
		$data['send_time'] = $date;
		$data['user_id'] = $user_id;
		$result = $this->mag_db->insert_row(USER_COMMENT_TABLE, $data);
		if (!$result){
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '1',
						'msg' => '操作不成功',
						'data' => null,
						);
		}else{
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $data,
						);
		}
		$this->_json_output($return);
	}	//}}}
}

