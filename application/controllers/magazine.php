<?php class Magazine extends MY_Controller { 

	var $apiver;
	
	function Magazine (){	//{{{ 
		parent::__construct();
		$this->load->model('mag_db');
		$this->load->model('User_Model');
		$this->load->model('Ads_Model');
		$this->load->model('Mag_Model');
		$this->load->model('Love_Model');
		$this->load->model('mag_element_model');
		$this->load->model('mag_file_model');
		$this->apiver = $this->config->item('api_version');
		$this->_get_session();
	}	//}}}

	function _get_session(){	//{{{
                if(!session_id()) {
                       session_start(); 
                       $sid = session_id();
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
		$userdata['session_id'] = session_id();
		$return = array(
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $userdata,
				);
		
		$this->_json_output($return);
	}	//}}}

	function login (){ //{{{
		$key = $_SESSION['key'];	
		$username = $this->_get_non_empty('username');
		$passwd = md5(md5($this->_get_non_empty('passwd')).$key);
		$return = $this->User_Model->login($username, $passwd, $key);
		
		$return['session_id'] = session_id();
		$this->_json_output($return);
	}	//}}}

	function getKey (){	//{{{
		$key = $this->_generate_key();
		$_SESSION['key'] = $key;
		$return['session_id'] = session_id();
		$return['key'] = $key;
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
				'data' => $this->Mag_Model->_get_category_list(),
				);
		$this->_json_output($category);
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
				'data' => $this->Mag_Model->_get_mag_list($where, $from_url),
				'extra' => $this->Mag_Model->_mag_list_extra($where, $from_url),
				);
		$this->_json_output($mag_list); 
	}	//}}}

	function detail(){	//{{{
		$id = $this->_get_non_empty('id');
		$where = array(MAGAZINE_TABLE.'.magazine_id' => $id);
		$data = $this->Mag_Model->_get_mag_list($where);
		$detail = array(
				'apiver' => $this->apiver,
				'errcode' => '0',
				'data' => $data[0],
				);	
		$this->_json_output($detail);
	}	//}}}

	function download(){	//获取杂志下载地址{{{
		$id = $this->_get_non_empty('id');
		$where = array(MAGAZINE_TABLE.'.magazine_id' => $id);
		$mag = $this->Mag_Model->_get_mag_list($where);
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
	
	function love(){					//喜欢		{{{
		$loved_id = $this->_get_non_empty('loved_id');
		$user_id = $_SESSION['user_id'];
		$loved_type = $this->_get_non_empty('loved_type');
		$result = $this->Love_Model->_loved_check($loved_id, $user_id, $loved_type);
		$data = array('loved_id' => $loved_id, 'user_id' => $user_id, 'loved_type' => $loved_type);
		if($result == 'empty'){
			$this->mag_db->insert_row(USER_LOVE_TABLE, $data);
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
		$user_id = $_SESSION['id'];
		$type = $this->input->get('type');
		$limit = $this->_get_non_empty('limit');
		$start = $this->_get_non_empty('start');
		if (!$type){
				$where_mag = array('user_love.user_id' => $user_id, 'loved_type' => 'magazine');
				$where_author = array('user_love.user_id' => $user_id, 'loved_type' => 'author');
				$where_elem = array('user_love.user_id' => $user_id, 'loved_type' => 'element');
				$result_mag = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAGAZINE_TABLE, 'magazine_id', $where_mag, $limit, $start);
				$result_author = $this->mag_db->loved_rows(USER_LOVE_TABLE, USER_TABLE, 'user_id', $where_author, $limit, $start);
				$result_elem = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAG_ELEMENT_TABLE, 'mag_element_id', $where_elem, $limit, $start);
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
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAG_ELEMENT_TABLE, 'mag_element_id', $where, $limit, $start);
				}else if ($type == 'author'){
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE, USER_TABLE, 'user_id', $where, $limit, $start);
				}else if ($type == 'magazine'){
					$result = $this->mag_db->loved_rows(USER_LOVE_TABLE, MAGAZINE_TABLE, 'magazine_id', $where, $limit, $start);
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
		$user_id = $_SESSION['user_id'];
		$com_data = $this->input->post('data');
//		$com_data = '{"magazine_id":"2","comment":"good!good!good!","user_name":"zhangsan","parents_id":"2"}';
		$data = json_decode($com_data, true);
		$data['send_time'] = $date;
		$data['user_id'] = $user_id;
		$result = $this->mag_db->insert_row(USER_COMMENT_TABLE, $data);
		$item = $this->mag_db->row(USER_COMMENT_TABLE,array('user_comment_id' => $result));
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
						'data' => $item,
						);
		}
		$this->_json_output($return);
	}	//}}}
	
	function get_user_comment(){//{{{         杂志评论取得
		$user_id = $_SESSION['user_id'];
		$magazine_id = $this->input->get('magazine_id');
		$limit = $this->_get_non_empty('limit');
		$start = $this->_get_non_empty('start');
		if ($magazine_id == ''){
			$where = array('user_id' => $user_id);
		}else{
			$where = array('user_id' => $user_id, 'magazine_id' => $magazine_id);
		}
		$result = $this->mag_db->rows(USER_COMMENT_TABLE, $where, $limit, $start);
		if ($result){
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $result,
						);
		}else{
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '1',
						'data' => null,
						'msg' => '操作不成功，请查看参数是否正确',
						);
		}
		$this->_json_output($return);
	}	//}}}
	
	function get_mag_element(){		//{{{		元素取得接口		
		$for = $this->_get_non_empty('for');
		$limit = $this->_get_non_empty('limit');
		$start = $this->_get_non_empty('start');
		$type = $this->input->get('type');
		$result = $this->mag_element_model->_get_mag_element($for, $limit, $start, $type);
		if ($result){
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '0',
						'data' => $result,
						);
		}else{
			$return = array(
						'apiver' => $this->apiver,
						'errcode' => '1',
						'data' => null,
						'msg' => '操作错误,请查看参数是否正确',
						);
		}
		$this->_json_output($return);
	}//}}}
	function uploadfile(){
		
		@$user_id = $_SESSION['user_id'];
		$data = $this->input->post('data');
		$file_data = json_decode($data, true);
		$this->mag_file_model->save_mag_file($file_data,$user_id);

	}
}

